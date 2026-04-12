<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;

class ScheduleController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('view_schedule')) {
            abort(403, 'Шумо ҳуқуқи дидани ҷадвалро надоред.');
        }
        return Inertia::render('Schedule', [
            'employees' => Employee::all(),
            'shifts' => Shift::all()
        ]);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('add_schedule')) {
            abort(403, 'Шумо ҳуқуқи илова кардани бастро надоред.');
        }

        $data = $request->validate([
            'employee_id' => 'nullable|integer',
            'date_key' => 'required|string',
            'shift_type' => 'required|string',
        ]);

        $data['employee_id'] = $data['employee_id'] ?? 0;
        Shift::create($data);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Add Shift',
            'entity_type' => 'Schedule',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, Shift $shift)
    {
        if (!$request->user()->hasPermission('delete_schedule')) {
            abort(403, 'Шумо ҳуқуқи нест кардани бастро надоред.');
        }

        $shift->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Delete Shift',
            'entity_type' => 'Schedule',
            'description' => json_encode(['id' => $shift->id]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv(Request $request)
    {
        if (!$request->user()->hasPermission('export_schedule')) {
            abort(403, 'Шумо ҳуқуқи экспорти ҷадвалро надоред.');
        }

        $shifts = Shift::all();
        $employees = Employee::all()->keyBy('id');
        $days = ['Душанбе', 'Сешанбе', 'Чоршанбе', 'Панҷшанбе', 'Ҷумъа', 'Шанбе', 'Якшанбе'];

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Export Schedule',
            'entity_type' => 'Schedule',
            'description' => 'Exported schedule data to CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        $callback = function() use ($shifts, $employees, $days) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['№', 'Рӯз', 'Вақти корӣ'], ';');
            foreach ($shifts as $index => $shift) {
                $dayName = $shift->date_key === 'weekdays' ? 'Душанбе - Ҷумъа' : ($days[$shift->date_key] ?? $shift->date_key);
                fputcsv($file, [
                    $index + 1,
                    $dayName,
                    $shift->shift_type
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="schedule_export.csv"',
        ]);
    }
}
