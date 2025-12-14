<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Thêm cột để theo dõi số tiền đền bù cho sách hỏng/mất
     */
    public function up(): void
    {
        Schema::table('CHITIETPHIEUMUON', function (Blueprint $table) {
            // Thêm cột số tiền đền bù cho sách hỏng/mất (do thủ thư nhập)
            $table->decimal('TienDenBu', 15, 2)->default(0)->after('TienPhat')
                  ->comment('Số tiền đền bù cho sách hỏng/mất');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('CHITIETPHIEUMUON', function (Blueprint $table) {
            $table->dropColumn(['TienDenBu']);
        });
    }
};
