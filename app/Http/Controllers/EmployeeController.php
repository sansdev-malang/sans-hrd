<?php

namespace App\Http\Controllers;

use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmployeeController extends Controller
{
    protected SchoolUnitService $service;

    public function __construct(SchoolUnitService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of combined employees.
     */
    public function index(Request $request)
    {
        $schoolUnits = SchoolUnit::where('is_active', true)->get();
        $rawEmployees = $this->service->getSdEmployees();
        
        $employeesCollection = collect($rawEmployees);

        // Apply search filter
        $search = $request->query('search');
        if (!empty($search)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['email'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['subject_position'] ?? ''), strtolower($search));
            });
        }

        // Apply unit filter
        $unit = $request->query('unit');
        if (!empty($unit)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unit) {
                return ($emp['unit_id'] ?? '') == $unit;
            });
        }

        // Apply status filter
        $status = $request->query('status');
        if (!empty($status)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($status) {
                return ($emp['status'] ?? '') == $status;
            });
        }

        // Paginate
        $perPage = 10;
        $page = $request->query('page', 1);
        $total = $employeesCollection->count();
        
        $paginatedEmployees = new \Illuminate\Pagination\LengthAwarePaginator(
            $employeesCollection->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('employees.index', [
            'employees' => $paginatedEmployees,
            'schoolUnits' => $schoolUnits,
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $units = SchoolUnit::where('is_active', true)->get();
        return view('employees.create', compact('units'));
    }

    /**
     * Store a newly created employee in the unit's database.
     */
    public function store(Request $request)
    {
        $messages = [
            'school_unit_id.required' => 'Unit sekolah tujuan wajib dipilih.',
            'school_unit_id.exists' => 'Unit sekolah tujuan tidak valid.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'employee_type_code.required' => 'Tipe pegawai wajib dipilih.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'employment_status.required' => 'Status kepegawaian wajib dipilih.',
            'status.required' => 'Status keaktifan wajib dipilih.',
        ];

        $request->validate([
            'school_unit_id' => 'required|exists:school_units,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nuptk_nip_nik' => 'nullable|string|max:255',
            'employee_type_code' => 'required|string',
            'subject_position' => 'nullable|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'employment_status' => 'required|string|max:255',
            'zkteco_uid' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            'photo' => 'nullable|image|max:2048',
        ], $messages);

        $unit = SchoolUnit::findOrFail($request->input('school_unit_id'));

        // Prepare request body for the unit API (determine unit dynamically)
        $unitNameLower = strtolower($unit->name);
        if (str_contains($unitNameLower, 'paud')) {
            $unitCode = 'paud';
        } elseif (str_contains($unitNameLower, 'sd')) {
            $unitCode = 'sd';
        } elseif (str_contains($unitNameLower, 'smp')) {
            $unitCode = 'smp';
        } else {
            $unitCode = 'sd';
        }

        $apiData = $request->only([
            'name', 'email', 'nuptk_nip_nik', 'employee_type_code',
            'subject_position', 'gender', 'employment_status', 'zkteco_uid', 'status'
        ]);
        $apiData['unit'] = $unitCode;

        // Call the unit's API
        $req = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ]);

        if ($request->hasFile('photo')) {
            $req = $req->attach(
                'photo',
                file_get_contents($request->file('photo')->getRealPath()),
                $request->file('photo')->getClientOriginalName()
            );
        }

        $response = $req->post(rtrim($unit->api_url, '/') . '/employees', $apiData);

        if ($response->successful()) {
            return redirect()->route('employees.index')
                ->with('success', "Pegawai berhasil ditambahkan ke unit {$unit->name}.");
        }

        if ($response->status() === 422) {
            $apiErrors = $response->json('errors') ?? [];
            return redirect()->back()
                ->withInput()
                ->withErrors($apiErrors);
        }

        $errorMsg = $response->json('message') ?? 'Gagal menambahkan pegawai ke unit sekolah.';
        return redirect()->back()
            ->withInput()
            ->withErrors(['api_error' => $errorMsg]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit($unitId, $id)
    {
        $unit = SchoolUnit::findOrFail($unitId);

        // Fetch all employees from the unit and find the specific one
        $response = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ])->get(rtrim($unit->api_url, '/') . '/employees');

        if (!$response->successful()) {
            return redirect()->route('employees.index')
                ->withErrors(['error' => "Gagal terhubung ke API unit {$unit->name}."]);
        }

        $employees = $response->json('data') ?? [];
        $employee = collect($employees)->firstWhere('id', $id);

        if (!$employee) {
            return redirect()->route('employees.index')
                ->withErrors(['error' => 'Pegawai tidak ditemukan pada unit sekolah terkait.']);
        }

        return view('employees.edit', compact('employee', 'unit', 'id'));
    }

    /**
     * Update the specified employee in the unit's database.
     */
    public function update(Request $request, $unitId, $id)
    {
        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'employee_type_code.required' => 'Tipe pegawai wajib dipilih.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'employment_status.required' => 'Status kepegawaian wajib dipilih.',
            'status.required' => 'Status keaktifan wajib dipilih.',
        ];

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nuptk_nip_nik' => 'nullable|string|max:255',
            'employee_type_code' => 'required|string',
            'subject_position' => 'nullable|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'employment_status' => 'required|string|max:255',
            'zkteco_uid' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
            'photo' => 'nullable|image|max:2048',
        ], $messages);

        $unit = SchoolUnit::findOrFail($unitId);
        $unitNameLower = strtolower($unit->name);
        if (str_contains($unitNameLower, 'paud')) {
            $unitCode = 'paud';
        } elseif (str_contains($unitNameLower, 'sd')) {
            $unitCode = 'sd';
        } elseif (str_contains($unitNameLower, 'smp')) {
            $unitCode = 'smp';
        } else {
            $unitCode = 'sd';
        }

        $apiData = $request->only([
            'name', 'email', 'nuptk_nip_nik', 'employee_type_code',
            'subject_position', 'gender', 'employment_status', 'zkteco_uid', 'status'
        ]);
        $apiData['unit'] = $unitCode;

        // Call the unit's API
        $req = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ]);

        if ($request->hasFile('photo')) {
            $req = $req->attach(
                'photo',
                file_get_contents($request->file('photo')->getRealPath()),
                $request->file('photo')->getClientOriginalName()
            );
            // Spoof PUT method for multipart uploads
            $apiData['_method'] = 'PUT';
            $response = $req->post(rtrim($unit->api_url, '/') . '/employees/' . $id, $apiData);
        } else {
            $response = $req->put(rtrim($unit->api_url, '/') . '/employees/' . $id, $apiData);
        }

        if ($response->successful()) {
            return redirect()->route('employees.index')
                ->with('success', "Data pegawai di unit {$unit->name} berhasil diperbarui.");
        }

        if ($response->status() === 422) {
            $apiErrors = $response->json('errors') ?? [];
            return redirect()->back()
                ->withInput()
                ->withErrors($apiErrors);
        }

        $errorMsg = $response->json('message') ?? 'Gagal memperbarui data pegawai.';
        return redirect()->back()
            ->withInput()
            ->withErrors(['api_error' => $errorMsg]);
    }

    /**
     * Remove the specified employee from the unit's database.
     */
    public function destroy($unitId, $id)
    {
        $unit = SchoolUnit::findOrFail($unitId);

        // Call the unit's API
        $response = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ])->delete(rtrim($unit->api_url, '/') . '/employees/' . $id);

        if ($response->successful()) {
            return redirect()->route('employees.index')
                ->with('success', "Pegawai berhasil dihapus dari unit {$unit->name}.");
        }

        return redirect()->route('employees.index')
            ->withErrors(['error' => "Gagal menghapus pegawai dari unit {$unit->name}."]);
    }

    /**
     * Download XLSX format template for employees import.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = [
            'Nama Lengkap',
            'Email',
            'NUPTK/NIP/NIK',
            'Kode Tipe Pegawai (contoh: teacher, employee)',
            'Unit Sekolah (paud/sd/smp)',
            'Jabatan / Mapel',
            'Jenis Kelamin (Male/Female)',
            'Status Kepegawaian',
            'ID ZKTeco (Alfanumerik)',
            'Status (Active/Leave/Inactive)'
        ];

        // Example data row
        $example = [
            'Budi Santoso',
            'budi@example.com',
            '198501012010121002',
            'teacher',
            'sd',
            'Guru Matematika',
            'Male',
            'PNS',
            '1001',
            'Active'
        ];

        // Put headers in row 1
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        // Put example in row 2
        foreach ($example as $colIndex => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        // Format headers (bold text & light gray background fill)
        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Auto-size columns
        foreach (range(1, 10) as $colIndex) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_pegawai.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import employees from uploaded XLSX file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Remove header row
        $header = array_shift($rows);

        $importedCount = 0;
        $errors = [];

        $activeUnits = SchoolUnit::where('is_active', true)->get();

        foreach ($rows as $index => $row) {
            // Skip empty rows (must have name)
            if (empty($row[0])) {
                continue;
            }

            // Map variables
            $name = trim($row[0]);
            $email = !empty($row[1]) ? trim($row[1]) : null;
            $nuptk_nip_nik = !empty($row[2]) ? trim($row[2]) : null;
            $type = strtolower(trim($row[3]));
            $unitCode = strtolower(trim($row[4]));
            $subject_position = !empty($row[5]) ? trim($row[5]) : null;
            $gender = trim($row[6]);
            $employment_status = !empty($row[7]) ? trim($row[7]) : null;
            $zkteco_uid = !empty($row[8]) ? trim($row[8]) : null;
            $status = !empty($row[9]) ? trim($row[9]) : 'Active';

            // Find the school unit matching the unit code
            $unit = $activeUnits->first(function ($u) use ($unitCode) {
                $name = strtolower($u->name);
                if ($unitCode === 'paud' && str_contains($name, 'paud')) return true;
                if ($unitCode === 'sd' && str_contains($name, 'sd')) return true;
                if ($unitCode === 'smp' && str_contains($name, 'smp')) return true;
                return false;
            });

            if (!$unit) {
                $errors[] = "Baris " . ($index + 2) . ": Unit sekolah '{$unitCode}' tidak terdaftar atau tidak aktif di sistem pusat.";
                continue;
            }

            // Call the unit's REST API to store the employee
            $apiData = [
                'name' => $name,
                'email' => $email,
                'nuptk_nip_nik' => $nuptk_nip_nik,
                'employee_type_code' => $type,
                'unit' => $unitCode,
                'subject_position' => $subject_position,
                'gender' => $gender,
                'employment_status' => $employment_status,
                'zkteco_uid' => $zkteco_uid,
                'status' => $status,
            ];

            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/employees', $apiData);

            if ($response->successful()) {
                $importedCount++;
            } else {
                if ($response->status() === 422) {
                    $apiErrors = $response->json('errors') ?? [];
                    $errorDetails = [];
                    foreach ($apiErrors as $field => $messages) {
                        $errorDetails[] = implode(', ', (array)$messages);
                    }
                    $errors[] = "Baris " . ($index + 2) . " (Unit {$unit->name}): " . implode('; ', $errorDetails);
                } else {
                    $errors[] = "Baris " . ($index + 2) . " (Unit {$unit->name}): Gagal menyimpan. Detail: " . ($response->json('message') ?? 'Internal Server Error');
                }
            }
        }

        if (count($errors) > 0) {
            return redirect()->route('employees.index')
                ->with('success', "Impor selesai. Berhasil mengimpor {$importedCount} data pegawai.")
                ->with('import_errors', $errors);
        }

        return redirect()->route('employees.index')->with('success', "Berhasil mengimpor {$importedCount} data pegawai!");
    }

    /**
     * Fetch dynamic employee types from a school unit's API.
     */
    public function getEmployeeTypes($id)
    {
        $unit = SchoolUnit::findOrFail($id);
        try {
            $response = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->get(rtrim($unit->api_url, '/') . '/employee-types');

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // Ignore error and fallback
        }

        return response()->json([
            ['code' => 'teacher', 'name' => 'Guru'],
            ['code' => 'employee', 'name' => 'Staf / Karyawan'],
            ['code' => 'management', 'name' => 'Manajemen']
        ]);
    }
}
