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
            'employee_id' => 'required|integer',
            'date_key' => 'required|string',
            'shift_type' => 'required|string',
        ]);

        Shift::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date_key' => $data['date_key']],
            ['shift_type' => $data['shift_type']]
        );

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
}
