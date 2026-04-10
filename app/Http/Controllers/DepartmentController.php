<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all()->map(function($dept) {
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
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $dept = Department::create($validated);

        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Create Department',
            'entity_type' => 'Department',
            'description' => json_encode(['name' => $dept->name]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $department->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Update Department',
            'entity_type' => 'Department',
            'description' => json_encode(['id' => $department->id, 'name' => $department->name]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(Department $department)
    {
        $id = $department->id;
        $name = $department->name;
        $department->delete();

        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Delete Department',
            'entity_type' => 'Department',
            'description' => json_encode(['id' => $id, 'name' => $name]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
