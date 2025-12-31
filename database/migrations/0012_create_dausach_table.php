<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('DAUSACH', function (Blueprint $table) {
            $table->bigIncrements('MaDauSach');
            $table->string('TenDauSach');
            $table->unsignedBigInteger('MaTheLoai');
            $table->dateTime('NgayNhap')->nullable();


            $table->foreign('MaTheLoai')
                ->references('MaTheLoai')
                ->on('THELOAI')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('MaTheLoai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('DAUSACH');
    }
};
