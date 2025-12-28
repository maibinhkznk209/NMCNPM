<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PHIEUNHANDAUSACH', function (Blueprint $table) {
            $table->string('MaPhieuNhanDauSach', 20)->primary();
            $table->unsignedBigInteger('MaDauSach');
            $table->date('NgayNhap');

            $table->foreign('MaDauSach')
                ->references('MaDauSach')
                ->on('DAUSACH')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('MaDauSach');
            $table->index('NgayNhap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEUNHANDAUSACH');
    }
};
