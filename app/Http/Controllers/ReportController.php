<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Support\Facades\Response;
use App\Models\AuditLog;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports');
    }

    public function download($type)
    {
        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Download Report',
            'entity_type' => 'Reports',
            'description' => "Downloaded report type: {$type}",
            'timestamp' => now()->toIso8601String()
        ]);

        $employees = \App\Models\Employee::all();

        if ($type === 'pdf') {
            if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                abort(500, "Please run 'composer require barryvdh/laravel-dompdf' to enable PDF generation.");
            }
            
            $html = '<h1 style="font-family: sans-serif">Employees Report</h1><table border="1" cellpadding="5" cellspacing="0" style="width:100%; font-family: sans-serif; font-size:12px; border-collapse: collapse;">';
            $html .= '<tr style="background:#f3f4f6"><th>Name</th><th>Department</th><th>Role</th><th>Status</th></tr>';
            foreach($employees as $emp) {
                $html .= "<tr><td>{$emp->name} {$emp->last_name}</td><td>{$emp->department}</td><td>{$emp->role}</td><td>{$emp->status}</td></tr>";
            }
            $html .= '</table>';
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download("employee_report_" . date('Y_m_d') . ".pdf");
        }

        // Default to CSV
        $filename = "report_" . date('Y_m_d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($employees) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Department', 'Role', 'Status']);
            foreach ($employees as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->name . ' ' . $row->last_name,
                    $row->department,
                    $row->role,
                    $row->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
