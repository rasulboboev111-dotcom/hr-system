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
            $records = PayrollRecord::where('month_year', date('Y-m'))->get()->keyBy('employee_id');

            foreach ($employees as $index => $emp) {
                // Determine salary
                $salary = 8500;
                $record = $records->get($emp->id);
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

    public function importCsv(Request $request)
    {
        if (!$request->user()->hasPermission('add_payroll') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи импорти маълумоти маошро надоред.');
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, 1000, ';'); // skip header

        while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
            if (count($data) < 5) continue;
            
            $fullName = trim($data[1]);
            $role = trim($data[2]);
            $department = trim($data[3]);
            $salary = floatval(str_replace(' ', '', str_replace(',', '.', trim($data[4]))));
            
            $nameParts = explode(' ', $fullName, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            if (empty($firstName)) continue;

            try {
                $employee = Employee::firstOrCreate(
                    ['name' => $firstName, 'last_name' => $lastName],
                    [
                        'email' => strtolower($firstName) . rand(100,999) . '@example.com',
                        'role' => $role === 'Маълум нест' ? 'Unknown' : $role,
                        'department' => $department === 'Маълум нест' ? 'Unknown' : $department,
                        'status' => 'active'
                    ]
                );

                PayrollRecord::updateOrCreate(
                    ['employee_id' => $employee->id, 'month_year' => date('Y-m')],
                    ['bonus' => 0, 'salary' => $salary]
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Import Error: " . $e->getMessage(), ['data' => $data]);
                continue;
            }
        }

        fclose($handle);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->username,
            'action' => 'Import Payroll',
            'entity_type' => 'Payroll',
            'description' => 'Imported payroll data from CSV'
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->hasPermission('delete_payroll') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи нест кардани маълумоти маошро надоред.');
        }

        // We delete the record for the current month/year or the one associated with the record ID
        // For simplicity, we find the record for this employee and delete it
        PayrollRecord::where('employee_id', $id)->delete();

        return redirect()->back();
    }
}
