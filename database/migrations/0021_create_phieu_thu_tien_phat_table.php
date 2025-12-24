<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       
        Schema::create('PHIEUPHAT', function (Blueprint $table) {
            $table->string('MaPhieuPhat', 20)->primary();
            $table->string('MaDocGia');

            $table->decimal('SoTienNop', 15, 2);
            $table->date('NgayThu');

            $table->foreign('MaDocGia')
                ->references('MaDocGia')
                ->on('DOCGIA')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEUPHAT');
    }
};