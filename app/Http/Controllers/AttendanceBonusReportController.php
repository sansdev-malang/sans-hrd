<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\BonusSchema;
use App\Models\EmployeeWorkingShift;
use App\Models\Setting;
use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceBonusReportController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        $unitId = $request->query('unit_id');

        // Fetch cutoff date from settings, default to 26
        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 26);

        // Determine Start and End dates based on the selected month and cutoff date
        // E.g. month = '2026-07', cutoff = 26
        // endDate = 2026-07-26
        // startDate = 2026-06-27
        
        $monthCarbon = Carbon::createFromFormat('Y-m', $month);
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $startDateReq = $startDate->format('Y-m-d');
        $endDateReq = $endDate->format('Y-m-d');

        // 2. Fetch Active Global Bonus Schema
        $activeSchema = BonusSchema::with('tiers')
            ->where('is_active', true)
            ->first();

        // 3. Fetch Employees (Filter by unit if needed)
        $rawEmployees = $this->service->getSdEmployees();
        $employeesCollection = collect($rawEmployees);

        if (!empty($unitId)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                return ($emp['unit_id'] ?? '') == $unitId;
            });
        }

        // Apply search filter
        $search = $request->query('search');
        if (!empty($search)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search));
            });
        }

        // 4. Pre-fetch related data to avoid N+1 query problem during calculations
        $uids = $employeesCollection->pluck('zkteco_uid')->filter()->toArray();
        $employeeIds = $employeesCollection->pluck('id')->filter()->toArray();

        // Pre-fetch Attendance Logs for this month
        $attendanceLogs = AttendanceLog::whereIn('uid', $uids)
            ->whereBetween('timestamp', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
            ->get()
            ->groupBy(function($log) {
                return $log->uid . '_' . Carbon::parse($log->timestamp)->format('Y-m-d');
            });

        // Pre-fetch Assigned Shifts
        $assignedShifts = EmployeeWorkingShift::with(['workingShift.details'])
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where(function($q) use ($startDate) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                      });
            })
            ->get()
            ->groupBy(function($shift) {
                return $shift->school_unit_id . '_' . $shift->employee_id;
            });

        // Pre-fetch Holidays
        $holidays = \App\Models\Holiday::with('adjustments')
            ->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        // Pre-fetch Leave Requests (Approved)
        $leaves = \App\Models\LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->groupBy(function($leave) {
                return $leave->school_unit_id . '_' . $leave->employee_id;
            });

        // 5. Calculate Attendance Report
        $reports = [];

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

            $totalPresent = 0;
            $totalLateMinutes = 0;
            $totalAbsent = 0;
            $totalBonusNominal = 0;
            $dailyDetails = [];

            // Loop through each day of the month (up to today if in current month)
            $lastDay = $endDate > now() ? now()->endOfDay() : $endDate;
            
            $currentDate = $startDate->copy();
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek; // 1 (Mon) - 7 (Sun)

                // Skip Holidays
                if (in_array($dateStr, $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                // Skip Leaves
                $isOnLeave = false;
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            break;
                        }
                    }
                }
                if ($isOnLeave) {
                    $currentDate->addDay();
                    continue;
                }

                // Check Shift Assignment
                $hasShiftToday = false;
                $shiftStartTime = null;
                $shiftKey = $unit . '_' . $empId;

                if (isset($assignedShifts[$shiftKey])) {
                    foreach ($assignedShifts[$shiftKey] as $assignment) {
                        $assignStartDate = substr($assignment->start_date, 0, 10);
                        $assignEndDate = $assignment->end_date ? substr($assignment->end_date, 0, 10) : null;
                        if ($dateStr >= $assignStartDate && (!$assignEndDate || $dateStr <= $assignEndDate)) {
                            // Find detail for this day of week
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
                    $dailyLateMinutes = 0;
                    $dailyStatus = 'Absent';
                    $dailyCheckIn = null;
                    $dailyBonus = 0;
                    $dailyTierLevel = null;

                    // Check Attendance
                    $logKey = $uid . '_' . $dateStr;
                    if (isset($attendanceLogs[$logKey])) {
                        $dailyStatus = 'Present';
                        $totalPresent++;
                        
                        // Calculate Late
                        $firstCheckIn = collect($attendanceLogs[$logKey])->sortBy('timestamp')->first();
                        $checkInCarbon = Carbon::parse($firstCheckIn->timestamp);
                        $expectedStart = Carbon::parse($dateStr . ' ' . $shiftStartTime);
                        $dailyCheckIn = $checkInCarbon->format('H:i:s');

                        if ($checkInCarbon > $expectedStart) {
                            $diff = (int) $expectedStart->diffInMinutes($checkInCarbon);
                            $dailyLateMinutes = $diff;
                            $totalLateMinutes += $diff;
                        }

                        // Calculate Daily Bonus
                        if ($activeSchema && $activeSchema->tiers->count() > 0) {
                            $qualifyingTiers = $activeSchema->tiers->filter(function($tier) use ($dailyLateMinutes) {
                                return $dailyLateMinutes <= $tier->max_late_minutes;
                            })->sortByDesc('nominal');

                            if ($qualifyingTiers->count() > 0) {
                                $bestTier = $qualifyingTiers->first();
                                $dailyBonus = $bestTier->nominal;
                                $dailyTierLevel = $bestTier->tier_level;
                            }
                        }

                        $totalBonusNominal += $dailyBonus;
                    } else {
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $shiftStartDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                        
                        if ($now->lessThan($shiftStartDateTime)) {
                            $dailyStatus = 'Pending';
                        } else {
                            $totalAbsent++;
                        }
                    }

                    $dailyDetails[$dateStr] = [
                        'date' => $dateStr,
                        'shift_start' => $shiftStartTime,
                        'check_in' => $dailyCheckIn,
                        'late_minutes' => $dailyLateMinutes,
                        'status' => $dailyStatus,
                        'bonus_nominal' => $dailyBonus,
                        'tier_level' => $dailyTierLevel
                    ];
                }

                $currentDate->addDay();
            }

            $reports[] = [
                'employee' => $emp,
                'total_present' => $totalPresent,
                'total_late_minutes' => $totalLateMinutes,
                'total_absent' => $totalAbsent,
                'bonus_nominal' => $totalBonusNominal,
                'daily_details' => $dailyDetails ?? [],
                'daily_details' => $dailyDetails,
            ];
        }

                // Convert array to a length-aware paginator for the view
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

        $totalSemuaBonus = collect($reports)->sum('bonus_nominal');
        $schoolUnits = SchoolUnit::where('is_active', true)->get();

        return view('bonus-reports.index', compact('paginatedReports', 'schoolUnits', 'activeSchema', 'month', 'startDateReq', 'endDateReq', 'cutoffDate', 'totalSemuaBonus'));
    }

        public function export(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $unitId = $request->query('unit_id');
        $format = $request->query('format', 'excel'); // 'excel' or 'pdf'

        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 26);
        $monthCarbon = Carbon::createFromFormat('Y-m', $month);
        
        $endDate = $monthCarbon->copy()->setDay($cutoffDate)->endOfDay();
        $startDate = $monthCarbon->copy()->subMonth()->setDay($cutoffDate + 1)->startOfDay();

        $activeSchema = BonusSchema::with('tiers')
            ->where('is_active', true)
            ->first();

        $rawEmployees = $this->service->getSdEmployees();
        $employeesCollection = collect($rawEmployees);

        if (!empty($unitId)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                return ($emp['unit_id'] ?? '') == $unitId;
            });
        }
        
        $search = $request->query('search');
        if (!empty($search)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($search) {
                return stripos($emp['name'], $search) !== false || stripos($emp['nuptk'] ?? '', $search) !== false;
            });
        }

        if ($employeesCollection->isEmpty()) {
            return back()->with('error', 'Tidak ada data pegawai untuk diekspor pada filter ini.');
        }

        $employeeIds = $employeesCollection->pluck('id')->toArray();
        $uids = $employeesCollection->pluck('zkteco_uid')->filter()->toArray();

        $attendanceLogs = collect();
        if (!empty($uids)) {
            $attendanceLogs = \App\Models\AttendanceLog::whereIn('uid', $uids)
                ->whereBetween('timestamp', [$startDate, $endDate])
                ->get()
                ->groupBy(function($log) {
                    return $log->uid . '_' . \Carbon\Carbon::parse($log->timestamp)->format('Y-m-d');
                });
        }

        $assignedShifts = \App\Models\EmployeeWorkingShift::with(['workingShift', 'workingShift.details'])
            ->whereIn('employee_id', $employeeIds)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where(function($q) use ($startDate) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', $startDate->format('Y-m-d'));
                      });
            })
            ->get()
            ->groupBy(function($shift) {
                return $shift->school_unit_id . '_' . $shift->employee_id;
            });

        $holidays = \App\Models\Holiday::with('adjustments')
            ->whereBetween('original_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
        $holidayDates = $holidays->pluck('original_date')->toArray();

        $leaves = \App\Models\LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'Approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate->format('Y-m-d'))
                      ->where('end_date', '>=', $startDate->format('Y-m-d'));
            })
            ->get()
            ->groupBy(function($leave) {
                return $leave->school_unit_id . '_' . $leave->employee_id;
            });

        $reports = [];
        $totalSemuaBonus = 0;

        foreach ($employeesCollection as $emp) {
            $uid = $emp['zkteco_uid'] ?? null;
            $empId = $emp['id'] ?? null;
            $unit = $emp['unit_id'] ?? null;

            if (!$uid || !$empId) continue;

                        $totalPresent = 0;
            $totalLateMinutes = 0;
            $totalAbsent = 0;
            $totalBonusNominal = 0;
            $dailyDetails = [];

            $lastDay = $endDate > now() ? now()->endOfDay() : $endDate;
            $currentDate = $startDate->copy();
            
            while ($currentDate <= $lastDay) {
                $dateStr = $currentDate->format('Y-m-d');
                $dayOfWeek = $currentDate->dayOfWeek;

                if (in_array($dateStr, $holidayDates)) {
                    $currentDate->addDay();
                    continue;
                }

                $isOnLeave = false;
                $leaveKey = $unit . '_' . $empId;
                if (isset($leaves[$leaveKey])) {
                    foreach ($leaves[$leaveKey] as $leave) {
                        $leaveStart = substr($leave->start_date, 0, 10);
                        $leaveEnd = substr($leave->end_date, 0, 10);
                        if ($dateStr >= $leaveStart && $dateStr <= $leaveEnd) {
                            $isOnLeave = true;
                            break;
                        }
                    }
                }
                if ($isOnLeave) {
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
                    $dailyLateMinutes = 0;
                    $dailyBonus = 0;
                    
                    $logKey = $uid . '_' . $dateStr;
                    if (isset($attendanceLogs[$logKey])) {
                        $totalPresent++;
                        
                        $firstCheckIn = collect($attendanceLogs[$logKey])->sortBy('timestamp')->first();
                        $checkInCarbon = \Carbon\Carbon::parse($firstCheckIn->timestamp);
                        $expectedStart = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime);

                        if ($checkInCarbon > $expectedStart) {
                            $diff = (int) $expectedStart->diffInMinutes($checkInCarbon);
                            $dailyLateMinutes = $diff;
                            $totalLateMinutes += $diff;
                        }

                        if ($activeSchema && $activeSchema->tiers->count() > 0) {
                            $qualifyingTiers = $activeSchema->tiers->filter(function($tier) use ($dailyLateMinutes) {
                                return $dailyLateMinutes <= $tier->max_late_minutes;
                            })->sortByDesc('nominal');

                            if ($qualifyingTiers->count() > 0) {
                                $bestTier = $qualifyingTiers->first();
                                $dailyBonus = $bestTier->nominal;
                            }
                        }
                        $totalBonusNominal += $dailyBonus;
                    } else {
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $shiftStartDateTime = \Carbon\Carbon::parse($dateStr . ' ' . $shiftStartTime, 'Asia/Jakarta');
                        
                        if ($now->lessThan($shiftStartDateTime)) {
                            // still pending
                        } else {
                            $totalAbsent++;
                        }
                    }

                    $dailyDetails[$dateStr] = [
                        'bonus_nominal' => $dailyBonus
                    ];
                }

                $currentDate->addDay();
            }

            $totalSemuaBonus += $totalBonusNominal;
            $reports[] = [
                'employee' => $emp,
                'total_present' => $totalPresent,
                'total_late_minutes' => $totalLateMinutes,
                'total_absent' => $totalAbsent,
                'bonus_nominal' => $totalBonusNominal,
                'daily_details' => $dailyDetails,
            ];
        }
        
        $periodeStr = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y');

                $dates = [];
        $start = $startDate->copy();
        while($start <= $endDate) {
            $dates[] = $start->copy();
            $start->addDay();
        }

                        $unitName = 'Semua_Unit';
        if ($unitId) {
            $unitObj = \App\Models\SchoolUnit::find($unitId);
            if ($unitObj) {
                $unitName = str_replace(' ', '_', $unitObj->name);
            }
        }
        
        $searchStr = '';
        $searchQuery = $request->query('search');
        if (!empty($searchQuery)) {
            $searchStr = '_Pencarian_' . preg_replace('/[^a-zA-Z0-9]+/', '_', $searchQuery);
        }
        
        $baseFileName = "Rekapan_Bonus_{$unitName}_{$month}{$searchStr}";

        if ($format === 'pdf') {
            ini_set('memory_limit', '512M');
            set_time_limit(300);
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('bonus-reports.export-pdf', compact('reports', 'periodeStr', 'totalSemuaBonus', 'unitId', 'dates'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($baseFileName . ".pdf");
        } 
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'NUPTK / NIP');
        $sheet->setCellValue('C1', 'Nama Pegawai');
        $sheet->setCellValue('D1', 'Unit');
        $sheet->setCellValue('E1', 'Total Bonus (Rp)');

        // Build dynamic date headers starting from column F (Index 6)
        $colIndex = 6;
        $hari = [0 => 'MIN', 1 => 'SEN', 2 => 'SEL', 3 => 'RAB', 4 => 'KAM', 5 => 'JUM', 6 => 'SAB'];
        foreach($dates as $date) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $dayName = $hari[$date->dayOfWeek];
            $sheet->setCellValue($colLetter . '1', $dayName . "\n" . $date->format('d/m'));
            if ($date->dayOfWeek == 0) {
                $sheet->getStyle($colLetter . '1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
            }
            $colIndex++;
        }

        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex - 1);

        $sheet->getStyle('A1:' . $lastColLetter . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastColLetter . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEFEFEF');
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle('A1:' . $lastColLetter . '1')->getAlignment()->setWrapText(true)
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
        $row = 2;
        $no = 1;
        foreach ($reports as $report) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValueExplicit('B' . $row, $report['employee']['nuptk'] ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $report['employee']['name']);
            $sheet->setCellValue('D' . $row, $report['employee']['unit']['name'] ?? ($report['employee']['unit_name'] ?? '-'));
            $sheet->setCellValue('E' . $row, $report['bonus_nominal']);
            
            $colIdx = 6;
            foreach($dates as $date) {
                $dateStr = $date->format('Y-m-d');
                $detail = $report['daily_details'][$dateStr] ?? null;
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                
                if ($detail) {
                    $sheet->setCellValue($colLetter . $row, $detail['bonus_nominal'] > 0 ? $detail['bonus_nominal'] : '-');
                } else {
                    $sheet->setCellValue($colLetter . $row, '-');
                }
                
                // Align center
                $sheet->getStyle($colLetter . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $colIdx++;
            }
            $row++;
        }
        
        $sheet->setCellValue('D' . $row, 'TOTAL:');
        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
        $sheet->setCellValue('E' . $row, $totalSemuaBonus);
        $sheet->getStyle('E' . $row)->getFont()->setBold(true);
        
        $sheet->getStyle('E2:E'.$row)->getNumberFormat()->setFormatCode('#,##0');
        
        foreach(range(1, $colIndex - 1) as $c) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $sheet->freezePane('F2');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = $baseFileName . ".xlsx";
        
        ob_end_clean(); // Make sure no whitespace is output before the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
