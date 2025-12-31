<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        if (!Schema::hasTable('CUONSACH') || !Schema::hasColumn('CUONSACH', 'TinhTrang')) {
            return;
        }


        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `CUONSACH` MODIFY `TinhTrang` TINYINT NOT NULL DEFAULT 1 COMMENT '0=Đang mượn, 1=Có sẵn, 3=Hỏng, 4=Bị mất'"
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('CUONSACH') || !Schema::hasColumn('CUONSACH', 'TinhTrang')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `CUONSACH` MODIFY `TinhTrang` TINYINT NOT NULL DEFAULT 1 COMMENT '0=Đang mượn, 1=Có sẵn'"
            );
        }
    }
};
