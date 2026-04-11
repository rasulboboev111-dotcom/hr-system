<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Employee;
use Inertia\Inertia;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::all()->map(function($pos) {
            $decoded = json_decode($pos->required_skills, true);
            if (is_array($decoded)) {
                $pos->skills = implode(', ', $decoded);
            } else {
                $pos->skills = $pos->required_skills ?? '';
            }
            return $pos;
        });

        $stats = [
            'total' => Position::count(),
            'vacant' => Position::where('status', 'vacant')->count(),
            'filled' => Position::where('status', 'filled')->count(),
            'on_hold' => Position::where('status', 'on_hold')->count(),
        ];

        return Inertia::render('Positions', [
            'positions' => $positions,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'status' => 'required|string',
            'salary' => 'nullable|numeric',
            'skills' => 'nullable|string'
        ]);
        
        $skillsStr = $validated['skills'] ?? '';
        $skillsArray = array_values(array_filter(array_map('trim', explode(',', $skillsStr))));
        $validated['required_skills'] = json_encode($skillsArray);
        unset($validated['skills']);

        $pos = Position::create($validated);
        
        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Create Position',
            'entity_type' => 'Position',
            'description' => json_encode(['title' => $pos->title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'status' => 'required|string',
            'salary' => 'nullable|numeric',
            'skills' => 'nullable|string'
        ]);
        
        $skillsStr = $validated['skills'] ?? '';
        $skillsArray = array_values(array_filter(array_map('trim', explode(',', $skillsStr))));
        $validated['required_skills'] = json_encode($skillsArray);
        unset($validated['skills']);
        
        $position->update($validated);

        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Update Position',
            'entity_type' => 'Position',
            'description' => json_encode(['id' => $position->id, 'title' => $position->title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(Position $position)
    {
        $id = $position->id;
        $title = $position->title;
        $position->delete();
        
        \App\Models\AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Delete Position',
            'entity_type' => 'Position',
            'description' => json_encode(['id' => $id, 'title' => $title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
