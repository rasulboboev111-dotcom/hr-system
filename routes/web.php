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
    Route::get('/positions/export', [PositionController::class, 'exportCsv']);
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
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update']);

    Route::get('/archive', function (\Illuminate\Http\Request $request) { 
        if (!$request->user()->hasPermission('view_archive') && !$request->user()->hasPermission('view_all') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи дидани архивро надоред.');
        }

        $query = \App\Models\Employee::onlyTrashed();

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
        
        return Inertia::render('Archive', [
            'employees' => $query->get(),
            'filters' => $request->only('search'),
            'departments' => \App\Models\Department::all(),
            'positions' => \App\Models\Position::all()
        ]); 
    });

    Route::post('/archive', function (\Illuminate\Http\Request $request) {
        if (!$request->user()->hasPermission('add_archive') && !$request->user()->hasPermission('edit_all') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи илова ба архивро надоред.');
        }

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

    Route::put('/archive/{id}', function (\Illuminate\Http\Request $request, $id) {
        if (!$request->user()->hasPermission('edit_archive') && !$request->user()->hasPermission('edit_all') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи таҳрири архивро надоред.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email'.(\App\Models\Employee::withTrashed()->where('email', $request->email)->where('id', '!=', $id)->exists() ? '|unique:employees,email' : ''),
            'role' => 'required|string',
            'department' => 'required|string',
        ]);
        
        $employee = \App\Models\Employee::withTrashed()->findOrFail($id);
        $employee->update($validated);
        
        return redirect()->back();
    });

    Route::delete('/archive/{id}', function (\Illuminate\Http\Request $request, $id) {
        if (!$request->user()->hasPermission('delete_archive') && !$request->user()->hasPermission('edit_all') && !$request->user()->hasPermission('all')) {
            abort(403, 'Шумо ҳуқуқи тозакунии архивро надоред.');
        }

        $employee = \App\Models\Employee::withTrashed()->findOrFail($id);
        $employee->forceDelete();
        return redirect()->back();
    });

    Route::get('/calendar', [\App\Http\Controllers\CalendarController::class, 'index']);
    Route::post('/calendar/events', [\App\Http\Controllers\CalendarController::class, 'store']);
    Route::delete('/calendar/events/{event}', [\App\Http\Controllers\CalendarController::class, 'destroy']);

    Route::get('/schedule', [\App\Http\Controllers\ScheduleController::class, 'index']);
    Route::get('/schedule/export', [\App\Http\Controllers\ScheduleController::class, 'exportCsv']);
    Route::post('/schedule', [\App\Http\Controllers\ScheduleController::class, 'store']);
    Route::delete('/schedule/{shift}', [\App\Http\Controllers\ScheduleController::class, 'destroy']);

    Route::get('/timesheet', [\App\Http\Controllers\TimesheetController::class, 'index']);
    Route::get('/timesheet/export', [\App\Http\Controllers\TimesheetController::class, 'exportCsv']);
    Route::post('/timesheet/import', [\App\Http\Controllers\TimesheetController::class, 'importCsv']);
    Route::post('/timesheet', [\App\Http\Controllers\TimesheetController::class, 'store']);

    Route::get('/payroll', [\App\Http\Controllers\PayrollController::class, 'index']);
    Route::post('/payroll/bonus', [\App\Http\Controllers\PayrollController::class, 'storeBonus']);
    Route::get('/payroll/export', [\App\Http\Controllers\PayrollController::class, 'exportCsv']);

    Route::get('/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'usersIndex']);
        Route::post('/roles', [\App\Http\Controllers\AdminController::class, 'storeRole']);
        Route::delete('/roles/{id}', [\App\Http\Controllers\AdminController::class, 'destroyRole']);
        Route::post('/permissions', [\App\Http\Controllers\AdminController::class, 'storePermission']);
        Route::get('/users/export', [\App\Http\Controllers\AdminController::class, 'exportCsv']);
        Route::post('/users/import', [\App\Http\Controllers\AdminController::class, 'importCsv']);
        Route::post('/users', [\App\Http\Controllers\AdminController::class, 'storeUser']);
        Route::put('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser']);
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'destroyUser']);
        
        Route::get('/audit', [\App\Http\Controllers\AdminController::class, 'auditIndex']);
        Route::delete('/audit/clear', [\App\Http\Controllers\AdminController::class, 'clearAudit']);
    });
});

