<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bảng liên kết SACH - THELOAI (n-n).
     *
     * Lưu ý: pipeline hiện tại đã có DAUSACH.MaTheLoai (1 DAUSACH thuộc 1 THELOAI),
     * nhưng giữ bảng này để tương thích/ mở rộng (nhiều thể loại cho 1 sách) nếu cần.
     */
    public function up(): void
    {
        Schema::create('SACH_THELOAI', function (Blueprint $table) {
            $table->unsignedBigInteger('MaSach');
            $table->unsignedBigInteger('MaTheLoai');

            $table->primary(['MaSach', 'MaTheLoai']);

            $table->foreign('MaSach')
                ->references('MaSach')
                ->on('SACH')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('MaTheLoai')
                ->references('MaTheLoai')
                ->on('THELOAI')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SACH_THELOAI');
    }
};
