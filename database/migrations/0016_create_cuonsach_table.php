<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CUONSACH', function (Blueprint $table) {
            $table->bigIncrements('MaCuonSach');

            $table->unsignedBigInteger('MaSach');

            $table->dateTime('NgayNhap')->nullable();

            $table->tinyInteger('TinhTrang')
                ->default(1)
                ->comment('0=Đang mượn, 1=Có sẵn, 2=Hỏng, 3=Bị mất');

            $table->foreign('MaSach')
                ->references('MaSach')
                ->on('SACH')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('MaSach');
            $table->index('TinhTrang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CUONSACH');
    }
};
