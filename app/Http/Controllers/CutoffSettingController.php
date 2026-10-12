<?php

namespace App\Http\Controllers;

use App\Models\BonusSchema;
use App\Models\SchoolUnit;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CutoffSettingController extends Controller
{
    /**
     * Display the cutoff settings form.
     */
    public function index()
    {
        $cutoffDate = (int) Setting::get('payroll_cutoff_date', 25);
        $defaultPicketEffectiveDate = Carbon::parse('2026-10-01')->subMonth()->setDay($cutoffDate + 1)->format('Y-m-d');
        
        $schoolUnits = SchoolUnit::where('is_active', true)->orderBy('id')->get()->map(function($unit) use ($defaultPicketEffectiveDate) {
            $unit->picket_effective_date = Setting::get(
                'picket_effective_date_' . $unit->id, 
                Setting::get('picket_bonus_effective_date', $defaultPicketEffectiveDate)
            );
            return $unit;
        });

        $picketBonusSchemaId = Setting::get('picket_bonus_schema_id');
        $bonusSchemas = BonusSchema::where('is_active', true)->with('tiers')->orderBy('name')->get();

        return view('settings.index', compact('cutoffDate', 'defaultPicketEffectiveDate', 'schoolUnits', 'bonusSchemas', 'picketBonusSchemaId'));
    }

    /**
     * Update the cutoff settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'payroll_cutoff_date' => 'required|integer|min:1|max:31',
            'picket_bonus_schema_id' => 'nullable|exists:bonus_schemas,id',
            'unit_picket_effective_dates' => 'nullable|array',
            'unit_picket_effective_dates.*' => 'nullable|date',
        ]);

        Setting::set('payroll_cutoff_date', $request->input('payroll_cutoff_date'));

        if ($request->has('picket_bonus_schema_id')) {
            Setting::set('picket_bonus_schema_id', $request->input('picket_bonus_schema_id'));
        }

        if ($request->has('unit_picket_effective_dates')) {
            foreach ($request->input('unit_picket_effective_dates') as $unitId => $effectiveDate) {
                if (!empty($effectiveDate)) {
                    Setting::set('picket_effective_date_' . $unitId, $effectiveDate);
                }
            }
        }

        return redirect()->back()->with('success', 'Pengaturan cut-off & konfigurasi piket berhasil diperbarui!');
    }
}
