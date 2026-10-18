<?php

namespace App\Http\Controllers;

use App\Models\BonusSchema;
use App\Models\SchoolUnit;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BonusSchemaController extends Controller
{
    /**
     * Display a listing of bonus schemas.
     */
    public function index()
    {
        $schemas = BonusSchema::with('tiers')->orderBy('name')->get();
        $picketBonusSchemaId = (int) Setting::get('picket_bonus_schema_id');
        return view('bonus-schemas.index', compact('schemas', 'picketBonusSchemaId'));
    }

    /**
     * Set a schema specifically as the Picket Bonus Schema.
     */
    public function setAsPicketSchema(BonusSchema $bonusSchema)
    {
        Setting::set('picket_bonus_schema_id', $bonusSchema->id);
        return redirect()->back()->with('success', 'Skema "' . $bonusSchema->name . '" berhasil ditetapkan sebagai Skema Khusus Piket!');
    }

    /**
     * Unset the Picket Bonus Schema.
     */
    public function unsetPicketSchema()
    {
        Setting::set('picket_bonus_schema_id', null);
        return redirect()->back()->with('success', 'Skema khusus piket dinonaktifkan (kalkulasi piket kembali menggunakan skema reguler).');
    }

    /**
     * Store a newly created bonus schema in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'calculation_mode' => 'nullable|string|in:early_arrival,late_tolerance',
            'tiers' => 'required|array|min:1',
            'tiers.*.tier_level' => 'required|integer|min:1',
            'tiers.*.nominal' => 'required|numeric|min:0',
            'tiers.*.min_early_minutes' => 'nullable|integer|min:0',
            'tiers.*.max_late_minutes' => 'nullable|integer|min:0',
            'tiers.*.max_absent_days' => 'sometimes|integer|min:0',
        ]);

        $isActive = $request->has('is_active') ? (bool)$request->input('is_active') : true;
        $calculationMode = $request->input('calculation_mode', 'early_arrival');
        if (!in_array($calculationMode, ['early_arrival', 'late_tolerance'])) {
            $calculationMode = 'early_arrival';
        }

        $schema = BonusSchema::create([
            'name' => $validated['name'],
            'is_active' => $isActive,
            'calculation_mode' => $calculationMode,
        ]);

        foreach ($validated['tiers'] as $tData) {
            $schema->tiers()->create([
                'tier_level' => $tData['tier_level'],
                'nominal' => $tData['nominal'],
                'min_early_minutes' => $tData['min_early_minutes'] ?? 0,
                'max_late_minutes' => $tData['max_late_minutes'] ?? 0,
                'max_absent_days' => $tData['max_absent_days'] ?? 0,
            ]);
        }

        // Auto sync to units
        $failed = $this->syncBonusSchemasToUnits();

        if (!empty($failed)) {
            return redirect()->route('bonus-schemas.index', ['tab' => $calculationMode])
                ->with('error', 'Skema bonus berhasil dibuat lokal, namun gagal disinkronkan ke unit: ' . implode(', ', $failed) . '. Silakan klik tombol Sync Ulang.');
        }

        return redirect()->route('bonus-schemas.index', ['tab' => $calculationMode])
            ->with('success', 'Skema bonus berhasil dibuat dan disinkronkan ke semua unit.');
    }

    /**
     * Update the specified bonus schema in storage.
     */
    public function update(Request $request, BonusSchema $bonusSchema)
    {
        if (in_array($bonusSchema->id, [1, 2])) {
            return redirect()->route('bonus-schemas.index', ['tab' => 'late_tolerance'])
                ->with('error', 'Skema riwayat "' . $bonusSchema->name . '" terkunci demi menjaga integritas data riwayat absensi masa lalu.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'calculation_mode' => 'nullable|string|in:early_arrival,late_tolerance',
            'tiers' => 'required|array|min:1',
            'tiers.*.tier_level' => 'required|integer|min:1',
            'tiers.*.nominal' => 'required|numeric|min:0',
            'tiers.*.min_early_minutes' => 'nullable|integer|min:0',
            'tiers.*.max_late_minutes' => 'nullable|integer|min:0',
            'tiers.*.max_absent_days' => 'sometimes|integer|min:0',
        ]);

        $isActive = $request->has('is_active');
        $calculationMode = $request->input('calculation_mode', $bonusSchema->calculation_mode ?? 'early_arrival');
        if (!in_array($calculationMode, ['early_arrival', 'late_tolerance'])) {
            $calculationMode = 'early_arrival';
        }

        $bonusSchema->update([
            'name' => $validated['name'],
            'is_active' => $isActive,
            'calculation_mode' => $calculationMode,
        ]);

        $bonusSchema->tiers()->delete();

        foreach ($validated['tiers'] as $tData) {
            $bonusSchema->tiers()->create([
                'tier_level' => $tData['tier_level'],
                'nominal' => $tData['nominal'],
                'min_early_minutes' => $tData['min_early_minutes'] ?? 0,
                'max_late_minutes' => $tData['max_late_minutes'] ?? 0,
                'max_absent_days' => $tData['max_absent_days'] ?? 0,
            ]);
        }

        // Auto sync to units
        $failed = $this->syncBonusSchemasToUnits();

        if (!empty($failed)) {
            return redirect()->route('bonus-schemas.index', ['tab' => $calculationMode])
                ->with('error', 'Skema bonus berhasil diperbarui lokal, namun gagal disinkronkan ke unit: ' . implode(', ', $failed) . '. Silakan klik tombol Sync Ulang.');
        }

        return redirect()->route('bonus-schemas.index', ['tab' => $calculationMode])
            ->with('success', 'Skema bonus berhasil diperbarui dan disinkronkan ke semua unit.');
    }

    /**
     * Remove the specified bonus schema from storage.
     */
    public function destroy(Request $request, BonusSchema $bonusSchema)
    {
        if (in_array($bonusSchema->id, [1, 2])) {
            return redirect()->route('bonus-schemas.index', ['tab' => 'late_tolerance'])
                ->with('error', 'Skema riwayat "' . $bonusSchema->name . '" terkunci demi menjaga integritas data riwayat absensi masa lalu.');
        }

        $mode = $bonusSchema->calculation_mode ?? $request->query('tab', 'early_arrival');
        $bonusSchema->delete();

        // Auto sync to units
        $failed = $this->syncBonusSchemasToUnits();

        if (!empty($failed)) {
            return redirect()->route('bonus-schemas.index', ['tab' => $mode])
                ->with('error', 'Skema bonus berhasil dihapus lokal, namun gagal menyinkronkan penghapusan ke unit: ' . implode(', ', $failed) . '. Silakan klik tombol Sync Ulang.');
        }

        return redirect()->route('bonus-schemas.index', ['tab' => $mode])
            ->with('success', 'Skema bonus berhasil dihapus.');
    }

    /**
     * Toggle active status of a bonus schema.
     */
    public function toggleActive(BonusSchema $bonusSchema)
    {
        $newStatus = !$bonusSchema->is_active;
        $bonusSchema->update(['is_active' => $newStatus]);

        // Auto sync to units
        $failed = $this->syncBonusSchemasToUnits();

        $statusMsg = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        if (!empty($failed)) {
            return redirect()->back()
                ->with('error', 'Skema bonus berhasil ' . $statusMsg . ' lokal, namun gagal disinkronkan ke unit: ' . implode(', ', $failed) . '. Silakan klik tombol Sync Ulang.');
        }

        return redirect()->back()
            ->with('success', 'Skema bonus "' . $bonusSchema->name . '" berhasil ' . $statusMsg . ' dan disinkronkan ke semua unit.');
    }

    /**
     * Trigger manual synchronization of all bonus schemas.
     */
    public function triggerSync(Request $request)
    {
        $tab = $request->query('tab', 'early_arrival');
        $failed = $this->syncBonusSchemasToUnits();

        if (!empty($failed)) {
            return redirect()->route('bonus-schemas.index', ['tab' => $tab])
                ->with('error', 'Gagal menyinkronkan skema bonus ke unit: ' . implode(', ', $failed) . '. Silakan coba beberapa saat lagi.');
        }

        return redirect()->route('bonus-schemas.index', ['tab' => $tab])
            ->with('success', 'Sinkronisasi data skema bonus selesai dan berhasil terkirim ke semua unit.');
    }

    /**
     * Helper to sync schemas to active units.
     * Returns an array of failed unit names.
     */
    private function syncBonusSchemasToUnits(): array
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $schemas = BonusSchema::with('tiers')->get()->map(function ($schema) {
            return [
                'name' => $schema->name,
                'is_active' => $schema->is_active,
                'calculation_mode' => $schema->calculation_mode ?? 'early_arrival',
                'tiers' => $schema->tiers->map(function ($t) {
                    return [
                        'tier_level' => $t->tier_level,
                        'nominal' => $t->nominal,
                        'min_early_minutes' => $t->min_early_minutes ?? 0,
                        'max_late_minutes' => $t->max_late_minutes ?? 0,
                        'max_absent_days' => $t->max_absent_days ?? 0,
                    ];
                })->toArray()
            ];
        })->toArray();

        if ($units->isEmpty()) {
            return [];
        }

        $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($units, $schemas) {
            return $units->map(function ($unit) use ($pool, $schemas) {
                return $pool->as($unit->id)
                    ->withHeaders([
                        'X-API-TOKEN' => $unit->api_token,
                        'Accept' => 'application/json',
                    ])->timeout(5)->post(rtrim($unit->api_url, '/') . '/sync/bonus-schemas', [
                        'schemas' => $schemas
                    ]);
            });
        });

        $failedUnits = [];
        foreach ($units as $unit) {
            $response = $responses[$unit->id] ?? null;
            if (!($response instanceof \Illuminate\Http\Client\Response) || !$response->successful()) {
                $status = ($response instanceof \Illuminate\Http\Client\Response) ? $response->status() : 'Error/Connection';
                Log::error("Failed to sync bonus schemas to unit {$unit->name}. Status: {$status}");
                $failedUnits[] = $unit->name;
            }
        }

        return $failedUnits;
    }
}
