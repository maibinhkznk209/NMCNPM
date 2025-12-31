<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {



        if (!Schema::hasTable('SACH')) {
            return;
        }

        if (!Schema::hasColumn('SACH', 'TriGia')) {
            Schema::table('SACH', function (Blueprint $table) {
                $table->decimal('TriGia', 15, 2)
                    ->default(0)
                    ->comment('Trị giá sách (VNĐ)');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('SACH') && Schema::hasColumn('SACH', 'TriGia')) {
            Schema::table('SACH', function (Blueprint $table) {
                $table->dropColumn('TriGia');
            });
        }
    }
};
