<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view_employees')) {
            abort(403, 'Шумо ҳуқуқи дидани кормандонро надоред.');
        }
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
        if (!$request->user()->hasPermission('add_employees')) {
            abort(403, 'Шумо ҳуқуқи илова кардани кормандонро надоред.');
        }

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
        if (!$request->user()->hasPermission('edit_employees')) {
            abort(403, 'Шумо ҳуқуқи таҳрир кардани кормандонро надоред.');
        }

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

    public function destroy(Request $request, Employee $employee)
    {
        if (!$request->user()->hasPermission('delete_employees')) {
            abort(403, 'Шумо ҳуқуқи нест кардани кормандонро надоред.');
        }

        $employee->delete();
        return redirect()->back();
    }

    public function exportCsv(Request $request)
    {
        if (!$request->user()->hasPermission('export_employees')) {
            abort(403, 'Шумо ҳуқуқи экспорти маълумотро надоред.');
        }

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
        if (!$request->user()->hasPermission('import_employees')) {
            abort(403, 'Шумо ҳуқуқи импорти маълумотро надоред.');
        }

        $request->validate([
            'file' => 'required|file'
        ]);

        $path = $request->file('file')->getRealPath();
        $file = fopen($path, 'r');
        if (!$file) return redirect()->back()->withErrors(['file' => 'Failed to open file.']);

        // Detect delimiter and handle BOM
        $firstLine = fgets($file);
        if ($firstLine === false) {
            fclose($file);
            return redirect()->back();
        }

        // Strip BOM
        $bom = pack('H*', 'EFBBBF');
        $firstLine = preg_replace("/^$bom/", '', $firstLine);
        
        $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
        rewind($file);
        
        // Skip header
        fgetcsv($file, 0, $delimiter);

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            // Clean weird characters and trim
            $cleanRow = array_map(function($val) {
                if ($val === null) return '';
                $val = preg_replace('/[\x00-\x1F\x7F\xA0]/u', ' ', $val);
                return trim(preg_replace('/\s+/', ' ', $val));
            }, $row);

            if (count($cleanRow) >= 6 && !empty($cleanRow[3])) { // row[3] is email
                $emp = Employee::withTrashed()->where('email', $cleanRow[3])->first();
                if ($emp) {
                    $emp->restore();
                    $emp->update([
                        'name' => $cleanRow[1],
                        'last_name' => $cleanRow[2],
                        'role' => $cleanRow[4],
                        'department' => $cleanRow[5],
                        'status' => $cleanRow[6] ?? 'active'
                    ]);
                } else {
                    Employee::create([
                        'email' => $cleanRow[3],
                        'name' => $cleanRow[1],
                        'last_name' => $cleanRow[2],
                        'role' => $cleanRow[4],
                        'department' => $cleanRow[5],
                        'status' => $cleanRow[6] ?? 'active'
                    ]);
                }
            }
        }
        fclose($file);

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
