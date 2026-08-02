<?php

namespace App\Http\Controllers;

use App\Models\BonusSchema;
use App\Models\SchoolUnit;
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
        return view('bonus-schemas.index', compact('schemas'));
    }

    /**
     * Store a newly created bonus schema in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'tiers' => 'required|array|min:1',
            'tiers.*.tier_level' => 'required|integer|min:1',
            'tiers.*.nominal' => 'required|numeric|min:0',
            'tiers.*.max_late_minutes' => 'required|integer|min:0',
            'tiers.*.max_absent_days' => 'sometimes|integer|min:0',
        ]);

        $isActive = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        $schema = BonusSchema::create([
            'name' => $validated['name'],
            'is_active' => $isActive,
        ]);

        foreach ($validated['tiers'] as $tData) {
            $schema->tiers()->create([
                'tier_level' => $tData['tier_level'],
                'nominal' => $tData['nominal'],
                'max_late_minutes' => $tData['max_late_minutes'],
                'max_absent_days' => $tData['max_absent_days'] ?? 0,
            ]);
        }

        // Auto sync to units
        $this->syncBonusSchemasToUnits();

        return redirect()->route('bonus-schemas.index')
            ->with('success', 'Skema bonus berhasil dibuat dan disinkronkan ke semua unit.');
    }

    /**
     * Update the specified bonus schema in storage.
     */
    public function update(Request $request, BonusSchema $bonusSchema)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'tiers' => 'required|array|min:1',
            'tiers.*.tier_level' => 'required|integer|min:1',
            'tiers.*.nominal' => 'required|numeric|min:0',
            'tiers.*.max_late_minutes' => 'required|integer|min:0',
            'tiers.*.max_absent_days' => 'sometimes|integer|min:0',
        ]);

        $isActive = $request->has('is_active');

        $bonusSchema->update([
            'name' => $validated['name'],
            'is_active' => $isActive,
        ]);

        $bonusSchema->tiers()->delete();

        foreach ($validated['tiers'] as $tData) {
            $bonusSchema->tiers()->create([
                'tier_level' => $tData['tier_level'],
                'nominal' => $tData['nominal'],
                'max_late_minutes' => $tData['max_late_minutes'],
                'max_absent_days' => $tData['max_absent_days'] ?? 0,
            ]);
        }

        // Auto sync to units
        $this->syncBonusSchemasToUnits();

        return redirect()->route('bonus-schemas.index')
            ->with('success', 'Skema bonus berhasil diperbarui dan disinkronkan ke semua unit.');
    }

    /**
     * Remove the specified bonus schema from storage.
     */
    public function destroy(BonusSchema $bonusSchema)
    {
        $bonusSchema->delete();

        // Auto sync to units
        $this->syncBonusSchemasToUnits();

        return redirect()->route('bonus-schemas.index')
            ->with('success', 'Skema bonus berhasil dihapus.');
    }

    /**
     * Trigger manual synchronization of all bonus schemas.
     */
    public function triggerSync()
    {
        $this->syncBonusSchemasToUnits();
        return redirect()->route('bonus-schemas.index')
            ->with('success', 'Sinkronisasi data skema bonus selesai.');
    }

    /**
     * Helper to sync schemas to active units.
     */
    private function syncBonusSchemasToUnits()
    {
        $units = SchoolUnit::where('is_active', true)->get();
        $schemas = BonusSchema::with('tiers')->get()->map(function ($schema) {
            return [
                'name' => $schema->name,
                'is_active' => $schema->is_active,
                'tiers' => $schema->tiers->map(function ($t) {
                    return [
                        'tier_level' => $t->tier_level,
                        'nominal' => $t->nominal,
                        'max_late_minutes' => $t->max_late_minutes,
                        'max_absent_days' => $t->max_absent_days,
                    ];
                })->toArray()
            ];
        })->toArray();

        if ($units->isEmpty()) {
            return;
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

        foreach ($units as $unit) {
            $response = $responses[$unit->id] ?? null;
            if (!$response || !$response->successful()) {
                $status = $response instanceof \Illuminate\Http\Client\Response ? $response->status() : 'Error/Timeout';
                Log::error("Failed to sync bonus schemas to unit {$unit->name}. Status: {$status}");
            }
        }
    }
}
