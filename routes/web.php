<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:hrd'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('school-units', \App\Http\Controllers\SchoolUnitController::class);

    // Distributed Employee CRUD
    Route::get('employees/download-template', [\App\Http\Controllers\EmployeeController::class, 'downloadTemplate'])->name('employees.download-template');
    Route::post('employees/import', [\App\Http\Controllers\EmployeeController::class, 'import'])->name('employees.import');
    Route::get('employees', [\App\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [\App\Http\Controllers\EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{unitId}/{id}/edit', [\App\Http\Controllers\EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{unitId}/{id}', [\App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{unitId}/{id}', [\App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::get('school-units/{id}/employee-types', [\App\Http\Controllers\EmployeeController::class, 'getEmployeeTypes'])->name('school-units.employee-types');

    // Working Shifts Template CRUD & Sync
    Route::get('working-shifts/sync', [\App\Http\Controllers\WorkingShiftController::class, 'triggerSync'])->name('working-shifts.sync');
    Route::resource('working-shifts', \App\Http\Controllers\WorkingShiftController::class);

    // Employee Working Shift Scheduling
    Route::get('employee-working-shifts/unit/{unitId}/employees', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'getEmployeesByUnit']);
    Route::get('employee-working-shifts/batch-edit', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'editBatch'])->name('employee-working-shifts.edit-batch');
    Route::put('employee-working-shifts/batch-update', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'updateBatch'])->name('employee-working-shifts.update-batch');
    Route::delete('employee-working-shifts/batch-destroy', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'destroyBatch'])->name('employee-working-shifts.destroy-batch');
    Route::resource('employee-working-shifts', \App\Http\Controllers\EmployeeWorkingShiftController::class);

    // Holidays & Reschedules (Adjustments)
    Route::get('holidays/sync', [\App\Http\Controllers\HolidayController::class, 'triggerSync'])->name('holidays.sync');
    Route::post('holidays/adjustments', [\App\Http\Controllers\HolidayController::class, 'storeAdjustment'])->name('holidays.store-adjustment');
    Route::delete('holidays/adjustments/{id}', [\App\Http\Controllers\HolidayController::class, 'destroyAdjustment'])->name('holidays.destroy-adjustment');
    Route::resource('holidays', \App\Http\Controllers\HolidayController::class);

    // Bonus Schemas & Tiers
    Route::get('bonus-schemas/sync', [\App\Http\Controllers\BonusSchemaController::class, 'triggerSync'])->name('bonus-schemas.sync');
    Route::resource('bonus-schemas', \App\Http\Controllers\BonusSchemaController::class);

    // Leave Request Approvals
    Route::get('leave-approvals', [\App\Http\Controllers\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
    Route::post('leave-approvals/{id}/approve', [\App\Http\Controllers\LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
    Route::post('leave-approvals/{id}/reject', [\App\Http\Controllers\LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');

    // ZKTeco Device Management
    Route::post('zkteco-devices/{zktecoDevice}/ping', [\App\Http\Controllers\ZktecoDeviceController::class, 'ping'])->name('zkteco-devices.ping');
    Route::post('zkteco-devices/{zktecoDevice}/pull', [\App\Http\Controllers\ZktecoDeviceController::class, 'pullLogs'])->name('zkteco-devices.pull');
    Route::resource('zkteco-devices', \App\Http\Controllers\ZktecoDeviceController::class);

    // Attendance Logs
    Route::delete('attendance-logs/clear', [\App\Http\Controllers\AttendanceLogController::class, 'clear'])->name('attendance-logs.clear');
    Route::get('attendance-logs/export', [\App\Http\Controllers\AttendanceLogController::class, 'export'])->name('attendance-logs.export');
    Route::get('attendance-logs', [\App\Http\Controllers\AttendanceLogController::class, 'index'])->name('attendance-logs.index');
});

Route::redirect('/dashboard', '/');

Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
