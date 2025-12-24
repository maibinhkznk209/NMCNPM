<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * CT_PHIEUMUON
         * - Theo CT_PHIEUMUONFactory: MaPhieuMuon, MaSach, NgayTra, TienPhat
         * - MaDocGia đã nằm ở PHIEUMUON, nên không lưu lặp lại ở chi tiết.
         */
        Schema::create('CT_PHIEUMUON', function (Blueprint $table) {
            $table->string('MaPhieuMuon', 20);
            $table->unsignedBigInteger('MaSach');

            $table->date('NgayTra')->nullable();
            $table->decimal('TienPhat', 15, 2)->default(0);

            $table->primary(['MaPhieuMuon', 'MaSach']);

            $table->foreign('MaPhieuMuon')
                ->references('MaPhieuMuon')
                ->on('PHIEUMUON')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Pipeline hiện tại: SACH PK = MaSach (int)
            $table->foreign('MaSach')
                ->references('MaSach')
                ->on('SACH')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('MaSach');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CT_PHIEUMUON');
    }
};
