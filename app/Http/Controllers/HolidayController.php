<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Models\HolidayAdjustment;
use App\Models\SchoolUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayController extends Controller
{
    /**
     * Display a listing of holidays and adjustments.
     */
    public function index()
    {
        $holidays = Holiday::with('adjustments.schoolUnit')->orderBy('original_date', 'desc')->get();
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        return view('holidays.index', compact('holidays', 'units'));
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'original_date' => 'required|date|unique:holidays,original_date',
            'is_global' => 'sometimes|boolean',
        ]);

        $isGlobal = $request->has('is_global') ? (bool)$request->input('is_global') : true;

        Holiday::create([
            'name' => $validated['name'],
            'original_date' => $validated['original_date'],
            'is_global' => $isGlobal,
        ]);

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur nasional berhasil ditambahkan.');
    }

    /**
     * Store a holiday adjustment (reschedule).
     */
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'holiday_id' => 'required|exists:holidays,id',
            'adjusted_date' => 'required|date',
            'school_unit_id' => 'nullable|exists:school_units,id',
            'reason' => 'nullable|string',
        ]);

        $holiday = Holiday::findOrFail($validated['holiday_id']);

        HolidayAdjustment::create([
            'holiday_id' => $holiday->id,
            'original_date' => $holiday->original_date,
            'adjusted_date' => $validated['adjusted_date'],
            'school_unit_id' => $validated['school_unit_id'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', 'Penyesuaian hari libur berhasil dibuat.');
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    /**
     * Remove adjustment.
     */
    public function destroyAdjustment($id)
    {
        $adj = HolidayAdjustment::findOrFail($id);
        $adj->delete();

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', 'Penyesuaian hari libur berhasil dihapus.');
    }

    /**
     * Trigger manual synchronization.
     */
    public function triggerSync()
    {
        $this->syncHolidaysToUnits();
        return redirect()->route('holidays.index')
            ->with('success', 'Sinkronisasi data libur selesai.');
    }

    /**
     * Sync holidays to active units.
     */
    private function syncHolidaysToUnits()
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $holidays = Holiday::with('adjustments')->get()->map(function ($h) {
            return [
                'name' => $h->name,
                'original_date' => $h->original_date->format('Y-m-d'),
                'is_global' => $h->is_global,
                'adjustments' => $h->adjustments->map(function ($adj) {
                    return [
                        'original_date' => $adj->original_date->format('Y-m-d'),
                        'adjusted_date' => $adj->adjusted_date->format('Y-m-d'),
                        'school_unit_id' => $adj->school_unit_id,
                        'reason' => $adj->reason,
                    ];
                })->toArray()
            ];
        })->toArray();

        foreach ($units as $unit) {
            try {
                Http::withHeaders([
                    'X-API-TOKEN' => $unit->api_token,
                    'Accept' => 'application/json',
                ])->post(rtrim($unit->api_url, '/') . '/sync/holidays', [
                    'holidays' => $holidays
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to sync holidays to unit {$unit->name}: " . $e->getMessage());
            }
        }
    }
}
