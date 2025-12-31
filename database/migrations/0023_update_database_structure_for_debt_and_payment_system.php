<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        // DOCGIA.TongNo
        if (Schema::hasTable('DOCGIA') && !Schema::hasColumn('DOCGIA', 'TongNo')) {
            Schema::table('DOCGIA', function (Blueprint $table) {
                $table->decimal('TongNo', 15, 2)->default(0)->comment('Tổng nợ của độc giả');
            });
        }

        // SACH.TriGia
        if (Schema::hasTable('SACH') && !Schema::hasColumn('SACH', 'TriGia')) {
            Schema::table('SACH', function (Blueprint $table) {

                $table->decimal('TriGia', 15, 2)->default(0)->comment('Trị giá sách (VNĐ)');
            });
        }


        if (Schema::hasTable('PHIEUPHAT') && !Schema::hasColumn('PHIEUPHAT', 'SoTienNop')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->decimal('SoTienNop', 15, 2)->default(0)->comment('Số tiền nộp');
            });
        }
    }

    public function down(): void
    {

        if (Schema::hasTable('PHIEUPHAT') && Schema::hasColumn('PHIEUPHAT', 'SoTienNop')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->dropColumn('SoTienNop');
            });
        }

        if (Schema::hasTable('SACH') && Schema::hasColumn('SACH', 'TriGia')) {



        }

        if (Schema::hasTable('DOCGIA') && Schema::hasColumn('DOCGIA', 'TongNo')) {

        }
    }
};
