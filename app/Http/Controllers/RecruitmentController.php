<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Position;
use App\Models\AuditLog;

class RecruitmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::where('status', 'vacant');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('department', 'ilike', "%{$search}%");
            });
        }

        return Inertia::render('Recruitment', [
            'vacancies' => $query->get(),
            'filters' => $request->only('search')
        ]);
    }

    public function generateDescription(Request $request)
    {
        $title = $request->input('title', 'Unknown role');
        $dept = $request->input('dept', 'Unknown department');
        
        sleep(2); // Mocking generation time

        $description = "This is an AI generated job description for the {$title} role in the {$dept} department. Responsibilities include taking ownership of features and delivering quality code. Requirements: 3+ years experience, solid understanding of core principles.";

        AuditLog::create([
            'user_id' => 'system',
            'user_name' => 'Admin User',
            'action' => 'Generate Job Description',
            'entity_type' => 'Recruitment',
            'description' => "Generated AI JD for {$title}",
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json(['description' => $description]);
    }
}
