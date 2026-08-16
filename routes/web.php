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
    Route::get('employees/export/excel', [\App\Http\Controllers\EmployeeController::class, 'exportExcel'])->name('employees.export.excel');
    Route::get('employees/export/pdf', [\App\Http\Controllers\EmployeeController::class, 'exportPdf'])->name('employees.export.pdf');
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
    Route::get('employee-working-shifts/roster-employees', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'getRosterEmployees']);
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

    // Attendance Percentage Reports
    Route::get('attendance-percentage-reports', [\App\Http\Controllers\AttendancePercentageReportController::class, 'index'])->name('attendance-percentage-reports.index');

    // Manajemen Gaji
    Route::post('payslips/sync', [\App\Http\Controllers\PayslipController::class, 'triggerSync'])->name('payslips.sync');
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
    Route::get('leave-approvals/units/{unit_id}/leave-types', [\App\Http\Controllers\LeaveApprovalController::class, 'getUnitLeaveTypes'])->name('leave-approvals.unit-leave-types');
    Route::get('leave-approvals', [\App\Http\Controllers\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
    Route::post('leave-approvals/{id}/approve', [\App\Http\Controllers\LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
    Route::post('leave-approvals/{id}/reject', [\App\Http\Controllers\LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');
    Route::put('leave-approvals/{id}', [\App\Http\Controllers\LeaveApprovalController::class, 'update'])->name('leave-approvals.update');
    Route::delete('leave-approvals/{id}', [\App\Http\Controllers\LeaveApprovalController::class, 'destroy'])->name('leave-approvals.destroy');

    // ZKTeco Device Management
    Route::post('zkteco-devices/{zktecoDevice}/pull', [\App\Http\Controllers\ZktecoDeviceController::class, 'pullLogs'])->name('zkteco-devices.pull');
    Route::post('zkteco-devices/{zktecoDevice}/force-adms', [\App\Http\Controllers\ZktecoDeviceController::class, 'forceAdms'])->name('zkteco-devices.force-adms');
    Route::resource('zkteco-devices', \App\Http\Controllers\ZktecoDeviceController::class);

    // Raw Attendance Logs
    Route::get('raw-attendance-logs', [\App\Http\Controllers\RawAttendanceLogController::class, 'index'])->name('raw-attendance-logs.index');
    Route::post('raw-attendance-logs', [\App\Http\Controllers\RawAttendanceLogController::class, 'store'])->name('raw-attendance-logs.store');
    Route::put('raw-attendance-logs/{id}', [\App\Http\Controllers\RawAttendanceLogController::class, 'update'])->name('raw-attendance-logs.update');
    Route::delete('raw-attendance-logs/{id}', [\App\Http\Controllers\RawAttendanceLogController::class, 'destroy'])->name('raw-attendance-logs.destroy');

    // Attendance Logs
    Route::post('attendance-logs/sync', [\App\Http\Controllers\AttendanceLogController::class, 'sync'])->name('attendance-logs.sync');
    Route::delete('attendance-logs/clear', [\App\Http\Controllers\AttendanceLogController::class, 'clear'])->name('attendance-logs.clear');
    Route::get('attendance-logs/export', [\App\Http\Controllers\AttendanceLogController::class, 'export'])->name('attendance-logs.export');
    Route::get('attendance-logs', [\App\Http\Controllers\AttendanceLogController::class, 'index'])->name('attendance-logs.index');

    // Attendance History (New page)
    Route::get('attendance-history', [\App\Http\Controllers\AttendanceHistoryController::class, 'index'])->name('attendance-history.index');
    Route::get('attendance-history/export', [\App\Http\Controllers\AttendanceHistoryController::class, 'export'])->name('attendance-history.export');

    // Announcements CRUD
    Route::post('announcements/sync', [\App\Http\Controllers\AnnouncementController::class, 'triggerSync'])->name('announcements.sync');
    Route::resource('announcements', \App\Http\Controllers\AnnouncementController::class);

    // Synced Performance Reports Web Views
    Route::get('performance-reports', [\App\Http\Controllers\PerformanceReportController::class, 'index'])->name('performance-reports.index');
    Route::get('performance-reports/{id}', [\App\Http\Controllers\PerformanceReportController::class, 'show'])->name('performance-reports.show');
});

// Unit API access - PROTECTED with School Unit Token
Route::middleware(['throttle:60,1', 'verify_school_unit_token'])->prefix('api')->group(function () {
    // Attendance & Bonus Reports (Unit sync endpoints)
    Route::get('attendance-matrix', [\App\Http\Controllers\Api\AttendanceApiController::class, 'matrixReport']);
    Route::get('attendances', [\App\Http\Controllers\Api\AttendanceApiController::class, 'index']);
    Route::get('bonus-reports', [\App\Http\Controllers\Api\AttendanceApiController::class, 'bonusReport']);
    Route::get('payslips', [\App\Http\Controllers\Api\PayslipApiController::class, 'index']);

    // Leave Request Sync Endpoints (from units)
    Route::post('sync/leave-request', [\App\Http\Controllers\Api\LeaveSyncApiController::class, 'storeOrUpdate']);
    Route::post('sync/leave-request/delete', [\App\Http\Controllers\Api\LeaveSyncApiController::class, 'destroy']);
});

// PKG Integration API - PROTECTED with PKG Token
Route::middleware(['throttle:60,1', 'verify_pkg_api_token'])->prefix('api')->group(function () {
    Route::get('employees', [\App\Http\Controllers\Api\PkgIntegrationApiController::class, 'employees']);
    Route::get('attendances/summary', [\App\Http\Controllers\Api\PkgIntegrationApiController::class, 'attendanceSummary']);
    Route::post('auth/verify-credential', [\App\Http\Controllers\Api\PkgIntegrationApiController::class, 'verifyCredential']);
    Route::post('performance-reports', [\App\Http\Controllers\Api\PkgIntegrationApiController::class, 'receivePerformanceReport']);
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
    Route::get('/coming-soon', function () { return view('admin.coming-soon'); })->name('coming-soon');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Temporary routes for testing error pages
Route::get('/test-403', function () { abort(403); });
Route::get('/test-419', function () { abort(419); });
Route::get('/test-500', function () { abort(500); });
Route::get('/test-503', function () { abort(503); });
