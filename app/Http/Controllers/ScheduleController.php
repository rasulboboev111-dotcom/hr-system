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
        return Inertia::render('Schedule', [
            'employees' => Employee::all(),
            'shifts' => Shift::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'nullable|integer',
            'date_key' => 'required|string',
            'shift_type' => 'required|string',
        ]);

        $data['employee_id'] = $data['employee_id'] ?? 0;
        Shift::create($data);

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Add Shift',
            'entity_type' => 'Schedule',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Delete Shift',
            'entity_type' => 'Schedule',
            'description' => json_encode(['id' => $shift->id]),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv()
    {
        $shifts = Shift::all();
        $employees = Employee::all()->keyBy('id');
        $days = ['Душанбе', 'Сешанбе', 'Чоршанбе', 'Панҷшанбе', 'Ҷумъа', 'Шанбе', 'Якшанбе'];

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
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
