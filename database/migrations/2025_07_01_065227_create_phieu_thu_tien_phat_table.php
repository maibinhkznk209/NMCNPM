<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PHIEUTHUTIENPHAT', function (Blueprint $table) {
            $table->id();
            $table->string('MaPhieu', 20)->unique()->comment('Mã phiếu thu tiền phạt');
            $table->foreignId('docgia_id')->constrained('DOCGIA')->onDelete('cascade')->comment('ID độc giả');
            $table->decimal('SoTienNop', 15, 2)->comment('Số tiền nộp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEUTHUTIENPHAT');
    }
};