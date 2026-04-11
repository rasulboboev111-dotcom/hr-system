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
        return Inertia::render('Timesheet', [
            'employees' => Employee::all(),
            'attendances' => Attendance::all(),
            'departments' => \App\Models\Department::all()
        ]);
    }

    public function store(Request $request)
    {
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
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Update Timesheet',
            'entity_type' => 'Timesheet',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv()
    {
        $employees = Employee::all();
        $attendances = Attendance::all();

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
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
            fwrite($file, "\xEF\xBB\xBF");

            $header = ['Корманд'];
            for ($d = 1; $d <= 31; $d++) { $header[] = $d; }
            $header[] = 'Соатҳои умумӣ';
            fputcsv($file, $header, ';');

            foreach ($employees->filter(fn($e) => $e->status !== 'Retired') as $emp) {
                $row = [($emp->name) . ' ' . ($emp->last_name ?? '')];
                $presentDays = 0;
                for ($d = 1; $d <= 31; $d++) {
                    $status = $attMap[$emp->id][$d] ?? '';
                    $row[] = $status;
                    if ($status === 'P') $presentDays++;
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
        $request->validate([
            'file' => 'required|file'
        ]);

        $path = $request->file('file')->getRealPath();
        \Log::info("CSV Import started reading from: " . $path);
        
        $lines = file($path);
        if ($lines === false) {
            return redirect()->back()->withErrors(['file' => 'Failed to read file.']);
        }
        
        $data = array_map(function($v){return str_getcsv($v, ';');}, $lines);
        
        if (count($data) > 0) {
            // Remove BOM if present on first item
            $data[0][0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $data[0][0] ?? '');
            array_shift($data); // Remove header
        }

        foreach ($data as $row) {
            if (count($row) < 32 || empty($row[0])) continue;
            
            $nameStr = trim($row[0]);
            $nameParts = explode(' ', $nameStr, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';

            $employee = Employee::firstOrCreate(
                ['name' => $firstName, 'last_name' => $lastName],
                ['status' => 'Active']
            );

            for ($d = 1; $d <= 31; $d++) {
                $status = trim($row[$d] ?? '');
                if (in_array($status, ['P', 'L', 'A'])) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $employee->id, 'date_key' => (string)$d],
                        ['status' => $status]
                    );
                }
            }
        }

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Import Timesheet',
            'entity_type' => 'Timesheet',
            'description' => 'Imported timesheet data from CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
