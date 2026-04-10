<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'show'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/positions', [PositionController::class, 'index']);
    Route::post('/positions', [PositionController::class, 'store']);
    Route::put('/positions/{position}', [PositionController::class, 'update']);
    Route::delete('/positions/{position}', [PositionController::class, 'destroy']);
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{department}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy']);
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::post('/employees', [EmployeeController::class, 'store']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy']);
    Route::get('/employees/export', [EmployeeController::class, 'exportCsv']);
    Route::post('/employees/import', [EmployeeController::class, 'importCsv']);

    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index']);
    Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update']);
    Route::get('/profile', function () { return Inertia::render('Profile'); });

    Route::get('/recruitment', [\App\Http\Controllers\RecruitmentController::class, 'index']);
    Route::post('/recruitment/generate', [\App\Http\Controllers\RecruitmentController::class, 'generateDescription']);

    Route::get('/archive', function (\Illuminate\Http\Request $request) { 
        $query = \App\Models\Employee::onlyTrashed();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }
        
        return Inertia::render('Archive', [
            'employees' => $query->get(),
            'filters' => $request->only('search')
        ]); 
    });

    Route::post('/archive', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email'.(\App\Models\Employee::where('email', $request->email)->exists() ? '' : '|unique:employees,email'),
            'role' => 'required|string',
            'department' => 'required|string',
        ]);
        
        $validated['status'] = 'Retired';
        
        $employee = \App\Models\Employee::create($validated);
        $employee->delete();
        
        return redirect()->back();
    });

    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index']);
    Route::post('/calendar/events', [\App\Http\Controllers\CalendarController::class, 'store']);
    Route::delete('/calendar/events/{event}', [\App\Http\Controllers\CalendarController::class, 'destroy']);

    Route::get('/schedule', [\App\Http\Controllers\ScheduleController::class, 'index']);
    Route::post('/schedule', [\App\Http\Controllers\ScheduleController::class, 'store']);
    Route::delete('/schedule/{shift}', [\App\Http\Controllers\ScheduleController::class, 'destroy']);

    Route::get('/timesheet', [\App\Http\Controllers\TimesheetController::class, 'index']);
    Route::post('/timesheet', [\App\Http\Controllers\TimesheetController::class, 'store']);

    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index']);
    Route::post('/payroll/bonus', [\App\Http\Controllers\PayrollController::class, 'storeBonus']);
    Route::get('/payroll/export', [\App\Http\Controllers\PayrollController::class, 'exportCsv']);

    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index']);
    Route::get('/reports/download/{type}', [\App\Http\Controllers\ReportController::class, 'download']);

    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index']);

    Route::get('/advisor', [\App\Http\Controllers\AdvisorController::class, 'index']);
    Route::post('/advisor/generate', [\App\Http\Controllers\AdvisorController::class, 'generate']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'usersIndex']);
        Route::post('/users', [\App\Http\Controllers\AdminController::class, 'storeUser']);
        Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'destroyUser']);
        
        Route::get('/audit', [\App\Http\Controllers\AdminController::class, 'auditIndex']);
    });
});

