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

        $csvContent = "Report ID, Report Name, Generated Date, Type\n1, System Report {$type}," . date('Y-m-d') . ",{$type}\n";
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="report_' . $type . '.csv"',
        ]);
    }
}
