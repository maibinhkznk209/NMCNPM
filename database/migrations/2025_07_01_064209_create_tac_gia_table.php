<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('TACGIA', function (Blueprint $table) {
            $table->id();
            $table->string('TenTacGia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('TACGIA');
    }
};