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
        $groupedHolidays = $this->getGroupedHolidays();
        $units = SchoolUnit::where('is_active', true)->orderBy('name')->get();
        $adjustments = HolidayAdjustment::with(['holiday', 'schoolUnit'])->orderBy('original_date', 'desc')->get();
        return view('holidays.index', compact('groupedHolidays', 'units', 'adjustments'));
    }

    /**
     * Store a newly created holiday in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'applies_to' => 'required|in:global,custom',
            'school_unit_ids' => 'required_if:applies_to,custom|array',
            'school_unit_ids.*' => 'exists:school_units,id',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $isGlobal = $validated['applies_to'] === 'global';
        $schoolUnitIds = $request->input('school_unit_ids', []);

        $currentDate = $startDate->copy();
        $createdCount = 0;

        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');

            // Find or create holiday for this day
            $holiday = Holiday::where('original_date', $dateStr)->first();
            if (!$holiday) {
                $holiday = Holiday::create([
                    'name' => $validated['name'],
                    'original_date' => $dateStr,
                    'is_global' => $isGlobal,
                ]);
            } else {
                $holiday->update([
                    'name' => $validated['name'],
                    'is_global' => $isGlobal,
                ]);
                // Clear any existing adjustments for this holiday
                $holiday->adjustments()->delete();
            }

            // Create adjustments if scope is specific units
            if (!$isGlobal && !empty($schoolUnitIds)) {
                foreach ($schoolUnitIds as $unitId) {
                    HolidayAdjustment::create([
                        'holiday_id' => $holiday->id,
                        'original_date' => $dateStr,
                        'adjusted_date' => $dateStr,
                        'school_unit_id' => $unitId,
                        'reason' => $validated['name'],
                    ]);
                }
            }

            $currentDate->addDay();
            $createdCount++;
        }

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', $createdCount . ' hari libur berhasil disimpan.');
    }

    /**
     * Store a holiday adjustment (reschedule).
     */
    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'holiday_id' => 'required|exists:holidays,id',
            'adjusted_date' => 'required|date',
            'school_unit_ids' => 'nullable|array',
            'school_unit_ids.*' => 'exists:school_units,id',
            'reason' => 'nullable|string',
        ]);

        $holiday = Holiday::findOrFail($validated['holiday_id']);
        $schoolUnitIds = $request->input('school_unit_ids');

        if (empty($schoolUnitIds)) {
            HolidayAdjustment::create([
                'holiday_id' => $holiday->id,
                'original_date' => $holiday->original_date,
                'adjusted_date' => $validated['adjusted_date'],
                'school_unit_id' => null,
                'reason' => $validated['reason'] ?? null,
            ]);
        } else {
            foreach ($schoolUnitIds as $unitId) {
                HolidayAdjustment::create([
                    'holiday_id' => $holiday->id,
                    'original_date' => $holiday->original_date,
                    'adjusted_date' => $validated['adjusted_date'],
                    'school_unit_id' => $unitId,
                    'reason' => $validated['reason'] ?? null,
                ]);
            }
        }

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', 'Penyesuaian hari libur berhasil dibuat.');
    }

    /**
     * Update the specified holiday in storage.
     */
    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'applies_to' => 'required|in:global,custom',
            'school_unit_ids' => 'required_if:applies_to,custom|array',
            'school_unit_ids.*' => 'exists:school_units,id',
            'ids' => 'required|array',
            'ids.*' => 'exists:holidays,id',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $isGlobal = $validated['applies_to'] === 'global';
        $schoolUnitIds = $request->input('school_unit_ids', []);
        $oldIds = $request->input('ids', []);

        // Validate that dates are not taken by other holidays (excluding the ones we are editing)
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');
            $existing = Holiday::where('original_date', $dateStr)
                ->whereNotIn('id', $oldIds)
                ->first();
            if ($existing) {
                return redirect()->back()->withErrors(['start_date' => "Tanggal {$dateStr} sudah terdaftar sebagai hari libur lain ({$existing->name})."]);
            }
            $currentDate->addDay();
        }

        // Delete the old holidays in the group
        Holiday::whereIn('id', $oldIds)->delete();

        // Create the new holiday range
        $currentDate = $startDate->copy();
        $createdCount = 0;

        while ($currentDate->lte($endDate)) {
            $dateStr = $currentDate->format('Y-m-d');

            $holiday = Holiday::create([
                'name' => $validated['name'],
                'original_date' => $dateStr,
                'is_global' => $isGlobal,
            ]);

            // Create adjustments if custom
            if (!$isGlobal && !empty($schoolUnitIds)) {
                foreach ($schoolUnitIds as $unitId) {
                    HolidayAdjustment::create([
                        'holiday_id' => $holiday->id,
                        'original_date' => $dateStr,
                        'adjusted_date' => $dateStr,
                        'school_unit_id' => $unitId,
                        'reason' => $validated['name'],
                    ]);
                }
            }

            $currentDate->addDay();
            $createdCount++;
        }

        // Auto sync
        $this->syncHolidaysToUnits();

        return redirect()->route('holidays.index')
            ->with('success', 'Hari libur berhasil diperbarui.');
    }

    /**
     * Remove the specified holiday.
     */
    public function destroy(Holiday $holiday)
    {
        $ids = request('ids', [$holiday->id]);
        Holiday::whereIn('id', $ids)->delete();

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
                Http::timeout(3)->withHeaders([
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

    /**
     * Helper to group consecutive holidays with the same name.
     */
    private function getGroupedHolidays()
    {
        $holidays = Holiday::with('adjustments.schoolUnit')->orderBy('original_date', 'asc')->get();
        $grouped = [];

        $byName = $holidays->groupBy('name');

        foreach ($byName as $name => $items) {
            $items = $items->sortBy('original_date')->values();
            
            if ($items->isEmpty()) continue;
            
            $currentGroup = [];
            $prevDate = null;
            
            foreach ($items as $item) {
                $currentDate = $item->original_date;
                
                if ($prevDate === null) {
                    $currentGroup[] = $item;
                } else {
                    $diff = abs($currentDate->diffInDays($prevDate));
                    if ($diff == 1) {
                        $currentGroup[] = $item;
                    } else {
                        $grouped[] = $this->buildGroupRow($name, $currentGroup);
                        $currentGroup = [$item];
                    }
                }
                $prevDate = $currentDate;
            }
            
            if (!empty($currentGroup)) {
                $grouped[] = $this->buildGroupRow($name, $currentGroup);
            }
        }

        usort($grouped, function ($a, $b) {
            return strcmp($b['start_date']->format('Y-m-d'), $a['start_date']->format('Y-m-d'));
        });

        return $grouped;
    }

    private function buildGroupRow($name, $items)
    {
        $startDate = $items[0]->original_date;
        $endDate = end($items)->original_date;
        
        $isGlobal = true;
        $adjustments = collect();
        $ids = [];
        $itemsData = [];
        $adjustmentsData = [];
        
        foreach ($items as $item) {
            $ids[] = $item->id;
            $itemsData[] = [
                'id' => $item->id,
                'date_formatted' => $item->original_date->format('d M Y'),
            ];
            if (!$item->is_global) {
                $isGlobal = false;
            }
            foreach ($item->adjustments as $adj) {
                $adjustments->push($adj);
                $adjustmentsData[] = [
                    'id' => $adj->id,
                    'school_unit_name' => $adj->schoolUnit ? $adj->schoolUnit->name : 'Semua Unit',
                    'original_date_formatted' => $adj->original_date->format('d M Y'),
                    'adjusted_date_formatted' => $adj->adjusted_date->format('d M Y'),
                    'reason' => $adj->reason ?? 'Tidak ada alasan',
                    'destroy_url' => route('holidays.destroy-adjustment', $adj->id),
                ];
            }
        }
        
        return [
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_global' => $isGlobal,
            'ids' => $ids,
            'items_data' => $itemsData,
            'adjustments_data' => $adjustmentsData,
            'adjustments' => $adjustments,
        ];
    }
}
