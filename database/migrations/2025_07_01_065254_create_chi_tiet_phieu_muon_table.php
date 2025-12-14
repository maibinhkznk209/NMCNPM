<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CHITIETPHIEUMUON', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phieumuon_id')->constrained('PHIEUMUON')->onDelete('cascade');
            $table->foreignId('sach_id')->constrained('SACH')->onDelete('cascade');
            $table->date('NgayTra')->nullable();
            $table->decimal('TienPhat', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CHITIETPHIEUMUON');
    }
};