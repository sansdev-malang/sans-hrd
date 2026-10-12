<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('working_shifts', function (Blueprint $table) {
            $table->foreignId('school_unit_id')
                ->nullable()
                ->after('id')
                ->constrained('school_units')
                ->nullOnDelete();
        });

        // Smart backfill existing shifts based on naming
        $paud = DB::table('school_units')->where('name', 'like', '%PAUD%')->first();
        $sd = DB::table('school_units')->where('name', 'like', '%SD%')->first();
        $smp = DB::table('school_units')->where('name', 'like', '%SMP%')->first();

        if ($paud) {
            DB::table('working_shifts')
                ->where('name', 'like', '%PAUD%')
                ->orWhere('code', 'like', '%paud%')
                ->update(['school_unit_id' => $paud->id]);
        }

        if ($sd) {
            DB::table('working_shifts')
                ->where(function($q) {
                    $q->where('name', 'like', '% SD%')
                      ->orWhere('name', 'like', '%SD %')
                      ->orWhere('code', 'like', '%_sd%')
                      ->orWhere('short_code', 'like', '%SD%');
                })
                ->update(['school_unit_id' => $sd->id]);
        }

        if ($smp) {
            DB::table('working_shifts')
                ->where(function($q) {
                    $q->where('name', 'like', '% SMP%')
                      ->orWhere('name', 'like', '%SMP %')
                      ->orWhere('code', 'like', '%_smp%')
                      ->orWhere('short_code', 'like', '%SMP%');
                })
                ->update(['school_unit_id' => $smp->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('working_shifts', function (Blueprint $table) {
            $table->dropForeign(['school_unit_id']);
            $table->dropColumn('school_unit_id');
        });
    }
};
