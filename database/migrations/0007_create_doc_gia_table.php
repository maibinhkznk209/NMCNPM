<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DOCGIA', function (Blueprint $table) {
            $table->string('MaDocGia')->primary();
            $table->string('MaLoaiDocGia', 20);
            $table->foreign('MaLoaiDocGia')
                ->references('MaLoaiDocGia')
                ->on('LOAIDOCGIA')
                ->cascadeOnDelete();
            $table->string('TenDocGia');
            $table->date('NgaySinh');
            $table->string('DiaChi');
            $table->string('Email')->unique();
            $table->date('NgayLapThe');
            $table->date('NgayHetHan');
            $table->decimal('TongNo', 15, 2)->default(0)->comment('Tổng nợ của độc giả');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DOCGIA');
    }
};