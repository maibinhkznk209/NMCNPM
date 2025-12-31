<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
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
