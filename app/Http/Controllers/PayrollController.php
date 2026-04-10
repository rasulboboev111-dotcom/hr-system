<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Response;

class PayrollController extends Controller
{
    public function index()
    {
        return Inertia::render('Payroll', [
            'employees' => Employee::all(),
            'payroll_records' => PayrollRecord::all()
        ]);
    }

    public function storeBonus(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|integer',
            'month_year' => 'required|string',
            'bonus' => 'required|numeric',
            'salary' => 'nullable|numeric'
        ]);

        PayrollRecord::updateOrCreate(
            ['employee_id' => $data['employee_id'], 'month_year' => $data['month_year']],
            ['bonus' => $data['bonus'], 'salary' => $data['salary'] ?? 8500]
        );

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Update Bonus',
            'entity_type' => 'Payroll',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv()
    {
        $employees = Employee::all();
        $csvContent = "ID,Name,Position,Base Salary,Bonus,Total\n";

        foreach ($employees as $emp) {
            $base = 8500; // Mock base or from DB relationship
            $bonus = 0; // Retrieve from PayrollRecord
            $record = PayrollRecord::where('employee_id', $emp->id)->where('month_year', date('Y-m'))->first();
            if ($record) {
                $bonus = $record->bonus;
            }
            $total = $base + $bonus;
            
            $csvContent .= "{$emp->id},{$emp->first_name} {$emp->last_name},Position,{$base},{$bonus},{$total}\n";
        }

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Export Payroll',
            'module' => 'Payroll',
            'details' => 'Exported payroll data to CSV',
            'timestamp' => now()->toIso8601String()
        ]);

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="payroll_export.csv"',
        ]);
    }
}
