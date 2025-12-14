<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('TAIKHOAN', function (Blueprint $table) {
            $table->id();
            $table->string('HoVaTen', 100);
            $table->string('Email', 150)->unique();
            $table->string('MatKhau');
            $table->foreignId('vaitro_id')->constrained('VAITRO')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('TAIKHOAN');
    }
};
