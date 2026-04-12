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
    public function index(Request $request)
    {
        if (!auth()->user()->hasPermission('view_payroll')) {
            abort(403, 'Шумо ҳуқуқи дидани маошро надоред.');
        }

        $query = Employee::query();

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

        return Inertia::render('Payroll', [
            'employees' => $query->get(),
            'payroll_records' => PayrollRecord::all(),
            'filters' => $request->only('search')
        ]);
    }

    public function storeBonus(Request $request)
    {
        if (!$request->user()->hasPermission('add_payroll')) {
            abort(403, 'Шумо ҳуқуқи илова кардани маълумоти маошро надоред.');
        }

        $data = $request->validate([
            'employee_name' => 'required|string',
            'role' => 'nullable|string',
            'department' => 'nullable|string',
            'month_year' => 'required|string',
            'salary' => 'required|numeric'
        ]);

        $nameParts = explode(' ', $data['employee_name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        $employee = Employee::firstOrCreate(
            ['name' => $firstName, 'last_name' => $lastName],
            [
                'email' => strtolower($firstName) . rand(100,999) . '@example.com',
                'role' => $data['role'] ?? 'Unknown',
                'department' => $data['department'] ?? 'Unknown',
                'status' => 'active'
            ]
        );

        if (!empty($data['role']) || !empty($data['department'])) {
            $employee->update(array_filter([
                'role' => $data['role'],
                'department' => $data['department']
            ]));
        }

        PayrollRecord::updateOrCreate(
            ['employee_id' => $employee->id, 'month_year' => $data['month_year']],
            ['bonus' => 0, 'salary' => $data['salary']]
        );

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Update Bonus',
            'entity_type' => 'Payroll',
            'description' => json_encode($data),
            'timestamp' => now()->toIso8601String()
        ]);

        return redirect()->back();
    }

    public function exportCsv(Request $request)
    {
        if (!$request->user()->hasPermission('export_payroll')) {
            abort(403, 'Шумо ҳуқуқи экспорти маълумоти маошро надоред.');
        }

        $employees = Employee::all();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Export Payroll',
            'entity_type' => 'Payroll',
            'description' => 'Exported payroll data to CSV'
        ]);

        $callback = function() use ($employees) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['№', 'Ном ва Насаб', 'Мансаб', 'Шӯъба', 'Маоши асосӣ'], ';');
            foreach ($employees as $index => $emp) {
                // Determine salary
                $salary = 8500;
                $record = PayrollRecord::where('employee_id', $emp->id)->where('month_year', date('Y-m'))->first();
                if ($record) {
                    $salary = $record->salary;
                }
                
                fputcsv($file, [
                    $index + 1,
                    $emp->name . ' ' . $emp->last_name,
                    $emp->role ?? 'Маълум нест',
                    $emp->department ?? 'Маълум нест',
                    $salary
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="payroll_export.csv"',
        ]);
    }
}
