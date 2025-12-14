<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SACH', function (Blueprint $table) {
            $table->id();
            $table->string('MaSach', 10)->unique();
            $table->string('TenSach');
            $table->unsignedBigInteger('MaTacGia')->nullable(); // Foreign key to TACGIA
            $table->unsignedBigInteger('MaNhaXuatBan')->nullable(); // Foreign key to NHAXUATBAN
            $table->integer('NamXuatBan');
            $table->date('NgayNhap');
            $table->tinyInteger('TinhTrang')->default(1); // 1 = có sẵn, 0 = đang mượn
            
            $table->foreign('MaTacGia')->references('id')->on('TACGIA')->onDelete('set null');
            $table->foreign('MaNhaXuatBan')->references('id')->on('NHAXUATBAN')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SACH');
    }
};