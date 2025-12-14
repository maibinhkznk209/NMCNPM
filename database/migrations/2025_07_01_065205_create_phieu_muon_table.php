<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PHIEUMUON', function (Blueprint $table) {
            $table->id();
            $table->string('MaPhieu')->unique();
            $table->foreignId('docgia_id')->constrained('DOCGIA');
            $table->date('NgayMuon');
            $table->date('NgayHenTra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEUMUON');
    }
};