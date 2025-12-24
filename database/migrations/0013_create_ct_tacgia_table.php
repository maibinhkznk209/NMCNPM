<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('CT_TACGIA', function (Blueprint $table) {
            $table->unsignedBigInteger('MaDauSach');
            $table->unsignedBigInteger('MaTacGia');

            $table->primary(['MaDauSach', 'MaTacGia']);

            $table->foreign('MaDauSach')
                ->references('MaDauSach')
                ->on('DAUSACH')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Theo SachController: TACGIA PK = MaTacGia
            $table->foreign('MaTacGia')
                ->references('MaTacGia')
                ->on('TACGIA')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CT_TACGIA');
    }
};
