<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_departments')) {
            abort(403, __('auth.access_denied'));
        }

        $query = Department::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'ilike', "%{$search}%");
        }

        $depts = $query->get();
        $deptNames = $depts->pluck('name')->toArray();
        
        $employeeCounts = Employee::whereIn('department', $deptNames)
            ->selectRaw('department, count(*) as count, sum(CAST(COALESCE(salary, 0) AS NUMERIC)) as total_salary')
            ->groupBy('department')
            ->get()
            ->keyBy('department');
            
        $positions = Position::whereIn('department', $deptNames)->get();

        $departments = $depts->map(function($dept) use ($employeeCounts, $positions) {
            $eData = $employeeCounts->get($dept->name);
            $employeesCount = $eData ? current((array)$eData)->count ?? $eData['count'] ?? 0 : 0;
            // Since we use Eloquent, $eData is an object
            if (is_object($eData)) {
                $employeesCount = $eData->count;
                $eSalary = $eData->total_salary;
            } else {
                $employeesCount = 0;
                $eSalary = 0;
            }

            $deptPositions = $positions->where('department', $dept->name);
            $vacanciesCount = $deptPositions->where('status', 'vacant')->count();
            $filledCount = $deptPositions->where('status', 'filled')->count();
            $pSalary = $deptPositions->sum('salary');
            
            $totalPositions = $vacanciesCount + $filledCount;
            $fillRate = $totalPositions > 0 ? round(($filledCount / $totalPositions) * 100) : ($employeesCount > 0 ? 100 : 0);
            
            $budget = $eSalary + $pSalary;

            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'employees' => $employeesCount,
                'vacancies' => $vacanciesCount,
                'fillRate' => $fillRate,
                'budget' => number_format((float)$budget) . ' ' . __('common.currency')
            ];
        });

        return Inertia::render('Departments', [
            'departments' => $departments,
            'filters' => $request->only('search'),
            'stats' => [
                'totalDepts' => Department::count(),
                'totalEmployees' => Employee::count(),
                'totalVacancies' => Position::where('status', 'vacant')->count(),
                'avgFillRate' => $departments->count() > 0
                    ? round($departments->avg('fillRate'))
                    : 0
            ]
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('add_departments')) {
            abort(403, __('auth.access_denied'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $dept = Department::create($validated);

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Create Department',
            'entity_type' => 'Department',
            'description' => json_encode(['name' => $dept->name]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Department $department)
    {
        if (!$request->user()->hasPermission('edit_departments')) {
            abort(403, __('auth.access_denied'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $department->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Update Department',
            'entity_type' => 'Department',
            'description' => json_encode(['id' => $department->id, 'name' => $department->name]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, Department $department)
    {
        if (!$request->user()->hasPermission('delete_departments')) {
            abort(403, __('auth.access_denied'));
        }

        $id = $department->id;
        $name = $department->name;
        $department->delete();

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Delete Department',
            'entity_type' => 'Department',
            'description' => json_encode(['id' => $id, 'name' => $name]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
