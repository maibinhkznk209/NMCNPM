<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        if (!Schema::hasTable('PHIEUPHAT')) {
            return;
        }


        if (!Schema::hasColumn('PHIEUPHAT', 'NgayThu')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->date('NgayThu')->nullable()->after('SoTienNop');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('PHIEUPHAT') && Schema::hasColumn('PHIEUPHAT', 'NgayThu')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->dropColumn('NgayThu');
            });
        }
    }
};
