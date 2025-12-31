<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::create('CT_PHIEUMUON', function (Blueprint $table) {
            $table->string('MaPhieuMuon', 20);
            $table->unsignedBigInteger('MaSach');

            $table->date('NgayTra')->nullable();
            $table->decimal('TienPhat', 15, 2)->default(0);

            $table->primary(['MaPhieuMuon', 'MaSach']);

            $table->foreign('MaPhieuMuon')
                ->references('MaPhieuMuon')
                ->on('PHIEUMUON')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();


            $table->foreign('MaSach')
                ->references('MaSach')
                ->on('SACH')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->index('MaSach');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('CT_PHIEUMUON');
    }
};
