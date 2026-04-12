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
            abort(403, 'Шумо ҳуқуқи дидани шӯъбаҳоро надоред.');
        }

        $query = Department::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'ilike', "%{$search}%");
        }

        $departments = $query->get()->map(function($dept) {
            $employeesCount = Employee::where('department', $dept->name)->count();
            $vacanciesCount = Position::where('department', $dept->name)->where('status', 'vacant')->count();
            $filledCount = Position::where('department', $dept->name)->where('status', 'filled')->count();
            
            $totalPositions = $vacanciesCount + $filledCount;
            $fillRate = $totalPositions > 0 ? round(($filledCount / $totalPositions) * 100) : ($employeesCount > 0 ? 100 : 0);
            
            // Calculate a mock budget based on employees and vacancies average
            $budget = Employee::where('department', $dept->name)->sum('salary') + Position::where('department', $dept->name)->sum('salary');

            return [
                'id' => $dept->id,
                'name' => $dept->name,
                'employees' => $employeesCount,
                'vacancies' => $vacanciesCount,
                'fillRate' => $fillRate,
                'budget' => number_format($budget) . ' TJ'
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
            abort(403, 'Шумо ҳуқуқи илова кардани шӯъбаҳоро надоред.');
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
            abort(403, 'Шумо ҳуқуқи таҳрир кардани шӯъбаҳоро надоред.');
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
            abort(403, 'Шумо ҳуқуқи нест кардани шӯъбаҳоро надоред.');
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
