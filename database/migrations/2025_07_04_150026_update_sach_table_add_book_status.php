<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Cập nhật comment cho cột TinhTrang trong bảng SACH:
     * - 0: Đang được mượn
     * - 1: Có sẵn (có thể mượn)
     * - 3: Hỏng (không thể mượn)
     * - 4: Bị mất (không thể mượn)
     */
    public function up(): void
    {
        Schema::table('SACH', function (Blueprint $table) {
            // Cập nhật comment cho cột TinhTrang để rõ ràng về các trạng thái
            $table->tinyInteger('TinhTrang')->default(1)->comment('0=Đang mượn, 1=Có sẵn, 3=Hỏng, 4=Bị mất')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('SACH', function (Blueprint $table) {
            // Khôi phục về comment cũ
            $table->tinyInteger('TinhTrang')->default(1)->comment('0=Đang mượn, 1=Có sẵn')->change();
        });
    }
};
