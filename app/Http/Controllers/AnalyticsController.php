<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Inertia\Inertia;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('view_analytics')) {
            abort(403, 'Шумо ҳуқуқи дидани таҳлилро надоред.');
        }
        $employees = Employee::all();

        // 1. Department Distribution (Pie Chart)
        $deptCounts = $employees->groupBy('department')->map(function ($items) {
            return $items->count();
        });
        $deptLabels = $deptCounts->keys()->toArray();
        $deptDataValues = $deptCounts->values()->toArray();

        $deptData = [
            'labels' => $deptLabels,
            'datasets' => [
                [
                    'data' => $deptDataValues,
                    'backgroundColor' => ['#1e40af', '#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
                    'borderWidth' => 0,
                    'hoverOffset' => 4
                ]
            ]
        ];

        // 2. Hiring Trends (Bar Chart) - Last 6 Months
        $monthsArray = [];
        $countsArray = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->translatedFormat('M'); // Localized short month
            $monthsArray[] = $monthName;
            
            // Count hires in this specific month/year
            $count = $employees->filter(function ($emp) use ($date) {
                if (!$emp->hire_date) return false;
                $empDate = Carbon::parse($emp->hire_date);
                return $empDate->year === $date->year && $empDate->month === $date->month;
            })->count();
            
            $countsArray[] = $count;
        }

        $hiringTrendData = [
            'labels' => $monthsArray,
            'datasets' => [
                [
                    'label' => 'Кормандони нав',
                    'data' => $countsArray,
                    'backgroundColor' => '#1e40af',
                    'borderRadius' => 4,
                    'barPercentage' => 0.5
                ]
            ]
        ];

        return Inertia::render('Analytics', [
            'deptDataProps' => $deptData,
            'hiringTrendDataProps' => $hiringTrendData
        ]);
    }
}
