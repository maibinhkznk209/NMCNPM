<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * PIPELINE (theo Sach.php + SachController.php):
         * - MaSach: PK (int auto increment)
         * - MaDauSach: FK -> DAUSACH.MaDauSach
         * - MaNXB: FK -> NHAXUATBAN.MaNXB
         * - NamXuatBan, TriGia, SoLuong
         */
        Schema::create('SACH', function (Blueprint $table) {
            $table->bigIncrements('MaSach');

            $table->unsignedBigInteger('MaDauSach');
            $table->unsignedBigInteger('MaNXB');

            $table->integer('NamXuatBan');
            $table->decimal('TriGia', 15, 2)->default(0);
            $table->unsignedInteger('SoLuong')->default(1);

            $table->foreign('MaDauSach')
                ->references('MaDauSach')
                ->on('DAUSACH')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('MaNXB')
                ->references('MaNXB')
                ->on('NHAXUATBAN')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('MaDauSach');
            $table->index('MaNXB');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SACH');
    }
};