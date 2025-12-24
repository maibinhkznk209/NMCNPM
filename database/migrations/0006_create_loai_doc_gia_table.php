<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LOAIDOCGIA', function (Blueprint $table) {
            $table->string('MaLoaiDocGia', 20)->primary()
                  ->comment('Mã loại độc giả (SV, GV, CB...)');

            $table->string('TenLoaiDocGia')
                  ->comment('Tên loại độc giả');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LOAIDOCGIA');
    }
};
