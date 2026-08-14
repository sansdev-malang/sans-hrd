<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Models\SchoolUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayslipApiController extends Controller
{
    public function index(Request $request)
    {
        $unitCode = $request->query('unit_id'); // e.g., 'smp' or 'sd'
        
        if (!$unitCode) {
            return response()->json(['error' => 'Missing unit_id parameter'], 400);
        }

        // Map short code to actual name in DB
        $unitName = $unitCode;
        if (strtolower($unitCode) === 'sd') $unitName = 'SD';
        elseif (strtolower($unitCode) === 'smp') $unitName = 'SMP';
        elseif (strtolower($unitCode) === 'paud') $unitName = 'PAUD';

        $unit = SchoolUnit::where('name', $unitName)->orWhere('id', $unitCode)->first();
        if (!$unit) {
            return response()->json(['error' => 'Invalid unit_id: ' . $unitCode], 400);
        }

        $month = $request->query('month', date('Y-m'));

        $payslips = Payslip::where('school_unit_id', $unit->id)
            ->where('period', $month)
            ->get();

        $formatted = $payslips->map(function ($p) {
            return [
                'employee_id' => $p->employee_id,
                'period' => $p->period,
                'file_url' => url(Storage::url($p->file_path)),
                'attachment_url' => $p->attachment_path ? url(Storage::url($p->attachment_path)) : null,
            ];
        })->keyBy('employee_id');

        return response()->json([
            'data' => $formatted
        ]);
    }
}
