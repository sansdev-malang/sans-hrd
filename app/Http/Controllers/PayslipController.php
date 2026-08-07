<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Models\SchoolUnit;
use App\Services\SchoolUnitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PayslipController extends Controller
{
    protected $schoolUnitService;

    public function __construct(SchoolUnitService $schoolUnitService)
    {
        $this->schoolUnitService = $schoolUnitService;
    }

    public function index(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        $unitId = $request->query('unit_id');

        // Fetch all employees from units
        $allEmployees = $this->schoolUnitService->getSdEmployees(); // The method gets all active units' employees
        
        $employeesCollection = collect($allEmployees);

        if ($unitId) {
            $employeesCollection = $employeesCollection->where('unit_id', (int)$unitId);
        }

        // Fetch uploaded payslips for the given month
        $payslips = Payslip::where('period', $month)->get()->keyBy(function($item) {
            return $item->school_unit_id . '-' . $item->employee_id;
        });

        // Combine
        $employees = $employeesCollection->map(function ($emp) use ($payslips) {
            $key = $emp['unit_id'] . '-' . $emp['id'];
            $emp['payslip'] = $payslips->get($key);
            return $emp;
        })->sortBy('name');

        $units = SchoolUnit::where('is_active', true)->get();

        return view('payslips.index', compact('employees', 'units', 'month', 'unitId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'school_unit_id' => 'required|integer',
            'period' => 'required|string|size:7', // YYYY-MM
            'payslip_file' => 'required|mimes:pdf|max:1024', // 5MB max
        ]);

        try {
            $file = $request->file('payslip_file');
            
            // Generate a safe filename
            $filename = 'payslip_' . $request->school_unit_id . '_' . $request->employee_id . '_' . $request->period . '_' . time() . '.pdf';
            
            // Store in storage/app/public/payslips
            $path = $file->storeAs('payslips/' . $request->period, $filename, 'public');

            // Find existing payslip to replace or create new
            $payslip = Payslip::firstOrNew([
                'employee_id' => $request->employee_id,
                'school_unit_id' => $request->school_unit_id,
                'period' => $request->period
            ]);

            // Delete old file if exists
            if ($payslip->exists && $payslip->file_path && Storage::disk('public')->exists($payslip->file_path)) {
                Storage::disk('public')->delete($payslip->file_path);
            }

            $payslip->file_path = $path;
            $payslip->save();

            // Notify the unit application about the new payslip
            $this->notifyUnitAboutPayslip($payslip);

            return back()->with('success', 'Slip gaji berhasil diunggah.');
        } catch (\Exception $e) {
            Log::error('Upload Payslip Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengunggah slip gaji: ' . $e->getMessage());
        }
    }

    /**
     * Send payslip notification to the unit application.
     */
    private function notifyUnitAboutPayslip(Payslip $payslip)
    {
        $unit = SchoolUnit::find($payslip->school_unit_id);
        if (!$unit) return;

        $fileUrl = asset('storage/' . $payslip->file_path);

        try {
            Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/sync/payslips', [
                'employee_id' => $payslip->employee_id,
                'period' => $payslip->period,
                'file_url' => $fileUrl,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send payslip notification to unit {$unit->name}: " . $e->getMessage());
        }
    }

    public function destroy(Payslip $payslip)
    {
        try {
            if ($payslip->file_path && Storage::disk('public')->exists($payslip->file_path)) {
                Storage::disk('public')->delete($payslip->file_path);
            }
            $payslip->delete();
            
            return back()->with('success', 'Slip gaji berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Delete Payslip Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus slip gaji: ' . $e->getMessage());
        }
    }
}
