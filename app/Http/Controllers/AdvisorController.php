<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AuditLog;

class AdvisorController extends Controller
{
    public function index()
    {
        return Inertia::render('Advisor');
    }

    public function generate(Request $request)
    {
        // Simulate AI analysis delay for the boilerplate
        sleep(2);
        
        $mockResult = [
            'placementRecommendations' => [
                ['employeeId' => 'e1', 'projectId' => 'p1', 'confidenceScore' => 92, 'rationale' => 'Excellent fit for modern stack requirements.'],
                ['employeeId' => 'e2', 'teamId' => 't1', 'confidenceScore' => 88, 'rationale' => 'Can mentor junior developers effectively.']
            ],
            'skillGapAnalysis' => [
                ['teamId' => 't2', 'missingSkills' => ['GraphQL', 'Redis'], 'impact' => 'High latency in data delivery', 'suggestedTraining' => ['Advanced Redis Caching', 'GraphQL API Design']]
            ]
        ];

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Generate AI Staffing Plan',
            'entity_type' => 'Advisor',
            'description' => 'Ran smart staffing advisor',
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json($mockResult);
    }
}
