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
        // 1. Thêm cột TongNo cho bảng DOCGIA nếu chưa có
        if (!Schema::hasColumn('DOCGIA', 'TongNo')) {
            Schema::table('DOCGIA', function (Blueprint $table) {
                $table->decimal('TongNo', 15, 2)->default(0)->comment('Tổng nợ của độc giả')->after('NgayHetHan');
            });
        }

        // 2. Thêm cột TriGia cho bảng SACH nếu chưa có
        if (!Schema::hasColumn('SACH', 'TriGia')) {
            Schema::table('SACH', function (Blueprint $table) {
                $table->decimal('TriGia', 12, 2)->default(0)->comment('Trị giá sách (VNĐ)')->after('NgayNhap');
            });
        }

        // 3. Đảm bảo bảng PHIEUTHUTIENPHAT có cấu trúc đúng
        if (!Schema::hasTable('PHIEUTHUTIENPHAT')) {
            Schema::create('PHIEUTHUTIENPHAT', function (Blueprint $table) {
                $table->id();
                $table->string('MaPhieu', 20)->unique()->comment('Mã phiếu thu tiền phạt');
                $table->foreignId('docgia_id')->constrained('DOCGIA')->onDelete('cascade')->comment('ID độc giả');
                $table->decimal('SoTienNop', 15, 2)->comment('Số tiền nộp');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa các cột đã thêm
        if (Schema::hasColumn('DOCGIA', 'TongNo')) {
            Schema::table('DOCGIA', function (Blueprint $table) {
                $table->dropColumn('TongNo');
            });
        }

        if (Schema::hasColumn('SACH', 'TriGia')) {
            Schema::table('SACH', function (Blueprint $table) {
                $table->dropColumn('TriGia');
            });
        }
    }
};
