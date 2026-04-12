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
            'file' => 'required|file|mimes:csv,txt|max:4096'
        ]);

        $path = $request->file('file')->getRealPath();
        $content = file_get_contents($path);

        // Detect and convert encoding from Windows-1251 to UTF-8 if needed
        if (!mb_check_encoding($content, 'UTF-8')) {
            $converted = @mb_convert_encoding($content, 'UTF-8', 'Windows-1251');
            if (mb_check_encoding($converted, 'UTF-8')) {
                $content = $converted;
                \Log::info("Employee CSV encoding converted from Windows-1251 to UTF-8");
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

        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            // Clean weird characters and trim with security sanitization
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
