<?php

namespace App\Http\Controllers;

use App\Models\WorkingShift;
use App\Models\SchoolUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorkingShiftController extends Controller
{
    /**
     * Display a listing of working shifts.
     */
    public function index()
    {
        $shifts = WorkingShift::with('details')->latest()->get();
        return view('working-shifts.index', compact('shifts'));
    }

    /**
     * Store a newly created working shift in storage.
     */
    public function store(Request $request)
    {
        if (!$request->filled('code') && $request->filled('name')) {
            $request->merge([
                'code' => strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $request->input('name')))
            ]);
        } elseif ($request->filled('code')) {
            $request->merge([
                'code' => strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $request->input('code')))
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:working_shifts,code',
            'short_code' => 'nullable|string|max:10',
            'is_shift' => 'sometimes|boolean',
            'description' => 'nullable|string',
            'days' => 'required|array|min:7|max:7',
            'days.*.start_time' => 'nullable|string',
            'days.*.end_time' => 'nullable|string',
            'days.*.is_off' => 'sometimes|boolean',
        ]);

        $isShift = $request->has('is_shift') ? (bool)$request->input('is_shift') : false;

        $shift = WorkingShift::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'short_code' => $validated['short_code'] ?? null,
            'is_shift' => $isShift,
            'description' => $validated['description'] ?? null,
        ]);

        foreach ($validated['days'] as $dayOfWeek => $dayData) {
            $isOff = isset($dayData['is_off']) ? (bool)$dayData['is_off'] : false;
            $shift->details()->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => $isOff ? null : ($dayData['start_time'] ?? null),
                'end_time' => $isOff ? null : ($dayData['end_time'] ?? null),
                'is_off' => $isOff,
            ]);
        }

        // Auto sync to units
        $failed = $this->syncShiftsToUnits();

        if (!empty($failed)) {
            return redirect()->route('working-shifts.index')
                ->with('error', 'Shift kerja berhasil ditambahkan secara lokal, namun gagal disinkronkan ke unit: ' . implode(', ', $failed) . '. Silakan klik tombol Sync Ulang.');
        }

        return redirect()->route('working-shifts.index')
            ->with('success', 'Shift kerja berhasil ditambahkan dan disinkronkan ke semua unit.');
    }

    /**
     * Update the specified working shift in storage.
     */
    public function update(Request $request, WorkingShift $workingShift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_code' => 'nullable|string|max:10',
            'is_shift' => 'sometimes|boolean',
            'description' => 'nullable|string',
            'days' => 'required|array|min:7|max:7',
            'days.*.start_time' => 'nullable|string',
            'days.*.end_time' => 'nullable|string',
            'days.*.is_off' => 'sometimes|boolean',
        ]);

        $isShift = $request->has('is_shift');

        $workingShift->update([
            'name' => $validated['name'],
            'short_code' => $validated['short_code'] ?? null,
            'is_shift' => $isShift,
            'description' => $validated['description'] ?? null,
        ]);

        $workingShift->details()->delete();

        foreach ($validated['days'] as $dayOfWeek => $dayData) {
            $isOff = isset($dayData['is_off']) ? (bool)$dayData['is_off'] : false;
            $workingShift->details()->create([
                'day_of_week' => $dayOfWeek,
                'start_time' => $isOff ? null : ($dayData['start_time'] ?? null),
                'end_time' => $isOff ? null : ($dayData['end_time'] ?? null),
                'is_off' => $isOff,
            ]);
        }

        // Auto sync to units
        $failed = $this->syncShiftsToUnits();

        if (!empty($failed)) {
            return redirect()->route('working-shifts.index')
                ->with('error', 'Shift kerja berhasil diperbarui secara lokal, namun gagal disinkronkan ke unit: ' . implode(', ', $failed) . '. Silakan klik tombol Sync Ulang.');
        }

        return redirect()->route('working-shifts.index')
            ->with('success', 'Shift kerja berhasil diperbarui dan disinkronkan ke semua unit.');
    }

    /**
     * Remove the specified working shift from storage.
     */
    public function destroy(WorkingShift $workingShift)
    {
        $workingShift->delete();

        // Auto sync to units
        $failed = $this->syncShiftsToUnits();

        if (!empty($failed)) {
            return redirect()->route('working-shifts.index')
                ->with('error', 'Shift kerja berhasil dihapus secara lokal, namun gagal menyinkronkan penghapusan ke unit: ' . implode(', ', $failed) . '. Silakan klik tombol Sync Ulang.');
        }

        return redirect()->route('working-shifts.index')
            ->with('success', 'Shift kerja berhasil dihapus.');
    }

    /**
     * Trigger manual synchronization of all shifts.
     */
    public function triggerSync()
    {
        $failed = $this->syncShiftsToUnits();

        if (!empty($failed)) {
            return redirect()->route('working-shifts.index')
                ->with('error', 'Gagal menyinkronkan data shift ke unit sekolah: ' . implode(', ', $failed) . '. Silakan coba beberapa saat lagi.');
        }

        return redirect()->route('working-shifts.index')
            ->with('success', 'Sinkronisasi data shift ke semua unit sekolah selesai dan berhasil.');
    }

    /**
     * Helper to sync shifts to all active units.
     * Returns an array of failed unit names.
     */
    private function syncShiftsToUnits(): array
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $shifts = WorkingShift::with('details')->get()->map(function ($shift) {
            return [
                'name' => $shift->name,
                'code' => $shift->code,
                'short_code' => $shift->short_code,
                'is_shift' => $shift->is_shift,
                'description' => $shift->description,
                'details' => $shift->details->map(function ($d) {
                    return [
                        'day_of_week' => $d->day_of_week,
                        'start_time' => $d->start_time,
                        'end_time' => $d->end_time,
                        'is_off' => $d->is_off,
                    ];
                })->toArray()
            ];
        })->toArray();

        if ($units->isEmpty()) {
            return [];
        }

        $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($units, $shifts) {
            return $units->map(function ($unit) use ($pool, $shifts) {
                return $pool->as($unit->id)
                    ->withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->timeout(5)->post(rtrim($unit->api_url, '/') . '/sync/shifts', [
                        'shifts' => $shifts
                    ]);
            });
        });

        $failedUnits = [];
        foreach ($units as $unit) {
            $response = $responses[$unit->id] ?? null;
            if (!$response || !$response->successful()) {
                $status = $response instanceof \Illuminate\Http\Client\Response ? $response->status() : 'Error/Timeout';
                Log::error("Failed to sync shifts to unit {$unit->name}. Status: {$status}");
                $failedUnits[] = $unit->name;
            }
        }

        return $failedUnits;
    }
}
