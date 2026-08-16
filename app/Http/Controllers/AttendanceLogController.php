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

    private function generateReportsData($startDate, $endDate, $search = null, $unitId = null, $position = null)
    {
        $rawEmployees = collect($this->service->getAllEmployees());

        if (!empty($unitId)) {
            $rawEmployees = $rawEmployees->filter(fn($e) => isset($e['unit_id']) && $e['unit_id'] == $unitId);
        }
        if (!empty($search)) {
            $rawEmployees = $rawEmployees->filter(function($e) use ($search) {
                return stripos($e['name'], $search) !== false 
                    || (isset($e['zkteco_uid']) && stripos((string)$e['zkteco_uid'], $search) !== false) 
                    || (isset($e['nuptk']) && stripos($e['nuptk'], $search) !== false);
            });
        }
        if (!empty($position)) {
            $rawEmployees = $rawEmployees->filter(function($e) use ($position) {
                $empPos = $e['position'] ?? $e['subject_position'] ?? '';
                return $empPos == $position;
            });
        }
        $employeesCollection = $rawEmployees->values();

        return $this->generateReportsDataForCollection($startDate, $endDate, $employeesCollection);
    }

    private function generateReportsDataForCollection($startDate, $endDate, $employeesCollection)
    {
        $uids = $employeesCollection->pluck('zkteco_uid')->filter()->toArray();
        $employeeIds = $employeesCollection->pluck('id')->filter()->toArray();

        $holidays = \App\Models\Holiday::with('adjustments')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereHas('adjustments', function ($q2) use ($startDate, $endDate) {
                      $q2->whereBetween('adjusted_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                  });
            })->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        $leavesData = \App\Models\LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            })->get();

        $leaves = [];
        foreach ($leavesData as $l) {
            $key = $l->school_unit_id . '_' . $l->employee_id;
            $leaves[$key][] = $l;
        }

        foreach ($leaves as $key => $empLeaves) {
            usort($leaves[$key], function($a, $b) {
                $statusOrder = ['Approved' => 1, 'Pending' => 2, 'Rejected' => 3];
                $orderA = $statusOrder[$a->status] ?? 4;
                $orderB = $statusOrder[$b->status] ?? 4;
                return $orderA <=> $orderB;
            });
        }

        $shiftsData = \App\Models\EmployeeWorkingShift::with('workingShift.details')
            ->whereIn('employee_id', $employeeIds)
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
        foreach ($assignedShifts as $key => &$shifts) {
            usort($shifts, function($a, $b) {
                $scoreA = ($a->roster_name !== null && $a->roster_name !== '') ? 3 : ($a->end_date !== null ? 2 : 1);
                $scoreB = ($b->roster_name !== null && $b->roster_name !== '') ? 3 : ($b->end_date !== null ? 2 : 1);
                
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA; // Descending
                }
                
                $dateA = $a->start_date instanceof \Carbon\Carbon ? $a->start_date->format('Y-m-d') : substr($a->start_date, 0, 10);
                $dateB = $b->start_date instanceof \Carbon\Carbon ? $b->start_date->format('Y-m-d') : substr($b->start_date, 0, 10);
                if ($dateA !== $dateB) {
                    return strcmp($dateB, $dateA); // Descending
                }
                
                return $b->id <=> $a->id; // Descending
            });
        }

        // Fetch logs up to next day noon to catch night shift clock outs
        $logsData = \App\Models\AttendanceLog::whereIn('uid', $uids)
            ->whereBetween('timestamp', [
                $startDate->format('Y-m-d 00:00:00'),
                $endDate->copy()->addDay()->format('Y-m-d 12:00:00')
            ])->get();

        $attendanceLogs = [];
        foreach ($logsData as $log) {
            $attendanceLogs[(string)$log->uid][] = $log->timestamp;
        }
        foreach ($attendanceLogs as &$ulogs) {
            sort($ulogs);
        }

        return $this->processAttendanceReport($startDate, $endDate, $employeesCollection, $holidayDates, $leaves, $assignedShifts, $attendanceLogs);
    }

    private function processAttendanceReport($startDate, $endDate, $employeesCollection, $holidayDates, $leaves, $assignedShifts, $attendanceLogs)
    {
        $reports = [];
        $lastDay = clone $endDate;
        if ($endDate > now()) {
            $lastDay = now()->endOfDay();
        }

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;
            $userLogs = $attendanceLogs[(string)$uid] ?? [];

            $dailyDetails = [];
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 0 (Sun) to 6 (Sat)

                if (in_array($dateStr, $holidayDates)) {
                    $dailyDetails[$dateStr] = ['status' => 'Libur'];
                    $currentDate->addDay();
                    continue;
                }

                $isOnLeave = false;
                $leaveCode = 'I';
                $originalType = 'Izin';
                $hasRequiresAttendanceLeave = false;
                $activeLeave = null;
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            $leaveCode = $leave->status_code ?? 'I';
                            $originalType = $leave->type_name;
                            $hasRequiresAttendanceLeave = (bool) $leave->requires_attendance;
                            $activeLeave = $leave;
                            break;
                        }
                    }
                }
                $hasShiftToday = false;
                $isOffShift = false;
                $shiftStartTime = null;
                $shiftEndTime = null;
                $shiftKey = $unit . '_' . $empId;
                
                $isShiftWorker = false;
                $checkInLog = null;
                $checkOutLog = null;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        if ($assignment->workingShift->is_shift) {
                            $isShiftWorker = true;
                        }
                        
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            $detail = $assignment->workingShift->details->where('day_of_week', $dayOfWeek)->first();
                            if ($detail) {
                                if ($detail->is_off) {
                                    $isOffShift = true;
                                } else {
                                    $hasShiftToday = true;
                                    $shiftStartTime = $detail->start_time;
                                    $shiftEndTime = $detail->end_time;
                                }
                            }
                            break;
                        }
                    }
                }
                
                if ($isShiftWorker && !$hasShiftToday && !$isOffShift) {
                    $isOffShift = true;
                }

                if ($hasShiftToday) {
                    $isNightShift = $shiftStartTime > $shiftEndTime;
                    $expectedIn = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime);
                    $expectedOut = \Carbon\Carbon::parse($dateStr . ' ' . $shiftEndTime);
                    if ($isNightShift) {
                        $expectedOut->addDay();
                    }

                    $inStart = $expectedIn->copy()->subHours(6);
                    $inEnd = $expectedIn->copy()->addHours(6);
                    $outStart = $expectedOut->copy()->subHours(6);
                    $outEnd = $expectedOut->copy()->addHours(6);

                    $checkInLog = null;
                    $checkOutLog = null;

                    foreach ($userLogs as $tsStr) {
                        $ts = \Carbon\Carbon::parse($tsStr);
                        if ($ts->between($inStart, $inEnd)) {
                            if (!$checkInLog || $ts < \Carbon\Carbon::parse($checkInLog)) {
                                $checkInLog = $tsStr;
                            }
                        }
                        if ($ts->between($outStart, $outEnd)) {
                            if (!$checkOutLog || $ts > \Carbon\Carbon::parse($checkOutLog)) {
                                $checkOutLog = $tsStr;
                            }
                        }
                    }

                    if ($checkInLog || $checkOutLog) {
                        $isLate = false;
                        if ($checkInLog) {
                            if (\Carbon\Carbon::parse($checkInLog)->second(0) > $expectedIn->second(0)) {
                                $isLate = true;
                            }
                        }

                        if ($checkInLog && $checkOutLog && $checkInLog === $checkOutLog) {
                            $diffIn = \Carbon\Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                            $diffOut = \Carbon\Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                            if ($diffIn < $diffOut) {
                                $checkOutLog = null;
                            } else {
                                $checkInLog = null;
                            }
                        }

                        if ($checkInLog && $checkOutLog && $checkInLog !== $checkOutLog) {
                            if (\Carbon\Carbon::parse($checkInLog)->diffInHours(\Carbon\Carbon::parse($checkOutLog)) < 2) {
                                $diffIn = \Carbon\Carbon::parse($checkInLog)->diffInMinutes($expectedIn);
                                $diffOut = \Carbon\Carbon::parse($checkOutLog)->diffInMinutes($expectedOut);
                                if ($diffIn < $diffOut) {
                                    $checkOutLog = null;
                                } else {
                                    $checkInLog = null;
                                }
                            }
                        }

                        $dailyDetails[$dateStr] = [
                            'status' => 'Hadir',
                            'check_in' => $checkInLog ? substr($checkInLog, 11, 5) : null,
                            'check_out' => $checkOutLog ? substr($checkOutLog, 11, 5) : null,
                            'is_late' => $isLate
                        ];
                    } else {
                        // Determine if it is actually Alfa or if the shift hasn't started yet
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $shiftStartDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                        
                        if ($now->lessThan($shiftStartDateTime)) {
                            $dailyDetails[$dateStr] = ['status' => 'Pending'];
                        } else {
                            $dailyDetails[$dateStr] = ['status' => 'Alfa'];
                        }
                    }
                } elseif ($isOffShift) {
                    $dailyDetails[$dateStr] = ['status' => 'Off'];
                } else {
                    if ($dayOfWeek == 0) { // Sunday
                        $dailyDetails[$dateStr] = ['status' => 'Libur'];
                    }
                }

                if ($isOnLeave && $activeLeave) {
                    // Opsi A: Jangan timpa status jika hari ini adalah hari libur terjadwal (Sunday tanpa shift atau off-shift)
                    $isRestDay = $isOffShift || ($dayOfWeek == 0 && !$hasShiftToday);

                    if (!$isRestDay && ($activeLeave->status === 'Approved' || $activeLeave->status === 'Pending')) {
                        $isLateForLeave = false;
                        if ($activeLeave->status === 'Approved') {
                            if ($hasRequiresAttendanceLeave && ($activeLeave->gets_presence_bonus || $activeLeave->status_code === 'H')) {
                                $isLateForLeave = $isLate ?? false;
                            }
                        } else {
                            $isLateForLeave = $isLate ?? false;
                        }

                        $dailyDetails[$dateStr] = [
                            'status' => 'Cuti/Izin',
                            'is_pending' => ($activeLeave->status === 'Pending'),
                            'leave_code' => $leaveCode,
                            'leave_type' => $originalType,
                            'check_in' => $checkInLog ? substr($checkInLog, 11, 5) : null,
                            'check_out' => $checkOutLog ? substr($checkOutLog, 11, 5) : null,
                            'is_late' => $isLateForLeave,
                        ];
                    } elseif ($activeLeave->status === 'Rejected') {
                        $dailyDetails[$dateStr]['rejected_leave'] = [
                            'leave_code' => $leaveCode,
                            'leave_type' => $originalType,
                        ];
                    }
                }

                $currentDate->addDay();
            }

            $reports[] = [
                'employee' => $emp,
                'daily_details' => $dailyDetails,
            ];
        }

        return $reports;
    }

    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $cutoffDate = (int) \App\Models\Setting::get('payroll_cutoff_date', 26);
        $monthCarbon = \Carbon\Carbon::createFromFormat('Y-m', $month);
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $search = $request->query('search');
        $unitId = $request->query('unit_id');
        $position = $request->query('position');

        // Extract unique positions (jabatan) from raw employee data using the renamed service method, filtered by unit if specified
        $rawEmployeesForPos = $this->service->getAllEmployees();
        $filteredEmployeesForPos = collect($rawEmployeesForPos);
        if (!empty($unitId)) {
            $filteredEmployeesForPos = $filteredEmployeesForPos->filter(fn($e) => isset($e['unit_id']) && $e['unit_id'] == $unitId);
        }

        $positions = $filteredEmployeesForPos
            ->map(function ($emp) {
                return $emp['position'] ?? $emp['subject_position'] ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // 1. Filter the entire employee list first
        $rawEmployees = collect($rawEmployeesForPos);

        if (!empty($unitId)) {
            $rawEmployees = $rawEmployees->filter(fn($e) => isset($e['unit_id']) && $e['unit_id'] == $unitId);
        }
        if (!empty($search)) {
            $rawEmployees = $rawEmployees->filter(function($e) use ($search) {
                return stripos($e['name'], $search) !== false 
                    || (isset($e['zkteco_uid']) && stripos((string)$e['zkteco_uid'], $search) !== false) 
                    || (isset($e['nuptk']) && stripos($e['nuptk'], $search) !== false);
            });
        }
        if (!empty($position)) {
            $rawEmployees = $rawEmployees->filter(function($e) use ($position) {
                $empPos = $e['position'] ?? $e['subject_position'] ?? '';
                return $empPos == $position;
            });
        }
        $employeesCollection = $rawEmployees->values();
        $total = $employeesCollection->count();

        // 2. Paginate employees list first
        $perPageReq = $request->query('per_page', 50);
        $perPage = $perPageReq === 'all' ? ($total > 0 ? $total : 1) : (int) $perPageReq;
        $page = (int) $request->query('page', 1);
        
        $paginatedEmployees = $employeesCollection->slice(($page - 1) * $perPage, $perPage)->values();

        // 3. ONLY run attendance matrix reports for the paginated subset of employees!
        $reports = $this->generateReportsDataForCollection($startDate, $endDate, $paginatedEmployees);

        $paginatedReports = new \Illuminate\Pagination\LengthAwarePaginator(
            $reports,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $schoolUnits = \App\Models\SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');

        return view('attendance-logs.index', compact('paginatedReports', 'schoolUnits', 'month', 'startDateReq', 'endDateReq', 'positions'));
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

        $search = $request->query('search');
        $unitId = $request->query('unit_id');
        $position = $request->query('position');

        $reports = $this->generateReportsData($startDate, $endDate, $search, $unitId, $position);

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
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('attendance-logs.export-pdf', compact('reports', 'periodeStr', 'unitId', 'dates'))
                ->setPaper('a4', 'landscape');
            $response = $pdf->download($baseFileName . ".pdf");
            if ($request->filled('download_token')) {
                $response->headers->setCookie(cookie('download_token', $request->query('download_token'), 1, '/', null, false, false));
            }
            return $response;
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
            $sheet->setCellValue($colLetter . '1', $dateObj->translatedFormat('D') . ", " . $dateObj->format('d/M'));
            $sheet->getColumnDimension($colLetter)->setWidth(12);
            if ($dateObj->isSunday()) {
                $sheet->getStyle($colLetter . '1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
            }
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
                    $sheet->getStyle($colLetter . $row)->getAlignment()->setWrapText(true)->setHorizontal('center')->setVertical('center');
                    
                    if ($detail['status'] === 'Hadir') {
                        $in = $detail['check_in'] ?? '-';
                        $out = $detail['check_out'] ?? '-';
                        if (!empty($detail['pending_leave'])) {
                            $cellValue = $in . "\n" . $detail['pending_leave']['leave_code'] . "\n" . $out;
                        } else {
                            $cellValue = $in . "\n" . $out;
                        }
                    } elseif ($detail['status'] === 'Alfa') {
                        $cellValue = 'A';
                        if (!empty($detail['pending_leave'])) {
                            $cellValue = "A\n" . $detail['pending_leave']['leave_code'];
                        }
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                    } elseif ($detail['status'] === 'Cuti/Izin') {
                        $leaveCode = $detail['leave_code'] ?? 'I';
                        $isPending = !empty($detail['is_pending']);
                        $in = $detail['check_in'] ?? null;
                        $out = $detail['check_out'] ?? null;
                        
                        if ($in || $out) {
                            $cellValue = ($in ?: '-') . "\n" . $leaveCode . ($isPending ? ' (P)' : '') . "\n" . ($out ?: '-');
                        } else {
                            $cellValue = $leaveCode . ($isPending ? ' (P)' : '');
                        }
                        
                        $excelColorMap = [
                            'S' => 'FFE28743', // Warm Amber
                            'I' => 'FF8A2BE2', // Purple
                            'C' => 'FF1F75FE', // Blue
                            'H' => 'FF10B981', // Emerald/Green
                        ];
                        $colorHex = $excelColorMap[$leaveCode] ?? 'FF1F75FE';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($colorHex));
                        if ($isPending) {
                            $sheet->getStyle($colLetter . $row)->getFont()->setItalic(true);
                        }
                    } elseif ($detail['status'] === 'Libur') {
                        $cellValue = '-';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
                    } elseif ($detail['status'] === 'Off') {
                        $cellValue = 'OFF';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF9CA3AF'));
                    }
                } else {
                    if ($date->isSunday()) {
                        $cellValue = '-';
                        $sheet->getStyle($colLetter . $row)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
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

        $response = response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, $responseHeaders);

        if ($request->filled('download_token')) {
            $response->headers->setCookie(cookie('download_token', $request->query('download_token'), 1, '/', null, false, false));
        }

        return $response;
    }

    public function clear()
    {
        AttendanceLog::truncate();
        return redirect()->route('attendance-logs.index')->with('success', 'Semua log absensi berhasil dikosongkan.');
    }
}
