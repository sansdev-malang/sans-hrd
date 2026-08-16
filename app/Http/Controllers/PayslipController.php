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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
        $position = $request->query('position');
        $search = $request->query('search');

        // Fetch all employees from units
        $allEmployees = $this->schoolUnitService->getAllEmployees(); 
        
        $employeesCollection = collect($allEmployees);

        if ($unitId) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($unitId) {
                return ($emp['unit_id'] ?? '') == $unitId;
            });
        }

        // Extract unique positions (jabatan) - adapts to selected unit!
        $positions = $employeesCollection
            ->map(function ($emp) {
                return $emp['position'] ?? $emp['subject_position'] ?? null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        if ($position) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($position) {
                return ($emp['position'] ?? $emp['subject_position'] ?? '') === $position;
            });
        }

        if (!empty($search)) {
            $employeesCollection = $employeesCollection->filter(function ($emp) use ($search) {
                return str_contains(strtolower($emp['name'] ?? ''), strtolower($search))
                    || str_contains(strtolower($emp['nuptk_nip_nik'] ?? ''), strtolower($search));
            });
        }

        // Fetch uploaded payslips for the given month
        $payslips = Payslip::where('period', $month)->get()->keyBy(function($item) {
            return $item->school_unit_id . '-' . $item->employee_id;
        });

        // Combine
        $employeesList = $employeesCollection->map(function ($emp) use ($payslips) {
            $key = $emp['unit_id'] . '-' . $emp['id'];
            $emp['payslip'] = $payslips->get($key);
            return $emp;
        })->sortBy('name')->values()->toArray();

        // Paginate
        $total = count($employeesList);
        $perPageReq = $request->query('per_page', 50);
        $perPage = $perPageReq === 'all' ? ($total > 0 ? $total : 1) : (int) $perPageReq;
        
        $page = $request->query('page', 1);
        $paginatedEmployees = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($employeesList, ($page - 1) * $perPage, $perPage),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $units = SchoolUnit::where('is_active', true)->get();

        return view('payslips.index', compact('paginatedEmployees', 'units', 'month', 'unitId', 'positions', 'position'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'school_unit_id' => 'required|integer',
            'period' => 'required|string|size:7', // YYYY-MM
            'payslip_file' => 'required|mimes:pdf|max:512', // 512 KB max
            'attachment_file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:2048', // 2MB max
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

            // Handle optional attachment file
            if ($request->hasFile('attachment_file')) {
                // Delete old attachment file if exists
                if ($payslip->exists && $payslip->attachment_path && Storage::disk('public')->exists($payslip->attachment_path)) {
                    Storage::disk('public')->delete($payslip->attachment_path);
                }

                $attachmentFile = $request->file('attachment_file');
                $ext = strtolower($attachmentFile->getClientOriginalExtension());
                
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    // Compress Image using Intervention Image (v4)
                    $attachmentFilename = 'payslip_attachment_' . $request->school_unit_id . '_' . $request->employee_id . '_' . $request->period . '_' . time() . '.jpg';
                    $attachmentPath = 'payslips/' . $request->period . '/' . $attachmentFilename;

                    $manager = new ImageManager(new Driver());
                    $img = $manager->decode($attachmentFile->getPathname());
                    
                    // Resize/scale to maximum width of 1200px (preserving aspect ratio)
                    $img->scale(width: 1200);
                    
                    // Encode to JPEG at 70% quality
                    $encoded = $img->encode(new \Intervention\Image\Encoders\JpegEncoder(70));
                    
                    Storage::disk('public')->put($attachmentPath, (string) $encoded);
                } else {
                    // PDF attachment - store as is
                    $attachmentFilename = 'payslip_attachment_' . $request->school_unit_id . '_' . $request->employee_id . '_' . $request->period . '_' . time() . '.' . $ext;
                    $attachmentPath = $attachmentFile->storeAs('payslips/' . $request->period, $attachmentFilename, 'public');
                }

                $payslip->attachment_path = $attachmentPath;
            }

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
        $attachmentUrl = $payslip->attachment_path ? asset('storage/' . $payslip->attachment_path) : null;

        try {
            Http::withHeaders([
                'X-API-TOKEN' => $unit->api_token,
                'Accept' => 'application/json',
            ])->post(rtrim($unit->api_url, '/') . '/sync/payslips', [
                'employee_id' => $payslip->employee_id,
                'period' => $payslip->period,
                'file_url' => $fileUrl,
                'attachment_url' => $attachmentUrl,
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
            if ($payslip->attachment_path && Storage::disk('public')->exists($payslip->attachment_path)) {
                Storage::disk('public')->delete($payslip->attachment_path);
            }
            $payslip->delete();
            
            return back()->with('success', 'Slip gaji berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Delete Payslip Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus slip gaji: ' . $e->getMessage());
        }
    }
}
