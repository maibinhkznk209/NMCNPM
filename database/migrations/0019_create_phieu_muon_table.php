<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * PHIEUMUON
         * - PK: MaPhieuMuon (string) theo App\Models\PhieuMuon
         * - FK: MaDocGia -> DOCGIA.MaDocGia
         */
        Schema::create('PHIEUMUON', function (Blueprint $table) {
            $table->string('MaPhieuMuon', 20)->primary();
            $table->string('MaDocGia');
            $table->date('NgayMuon');
            $table->date('NgayHenTra');

            $table->foreign('MaDocGia')
                ->references('MaDocGia')
                ->on('DOCGIA')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index('MaDocGia');
            $table->index('NgayMuon');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PHIEUMUON');
    }
};
