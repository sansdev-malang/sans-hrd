<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:hrd'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('school-units', \App\Http\Controllers\SchoolUnitController::class);

    // Distributed Employee CRUD
    Route::post('employees/sync', [\App\Http\Controllers\EmployeeController::class, 'triggerSync'])->name('employees.sync');
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
    Route::patch('working-shifts/{workingShift}/toggle-active', [\App\Http\Controllers\WorkingShiftController::class, 'toggleActive'])->name('working-shifts.toggle-active');
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
    Route::post('employee-working-shifts/sync', [\App\Http\Controllers\EmployeeWorkingShiftController::class, 'triggerSync'])->name('employee-working-shifts.sync-trigger');
    Route::resource('employee-working-shifts', \App\Http\Controllers\EmployeeWorkingShiftController::class);

    // Picket Schedules (Read-Only Monitoring from Units)
    Route::get('picket-schedules', [\App\Http\Controllers\PicketScheduleController::class, 'index'])->name('picket-schedules.index');
    Route::post('picket-schedules/sync', [\App\Http\Controllers\PicketScheduleController::class, 'sync'])->name('picket-schedules.sync');

    // Holidays, Reschedules & Holiday Rewards
    Route::get('holidays/sync', [\App\Http\Controllers\HolidayController::class, 'triggerSync'])->name('holidays.sync');
    Route::post('holidays/rewards', [\App\Http\Controllers\HolidayController::class, 'storeReward'])->name('holidays.store-reward');
    Route::put('holidays/rewards/{id}', [\App\Http\Controllers\HolidayController::class, 'updateReward'])->name('holidays.update-reward');
    Route::delete('holidays/rewards/{id}', [\App\Http\Controllers\HolidayController::class, 'destroyReward'])->name('holidays.destroy-reward');
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
    Route::post('payslips/notes', [\App\Http\Controllers\PayslipController::class, 'updateNotes'])->name('payslips.notes.update');
    Route::get('payslips', [\App\Http\Controllers\PayslipController::class, 'index'])->name('payslips.index');
    Route::post('payslips', [\App\Http\Controllers\PayslipController::class, 'store'])->name('payslips.store');
    Route::delete('payslips/{payslip}', [\App\Http\Controllers\PayslipController::class, 'destroy'])->name('payslips.destroy');
    Route::delete('payslips/{payslip}/attachment', [\App\Http\Controllers\PayslipController::class, 'destroyAttachment'])->name('payslips.destroyAttachment');

    // Cutoff Settings
    Route::get('cutoff-settings', [\App\Http\Controllers\CutoffSettingController::class, 'index'])->name('cutoff-settings.index');
    Route::put('cutoff-settings', [\App\Http\Controllers\CutoffSettingController::class, 'update'])->name('cutoff-settings.update');

    // Settings
    Route::get('settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    Route::get('settings/adms', [\App\Http\Controllers\SettingController::class, 'adms'])->name('settings.adms');
    Route::put('settings/adms', [\App\Http\Controllers\SettingController::class, 'updateAdms'])->name('settings.update-adms');

    // Leave Request Approvals
    Route::post('leave-approvals/sync', [\App\Http\Controllers\LeaveApprovalController::class, 'triggerSync'])->name('leave-approvals.sync');
    Route::get('leave-approvals/units/{unit_id}/leave-types', [\App\Http\Controllers\LeaveApprovalController::class, 'getUnitLeaveTypes'])->name('leave-approvals.unit-leave-types');
    Route::get('leave-approvals', [\App\Http\Controllers\LeaveApprovalController::class, 'index'])->name('leave-approvals.index');
    Route::post('leave-approvals/{id}/approve', [\App\Http\Controllers\LeaveApprovalController::class, 'approve'])->name('leave-approvals.approve');
    Route::post('leave-approvals/{id}/reject', [\App\Http\Controllers\LeaveApprovalController::class, 'reject'])->name('leave-approvals.reject');
    Route::put('leave-approvals/{id}', [\App\Http\Controllers\LeaveApprovalController::class, 'update'])->name('leave-approvals.update');
    Route::delete('leave-approvals/{id}', [\App\Http\Controllers\LeaveApprovalController::class, 'destroy'])->name('leave-approvals.destroy');

    // Master Leave Types CRUD & Sync
    Route::post('leave-types/pull', [\App\Http\Controllers\LeaveTypeController::class, 'pullFromUnits'])->name('leave-types.pull');
    Route::post('leave-types/push-all', [\App\Http\Controllers\LeaveTypeController::class, 'pushAllToUnits'])->name('leave-types.push-all');
    Route::resource('leave-types', \App\Http\Controllers\LeaveTypeController::class);

    // ZKTeco Device Management
    Route::post('zkteco-devices/{zktecoDevice}/pull', [\App\Http\Controllers\ZktecoDeviceController::class, 'pullLogs'])->name('zkteco-devices.pull');
    Route::post('zkteco-devices/{zktecoDevice}/force-adms', [\App\Http\Controllers\ZktecoDeviceController::class, 'forceAdms'])->name('zkteco-devices.force-adms');
    Route::resource('zkteco-devices', \App\Http\Controllers\ZktecoDeviceController::class);

    // Raw Attendance Logs
    Route::get('raw-attendance-logs/template', [\App\Http\Controllers\RawAttendanceLogController::class, 'downloadTemplate'])->name('raw-attendance-logs.template');
    Route::post('raw-attendance-logs/import', [\App\Http\Controllers\RawAttendanceLogController::class, 'importExcel'])->name('raw-attendance-logs.import');
    Route::get('raw-attendance-logs/export', [\App\Http\Controllers\RawAttendanceLogController::class, 'export'])->name('raw-attendance-logs.export');
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

    // Layanan SDM (Mockup / Tahap Pengembangan)
    Route::get('layanan-sdm', [\App\Http\Controllers\LayananSdmController::class, 'index'])->name('layanan-sdm.index');
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

    // Employee Sync Endpoints (from units)
    Route::post('sync/clear-employee-cache', [\App\Http\Controllers\Api\EmployeeSyncApiController::class, 'clearCache']);
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

    // System Logs
    Route::get('system-logs', [\App\Http\Controllers\SystemLogController::class, 'index'])->name('system-logs.index');
    Route::get('system-logs/download', [\App\Http\Controllers\SystemLogController::class, 'download'])->name('system-logs.download');
    Route::post('system-logs/clear', [\App\Http\Controllers\SystemLogController::class, 'clear'])->name('system-logs.clear');
    Route::delete('system-logs/delete', [\App\Http\Controllers\SystemLogController::class, 'destroy'])->name('system-logs.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/coming-soon', function () { return view('admin.coming-soon'); })->name('coming-soon');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// PWA Offline Fallback Route
Route::view('/offline', 'errors.offline')->name('offline');

