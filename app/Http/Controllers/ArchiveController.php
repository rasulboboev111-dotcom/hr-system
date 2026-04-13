<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use Inertia\Inertia;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasPermission('view_archive') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи дидани архивро надоред.');
        }

        $query = Employee::onlyTrashed();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('department', 'ilike', "%{$search}%")
                  ->orWhere('role', 'ilike', "%{$search}%");
            });
        }
        
        return Inertia::render('Archive', [
            'employees' => $query->get(),
            'filters' => $request->only('search'),
            'departments' => Department::all(),
            'positions' => Position::all()
        ]); 
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('add_archive') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи илова ба архивро надоред.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email' . (Employee::where('email', $request->email)->exists() ? '' : '|unique:employees,email'),
            'role' => 'required|string',
            'department' => 'required|string',
        ]);
        
        $validated['status'] = 'Retired';
        
        $employee = Employee::create($validated);
        $employee->delete();
        
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        if (!$request->user()->hasPermission('edit_archive') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи таҳрири архивро надоред.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email' . (Employee::withTrashed()->where('email', $request->email)->where('id', '!=', $id)->exists() ? '|unique:employees,email' : ''),
            'role' => 'required|string',
            'department' => 'required|string',
        ]);
        
        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->update($validated);
        
        return redirect()->back();
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->hasPermission('delete_archive') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи тозакунии архивро надоред.');
        }

        $employee = Employee::withTrashed()->findOrFail($id);
        $employee->forceDelete();
        return redirect()->back();
    }
}
