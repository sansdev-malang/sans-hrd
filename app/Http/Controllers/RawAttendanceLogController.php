<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\ZktecoDevice;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RawAttendanceLogController extends Controller
{
    protected $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $unitId = $request->query('unit_id');
        $position = $request->query('position');

        // Fetch employee mapping
        $employees = $this->service->getSdEmployees();

        // Build filtered employee list
        $filteredEmployees = collect($employees);
        if (!empty($unitId)) {
            $filteredEmployees = $filteredEmployees->filter(function($emp) use ($unitId) {
                return (string)($emp['unit_id'] ?? '') === (string)$unitId;
            });
        }
        if (!empty($position)) {
            $filteredEmployees = $filteredEmployees->filter(function($emp) use ($position) {
                $pos = $emp['position'] ?? $emp['subject_position'] ?? '';
                return $pos === $position;
            });
        }
        if (!empty($search)) {
            $filteredEmployees = $filteredEmployees->filter(function($emp) use ($search) {
                $nameMatch = stripos($emp['name'] ?? '', $search) !== false;
                $uidMatch = stripos((string)($emp['zkteco_uid'] ?? ''), $search) !== false;
                return $nameMatch || $uidMatch;
            });
        }

        // Map employees for display
        $employeeMap = [];
        foreach ($employees as $emp) {
            if (!empty($emp['zkteco_uid'])) {
                $employeeMap[(string)$emp['zkteco_uid']] = $emp['name'] ?? null;
            }
        }

        $query = AttendanceLog::with('device');

        // If any filters are applied, constrain query to matching employee uids
        if (!empty($unitId) || !empty($position) || !empty($search)) {
            $uids = $filteredEmployees->pluck('zkteco_uid')->filter()->map(function($uid) {
                return (string)$uid;
            })->unique()->toArray();

            $query->where(function($q) use ($uids, $search) {
                if (!empty($uids)) {
                    $q->whereIn('uid', $uids);
                } else {
                    $q->where('uid', 'INVALID_UID');
                }

                if (!empty($search)) {
                    $q->orWhere('local_name', 'like', "%{$search}%")
                      ->orWhereHas('device', function($qd) use ($search) {
                          $qd->where('name', 'like', "%{$search}%");
                      });
                }
            });
        }

        $logs = $query->orderBy('timestamp', 'desc')->paginate(50);

        $devices = ZktecoDevice::all();
        $schoolUnits = \App\Models\SchoolUnit::where('is_active', true)->get();
        
        $positions = collect($employees)
            ->map(function ($emp) {
                return $emp['position'] ?? $emp['subject_position'] ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $unitPositions = [];
        foreach ($schoolUnits as $unit) {
            $unitPositions[$unit->id] = collect($employees)
                ->filter(function($emp) use ($unit) {
                    return (string)($emp['unit_id'] ?? '') === (string)$unit->id;
                })
                ->map(function ($emp) {
                    return $emp['position'] ?? $emp['subject_position'] ?? null;
                })
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->toArray();
        }

        return view('raw-attendance-logs.index', compact(
            'logs', 'search', 'unitId', 'position', 'employeeMap', 
            'devices', 'employees', 'schoolUnits', 'positions', 'unitPositions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'zkteco_device_id' => 'required|exists:zkteco_devices,id',
            'uid' => 'required|string|max:255',
            'timestamp' => 'required|date',
            'state' => 'required|integer',
            'type' => 'required|integer',
            'local_name' => 'nullable|string|max:255',
        ]);

        AttendanceLog::create($validated);

        return redirect()->route('raw-attendance-logs.index')->with('success', 'Log mentah mesin berhasil ditambahkan secara manual.');
    }

    public function update(Request $request, $id)
    {
        $log = AttendanceLog::findOrFail($id);

        $validated = $request->validate([
            'zkteco_device_id' => 'required|exists:zkteco_devices,id',
            'uid' => 'required|string|max:255',
            'timestamp' => 'required|date',
            'state' => 'required|integer',
            'type' => 'required|integer',
            'local_name' => 'nullable|string|max:255',
        ]);

        $log->update($validated);

        return redirect()->route('raw-attendance-logs.index')->with('success', 'Log mentah mesin berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $log = AttendanceLog::findOrFail($id);
        $log->delete();

        return redirect()->route('raw-attendance-logs.index')->with('success', 'Log mentah mesin berhasil dihapus.');
    }
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        
        // Sheet 1: Data Entry
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Data Entry');
        $sheet1->setCellValue('A1', 'UID Pegawai');
        $sheet1->setCellValue('B1', 'ID Mesin');
        $sheet1->setCellValue('C1', 'Waktu Absen (YYYY-MM-DD HH:MM:SS)');
        $sheet1->setCellValue('D1', 'Status (Lihat Referensi)');
        $sheet1->setCellValue('E1', 'Tipe (Lihat Referensi)');
        $sheet1->setCellValue('F1', 'Nama Lokal (Opsional)');

        // Make headers bold
        $sheet1->getStyle('A1:F1')->getFont()->setBold(true);

        // Sheet 2: Referensi
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Referensi');

        // Referensi Status & Tipe
        $sheet2->setCellValue('A1', 'Kode Status');
        $sheet2->setCellValue('A2', '0 = Check-In (Masuk)');
        $sheet2->setCellValue('A3', '1 = Check-Out (Pulang)');
        $sheet2->setCellValue('A4', '2 = Break-Out');
        $sheet2->setCellValue('A5', '3 = Break-In');
        $sheet2->setCellValue('A6', '4 = Overtime-In');
        $sheet2->setCellValue('A7', '5 = Overtime-Out');
        $sheet2->setCellValue('A8', '255 = Otomatis');

        $sheet2->setCellValue('C1', 'Kode Tipe');
        $sheet2->setCellValue('C2', '15 = Wajah (Face)');
        $sheet2->setCellValue('C3', '3 = Sidik Jari (Fingerprint)');
        $sheet2->setCellValue('C4', '4 = Kartu RFID (Card)');
        $sheet2->setCellValue('C5', '0 = Password (PIN)');
        $sheet2->setCellValue('C6', '255 = Wajah (API)');

        $sheet2->getStyle('A1')->getFont()->setBold(true);
        $sheet2->getStyle('C1')->getFont()->setBold(true);

        // Referensi Mesin
        $sheet2->setCellValue('E1', 'ID Mesin');
        $sheet2->setCellValue('F1', 'Nama Mesin');
        $sheet2->getStyle('E1:F1')->getFont()->setBold(true);
        $devices = ZktecoDevice::all();
        $row = 2;
        foreach ($devices as $dev) {
            $sheet2->setCellValue('E' . $row, $dev->id);
            $sheet2->setCellValue('F' . $row, $dev->name . ' (SN: ' . $dev->sn . ')');
            $row++;
        }

        // Referensi Pegawai
        $sheet2->setCellValue('H1', 'UID Pegawai');
        $sheet2->setCellValue('I1', 'Nama Pegawai');
        $sheet2->setCellValue('J1', 'Unit & Jabatan');
        $sheet2->getStyle('H1:J1')->getFont()->setBold(true);
        
        $employees = $this->service->getSdEmployees();
        $row = 2;
        foreach ($employees as $emp) {
            if (!empty($emp['zkteco_uid'])) {
                $sheet2->setCellValue('H' . $row, $emp['zkteco_uid']);
                $sheet2->setCellValue('I' . $row, $emp['name'] ?? '-');
                
                $unitName = '-';
                if (!empty($emp['unit_id'])) {
                    $schoolUnits = \App\Models\SchoolUnit::all();
                    $unit = $schoolUnits->firstWhere('id', $emp['unit_id']);
                    if ($unit) $unitName = $unit->name;
                }
                $pos = $emp['position'] ?? $emp['subject_position'] ?? '-';
                
                $sheet2->setCellValue('J' . $row, $unitName . ' - ' . $pos);
                $row++;
            }
        }

        // Auto-size columns for better readability
        foreach (range('A', 'F') as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }
        foreach (['A', 'C', 'E', 'F', 'H', 'I', 'J'] as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Set active sheet to 1 (Data Entry)
        $spreadsheet->setActiveSheetIndex(0);

        // Download
        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_Import_Log_Absensi.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getSheetByName('Data Entry');
            
            if (!$sheet) {
                // fallback to first sheet if renamed
                $sheet = $spreadsheet->getActiveSheet();
            }

            $rows = $sheet->toArray();
            
            // Remove header row
            array_shift($rows);

            $insertData = [];
            $now = now();
            
            DB::beginTransaction();
            
            $count = 0;
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || !isset($row[3]) || !isset($row[4])) {
                    continue;
                }

                $uid = $row[0];
                $deviceId = $row[1];
                $timestamp = $row[2]; // assuming format YYYY-MM-DD HH:MM:SS
                $state = $row[3];
                $type = $row[4];
                $localName = $row[5] ?? null;

                // Basic validation for dates
                if (!strtotime($timestamp)) {
                    continue; // Skip invalid date formats
                }

                $insertData[] = [
                    'zkteco_device_id' => $deviceId,
                    'uid' => $uid,
                    'timestamp' => $timestamp,
                    'state' => $state,
                    'type' => $type,
                    'local_name' => $localName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                
                $count++;
            }

            if (!empty($insertData)) {
                // chunk inserts for memory efficiency
                foreach (array_chunk($insertData, 500) as $chunk) {
                    AttendanceLog::insert($chunk);
                }
            }
            
            DB::commit();

            return redirect()->route('raw-attendance-logs.index')->with('success', "Berhasil mengimpor $count data log absensi.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('raw-attendance-logs.index')->withErrors(['file' => 'Gagal mengimpor file: ' . $e->getMessage()]);
        }
    }
}
