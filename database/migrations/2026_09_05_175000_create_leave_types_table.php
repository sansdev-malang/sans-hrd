<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('leave_types')) {
            Schema::create('leave_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('status_code', 2)->default('I'); // S, I, C, H
                $table->string('target_unit')->default('all'); // 'all', 'sd', 'smp', 'paud' or comma separated ids
                $table->boolean('requires_attendance')->default(true); // false = Bebas Absen, true = Wajib Absen
                $table->boolean('requires_approval')->default(true); // true = Perlu Persetujuan, false = Otomatis Setuju
                $table->boolean('gets_presence_bonus')->default(false); // true = Dapat Bonus
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
