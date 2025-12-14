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
        Schema::table('PHIEUTHUTIENPHAT', function (Blueprint $table) {
            $table->date('NgayThu')->nullable()->after('SoTienNop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('PHIEUTHUTIENPHAT', function (Blueprint $table) {
            $table->dropColumn('NgayThu');
        });
    }
}; 