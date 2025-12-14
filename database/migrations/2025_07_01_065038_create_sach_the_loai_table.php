<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('SACH_THELOAI', function (Blueprint $table) {
            $table->foreignId('sach_id')->constrained('SACH')->onDelete('cascade');
            $table->foreignId('theloai_id')->constrained('THELOAI')->onDelete('cascade');
            $table->primary(['sach_id', 'theloai_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('SACH_THELOAI');
    }
};