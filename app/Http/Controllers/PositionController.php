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
        if (!auth()->user()->hasPermission('view_positions')) {
            abort(403, 'Шумо ҳуқуқи дидани мансабҳоро надоред.');
        }
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
            'stats' => $stats,
            'departments' => \App\Models\Department::all()
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('add_positions')) {
            abort(403, 'Шумо ҳуқуқи илова кардани мансабҳоро надоред.');
        }

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
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Create Position',
            'entity_type' => 'Position',
            'description' => json_encode(['title' => $pos->title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Position $position)
    {
        if (!$request->user()->hasPermission('edit_positions')) {
            abort(403, 'Шумо ҳуқуқи таҳрир кардани мансабҳоро надоред.');
        }

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
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Update Position',
            'entity_type' => 'Position',
            'description' => json_encode(['id' => $position->id, 'title' => $position->title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, Position $position)
    {
        if (!$request->user()->hasPermission('delete_positions')) {
            abort(403, 'Шумо ҳуқуқи нест кардани мансабҳоро надоред.');
        }

        $id = $position->id;
        $title = $position->title;
        $position->delete();
        
        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Delete Position',
            'entity_type' => 'Position',
            'description' => json_encode(['id' => $id, 'title' => $title]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv(Request $request)
    {
        if (!$request->user()->hasPermission('export_positions')) {
            abort(403, 'Шумо ҳуқуқи экспорти маълумотро надоред.');
        }

        $positions = Position::all();

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Export Positions',
            'entity_type' => 'Position',
            'description' => 'Exported positions data to CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        $callback = function() use ($positions) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['№', 'Номгӯй', 'Шӯъба', 'Ҳолат', 'Маош', 'Малакаҳо'], ';');
            foreach ($positions as $index => $pos) {
                $skills = json_decode($pos->required_skills, true);
                $skillsStr = is_array($skills) ? implode(', ', $skills) : ($pos->required_skills ?? '');
                fputcsv($file, [
                    $index + 1,
                    $pos->title,
                    $pos->department,
                    $pos->status,
                    $pos->salary,
                    $skillsStr
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="positions_export.csv"',
        ]);
    }
}
