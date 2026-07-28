<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('employee_name')->nullable()->after('employee_id');
        });
    }

    public function down()
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('employee_name');
        });
    }
};