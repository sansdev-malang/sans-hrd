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

    public function index(Request $request)
    {
        $query = AttendanceLog::with('device')->orderBy('timestamp', 'desc');

        if ($request->filled('device_id')) {
            $query->where('zkteco_device_id', $request->device_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('timestamp', $request->date);
        }

        $logs = $query->paginate(50)->withQueryString();
        $devices = ZktecoDevice::orderBy('name')->get();

        // Ambil data pegawai dari API unit untuk mapping nama berdasarkan UID
        $rawEmployees = $this->service->getSdEmployees();
        $employeeMap = [];
        foreach ($rawEmployees as $emp) {
            if (!empty($emp['zkteco_uid'])) {
                // UID dari zkteco mungkin bertipe string, pastikan mapping aman
                $employeeMap[(string)$emp['zkteco_uid']] = $emp['name'] ?? 'Unknown';
            }
        }

        return view('attendance-logs.index', compact('logs', 'devices', 'employeeMap'));
    }

    public function export(Request $request)
    {
        $query = AttendanceLog::with('device')->orderBy('timestamp', 'desc');

        if ($request->filled('device_id')) {
            $query->where('zkteco_device_id', $request->device_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('timestamp', $request->date);
        }

        $logs = $query->get();

        $rawEmployees = $this->service->getSdEmployees();
        $employeeMap = [];
        foreach ($rawEmployees as $emp) {
            if (!empty($emp['zkteco_uid'])) {
                $employeeMap[(string)$emp['zkteco_uid']] = $emp['name'] ?? 'Unknown';
            }
        }

        $stateMap = [
            0 => 'Masuk (Check-In)',
            1 => 'Pulang (Check-Out)',
            2 => 'Mulai Istirahat',
            3 => 'Selesai Istirahat',
            4 => 'Lembur Masuk',
            5 => 'Lembur Pulang',
            15 => 'Absen Normal'
        ];
        $typeMap = [
            0 => 'Password',
            1 => 'Sidik Jari',
            4 => 'Kartu RFID',
            15 => 'Wajah',
            255 => 'Biometrik / Auto'
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Log Absensi');

        // Set Headers
        $headers = ['No', 'Nama Mesin', 'Karyawan', 'UID', 'Waktu Absen', 'Status', 'Mode Verifikasi', 'Waktu Ditarik'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $row = 2;
        $no = 1;
        foreach ($logs as $log) {
            $karyawan = $employeeMap[(string)$log->uid] ?? ($log->local_name ? $log->local_name . ' (Data Mesin)' : 'Tidak Dikenal');
            $stateLabel = $stateMap[$log->state] ?? 'Status '.$log->state;
            $typeLabel = $typeMap[$log->type] ?? 'Mode '.$log->type;

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $log->device->name ?? 'Mesin Terhapus');
            $sheet->setCellValue('C' . $row, $karyawan);
            // Prefix UID with ' to prevent scientific notation in Excel
            $sheet->setCellValue('D' . $row, "'" . $log->uid); 
            $sheet->setCellValue('E' . $row, \Carbon\Carbon::parse($log->timestamp)->format('Y-m-d H:i:s'));
            $sheet->setCellValue('F' . $row, $stateLabel);
            $sheet->setCellValue('G' . $row, $typeLabel);
            $sheet->setCellValue('H' . $row, $log->created_at->format('Y-m-d H:i:s'));
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $filename = 'log_absensi_' . date('Y-m-d_His') . '.xlsx';
        
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
