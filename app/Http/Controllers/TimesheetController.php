<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;

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
            'file' => 'required|file'
        ]);

        $path = $request->file('file')->getRealPath();
        \Log::info("CSV Import started reading from: " . $path);
        
        $file = fopen($path, 'r');
        if (!$file) {
            return redirect()->back()->withErrors(['file' => 'Failed to open file.']);
        }
        
        // Detect delimiter and handle BOM
        $firstLine = fgets($file);
        if ($firstLine === false) {
            fclose($file);
            return redirect()->back(); // empty file
        }
        
        // Strip BOM
        $bom = pack('H*', 'EFBBBF');
        $firstLine = preg_replace("/^$bom/", '', $firstLine);
        
        $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
        rewind($file);
        
        // Skip header
        fgetcsv($file, 0, $delimiter);
        
        $importedCount = 0;
        
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            // Clean and trim
            $cleanRow = array_map(function($val) {
                if ($val === null) return '';
                // Clean weird characters and normalize spaces
                $val = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $val);
                return trim(preg_replace('/\s+/', ' ', $val));
            }, $row);

            if (empty($cleanRow[0]) || count($cleanRow) < 2) continue;

            $nameStr = $cleanRow[0];
            $nameParts = explode(' ', $nameStr, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $employee = Employee::firstOrCreate(
                ['name' => $firstName, 'last_name' => $lastName],
                ['status' => 'Active']
            );

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
