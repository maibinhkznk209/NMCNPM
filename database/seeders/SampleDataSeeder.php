<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->infoLine("Bắt đầu tạo dữ liệu mẫu...");

        DB::beginTransaction();
        try {
            $this->createReaderTypes();
            $this->createAuthors();
            $this->createGenres();
            $this->createPublishers();
            $this->createRegulations();
            $this->createReaders();
            $this->createBooks();

            $this->createBorrowRecords(); // optional
            $this->createFineRecords();   // optional

            DB::commit();
            $this->infoLine("Hoàn thành tạo dữ liệu mẫu!");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->infoLine("Seeder lỗi: " . $e->getMessage());
            throw $e;
        }
    }

    /* =========================
     * Helpers
     * ========================= */
    private function infoLine(string $msg): void
    {

        if ($this->command) $this->command->info($msg);
        else echo $msg . PHP_EOL;
    }

    private function warnLine(string $msg): void
    {
        if ($this->command) $this->command->warn($msg);
        else echo "[WARN] " . $msg . PHP_EOL;
    }

    private function detectPk(string $table, array $candidates): ?string
    {
        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) return $col;
        }
        return null;
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        if (!Schema::hasTable($table)) return [];
        $out = [];
        foreach ($data as $k => $v) {
            if (Schema::hasColumn($table, $k)) $out[$k] = $v;
        }
        return $out;
    }

    private function upsert(string $table, array $unique, array $values): void
    {
        if (!Schema::hasTable($table)) {
            $this->warnLine("Không có bảng {$table}, bỏ qua.");
            return;
        }

        $unique = $this->onlyExistingColumns($table, $unique);
        $values = $this->onlyExistingColumns($table, $values);

        if (empty($values)) return;

        if (!empty($unique)) {
            DB::table($table)->updateOrInsert($unique, $values);
        } else {
            DB::table($table)->insert($values);
        }
    }

    /* =========================
     * 1) LOAIDOCGIA (PK: MaLoaiDocGia)
     * ========================= */
    private function createReaderTypes(): void
    {
        $this->infoLine("Tạo loại độc giả...");

        if (!Schema::hasTable('LOAIDOCGIA')) {
            $this->warnLine("Không có bảng LOAIDOCGIA, bỏ qua.");
            return;
        }

        $types = [
            ['MaLoaiDocGia' => 'LDG01', 'TenLoaiDocGia' => 'Sinh viên'],
            ['MaLoaiDocGia' => 'LDG02', 'TenLoaiDocGia' => 'Giảng viên'],
            ['MaLoaiDocGia' => 'LDG03', 'TenLoaiDocGia' => 'Cán bộ nhân viên'],
            ['MaLoaiDocGia' => 'LDG04', 'TenLoaiDocGia' => 'Độc giả bên ngoài'],
            ['MaLoaiDocGia' => 'LDG05', 'TenLoaiDocGia' => 'Học sinh'],
            ['MaLoaiDocGia' => 'LDG06', 'TenLoaiDocGia' => 'Nghiên cứu sinh'],
        ];

        foreach ($types as $row) {
            $this->upsert(
                'LOAIDOCGIA',
                ['MaLoaiDocGia' => $row['MaLoaiDocGia']],
                $row
            );
        }
    }

    /* =========================
     * 2) TACGIA
     * ========================= */
    private function createAuthors(): void
    {
        $this->infoLine("Tạo tác giả...");

        if (!Schema::hasTable('TACGIA')) {
            $this->warnLine("Không có bảng TACGIA, bỏ qua.");
            return;
        }

        $authors = [
            'Dương Hữu Thành',
            'Viktor Mayer-Schönberger',
            'Kenneth Cukier',
            'Vũ Duy Mẫn',
            'Lê Thanh',
            'Nguyễn Thị Mai Sinh',
            'Ngô Trung Hà',
            'Lê Thu Hương',
            'Chu Văn Cấp',
            'Trần Bình Trọng',
            'Phan Thanh Phố',
            'Từ Tích Mạch',
            'Trương Kính Kính',
            'Thúy Nga',
            'Phạm Đình Nghiệp',
            'Julian Hoxter',
            'Nguyễn Thanh Bình',
            'Bùi Thị Ngọc Thu',
            'Nhiều tác giả',
            'Deborah Install',
            'Vũ Bằng',
            'Ngô Ngọc Bội',
            'Triệu Bôn',
            'Trương Thiệu Vũ',
            'Nguyễn Tiến Thiêm',
            'Bill Gates',
            'Mai Chí Trung',
            'Sơn Tùng',
            'Nguyễn Thu Thủy',
            'Tô Hoài',
            'Alexander Dumas'
        ];

        foreach ($authors as $name) {
            $this->upsert('TACGIA', ['TenTacGia' => $name], ['TenTacGia' => $name]);
        }
    }

    /* =========================
     * 3) THELOAI
     * ========================= */
    private function createGenres(): void
    {
        $this->infoLine("Tạo thể loại...");

        if (!Schema::hasTable('THELOAI')) {
            $this->warnLine("Không có bảng THELOAI, bỏ qua.");
            return;
        }

        $genres = [
            'Công nghệ',
            'Du lịch',
            'Kinh tế',
            'Kỹ năng sống',
            'Nghệ thuật',
            'Thiếu nhi',
            'Văn học',
            'Y học',
            'Tiểu thuyết'
        ];

        foreach ($genres as $name) {
            $this->upsert('THELOAI', ['TenTheLoai' => $name], ['TenTheLoai' => $name]);
        }
    }

    /* =========================
     * 4) NHAXUATBAN
     * ========================= */
    private function createPublishers(): void
    {
        $this->infoLine("Tạo nhà xuất bản...");

        if (!Schema::hasTable('NHAXUATBAN')) {
            $this->warnLine("Không có bảng NHAXUATBAN, bỏ qua.");
            return;
        }

        $publishers = [
            'NXB Thông tin và Truyền thông',
            'NXB Trẻ',
            'NXB Giáo dục',
            'NXB Chính trị Quốc gia',
            'NXB Thanh niên',
            'NXB Lao động',
            'NXB Kim Đồng',
            'NXB Văn học',
            'NXB Phụ nữ',
            'NXB Hà Nội'
        ];

        foreach ($publishers as $name) {
            $this->upsert('NHAXUATBAN', ['TenNXB' => $name], ['TenNXB' => $name]);
        }
    }

    
    private function createRegulations(): void
    {
        $this->infoLine("Tạo tham số/quy định...");

        $table = null;
        if (Schema::hasTable('THAMSO')) $table = 'THAMSO';
        else if (Schema::hasTable('QUYDINH')) $table = 'QUYDINH';

        if (!$table) {
            $this->warnLine("Không có bảng THAMSO/QUYDINH, bỏ qua.");
            return;
        }

        $parameters = [
            ['TenThamSo' => 'TuoiToiThieu',   'GiaTri' => '18'],
            ['TenThamSo' => 'TuoiToiDa',      'GiaTri' => '55'],
            ['TenThamSo' => 'ThoiHanThe',     'GiaTri' => '6'],
            ['TenThamSo' => 'SoSachToiDa',    'GiaTri' => '5'],
            ['TenThamSo' => 'NgayMuonToiDa',  'GiaTri' => '14'],
            ['TenThamSo' => 'SoNamXuatBan',   'GiaTri' => '8'],
        ];

        foreach ($parameters as $p) {
            $this->upsert($table, ['TenThamSo' => $p['TenThamSo']], $p);
        }
    }

    /* =========================
     * 6) DOCGIA (TenDocGia, MaLoaiDocGia)
     * ========================= */
    private function createReaders(): void
    {
        $this->infoLine("Tạo độc giả...");

        if (!Schema::hasTable('DOCGIA')) {
            $this->warnLine("Không có bảng DOCGIA, bỏ qua.");
            return;
        }

        $readers = [
            [
                'MaDocGia' => 'DG001',
                'TenDocGia' => 'Nguyễn Văn An',
                'MaLoaiDocGia' => 'LDG01',
                'NgaySinh' => '2000-05-15',
                'DiaChi' => 'Hà Nội',
                'Email' => 'an@email.com',
                'NgayLapThe' => '2024-01-15',
                'NgayHetHan' => '2025-01-15',
                'TongNo' => 15000
            ],
            [
                'MaDocGia' => 'DG002',
                'TenDocGia' => 'Trần Thị Bình',
                'MaLoaiDocGia' => 'LDG01',
                'NgaySinh' => '2001-08-20',
                'DiaChi' => 'TP.HCM',
                'Email' => 'binh@email.com',
                'NgayLapThe' => '2024-02-10',
                'NgayHetHan' => '2025-02-10',
                'TongNo' => 25000
            ],
            [
                'MaDocGia' => 'DG003',
                'TenDocGia' => 'Lê Minh Châu',
                'MaLoaiDocGia' => 'LDG02',
                'NgaySinh' => '1985-12-10',
                'DiaChi' => 'Đà Nẵng',
                'Email' => 'chau@email.com',
                'NgayLapThe' => '2024-03-05',
                'NgayHetHan' => '2025-03-05',
                'TongNo' => 0
            ],
        ];

        $hasMaDocGia = Schema::hasColumn('DOCGIA', 'MaDocGia');

        foreach ($readers as $r) {
            if ($hasMaDocGia) {
                $this->upsert('DOCGIA', ['MaDocGia' => $r['MaDocGia']], $r);
            } else {

                $fallback = $r;
                unset($fallback['MaDocGia']);
                $this->upsert('DOCGIA', ['Email' => $r['Email']], $fallback);
            }
        }
    }

    
    private function createBooks(): void
    {
        $this->infoLine("Tạo đầu sách/sách/cuốn sách...");

        if (!Schema::hasTable('DAUSACH')) {
            $this->warnLine("Không có bảng DAUSACH (bạn nói TenDauSach nằm ở đây), bỏ qua phần sách theo cấu trúc mới.");
            return;
        }
        if (!Schema::hasTable('SACH')) {
            $this->warnLine("Không có bảng SACH, bỏ qua.");
            return;
        }

        $pkTheLoai = $this->detectPk('THELOAI', ['MaTheLoai', 'id']);
        $pkNXB     = $this->detectPk('NHAXUATBAN', ['MaNXB', 'id']);
        $pkTacGia  = $this->detectPk('TACGIA', ['MaTacGia', 'id']);
        $pkDauSach = $this->detectPk('DAUSACH', ['MaDauSach', 'id']);
        $pkSach    = $this->detectPk('SACH', ['MaSach', 'id']);

        if (!$pkTheLoai || !$pkNXB || !$pkTacGia || !$pkDauSach || !$pkSach) {
            $this->warnLine("Không xác định được PK cho một số bảng (THELOAI/NHAXUATBAN/TACGIA/DAUSACH/SACH). Kiểm tra lại migration.");
            return;
        }


        $mapTheLoai = DB::table('THELOAI')->pluck($pkTheLoai, 'TenTheLoai')->toArray();
        $mapNXB     = DB::table('NHAXUATBAN')->pluck($pkNXB, 'TenNXB')->toArray();
        $mapTacGia  = DB::table('TACGIA')->pluck($pkTacGia, 'TenTacGia')->toArray();

        $books = [
            [
                'TenDauSach' => 'Kiểm thử phần mềm',
                'TheLoai' => 'Công nghệ',
                'TacGia' => ['Dương Hữu Thành'],
                'NXB' => 'NXB Thông tin và Truyền thông',
                'NamXuatBan' => 2021,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Dữ liệu lớn: Cuộc cách mạng sẽ làm thay đổi cách chúng ta sống, làm việc và tư duy',
                'TheLoai' => 'Công nghệ',
                'TacGia' => ['Viktor Mayer-Schönberger', 'Kenneth Cukier', 'Vũ Duy Mẫn'],
                'NXB' => 'NXB Trẻ',
                'NamXuatBan' => 2018,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Giáo trình tổng quan du lịch',
                'TheLoai' => 'Du lịch',
                'TacGia' => ['Lê Thanh', 'Nguyễn Thị Mai Sinh', 'Ngô Trung Hà'],
                'NXB' => 'NXB Giáo dục',
                'NamXuatBan' => 2015,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Giáo trình nhập môn du lịch học',
                'TheLoai' => 'Du lịch',
                'TacGia' => ['Lê Thu Hương'],
                'NXB' => 'NXB Giáo dục',
                'NamXuatBan' => 2015,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Giáo trình kinh tế chính trị Mác-Lênin',
                'TheLoai' => 'Kinh tế',
                'TacGia' => ['Chu Văn Cấp', 'Trần Bình Trọng', 'Phan Thanh Phố'],
                'NXB' => 'NXB Chính trị Quốc gia',
                'NamXuatBan' => 2003,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Kỹ năng sơ cứu dành cho học sinh - Các cấp độ bỏng',
                'TheLoai' => 'Kỹ năng sống',
                'TacGia' => ['Từ Tích Mạch', 'Trương Kính Kính', 'Thúy Nga'],
                'NXB' => 'NXB Thanh niên',
                'NamXuatBan' => 2023,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Kỹ năng tổ chức các hoạt động công tác thanh thiếu niên',
                'TheLoai' => 'Kỹ năng sống',
                'TacGia' => ['Phạm Đình Nghiệp'],
                'NXB' => 'NXB Thanh niên',
                'NamXuatBan' => 2004,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Nghệ thuật kể chuyện bằng hình ảnh',
                'TheLoai' => 'Nghệ thuật',
                'TacGia' => ['Julian Hoxter', 'Nguyễn Thanh Bình'],
                'NXB' => 'NXB Lao động',
                'NamXuatBan' => 2024,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Nghệ thuật kể chuyện bằng dữ liệu',
                'TheLoai' => 'Nghệ thuật',
                'TacGia' => ['Bùi Thị Ngọc Thu'],
                'NXB' => 'NXB Lao động',
                'NamXuatBan' => 2021,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Kí sự tương lai - Máy tính và robot',
                'TheLoai' => 'Thiếu nhi',
                'TacGia' => ['Nhiều tác giả'],
                'NXB' => 'NXB Kim Đồng',
                'NamXuatBan' => 2023,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Có chú robot ở trong vườn',
                'TheLoai' => 'Thiếu nhi',
                'TacGia' => ['Deborah Install'],
                'NXB' => 'NXB Kim Đồng',
                'NamXuatBan' => 2023,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Văn học Việt Nam thế kỷ XX - Truyện ngắn 1945-1975',
                'TheLoai' => 'Văn học',
                'TacGia' => ['Vũ Bằng', 'Ngô Ngọc Bội', 'Triệu Bôn'],
                'NXB' => 'NXB Văn học',
                'NamXuatBan' => 2004,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Chăm sóc sức khoẻ phụ nữ qua các thời kỳ',
                'TheLoai' => 'Y học',
                'TacGia' => ['Trương Thiệu Vũ', 'Nguyễn Tiến Thiêm'],
                'NXB' => 'NXB Phụ nữ',
                'NamXuatBan' => 2002,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Mã nguồn - Khởi đầu của tôi',
                'TheLoai' => 'Công nghệ',
                'TacGia' => ['Bill Gates', 'Mai Chí Trung'],
                'NXB' => 'NXB Trẻ',
                'NamXuatBan' => 2025,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Búp sen xanh',
                'TheLoai' => 'Thiếu nhi',
                'TacGia' => ['Sơn Tùng'],
                'NXB' => 'NXB Kim Đồng',
                'NamXuatBan' => 1982,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Lớm',
                'TheLoai' => 'Tiểu thuyết',
                'TacGia' => ['Sơn Tùng'],
                'NXB' => 'NXB Thanh niên',
                'NamXuatBan' => 2001,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Dưới bầu trời xa cách',
                'TheLoai' => 'Tiểu thuyết',
                'TacGia' => ['Nguyễn Thu Thủy'],
                'NXB' => 'NXB Kim Đồng',
                'NamXuatBan' => 2025,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Những ngày đầu',
                'TheLoai' => 'Văn học',
                'TacGia' => ['Tô Hoài'],
                'NXB' => 'NXB Kim Đồng',
                'NamXuatBan' => 2023,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Ba chàng lính ngự lâm',
                'TheLoai' => 'Văn học',
                'TacGia' => ['Alexander Dumas'],
                'NXB' => 'NXB Hà Nội',
                'NamXuatBan' => 2021,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
            [
                'TenDauSach' => 'Ba chàng lính ngự lâm',
                'TheLoai' => 'Văn học',
                'TacGia' => ['Alexander Dumas'],
                'NXB' => 'NXB Văn học',
                'NamXuatBan' => 2020,
                'TriGia' => 80000,
                'SoLuong' => 4,
                'NgayNhap' => '2024-01-01',
            ],
        ];

        foreach ($books as $b) {
            $maTheLoai = $mapTheLoai[$b['TheLoai']] ?? null;
            $maNXB     = $mapNXB[$b['NXB']] ?? null;

            if (!$maTheLoai) {
                $this->warnLine("Không tìm thấy thể loại '{$b['TheLoai']}' trong THELOAI, bỏ qua '{$b['TenDauSach']}'.");
                continue;
            }
            if (!$maNXB) {
                $this->warnLine("Không tìm thấy NXB '{$b['NXB']}' trong NHAXUATBAN, bỏ qua '{$b['TenDauSach']}'.");
                continue;
            }

            // 7.1) DAUSACH
            $dauSachData = [
                'TenDauSach' => $b['TenDauSach'],
                'MaTheLoai'  => $maTheLoai,
                'NgayNhap'   => $b['NgayNhap'],
            ];
            $this->upsert('DAUSACH', ['TenDauSach' => $b['TenDauSach']], $dauSachData);

            $maDauSach = DB::table('DAUSACH')->where('TenDauSach', $b['TenDauSach'])->value($pkDauSach);
            if (!$maDauSach) {
                $this->warnLine("Không lấy được {$pkDauSach} cho '{$b['TenDauSach']}', bỏ qua.");
                continue;
            }


            if (Schema::hasTable('CT_TACGIA') && Schema::hasColumn('CT_TACGIA', 'MaDauSach') && Schema::hasColumn('CT_TACGIA', 'MaTacGia')) {
                DB::table('CT_TACGIA')->where('MaDauSach', $maDauSach)->delete();

                foreach ($b['TacGia'] as $tenTG) {
                    $maTG = $mapTacGia[$tenTG] ?? null;
                    if (!$maTG) {
                        $this->warnLine("Không tìm thấy tác giả '{$tenTG}' trong TACGIA, bỏ qua link CT_TACGIA.");
                        continue;
                    }
                    DB::table('CT_TACGIA')->insert([
                        'MaDauSach' => $maDauSach,
                        'MaTacGia'  => $maTG,
                    ]);
                }
            }

            // 7.3) SACH
            $sachData = [
                'MaDauSach'  => $maDauSach,
                'MaNXB'      => $maNXB,
                'NamXuatBan' => $b['NamXuatBan'],
                'TriGia'     => $b['TriGia'],
                'SoLuong'    => $b['SoLuong'],
            ];

            if (Schema::hasColumn('SACH', 'TinhTrang')) {
                $sachData['TinhTrang'] = 1;
            }
            $sachData = $this->onlyExistingColumns('SACH', $sachData);


            DB::table('SACH')
                ->where('MaDauSach', $maDauSach)
                ->where('NamXuatBan', $b['NamXuatBan'])
                ->where('MaNXB', $maNXB)
                ->delete();

            DB::table('SACH')->insert($sachData);


            $maSach = DB::table('SACH')
                ->where('MaDauSach', $maDauSach)
                ->where('NamXuatBan', $b['NamXuatBan'])
                ->where('MaNXB', $maNXB)
                ->value($pkSach);

            if (!$maSach) {
                $this->warnLine("Không lấy được {$pkSach} cho SACH của '{$b['TenDauSach']}'.");
                continue;
            }


            if (Schema::hasTable('CUONSACH') && Schema::hasColumn('CUONSACH', 'MaSach')) {

                DB::table('CUONSACH')->where('MaSach', $maSach)->delete();

                $ngayNhap = Carbon::parse($b['NgayNhap'])->toDateString();
                $tinhTrangCol = Schema::hasColumn('CUONSACH', 'TinhTrang') ? 'TinhTrang' : null;
                $ngayNhapCol  = Schema::hasColumn('CUONSACH', 'NgayNhap') ? 'NgayNhap' : null;

                for ($i = 0; $i < (int)$b['SoLuong']; $i++) {
                    $row = ['MaSach' => $maSach];
                    if ($ngayNhapCol) $row['NgayNhap'] = $ngayNhap;
                    if ($tinhTrangCol) $row['TinhTrang'] = 1;

                    DB::table('CUONSACH')->insert($row);
                }
            }
        }
    }

    /* =========================
     * 8) Optional - Borrow records
     * ========================= */
    private function createBorrowRecords(): void
    {
        $this->infoLine("Tạo phiếu mượn (optional) ...");

        $class = 'Database\\Seeders\\PhieuMuonSeeder';
        if (!class_exists($class)) {
            $this->warnLine("Không tìm thấy PhieuMuonSeeder, bỏ qua.");
            return;
        }

        try {
            $this->call($class);
        } catch (\Throwable $e) {
            $this->warnLine("Bỏ qua PhieuMuonSeeder do lỗi: " . $e->getMessage());
        }
    }

    /* =========================
     * 9) Optional - Fine records
     * ========================= */
    private function createFineRecords(): void
    {
        $this->infoLine("Tạo phiếu thu tiền phạt (optional) ...");

        $class = 'Database\\Seeders\\FineRecordsSeeder';
        if (!class_exists($class)) {
            $this->warnLine("Không tìm thấy FineRecordsSeeder, bỏ qua.");
            return;
        }

        try {
            $this->call($class);
        } catch (\Throwable $e) {
            $this->warnLine("Bỏ qua FineRecordsSeeder do lỗi: " . $e->getMessage());
        }
    }
}
