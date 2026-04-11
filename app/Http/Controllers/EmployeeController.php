<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(15)->withQueryString();
        
        // Ensure skills are decoded (Laravel pagination manipulates underlying collection through mapping)
        $employees->getCollection()->transform(function ($emp) {
            $emp->skills = json_decode($emp->skills, true) ?? [];
            return $emp;
        });

        return Inertia::render('Employees', [
            'employees' => $employees,
            'filters' => $request->only('search'),
            'departments' => \App\Models\Department::all(),
            'positions' => \App\Models\Position::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'role' => 'required|string',
            'department' => 'required|string',
        ]);
        
        $validated['status'] = 'active';

        Employee::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'role' => 'required|string',
            'department' => 'required|string',
        ]);
        
        $employee->update($validated);

        return redirect()->back();
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->back();
    }

    public function exportCsv()
    {
        $employees = Employee::all();

        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Export Employees',
            'entity_type' => 'Employee',
            'description' => 'Exported employees data to CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['№', 'Ном', 'Насаб', 'Почтаи электронӣ', 'Мансаб', 'Шӯъба', 'Ҳолат'], ';');
            foreach ($employees as $index => $emp) {
                fputcsv($file, [
                    $index + 1,
                    $emp->name,
                    $emp->last_name,
                    $emp->email,
                    $emp->role,
                    $emp->department,
                    $emp->status
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="employees_export.csv"',
        ]);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $csvData = file_get_contents($file);
        $rows = array_map("str_getcsv", explode("\n", $csvData));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            if (count($row) >= 6) {
                Employee::updateOrCreate(
                    ['email' => $row[3]], // assuming email is unique and at index 3
                    [
                        'name' => $row[1],
                        'last_name' => $row[2],
                        'role' => $row[4],
                        'department' => $row[5],
                        'status' => $row[6] ?? 'active'
                    ]
                );
            }
        }

        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Import Employees',
            'entity_type' => 'Employee',
            'description' => 'Imported employees from CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
