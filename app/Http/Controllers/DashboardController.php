<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Position;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = Employee::count();
        $activeVacancies = Position::where('status', 'vacant')->count();
        $totalPositions = Position::count();
        $fillRate = $totalPositions > 0 ? round((Position::where('status', 'filled')->count() / $totalPositions) * 100, 1) : 0;
        $departed = Employee::onlyTrashed()->where('deleted_at', '>=', now()->subDays(30))->count();
        $turnover = $totalEmployees > 0 ? round(($departed / $totalEmployees) * 100, 1) : 0;
        $topPerformers = Employee::where('status', 'active')->take(3)->get();

        return Inertia::render('Dashboard', [
            'totalEmployees' => $totalEmployees,
            'activeVacancies' => $activeVacancies,
            'fillRate' => $fillRate,
            'turnover' => $turnover,
            'topPerformers' => $topPerformers,
        ]);
    }
}
