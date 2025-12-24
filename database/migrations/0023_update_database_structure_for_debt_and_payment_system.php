<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration "legacy" dùng để đồng bộ cấu trúc nợ/phạt trên DB cũ.
     *
     * Phiên bản trước của file này bị lỗi cú pháp (có '...') và tham chiếu cột cũ.
     * Phiên bản này chỉ thực hiện các thay đổi an toàn (nếu thiếu cột thì thêm),
     * KHÔNG tự tạo bảng mới để tránh xung đột với các migration create_* hiện tại.
     */
    public function up(): void
    {
        // DOCGIA.TongNo
        if (Schema::hasTable('DOCGIA') && !Schema::hasColumn('DOCGIA', 'TongNo')) {
            Schema::table('DOCGIA', function (Blueprint $table) {
                $table->decimal('TongNo', 15, 2)->default(0)->comment('Tổng nợ của độc giả');
            });
        }

        // SACH.TriGia
        if (Schema::hasTable('SACH') && !Schema::hasColumn('SACH', 'TriGia')) {
            Schema::table('SACH', function (Blueprint $table) {
                // Không dùng after('NgayNhap') vì pipeline mới không có NgayNhap ở SACH
                $table->decimal('TriGia', 15, 2)->default(0)->comment('Trị giá sách (VNĐ)');
            });
        }

        // PHIEUPHAT.SoTienNop (nếu bảng/phần nộp tiền nằm ở PHIEUPHAT)
        if (Schema::hasTable('PHIEUPHAT') && !Schema::hasColumn('PHIEUPHAT', 'SoTienNop')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->decimal('SoTienNop', 15, 2)->default(0)->comment('Số tiền nộp');
            });
        }
    }

    public function down(): void
    {
        // Chỉ rollback các cột do migration này thêm
        if (Schema::hasTable('PHIEUPHAT') && Schema::hasColumn('PHIEUPHAT', 'SoTienNop')) {
            Schema::table('PHIEUPHAT', function (Blueprint $table) {
                $table->dropColumn('SoTienNop');
            });
        }

        if (Schema::hasTable('SACH') && Schema::hasColumn('SACH', 'TriGia')) {
            // TriGia có thể được tạo ở migration create_sach_table, không nên drop bừa.
            // Do đó, chỉ drop nếu bạn chắc TriGia do migration này tạo.
            // => Không drop trong down để an toàn.
        }

        if (Schema::hasTable('DOCGIA') && Schema::hasColumn('DOCGIA', 'TongNo')) {
            // TongNo thường là cột cốt lõi, không drop trong down để an toàn.
        }
    }
};
