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
    Route::get('employees/generate-uid/{unitId}', [\App\Http\Controllers\EmployeeController::class, 'generateUid'])->name('employees.generate-uid');
    Route::post('employees', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{unitId}/{id}/edit', [\App\Http\Controllers\EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{unitId}/{id}', [\App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{unitId}/{id}', [\App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::get('school-units/{id}/employee-types', [\App\Http\Controllers\EmployeeController::class, 'getEmployeeTypes'])->name('school-units.employee-types');

    // Working Shifts Template CRUD & Sync
    Route::get('working-shifts/sync', [\App\Http\Controllers\WorkingShiftController::class, 'triggerSync'])->name('working-shifts.sync');
    Route::resource('working-shifts', \App\Http\Controllers\WorkingShiftController::class);

    // Employee Working Shift Scheduling
    Route::get('employee-working-shifts/detail-roster', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'detailRoster'])->name('employee-working-shifts.detail-roster');
    Route::get('employee-working-shifts/roster', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'roster'])->name('employee-working-shifts.roster');
    Route::get('employee-working-shifts/export-roster', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'exportRoster'])->name('employee-working-shifts.export-roster');
    Route::post('employee-working-shifts/roster', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'updateRoster'])->name('employee-working-shifts.update-roster');
    Route::delete('employee-working-shifts/roster-batch', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'destroyRoster'])->name('employee-working-shifts.destroy-roster');
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

    // Bonus Reports
    Route::get('bonus-reports', [\App\Http\Controllers\AttendanceBonusReportController::class, 'index'])->name('bonus-reports.index');
    Route::get('bonus-reports/export', [\App\Http\Controllers\AttendanceBonusReportController::class, 'export'])->name('bonus-reports.export');

    // Manajemen Gaji
    Route::get('payslips', [\App\Http\Controllers\PayslipController::class, 'index'])->name('payslips.index');
    Route::post('payslips', [\App\Http\Controllers\PayslipController::class, 'store'])->name('payslips.store');
    Route::delete('payslips/{payslip}', [\App\Http\Controllers\PayslipController::class, 'destroy'])->name('payslips.destroy');

    // Cutoff Settings
    Route::get('cutoff-settings', [\App\Http\Controllers\CutoffSettingController::class, 'index'])->name('cutoff-settings.index');
    Route::put('cutoff-settings', [\App\Http\Controllers\CutoffSettingController::class, 'update'])->name('cutoff-settings.update');

    // Settings
    Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    Route::get('settings/adms', [\App\Http\Controllers\SettingController::class, 'adms'])->name('settings.adms');
    Route::put('settings/adms', [\App\Http\Controllers\SettingController::class, 'updateAdms'])->name('settings.update-adms');

    // Leave Request Approvals
    Route::get('leave-approvals', [\App\Http\Controllers\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
    Route::post('leave-approvals/{id}/approve', [\App\Http\Controllers\LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
    Route::post('leave-approvals/{id}/reject', [\App\Http\Controllers\LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');

    // ZKTeco Device Management
    Route::post('zkteco-devices/{zktecoDevice}/ping', [\App\Http\Controllers\ZktecoDeviceController::class, 'ping'])->name('zkteco-devices.ping');
    Route::post('zkteco-devices/{zktecoDevice}/pull', [\App\Http\Controllers\ZktecoDeviceController::class, 'pullLogs'])->name('zkteco-devices.pull');
    Route::resource('zkteco-devices', \App\Http\Controllers\ZktecoDeviceController::class);

    // Attendance Logs
    Route::post('attendance-logs/sync', [\App\Http\Controllers\AttendanceLogController::class, 'sync'])->name('attendance-logs.sync');
    Route::delete('attendance-logs/clear', [\App\Http\Controllers\AttendanceLogController::class, 'clear'])->name('attendance-logs.clear');
    Route::get('attendance-logs/export', [\App\Http\Controllers\AttendanceLogController::class, 'export'])->name('attendance-logs.export');
    Route::get('attendance-logs', [\App\Http\Controllers\AttendanceLogController::class, 'index'])->name('attendance-logs.index');
});

// Unit API access (unprotected internally for simplicity, or protect later if exposed publicly)
Route::prefix('api')->group(function () {
    Route::get('attendance-matrix', [\App\Http\Controllers\Api\AttendanceApiController::class, 'matrixReport']);
    Route::get('attendances', [\App\Http\Controllers\Api\AttendanceApiController::class, 'index']);
    Route::get('bonus-reports', [\App\Http\Controllers\Api\AttendanceApiController::class, 'bonusReport']);
    Route::get('payslips', [\App\Http\Controllers\Api\PayslipApiController::class, 'index']);
});

// ZKTeco ADMS Endpoints (Must bypass CSRF)
Route::prefix('iclock')->group(function () {
    Route::get('cdata', [\App\Http\Controllers\ZkTecoAdmsController::class, 'handshake']);
    Route::post('cdata', [\App\Http\Controllers\ZkTecoAdmsController::class, 'receiveData']);
    Route::get('getrequest', [\App\Http\Controllers\ZkTecoAdmsController::class, 'getRequest']);
    Route::post('devicecmd', [\App\Http\Controllers\ZkTecoAdmsController::class, 'deviceCmd']);
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
