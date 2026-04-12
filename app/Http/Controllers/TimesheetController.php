<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class TimesheetController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('view_timesheet')) {
            abort(403, 'Шумо ҳуқуқи дидани ҷадвали ҳузурро надоред.');
        }
        return Inertia::render('Timesheet', [
            'employees' => Employee::all(),
            'attendances' => Attendance::all(),
            'departments' => \App\Models\Department::all()
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('edit_timesheet')) {
            abort(403, 'Шумо ҳуқуқи таҳрири ҷадвали ҳузурро надоред.');
        }

        $data = $request->validate([
            'employee_name' => 'required|string',
            'department' => 'nullable|string',
            'date_key' => 'required|string',
            'status' => 'required|string'
        ]);

        $nameParts = explode(' ', $data['employee_name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $employee = Employee::firstOrCreate(
            ['name' => $firstName, 'last_name' => $lastName],
            ['status' => 'Active', 'department' => $data['department'] ?? null]
        );

        Attendance::updateOrCreate(
            ['employee_id' => $employee->id, 'date_key' => $data['date_key']],
            ['status' => $data['status']]
        );

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Update Timesheet',
            'entity_type' => 'Timesheet',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv(Request $request)
    {
        if (!$request->user()->hasPermission('export_timesheet')) {
            abort(403, 'Шумо ҳуқуқи экспорти ҷадвали ҳузурро надоред.');
        }

        $employees = Employee::all();
        $attendances = Attendance::all();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Export Timesheet',
            'entity_type' => 'Timesheet',
            'description' => 'Exported timesheet data to CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        $attMap = [];
        foreach ($attendances as $att) {
            $attMap[$att->employee_id][$att->date_key] = $att->status;
        }

        $callback = function() use ($employees, $attMap) {
            $file = fopen('php://output', 'w');
            
            // Prepend BOM to the first header element to avoid column shift
            $header = ["\xEF\xBB\xBF" . 'Корманд'];
            for ($d = 1; $d <= 31; $d++) { $header[] = (string)$d; }
            $header[] = 'Соатҳои умумӣ';
            fputcsv($file, $header, ';');

            foreach ($employees as $emp) {
                // Skip retired employees (case-insensitive check with trim)
                if (trim(strtolower($emp->status ?? '')) === 'retired') continue;

                $row = [($emp->name) . ' ' . ($emp->last_name ?? '')];
                $presentDays = 0;
                for ($d = 1; $d <= 31; $d++) {
                    $status = $attMap[$emp->id][$d] ?? '';
                    $row[] = $status;
                    if (strtoupper($status) === 'P') $presentDays++;
                }
                $row[] = $presentDays * 8;
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="timesheet_tjk_' . date('Y_m') . '.csv"',
        ]);
    }

    public function importCsv(Request $request)
    {
        if (!$request->user()->hasPermission('import_timesheet')) {
            abort(403, 'Шумо ҳуқуқи импорти маълумотро надоред.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:4096'
        ]);

        $path = $request->file('file')->getRealPath();
        $content = file_get_contents($path);

        // Detect and convert encoding from Windows-1251 to UTF-8 if needed
        if (!mb_check_encoding($content, 'UTF-8')) {
            $converted = @mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
            if (mb_check_encoding($converted, 'UTF-8')) {
                $content = $converted;
                \Log::info("CSV encoding converted from Windows-1251 to UTF-8");
            }
        }

        // Remove UTF-8 BOM if it exists
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Create a temporary stream for fgetcsv
        $file = fopen('php://temp', 'r+');
        fwrite($file, $content);
        rewind($file);

        if (!$file) {
            return redirect()->back()->withErrors(['file' => 'Failed to process file.']);
        }

        // Detect delimiter from the first line
        $firstLine = fgets($file);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($file);

        // Skip header
        fgetcsv($file, 0, $delimiter);

        $importedCount = 0;
        
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            // Clean and trim with security sanitization
            $cleanRow = array_map(function($val) {
                if ($val === null) return '';
                
                // 1. Strip HTML tags to prevent XSS
                $val = strip_tags($val);
                
                // 2. Clean weird characters and normalize spaces
                $val = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $val);
                $val = trim(preg_replace('/\s+/', ' ', $val));
                
                // 3. Prevent Formula Injection (prepend ' if starts with = + - @)
                if ($val !== '' && in_array($val[0], ['=', '+', '-', '@'])) {
                    $val = "'" . $val;
                }
                
                return $val;
            }, $row);

            if (empty($cleanRow[0]) || count($cleanRow) < 2) continue;

            $nameStr = $cleanRow[0];
            
            // Flexible employee matching (including archived ones)
            // Try matching by the full string (name + last_name) first
            $employee = Employee::withTrashed()
                ->where(DB::raw("TRIM(CONCAT(name, ' ', COALESCE(last_name, '')))"), 'ILIKE', $nameStr)
                ->first();

            // Fallback: try split names if direct match fails
            if (!$employee) {
                $nameParts = explode(' ', $nameStr, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? '';
                
                $employee = Employee::withTrashed()
                    ->where('name', 'ILIKE', $firstName)
                    ->where('last_name', 'ILIKE', $lastName)
                    ->first();
            }

            // If still not found, only then create a new one to avoid chaos
            if (!$employee) {
                $nameParts = explode(' ', $nameStr, 2);
                $employee = Employee::create([
                    'name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'status' => 'active'
                ]);
            }

            // Import days 1 to 31 if they exist in the row
            for ($d = 1; $d <= 31; $d++) {
                if (!isset($cleanRow[$d])) break;
                
                $status = strtoupper($cleanRow[$d]);
                if ($status !== '') {
                    Attendance::updateOrCreate(
                        ['employee_id' => $employee->id, 'date_key' => (string)$d],
                        ['status' => $status]
                    );
                    $importedCount++;
                }
            }
        }
        fclose($file);
        
        \Log::info("CSV Import complete. Total cells imported: " . $importedCount);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Import Timesheet',
            'entity_type' => 'Timesheet',
            'description' => 'Imported timesheet data from CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
