<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\ZktecoDevice;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    public function sync()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('zkteco:pull');
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            if (str_contains(strtolower($output), 'error') || str_contains(strtolower($output), 'failed')) {
                return redirect()->route('attendance-logs.index')->with('error', 'Gagal menarik data dari mesin ZKTeco. Silakan cek koneksi mesin.');
            }
            
            return redirect()->route('attendance-logs.index')->with('success', 'Berhasil melakukan sinkronisasi dengan mesin ZKTeco!');
        } catch (\Exception $e) {
            return redirect()->route('attendance-logs.index')->with('error', 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }
    }

        public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $cutoffDate = (int) \App\Models\Setting::get('payroll_cutoff_date', 26);
        $monthCarbon = \Carbon\Carbon::createFromFormat('Y-m', $month);
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $rawEmployees = collect($this->service->getSdEmployees());

        // Apply filters
        $search = $request->query('search');
        $unitId = $request->query('unit_id');

        if (!empty($unitId)) {
            $rawEmployees = $rawEmployees->filter(fn($e) => isset($e['unit_id']) && $e['unit_id'] == $unitId);
        }
        if (!empty($search)) {
            $rawEmployees = $rawEmployees->filter(function($e) use ($search) {
                return stripos($e['name'], $search) !== false || (isset($e['zkteco_uid']) && stripos((string)$e['zkteco_uid'], $search) !== false) || (isset($e['nuptk']) && stripos($e['nuptk'], $search) !== false);
            });
        }

        $employeesCollection = $rawEmployees->values();

        // Load dependencies
        $holidays = \App\Models\Holiday::with('adjustments')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereHas('adjustments', function ($q2) use ($startDate, $endDate) {
                      $q2->whereBetween('adjusted_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                  });
            })->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        $leavesData = \App\Models\LeaveRequest::where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        })->where('status', 'approved')->get();

        $leaves = [];
        foreach ($leavesData as $l) {
            $key = $l->school_unit_id . '_' . $l->employee_id;
            $leaves[$key][] = $l;
        }

        $shiftsData = \App\Models\EmployeeWorkingShift::with('workingShift.details')
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where(function($sq) use ($startDate) {
                      $sq->whereNull('end_date')
                         ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                  });
            })->get();

        $assignedShifts = [];
        foreach ($shiftsData as $s) {
            $key = $s->school_unit_id . '_' . $s->employee_id;
            $assignedShifts[$key][] = $s;
        }

        $logsData = \App\Models\AttendanceLog::whereBetween('timestamp', [
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d 23:59:59')
        ])->get();

        $attendanceLogs = [];
        foreach ($logsData as $log) {
            $date = substr($log->timestamp, 0, 10);
            $key = $log->uid . '_' . $date;
            $attendanceLogs[$key][] = $log;
        }

        $reports = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $dailyDetails = [];
            
            $lastDay = $endDate > now() ? now()->endOfDay() : $endDate;
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeekIso;

                if (in_array($dateStr, $holidayDates)) {
                    $dailyDetails[$dateStr] = ['status' => 'Libur'];
                    $currentDate->addDay();
                    continue;
                }

                $isOnLeave = false;
                $leaveType = 'IZIN'; // Default
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            $leaveType = strtoupper($leave->type ?? 'IZIN');
                            break;
                        }
                    }
                }
                if ($isOnLeave) {
                    // Limit text length for small cells
                    $displayType = strlen($leaveType) > 6 ? substr($leaveType, 0, 5) . '.' : $leaveType;
                    $dailyDetails[$dateStr] = ['status' => 'Cuti/Izin', 'leave_type' => $displayType];
                    $currentDate->addDay();
                    continue;
                }

                $hasShiftToday = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftKey = $unit . '_' . $empId;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail && !$detail->is_off) {
                                $hasShiftToday = true;
                                $shiftStartTime = $detail->start_time;
                                $shiftEndTime = $detail->end_time;
                            }
                            break;
                        }
                    }
                }

                if ($hasShiftToday) {
                    $logKey = $uid . '_' . $dateStr;
                    if (isset($attendanceLogs[$logKey])) {
                        $logsForDay = collect($attendanceLogs[$logKey])->sortBy('timestamp')->values();
                        $firstCheckIn = $logsForDay->first();
                        $lastCheckOut = $logsForDay->last();
                        
                        $checkInTime = substr($firstCheckIn->timestamp, 11, 5);
                        $checkOutTime = null;
                        
                        $isLate = false;
                        if ($shiftStartTime) {
                            $checkInCarbon = \Carbon\Carbon::parse($firstCheckIn->timestamp);
                            $expectedStart = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime);
                            if ($checkInCarbon > $expectedStart) {
                                $isLate = true;
                            }
                        }

                        if ($logsForDay->count() > 1) {
                            $firstCarbon = \Carbon\Carbon::parse($firstCheckIn->timestamp);
                            $lastCarbon = \Carbon\Carbon::parse($lastCheckOut->timestamp);
                            
                            // Valid check-out if more than 3 hours diff OR it's after 12:00 PM
                            if ($firstCarbon->diffInHours($lastCarbon) >= 3 || (int)$lastCarbon->format('H') >= 12) {
                                $checkOutTime = substr($lastCheckOut->timestamp, 11, 5);
                            }
                        }
                        
                        $dailyDetails[$dateStr] = [
                            'status' => 'Hadir',
                            'check_in' => $checkInTime,
                            'check_out' => $checkOutTime,
                            'is_late' => $isLate
                        ];
                    } else {
                        $dailyDetails[$dateStr] = ['status' => 'Alfa'];
                    }
                }

                $currentDate->addDay();
            }

            $reports[] = [
                'employee' => $emp,
                'daily_details' => $dailyDetails,
            ];
        }

        // Paginator
        $total = count($reports);
        $perPageReq = $request->query('per_page', 15);
        $perPage = $perPageReq === 'all' ? ($total > 0 ? $total : 1) : (int) $perPageReq;
        $page = $request->query('page', 1);
        
        $paginatedReports = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($reports, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $schoolUnits = \App\Models\SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');

        return view('attendance-logs.index', compact('paginatedReports', 'schoolUnits', 'month', 'startDateReq', 'endDateReq'));
    }

        public function export(Request $request)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);
        
        $month = $request->query('month', now()->format('Y-m'));
        $cutoffDate = (int) \App\Models\Setting::get('payroll_cutoff_date', 26);
        $monthCarbon = \Carbon\Carbon::createFromFormat('Y-m', $month);
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $rawEmployees = collect($this->service->getSdEmployees());

        $search = $request->query('search');
        $unitId = $request->query('unit_id');

        if (!empty($unitId)) {
            $rawEmployees = $rawEmployees->filter(fn($e) => isset($e['unit_id']) && $e['unit_id'] == $unitId);
        }
        if (!empty($search)) {
            $rawEmployees = $rawEmployees->filter(function($e) use ($search) {
                return stripos($e['name'], $search) !== false || (isset($e['zkteco_uid']) && stripos((string)$e['zkteco_uid'], $search) !== false) || (isset($e['nuptk']) && stripos($e['nuptk'], $search) !== false);
            });
        }

        $employeesCollection = $rawEmployees->values();

        $holidays = \App\Models\Holiday::with('adjustments')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereHas('adjustments', function ($q2) use ($startDate, $endDate) {
                      $q2->whereBetween('adjusted_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                  });
            })->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        $leavesData = \App\Models\LeaveRequest::where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate]);
        })->where('status', 'approved')->get();

        $leaves = [];
        foreach ($leavesData as $l) {
            $key = $l->school_unit_id . '_' . $l->employee_id;
            $leaves[$key][] = $l;
        }

        $shiftsData = \App\Models\EmployeeWorkingShift::with('workingShift.details')
            ->where(function($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate->format('Y-m-d'))
                  ->where(function($sq) use ($startDate) {
                      $sq->whereNull('end_date')
                         ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                  });
            })->get();

        $assignedShifts = [];
        foreach ($shiftsData as $s) {
            $key = $s->school_unit_id . '_' . $s->employee_id;
            $assignedShifts[$key][] = $s;
        }

        $logsData = \App\Models\AttendanceLog::whereBetween('timestamp', [
            $startDate->format('Y-m-d 00:00:00'),
            $endDate->format('Y-m-d 23:59:59')
        ])->get();

        $attendanceLogs = [];
        foreach ($logsData as $log) {
            $date = substr($log->timestamp, 0, 10);
            $key = $log->uid . '_' . $date;
            $attendanceLogs[$key][] = $log;
        }

        $reports = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $dailyDetails = [];
            
            $lastDay = $endDate > now() ? now()->endOfDay() : $endDate;
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeekIso;

                if (in_array($dateStr, $holidayDates)) {
                    $dailyDetails[$dateStr] = ['status' => 'Libur'];
                    $currentDate->addDay();
                    continue;
                }

                $isOnLeave = false;
                $leaveType = 'IZIN';
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            $leaveType = strtoupper($leave->type ?? 'IZIN');
                            break;
                        }
                    }
                }
                if ($isOnLeave) {
                    $displayType = strlen($leaveType) > 6 ? substr($leaveType, 0, 5) . '.' : $leaveType;
                    $dailyDetails[$dateStr] = ['status' => 'Cuti/Izin', 'leave_type' => $displayType];
                    $currentDate->addDay();
                    continue;
                }

                $hasShiftToday = false;
                $shiftStartTime = null;
                $shiftKey = $unit . '_' . $empId;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail && !$detail->is_off) {
                                $hasShiftToday = true;
                                $shiftStartTime = $detail->start_time;
                            }
                            break;
                        }
                    }
                }

                if ($hasShiftToday) {
                    $logKey = $uid . '_' . $dateStr;
                    if (isset($attendanceLogs[$logKey])) {
                        $logsForDay = collect($attendanceLogs[$logKey])->sortBy('timestamp')->values();
                        $firstCheckIn = $logsForDay->first();
                        $lastCheckOut = $logsForDay->last();
                        
                        $checkInTime = substr($firstCheckIn->timestamp, 11, 5);
                        $checkOutTime = null;
                        
                        if ($logsForDay->count() > 1) {
                            $firstCarbon = \Carbon\Carbon::parse($firstCheckIn->timestamp);
                            $lastCarbon = \Carbon\Carbon::parse($lastCheckOut->timestamp);
                            if ($firstCarbon->diffInHours($lastCarbon) >= 3 || (int)$lastCarbon->format('H') >= 12) {
                                $checkOutTime = substr($lastCheckOut->timestamp, 11, 5);
                            }
                        }
                        
                        $dailyDetails[$dateStr] = [
                            'status' => 'Hadir',
                            'check_in' => $checkInTime,
                            'check_out' => $checkOutTime
                        ];
                    } else {
                        $dailyDetails[$dateStr] = ['status' => 'Alfa'];
                    }
                }

                $currentDate->addDay();
            }

            $reports[] = [
                'employee' => $emp,
                'daily_details' => $dailyDetails,
            ];
        }

        $format = $request->query('format', 'excel');
        
        $periodeStr = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');
        $unitName = "Semua_Unit";
        if (!empty($unitId)) {
            $schoolUnit = \App\Models\SchoolUnit::find($unitId);
            if ($schoolUnit) $unitName = str_replace(' ', '_', $schoolUnit->name);
        }
        $searchStr = !empty($search) ? '_Pencarian_' . preg_replace('/[^A-Za-z0-9]/', '', $search) : '';
        $baseFileName = 'Matriks_Absensi_' . $unitName . '_' . $month . $searchStr;
        
        $start = $startDate->copy();
        $end = clone $endDate;
        $dates = [];
        while($start <= $end) {
            $dates[] = $start->copy();
            $start->addDay();
        }

        if ($format === 'pdf') {
            ini_set('memory_limit', '512M');
            set_time_limit(300);
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance-logs.export-pdf', compact('reports', 'periodeStr', 'unitId', 'dates'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($baseFileName . ".pdf");
        }

        // Generate Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriks Absensi');

        // Headers
        $sheet->setCellValue('A1', 'NO');
        $sheet->setCellValue('B1', 'NAMA PEGAWAI');
        
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        
        $colIndex = 3; // C
        foreach ($dates as $dateObj) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . '1', $dateObj->format('d/M'));
            $sheet->getColumnDimension($colLetter)->setWidth(12);
            $colIndex++;
        }

        // Header Styling
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);
        $headerRange = 'A1:' . $lastColLetter . '1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFD9E1F2');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal('left');

        // Freeze pane
        $sheet->freezePane('C2');

        // Populate Data
        $row = 2;
        $no = 1;
        foreach ($reports as $report) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $report['employee']['name'] ?? '-');
            
            $colIndex = 3;
            foreach ($dates as $date) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $dateStr = $date->format('Y-m-d');
                $detail = $report['daily_details'][$dateStr] ?? null;
                
                $cellValue = '-';
                if ($detail) {
                    if ($detail['status'] === 'Hadir') {
                        $in = $detail['check_in'] ?? '-';
                        $out = $detail['check_out'] ?? '-';
                        $cellValue = $in . "\n" . $out;
                        // Center align and wrap text
                        $sheet->getStyle($colLetter . $row)->getAlignment()
                              ->setWrapText(true)
                              ->setHorizontal('center')
                              ->setVertical('center');
                    } elseif ($detail['status'] === 'Alfa') {
                        $cellValue = 'A';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                    } elseif ($detail['status'] === 'Cuti/Izin') {
                        $cellValue = $detail['leave_type'] ?? 'IZIN';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE));
                    } elseif ($detail['status'] === 'Libur') {
                        $cellValue = 'L';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF9CA3AF'));
                    }
                } else {
                    if ($date->isWeekend()) {
                        $cellValue = 'L';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF9CA3AF'));
                    }
                }
                
                $sheet->setCellValue($colLetter . $row, $cellValue);
                if ($cellValue === 'A' || $cellValue === 'L' || $cellValue === '-' || $detail['status'] ?? '' === 'Cuti/Izin') {
                    $sheet->getStyle($colLetter . $row)->getAlignment()->setHorizontal('center')->setVertical('center');
                }
                $colIndex++;
            }
            $row++;
        }

        // Borders
        $dataRange = 'A1:' . $lastColLetter . ($row - 1);
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $filename = $baseFileName . '.xlsx';
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $responseHeaders = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, $responseHeaders);
    }

    public function clear()
    {
        AttendanceLog::truncate();
        return redirect()->route('attendance-logs.index')->with('success', 'Semua log absensi berhasil dikosongkan.');
    }
}
