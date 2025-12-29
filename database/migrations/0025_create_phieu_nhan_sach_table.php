<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PHIEUNHANSACH', function (Blueprint $table) {
            $table->string('MaPhieuNhanSach', 20)->primary();
            $table->unsignedBigInteger('MaSach');
            $table->unsignedInteger('SoLuong');
            $table->date('NgayNhap');

            $table->foreign('MaSach')
                ->references('MaSach')
                ->on('SACH')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('MaSach');
            $table->index('NgayNhap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEUNHANSACH');
    }
};
