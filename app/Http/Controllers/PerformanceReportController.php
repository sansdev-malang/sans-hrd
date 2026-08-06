<?php

namespace App\Http\Controllers;

use App\Models\PerformanceReport;
use App\Models\SchoolUnit;
use App\Models\Employee;
use Illuminate\Http\Request;

class PerformanceReportController extends Controller
{
    /**
     * Display a listing of synced performance reports.
     */
    public function index(Request $request)
    {
        $schoolUnits = SchoolUnit::orderBy('name')->get();

        // Get unique academic years from database
        $academicYears = PerformanceReport::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year')
            ->toArray();

        // If empty, default to active or standard ones
        if (empty($academicYears)) {
            $academicYears = ['2025/2026', '2024/2025'];
        }

        $query = PerformanceReport::with(['schoolUnit']);

        // Filter by Unit
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->input('unit_id'));
        }

        // Filter by Academic Year
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->input('academic_year'));
        }

        // Filter by Semester
        if ($request->filled('semester')) {
            $query->where('semester', $request->input('semester'));
        }

        // Filter by Search Name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $employeeIds = Employee::where('name', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();
            $query->whereIn('employee_id', $employeeIds);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Retrieve employees names map to display in view without looping queries
        $employeeIdsInReports = $reports->pluck('employee_id')->unique()->toArray();
        $employeesMap = Employee::whereIn('id', $employeeIdsInReports)
            ->get()
            ->keyBy('id');

        return view('performance-reports.index', compact('reports', 'schoolUnits', 'academicYears', 'employeesMap'));
    }

    /**
     * Show a detailed printable performance report card.
     */
    public function show($id)
    {
        $report = PerformanceReport::with('schoolUnit')->findOrFail($id);
        
        $employee = Employee::find($report->employee_id);
        if (!$employee) {
            abort(404, 'Data pegawai tidak ditemukan.');
        }

        // Predicate letters fallback based on score
        $predicateLetter = 'E';
        $score = $report->final_score;
        if ($score >= 91) {
            $predicateLetter = 'A';
        } elseif ($score >= 81) {
            $predicateLetter = 'B+';
        } elseif ($score >= 71) {
            $predicateLetter = 'B';
        } elseif ($score >= 61) {
            $predicateLetter = 'C';
        } elseif ($score >= 51) {
            $predicateLetter = 'D';
        }

        // Read Kop settings from SANS HRD settings or default values
        $reportYayasanName = \App\Models\Setting::get('report_yayasan_name', 'YAYASAN PENDIDIKAN ANAK SALEH');
        $reportYayasanAddress = \App\Models\Setting::get('report_yayasan_address', "Jl. Candi Panggung No. 1-3\nKota Malang\n65143");
        $reportDirectorName = \App\Models\Setting::get('report_director_name', 'Ar Raisul Karama Arifin, M.Psi.Psikolog');

        // Check if there are logo/stamp config
        $reportLogoPath = \App\Models\Setting::get('report_logo_path', null);
        $reportStampPath = \App\Models\Setting::get('report_stamp_path', null);

        // Parse photo
        $pos = $employee->position ?? 'Guru';
        if (!$pos || trim($pos) === '' || trim($pos) === '-') {
            $pos = 'Guru';
        }

        return view('performance-reports.show', [
            'report' => $report,
            'employee' => $employee,
            'pos' => $pos,
            'predicateLetter' => $predicateLetter,
            'reportYayasanName' => $reportYayasanName,
            'reportYayasanAddress' => $reportYayasanAddress,
            'reportDirectorName' => $reportDirectorName,
            'reportLogoPath' => $reportLogoPath,
            'reportStampPath' => $reportStampPath,
        ]);
    }
}
