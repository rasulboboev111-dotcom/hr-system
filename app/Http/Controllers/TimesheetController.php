<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;

class TimesheetController extends Controller
{
    public function index()
    {
        return Inertia::render('Timesheet', [
            'employees' => Employee::all(),
            'attendances' => Attendance::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer',
            'date_key' => 'required|string',
            'status' => 'required|string'
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'date_key' => $data['date_key']],
            ['status' => $data['status']]
        );

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Update Timesheet',
            'entity_type' => 'Timesheet',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }
}
