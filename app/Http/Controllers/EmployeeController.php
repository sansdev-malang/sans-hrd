<?php

namespace App\Http\Controllers;

use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

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
    public function index()
    {
        $employees = $this->service->getSdEmployees();
        return view('employees.index', compact('employees'));
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
        $request->validate([
            'school_unit_id' => 'required|exists:school_units,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nuptk_nip_nik' => 'required|string|max:255',
            'employee_type_code' => 'required|string|in:teacher,employee',
            'subject_position' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'employment_status' => 'required|string|max:255',
            'zkteco_uid' => 'required|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $unit = SchoolUnit::findOrFail($request->input('school_unit_id'));

        // Prepare request body for the unit API (SD uses unit 'sd' by default, or SMP 'smp')
        // Let's determine unit type from unit name/type
        $unitCode = str_contains(strtolower($unit->name), 'sd') ? 'sd' : 'smp';

        $apiData = $request->only([
            'name', 'email', 'nuptk_nip_nik', 'employee_type_code',
            'subject_position', 'gender', 'employment_status', 'zkteco_uid', 'status'
        ]);
        $apiData['unit'] = $unitCode;

        // Call the unit's API
        $response = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ])->post(rtrim($unit->api_url, '/') . '/employees', $apiData);

        if ($response->successful()) {
            return redirect()->route('employees.index')
                ->with('success', "Pegawai berhasil ditambahkan ke unit {$unit->name}.");
        }

        // If validation errors occurred at the unit side, return them
        $errorMsg = $response->json('message') ?? 'Gagal menambahkan pegawai ke unit sekolah.';
        if ($response->json('errors')) {
            $errors = Arr::flatten($response->json('errors'));
            $errorMsg .= ' Detail: ' . implode(', ', $errors);
        }

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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nuptk_nip_nik' => 'required|string|max:255',
            'employee_type_code' => 'required|string|in:teacher,employee',
            'subject_position' => 'required|string|max:255',
            'gender' => 'required|string|in:Male,Female',
            'employment_status' => 'required|string|max:255',
            'zkteco_uid' => 'required|string|max:255',
            'status' => 'required|string|in:Active,Inactive',
        ]);

        $unit = SchoolUnit::findOrFail($unitId);
        $unitCode = str_contains(strtolower($unit->name), 'sd') ? 'sd' : 'smp';

        $apiData = $request->only([
            'name', 'email', 'nuptk_nip_nik', 'employee_type_code',
            'subject_position', 'gender', 'employment_status', 'zkteco_uid', 'status'
        ]);
        $apiData['unit'] = $unitCode;

        // Call the unit's API
        $response = Http::withHeaders([
            'X-API-TOKEN' => $unit->api_token,
            'Accept' => 'application/json',
        ])->put(rtrim($unit->api_url, '/') . '/employees/' . $id, $apiData);

        if ($response->successful()) {
            return redirect()->route('employees.index')
                ->with('success', "Data pegawai di unit {$unit->name} berhasil diperbarui.");
        }

        $errorMsg = $response->json('message') ?? 'Gagal memperbarui data pegawai.';
        if ($response->json('errors')) {
            $errors = Arr::flatten($response->json('errors'));
            $errorMsg .= ' Detail: ' . implode(', ', $errors);
        }

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
}
