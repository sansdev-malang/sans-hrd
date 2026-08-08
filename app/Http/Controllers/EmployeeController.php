<?php

namespace App\Http\Controllers;
use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\ZktecoDevice;
use App\Models\AdmsCommand;
use App\Models\EmployeeDeviceMapping;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;

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
        
        // Extract unique positions (jabatan) from raw employee data
        $positions = collect($rawEmployees)
            ->map(function ($emp) {
                return $emp['position'] ?? $emp['subject_position'] ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
        
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

        // Apply position filter
        $position = $request->query('position');
        if (!empty($position)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($position) {
                $empPos = $emp['position'] ?? $emp['subject_position'] ?? '';
                return $empPos == $position;
            });
        }

        // Paginate
        $perPageRaw = $request->query('per_page', 50);
        $total = $employeesCollection->count();
        
        if ($perPageRaw === 'all') {
            $perPage = $total > 0 ? $total : 1;
        } else {
            $perPage = (int) $perPageRaw;
            if (!in_array($perPage, [10, 25, 50, 100, 500])) {
                $perPage = 50;
            }
        }
        
        $page = $request->query('page', 1);
        
        $paginatedEmployees = new \Illuminate\Pagination\LengthAwarePaginator(
            $employeesCollection->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $devices = ZktecoDevice::all();

        return view('employees.index', [
            'employees' => $paginatedEmployees,
            'schoolUnits' => $schoolUnits,
            'devices' => $devices,
            'positions' => $positions,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $rawEmployees = $this->service->getSdEmployees();
        $employeesCollection = collect($rawEmployees);

        $search = $request->query('search');
        if (!empty($search)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['email'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['subject_position'] ?? ''), strtolower($search));
            });
        }

        $unitId = $request->query('unit');
        $unitName = 'Semua Unit';
        if (!empty($unitId)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                return ($emp['unit_id'] ?? '') == $unitId;
            });
            $unitModel = SchoolUnit::find($unitId);
            if ($unitModel) {
                $unitName = $unitModel->name;
            }
        }

        $position = $request->query('position');
        if (!empty($position)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($position) {
                $empPos = $emp['position'] ?? $emp['subject_position'] ?? '';
                return $empPos == $position;
            });
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Unit');
        $sheet->setCellValue('C1', 'Nama Lengkap');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'Tipe Pegawai');
        $sheet->setCellValue('F1', 'Jabatan');
        $sheet->setCellValue('G1', 'No. HP');
        $sheet->setCellValue('H1', 'Status');

        $row = 2;
        $no = 1;
        foreach ($employeesCollection as $emp) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $emp['unit_name'] ?? '');
            $sheet->setCellValue('C' . $row, $emp['name'] ?? '');
            $sheet->setCellValue('D' . $row, $emp['email'] ?? '');
            $sheet->setCellValue('E' . $row, $emp['employee_type']['name'] ?? '');
            $sheet->setCellValue('F' . $row, $emp['position'] ?? '');
            $sheet->setCellValue('G' . $row, $emp['phone'] ?? '-');
            
            $statusText = 'Aktif';
            if (($emp['status'] ?? '') == 'Leave') $statusText = 'Cuti';
            if (($emp['status'] ?? '') == 'Inactive') $statusText = 'Nonaktif';
            
            $sheet->setCellValue('H' . $row, $statusText);
            $row++;
        }

        foreach(range('A','H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Data Pegawai - ' . $unitName . '.xlsx';


        if ($request->filled('download_token')) {
            setcookie('download_token', $request->query('download_token'), time() + 60, '/', '', false, false);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'. $fileName .'"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        $rawEmployees = $this->service->getSdEmployees();
        $employeesCollection = collect($rawEmployees);

        $search = $request->query('search');
        if (!empty($search)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['email'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['subject_position'] ?? ''), strtolower($search));
            });
        }

        $unitId = $request->query('unit');
        $unitName = 'Semua Unit';
        if (!empty($unitId)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                return ($emp['unit_id'] ?? '') == $unitId;
            });
            $unitModel = SchoolUnit::find($unitId);
            if ($unitModel) {
                $unitName = $unitModel->name;
            }
        }

        $position = $request->query('position');
        if (!empty($position)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($position) {
                $empPos = $emp['position'] ?? $emp['subject_position'] ?? '';
                return $empPos == $position;
            });
        }

        $fileName = 'Data Pegawai - ' . $unitName . '.pdf';
        
        $pdf = Pdf::loadView('employees.pdf', [
            'employees' => $employeesCollection,
            'unitName' => $unitName
        ])->setPaper('a4', 'landscape');

        $response = $pdf->download($fileName);
        if ($request->filled('download_token')) {
            $response->headers->setCookie(cookie('download_token', $request->query('download_token'), 1, '/', null, false, false));
        }
        return $response;
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $devices = ZktecoDevice::all();
        return view('employees.create', compact('units', 'devices'));
    }

    public function generateUid($unitId)
    {
        $unit = SchoolUnit::findOrFail($unitId);
        
        $unitNameLower = strtolower($unit->name);
        $prefix = 1000;
        if (str_contains($unitNameLower, 'paud')) {
            $prefix = 3000;
        } elseif (str_contains($unitNameLower, 'smp')) {
            $prefix = 2000;
        }

        $response = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ])->get(rtrim($unit->api_url, '/') . '/employees');
        
        $maxUid = $prefix;
        
        if ($response->successful()) {
            $employees = $response->json('data') ?? [];
            foreach ($employees as $emp) {
                if (!empty($emp['zkteco_uid'])) {
                    $uid = intval($emp['zkteco_uid']);
                    // Check if uid is in the prefix block (e.g. 1000-1999)
                    if ($uid >= $prefix && $uid < ($prefix + 1000)) {
                        if ($uid > $maxUid) {
                            $maxUid = $uid;
                        }
                    }
                }
            }
        }
        
        // Next available UID
        $nextUid = $maxUid === $prefix ? $prefix + 1 : $maxUid + 1;
        
        return response()->json([
            'status' => 'success',
            'next_uid' => $nextUid
        ]);
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
            'employee_type_code' => 'required|string',
            'front_title' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'back_title' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'gender' => 'required|in:Male,Female,L,P',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'nik' => 'nullable|string|max:255',
            'niy' => 'nullable|string|max:255',
            'nuptk' => 'nullable|string|max:255',
            'no_ukg' => 'nullable|string|max:255',
            'nrg' => 'nullable|string|max:255',
            'pangkat_golongan' => 'nullable|string|max:255',
            'last_education' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'additional_position' => 'nullable|string|max:255',
            'task_start_date' => 'nullable|date',
            'employment_status' => 'nullable|string|max:255',
            'appointment_date' => 'nullable|date',
            'last_sk_date' => 'nullable|date',
            'last_sk_number' => 'nullable|string|max:255',
            'work_period' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'zkteco_uid' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:Active,Leave,Inactive',
            'zkteco_device_ids' => 'nullable|array',
            'zkteco_device_ids.*' => 'exists:zkteco_devices,id',
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
            'front_title', 'back_title', 'employee_type_code', 'name', 'email', 'gender', 'birth_place', 'birth_date',
            'nik', 'niy', 'nuptk', 'no_ukg', 'nrg', 'pangkat_golongan', 'last_education', 'major',
            'position', 'additional_position', 'task_start_date', 'employment_status',
            'appointment_date', 'last_sk_date', 'last_sk_number', 'work_period', 'address', 'phone', 'notes',
            'zkteco_uid', 'status'
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
            // If zkteco_uid is provided, queue ADMS commands for selected devices
            $zkteco_uid = $request->input('zkteco_uid');
            $device_ids = $request->input('zkteco_device_ids', []);
            if (!empty($zkteco_uid)) {
                // Update mapping memori sinkronisasi
                EmployeeDeviceMapping::where('zkteco_uid', $zkteco_uid)->delete();
                
                if (!empty($device_ids)) {
                    foreach ($device_ids as $deviceId) {
                        EmployeeDeviceMapping::create([
                            'zkteco_uid' => $zkteco_uid,
                            'zkteco_device_id' => $deviceId
                        ]);
                        
                        AdmsCommand::create([
                            'zkteco_device_id' => $deviceId,
                            'command_string' => "DATA UPDATE USERINFO PIN={$zkteco_uid}\tName={$apiData['name']}"
                        ]);
                    }
                }
            }

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

        $units = SchoolUnit::where('is_active', true)->get();
        $devices = ZktecoDevice::all();
        
        $mappedDeviceIds = [];
        if (!empty($employee['zkteco_uid'])) {
            $mappedDeviceIds = EmployeeDeviceMapping::where('zkteco_uid', $employee['zkteco_uid'])
                ->pluck('zkteco_device_id')
                ->toArray();
        }
        
        return view('employees.edit', compact('employee', 'unit', 'id', 'units', 'devices', 'mappedDeviceIds'));
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
            'employee_type_code' => 'required|string',
            'front_title' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'back_title' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'gender' => 'required|in:Male,Female,L,P',
            'birth_place' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'nik' => 'nullable|string|max:255',
            'niy' => 'nullable|string|max:255',
            'nuptk' => 'nullable|string|max:255',
            'no_ukg' => 'nullable|string|max:255',
            'nrg' => 'nullable|string|max:255',
            'pangkat_golongan' => 'nullable|string|max:255',
            'last_education' => 'nullable|string|max:255',
            'major' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'additional_position' => 'nullable|string|max:255',
            'task_start_date' => 'nullable|date',
            'employment_status' => 'nullable|string|max:255',
            'appointment_date' => 'nullable|date',
            'last_sk_date' => 'nullable|date',
            'last_sk_number' => 'nullable|string|max:255',
            'work_period' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'zkteco_uid' => 'nullable|string|max:255',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:Active,Leave,Inactive',
            'zkteco_device_ids' => 'nullable|array',
            'zkteco_device_ids.*' => 'exists:zkteco_devices,id',
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
            'front_title', 'back_title', 'employee_type_code', 'name', 'email', 'gender', 'birth_place', 'birth_date',
            'nik', 'niy', 'nuptk', 'no_ukg', 'nrg', 'pangkat_golongan', 'last_education', 'major',
            'position', 'additional_position', 'task_start_date', 'employment_status',
            'appointment_date', 'last_sk_date', 'last_sk_number', 'work_period', 'address', 'phone', 'notes',
            'zkteco_uid', 'status'
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
            // If zkteco_uid is provided, queue ADMS commands for selected devices
            $zkteco_uid = $request->input('zkteco_uid');
            $device_ids = $request->input('zkteco_device_ids', []);
            
            if (!empty($zkteco_uid)) {
                // Update mapping memori sinkronisasi
                EmployeeDeviceMapping::where('zkteco_uid', $zkteco_uid)->delete();
                
                if (!empty($device_ids)) {
                    foreach ($device_ids as $deviceId) {
                        EmployeeDeviceMapping::create([
                            'zkteco_uid' => $zkteco_uid,
                            'zkteco_device_id' => $deviceId
                        ]);
                        
                        AdmsCommand::create([
                            'zkteco_device_id' => $deviceId,
                            'command_string' => "DATA UPDATE USERINFO PIN={$zkteco_uid}\tName={$apiData['name']}"
                        ]);
                    }
                }
            }

            return redirect()->route('employees.index')
                ->with('success', "Data pegawai berhasil diperbarui di unit {$unit->name}.");
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

        // Fetch employee data first to get zkteco_uid
        $response = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ])->get(rtrim($unit->api_url, '/') . '/employees');
        
        $zkteco_uid = null;
        if ($response->successful()) {
            $employees = $response->json('data') ?? [];
            $employee = collect($employees)->firstWhere('id', $id);
            if ($employee && !empty($employee['zkteco_uid'])) {
                $zkteco_uid = $employee['zkteco_uid'];
            }
        }

        // Call the unit's API to delete the employee
        $deleteResponse = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ])->delete(rtrim($unit->api_url, '/') . '/employees/' . $id);

        if ($deleteResponse->successful()) {
            // Jika berhasil dihapus di aplikasi, antrekan perintah hapus ke mesin
            if ($zkteco_uid) {
                $mappings = EmployeeDeviceMapping::where('zkteco_uid', $zkteco_uid)->get();
                foreach ($mappings as $mapping) {
                    AdmsCommand::create([
                        'zkteco_device_id' => $mapping->zkteco_device_id,
                        'command_string' => "DATA DELETE USERINFO PIN={$zkteco_uid}"
                    ]);
                }
                // Hapus memori mapping
                EmployeeDeviceMapping::where('zkteco_uid', $zkteco_uid)->delete();
            }

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

        // Headers for 28 fields + unit
        $headers = [
            'Gelar Depan', 'Nama Lengkap', 'Gelar Belakang', 'Email', 'Kode Tipe Pegawai (teacher/employee)', 'Unit Sekolah (paud/sd/smp)',
            'Jenis Kelamin (L/P)', 'Tempat Lahir', 'Tanggal Lahir (YYYY-MM-DD)', 
            'NIK', 'NIY', 'NUPTK', 'No UKG', 'NRG', 'Pangkat/Golongan', 
            'Pendidikan Terakhir', 'Jurusan', 'Jabatan Utama', 'Jabatan Tambahan', 
            'Tanggal Mulai Tugas (YYYY-MM-DD)', 'Status Kepegawaian', 'Tanggal Diangkat (YYYY-MM-DD)', 
            'Tanggal SK Terakhir (YYYY-MM-DD)', 'Nomor SK Terakhir', 'Masa Kerja Golongan', 
            'Alamat', 'No. HP/WA', 'Catatan Tambahan', 'ID ZKTeco (Alfanumerik)', 'Status (Active/Leave/Inactive)'
        ];

        $example = [
            'Dr.', 'Budi Santoso', 'S.Pd.', 'budi@example.com', 'teacher', 'sd',
            'L', 'Malang', '1985-01-01',
            '3573010101850001', '12345678', '198501012010121002', '201501234567', '-', 'Penata Muda / III.a',
            'S1 Pendidikan Matematika', 'Matematika', 'Guru Kelas', 'Wali Kelas',
            '2010-07-01', 'GTY', '2010-07-01',
            '2020-01-01', '012/SK/SANS/2020', '10 Tahun 6 Bulan',
            'Jl. Veteran No. 123, Malang', '081234567890', 'Guru berprestasi tingkat provinsi', '', 'Active'
        ];

        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
        }

        foreach ($example as $colIndex => $val) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '2', $val);
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = 'A1:' . $lastCol . '1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        foreach (range(1, count($headers)) as $colIndex) {
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
        array_shift($rows);

        $importedCount = 0;
        $errors = [];

        $activeUnits = SchoolUnit::where('is_active', true)->get();

        foreach ($rows as $index => $row) {
            // Skip empty rows (must have name)
            if (empty($row[1])) {
                continue;
            }

            $unitCode = strtolower(trim($row[5] ?? ''));
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

            // Map variables based on new 28 columns template
            $gender_raw = !empty($row[6]) ? trim($row[6]) : 'Male';
            if (strtoupper($gender_raw) === 'L' || strtolower($gender_raw) === 'laki-laki' || strtolower($gender_raw) === 'male') {
                $gender = 'Male';
            } elseif (strtoupper($gender_raw) === 'P' || strtolower($gender_raw) === 'perempuan' || strtolower($gender_raw) === 'female') {
                $gender = 'Female';
            } else {
                $gender = 'Male';
            }

            $status_raw = !empty($row[29]) ? trim($row[29]) : 'Active';
            if (strtolower($status_raw) == 'active') {
                $status = 'Active';
            } elseif (strtolower($status_raw) == 'leave') {
                $status = 'Leave';
            } else {
                $status = 'Inactive';
            }

            $apiData = [
                'front_title' => !empty($row[0]) ? trim($row[0]) : null,
                'name' => trim($row[1]),
                'back_title' => !empty($row[2]) ? trim($row[2]) : null,
                'email' => !empty($row[3]) ? trim($row[3]) : null,
                'employee_type_code' => !empty($row[4]) ? strtolower(trim($row[4])) : 'employee',
                'unit' => $unitCode,
                'gender' => $gender,
                'birth_place' => !empty($row[7]) ? trim($row[7]) : null,
                'birth_date' => !empty($row[8]) ? (date('Y-m-d', strtotime(trim($row[8]))) ?: null) : null,
                'nik' => !empty($row[9]) ? trim($row[9]) : null,
                'niy' => !empty($row[10]) ? trim($row[10]) : null,
                'nuptk' => !empty($row[11]) ? trim($row[11]) : null,
                'no_ukg' => !empty($row[12]) ? trim($row[12]) : null,
                'nrg' => !empty($row[13]) ? trim($row[13]) : null,
                'pangkat_golongan' => !empty($row[14]) ? trim($row[14]) : null,
                'last_education' => !empty($row[15]) ? trim($row[15]) : null,
                'major' => !empty($row[16]) ? trim($row[16]) : null,
                'position' => !empty($row[17]) ? trim($row[17]) : null,
                'additional_position' => !empty($row[18]) ? trim($row[18]) : null,
                'task_start_date' => !empty($row[19]) ? (date('Y-m-d', strtotime(trim($row[19]))) ?: null) : null,
                'employment_status' => !empty($row[20]) ? trim($row[20]) : null,
                'appointment_date' => !empty($row[21]) ? (date('Y-m-d', strtotime(trim($row[21]))) ?: null) : null,
                'last_sk_date' => !empty($row[22]) ? (date('Y-m-d', strtotime(trim($row[22]))) ?: null) : null,
                'last_sk_number' => !empty($row[23]) ? trim($row[23]) : null,
                'work_period' => !empty($row[24]) ? trim($row[24]) : null,
                'address' => !empty($row[25]) ? trim($row[25]) : null,
                'phone' => !empty($row[26]) ? trim($row[26]) : null,
                'notes' => !empty($row[27]) ? trim($row[27]) : null,
                'zkteco_uid' => !empty($row[28]) ? trim($row[28]) : null,
                'status' => $status,
            ];

            $req = Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ]);
            
            $response = $req->post(rtrim($unit->api_url, '/') . '/employees', $apiData);
            
            if ($response->successful()) {
                $importedCount++;
            } else {
                $errorMessage = 'Gagal menyimpan ke server unit.';
                if ($response->status() === 422) {
                    $resData = $response->json();
                    if (isset($resData['errors'])) {
                        $errs = [];
                        foreach ($resData['errors'] as $fieldErrs) {
                            $errs = array_merge($errs, $fieldErrs);
                        }
                        $errorMessage = implode(', ', $errs);
                    }
                }
                $errors[] = "Baris " . ($index + 2) . " ({$apiData['name']}): " . $errorMessage;
            }
        }

        if ($importedCount > 0) {
            $msg = "$importedCount pegawai berhasil diimpor.";
            if (count($errors) > 0) {
                return redirect()->route('employees.index')->with('success', $msg)->with('import_errors', $errors);
            }
            return redirect()->route('employees.index')->with('success', $msg);
        } else {
            return redirect()->route('employees.index')->with('error', 'Tidak ada data yang berhasil diimpor.')->with('import_errors', $errors);
        }
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




