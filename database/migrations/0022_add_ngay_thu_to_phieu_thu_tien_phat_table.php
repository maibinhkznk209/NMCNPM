<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nếu bảng không tồn tại thì bỏ qua (an toàn khi migrate theo thứ tự mới)
        if (!Schema::hasTable('PHIEUPHAT')) {
            return;
        }

        // Chỉ add nếu chưa có cột
        if (!Schema::hasColumn('PHIEUPHAT', 'NgayThu')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->date('NgayThu')->nullable()->after('SoTienNop');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('PHIEUPHAT') && Schema::hasColumn('PHIEUPHAT', 'NgayThu')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->dropColumn('NgayThu');
            });
        }
    }
};
