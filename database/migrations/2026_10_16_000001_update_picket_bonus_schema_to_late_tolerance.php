<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\BonusSchema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update Skema Piket to late_tolerance
        $picketSchema = BonusSchema::find(3);
        if (!$picketSchema) {
            $picketSchema = BonusSchema::where('name', 'like', '%piket%')->first();
        }

        if ($picketSchema) {
            $picketSchema->update([
                'name' => 'Skema Piket (Toleransi Keterlambatan)',
                'calculation_mode' => 'late_tolerance',
                'is_active' => true,
            ]);

            $picketSchema->tiers()->delete();

            $picketTiers = [
                ['tier_level' => 1, 'min_early_minutes' => 0, 'max_late_minutes' => 0,   'nominal' => 10000],
                ['tier_level' => 2, 'min_early_minutes' => 0, 'max_late_minutes' => 2,   'nominal' => 9000],
                ['tier_level' => 3, 'min_early_minutes' => 0, 'max_late_minutes' => 4,   'nominal' => 8000],
                ['tier_level' => 4, 'min_early_minutes' => 0, 'max_late_minutes' => 6,   'nominal' => 7000],
                ['tier_level' => 5, 'min_early_minutes' => 0, 'max_late_minutes' => 8,   'nominal' => 6000],
                ['tier_level' => 6, 'min_early_minutes' => 0, 'max_late_minutes' => 10,  'nominal' => 5000],
                ['tier_level' => 7, 'min_early_minutes' => 0, 'max_late_minutes' => 12,  'nominal' => 4000],
                ['tier_level' => 8, 'min_early_minutes' => 0, 'max_late_minutes' => 14,  'nominal' => 3000],
                ['tier_level' => 9, 'min_early_minutes' => 0, 'max_late_minutes' => 720, 'nominal' => 2000],
            ];

            foreach ($picketTiers as $t) {
                $picketSchema->tiers()->create($t);
            }

            Setting::set('picket_bonus_schema_id', $picketSchema->id);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
