<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\BonusSchema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Nonaktifkan Skema Riwayat Lama (ID 1 & 2) agar diarsipkan
        DB::table('bonus_schemas')
            ->whereIn('id', [1, 2])
            ->update([
                'is_active' => false,
                'calculation_mode' => 'late_tolerance',
            ]);

        // 2. Perbarui Skema Piket (Hadir Lebih Awal) - 9 Tier
        $picketSchema = BonusSchema::firstOrCreate(
            ['name' => 'Skema Piket (Hadir Lebih Awal)'],
            ['calculation_mode' => 'early_arrival', 'is_active' => true]
        );
        $picketSchema->update([
            'calculation_mode' => 'early_arrival',
            'is_active' => true,
        ]);
        $picketSchema->tiers()->delete();

        $picketTiers = [
            ['tier_level' => 1, 'min_early_minutes' => 14, 'max_late_minutes' => 0, 'nominal' => 10000],
            ['tier_level' => 2, 'min_early_minutes' => 12, 'max_late_minutes' => 0, 'nominal' => 9000],
            ['tier_level' => 3, 'min_early_minutes' => 10, 'max_late_minutes' => 0, 'nominal' => 8000],
            ['tier_level' => 4, 'min_early_minutes' => 8,  'max_late_minutes' => 0, 'nominal' => 7000],
            ['tier_level' => 5, 'min_early_minutes' => 6,  'max_late_minutes' => 0, 'nominal' => 6000],
            ['tier_level' => 6, 'min_early_minutes' => 4,  'max_late_minutes' => 0, 'nominal' => 5000],
            ['tier_level' => 7, 'min_early_minutes' => 2,  'max_late_minutes' => 0, 'nominal' => 4000],
            ['tier_level' => 8, 'min_early_minutes' => 1,  'max_late_minutes' => 0, 'nominal' => 3000],
            ['tier_level' => 9, 'min_early_minutes' => 0,  'max_late_minutes' => 0, 'nominal' => 2000],
        ];
        foreach ($picketTiers as $t) {
            $picketSchema->tiers()->create($t);
        }

        // Tetapkan sebagai skema piket aktif
        Setting::set('picket_bonus_schema_id', $picketSchema->id);

        // 3. Buat / Perbarui Skema Reguler (Toleransi Keterlambatan) - 9 Tier
        $regulerSchema = BonusSchema::firstOrCreate(
            ['name' => 'Skema Reguler'],
            ['calculation_mode' => 'late_tolerance', 'is_active' => true]
        );
        $regulerSchema->update([
            'calculation_mode' => 'late_tolerance',
            'is_active' => true,
        ]);
        $regulerSchema->tiers()->delete();

        $regulerTiers = [
            ['tier_level' => 1, 'max_late_minutes' => 0,   'min_early_minutes' => 0, 'nominal' => 10000],
            ['tier_level' => 2, 'max_late_minutes' => 2,   'min_early_minutes' => 0, 'nominal' => 9000],
            ['tier_level' => 3, 'max_late_minutes' => 4,   'min_early_minutes' => 0, 'nominal' => 8000],
            ['tier_level' => 4, 'max_late_minutes' => 6,   'min_early_minutes' => 0, 'nominal' => 7000],
            ['tier_level' => 5, 'max_late_minutes' => 8,   'min_early_minutes' => 0, 'nominal' => 6000],
            ['tier_level' => 6, 'max_late_minutes' => 10,  'min_early_minutes' => 0, 'nominal' => 5000],
            ['tier_level' => 7, 'max_late_minutes' => 12,  'min_early_minutes' => 0, 'nominal' => 4000],
            ['tier_level' => 8, 'max_late_minutes' => 14,  'min_early_minutes' => 0, 'nominal' => 3000],
            ['tier_level' => 9, 'max_late_minutes' => 720, 'min_early_minutes' => 0, 'nominal' => 2000],
        ];
        foreach ($regulerTiers as $t) {
            $regulerSchema->tiers()->create($t);
        }

        // 4. Sinkronkan ke unit jika service tersedia
        try {
            $controller = app(\App\Http\Controllers\BonusSchemaController::class);
            $ref = new \ReflectionClass($controller);
            if ($ref->hasMethod('syncBonusSchemasToUnits')) {
                $method = $ref->getMethod('syncBonusSchemasToUnits');
                $method->setAccessible(true);
                $method->invoke($controller);
            }
        } catch (\Throwable $e) {
            // Abaikan error jika unit offline saat migrasi
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive rollback to avoid altering attendance records
    }
};
