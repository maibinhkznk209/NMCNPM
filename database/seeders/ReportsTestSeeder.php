<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Thêm dữ liệu test cho bảng THELOAI
        $genres = [
            ['TenTheLoai' => 'Văn học', 'MoTa' => 'Sách văn học trong và ngoài nước'],
            ['TenTheLoai' => 'Khoa học', 'MoTa' => 'Sách khoa học tự nhiên và công nghệ'],
            ['TenTheLoai' => 'Thiếu nhi', 'MoTa' => 'Sách dành cho trẻ em'],
            ['TenTheLoai' => 'Kỹ năng sống', 'MoTa' => 'Sách phát triển bản thân'],
            ['TenTheLoai' => 'Lịch sử', 'MoTa' => 'Sách lịch sử Việt Nam và thế giới'],
        ];

        foreach ($genres as $genre) {
            DB::table('THELOAI')->insertOrIgnore([
                'TenTheLoai' => $genre['TenTheLoai'],
                'MoTa' => $genre['MoTa'],
            ]);
        }

        // Thêm dữ liệu test cho bảng TACGIA
        $authors = [
            ['TenTacGia' => 'Nguyễn Du', 'DiaChi' => 'Hà Nam', 'Website' => null],
            ['TenTacGia' => 'Dale Carnegie', 'DiaChi' => 'Missouri, USA', 'Website' => 'dalecarnegie.com'],
            ['TenTacGia' => 'Paulo Coelho', 'DiaChi' => 'Rio de Janeiro, Brazil', 'Website' => 'paulocoelho.com'],
            ['TenTacGia' => 'Stephen Hawking', 'DiaChi' => 'Oxford, England', 'Website' => null],
            ['TenTacGia' => 'Tô Hoài', 'DiaChi' => 'Hưng Yên', 'Website' => null],
        ];

        foreach ($authors as $author) {
            DB::table('TACGIA')->insertOrIgnore([
                'TenTacGia' => $author['TenTacGia'],
                'DiaChi' => $author['DiaChi'],
                'Website' => $author['Website'],
            ]);
        }

        // Thêm dữ liệu test cho bảng NHAXUATBAN
        $publishers = [
            ['TenNXB' => 'NXB Trẻ', 'DiaChi' => 'TP.HCM', 'Email' => 'info@nxbtre.com'],
            ['TenNXB' => 'NXB Kim Đồng', 'DiaChi' => 'Hà Nội', 'Email' => 'info@kimdong.com.vn'],
            ['TenNXB' => 'NXB Văn học', 'DiaChi' => 'Hà Nội', 'Email' => 'info@vanhoanhaxuatban.vn'],
            ['TenNXB' => 'NXB Khoa học tự nhiên và Công nghệ', 'DiaChi' => 'Hà Nội', 'Email' => 'info@nxbkhoahoc.com.vn'],
        ];

        foreach ($publishers as $publisher) {
            DB::table('NHAXUATBAN')->insertOrIgnore([
                'TenNXB' => $publisher['TenNXB'],
                'DiaChi' => $publisher['DiaChi'],
                'Email' => $publisher['Email'],
            ]);
        }

        // Thêm dữ liệu test cho bảng SACH
        $books = [
            ['TenSach' => 'Đắc nhân tâm', 'TacGiaId' => 2, 'NXBId' => 1, 'NamXuatBan' => 2020, 'SoLuong' => 10, 'TriGia' => 89000],
            ['TenSach' => 'Nhà giả kim', 'TacGiaId' => 3, 'NXBId' => 1, 'NamXuatBan' => 2019, 'SoLuong' => 8, 'TriGia' => 79000],
            ['TenSach' => 'Vũ trụ trong vỏ hạt dẻ', 'TacGiaId' => 4, 'NXBId' => 4, 'NamXuatBan' => 2018, 'SoLuong' => 5, 'TriGia' => 120000],
            ['TenSach' => 'Dế Mèn phiêu lưu ký', 'TacGiaId' => 5, 'NXBId' => 2, 'NamXuatBan' => 2021, 'SoLuong' => 12, 'TriGia' => 65000],
            ['TenSach' => 'Truyện Kiều', 'TacGiaId' => 1, 'NXBId' => 3, 'NamXuatBan' => 2020, 'SoLuong' => 15, 'TriGia' => 55000],
        ];

        foreach ($books as $index => $book) {
            $bookId = DB::table('SACH')->insertGetId([
                'TenSach' => $book['TenSach'],
                'TacGiaId' => $book['TacGiaId'],
                'NXBId' => $book['NXBId'],
                'NamXuatBan' => $book['NamXuatBan'],
                'SoLuong' => $book['SoLuong'],
                'TriGia' => $book['TriGia'],
            ]);

            // Thêm thể loại cho sách vào bảng SACH_THELOAI
            $genreMapping = [
                1 => [4], // Đắc nhân tâm -> Kỹ năng sống
                2 => [1], // Nhà giả kim -> Văn học
                3 => [2], // Vũ trụ trong vỏ hạt dẻ -> Khoa học
                4 => [3], // Dế Mèn -> Thiếu nhi
                5 => [1], // Truyện Kiều -> Văn học
            ];

            foreach ($genreMapping[$index + 1] as $genreId) {
                DB::table('SACH_THELOAI')->insertOrIgnore([
                    'sach_id' => $bookId,
                    'theloai_id' => $genreId,
                ]);
            }
        }

        // Thêm dữ liệu test cho bảng LOAIDOCGIA
        DB::table('LOAIDOCGIA')->insertOrIgnore([
            'TenLoaiDG' => 'Sinh viên',
            'TuoiToiDa' => 30,
        ]);

        DB::table('LOAIDOCGIA')->insertOrIgnore([
            'TenLoaiDG' => 'Học sinh',
            'TuoiToiDa' => 18,
        ]);

        // Thêm dữ liệu test cho bảng DOCGIA
        $readers = [
            ['HoTen' => 'Nguyễn Văn An', 'NgaySinh' => '2000-05-15', 'DiaChi' => 'Hà Nội', 'Email' => 'an@email.com', 'NgayLapThe' => '2024-01-15', 'LoaiDGId' => 1, 'TongNo' => 15000],
            ['HoTen' => 'Trần Thị Bình', 'NgaySinh' => '2001-08-20', 'DiaChi' => 'TP.HCM', 'Email' => 'binh@email.com', 'NgayLapThe' => '2024-02-10', 'LoaiDGId' => 1, 'TongNo' => 0],
            ['HoTen' => 'Lê Minh Châu', 'NgaySinh' => '2005-12-10', 'DiaChi' => 'Đà Nẵng', 'Email' => 'chau@email.com', 'NgayLapThe' => '2024-03-05', 'LoaiDGId' => 2, 'TongNo' => 32000],
        ];

        foreach ($readers as $reader) {
            DB::table('DOCGIA')->insertOrIgnore([
                'HoTen' => $reader['HoTen'],
                'NgaySinh' => $reader['NgaySinh'],
                'DiaChi' => $reader['DiaChi'],
                'Email' => $reader['Email'],
                'NgayLapThe' => $reader['NgayLapThe'],
                'LoaiDGId' => $reader['LoaiDGId'],
                'TongNo' => $reader['TongNo'],
            ]);
        }

        // Thêm dữ liệu test cho bảng PHIEUMUON
        $borrowRecords = [
            [
                'DocGiaId' => 1,
                'NgayMuon' => '2024-11-15',
                'NgayTraDuKien' => '2024-12-15',
                'NgayTraThucTe' => null,
                'TinhTrang' => 'Đang mượn'
            ],
            [
                'DocGiaId' => 2,
                'NgayMuon' => '2024-11-20',
                'NgayTraDuKien' => '2024-12-20',
                'NgayTraThucTe' => '2024-12-18',
                'TinhTrang' => 'Đã trả'
            ],
            [
                'DocGiaId' => 3,
                'NgayMuon' => '2024-12-01',
                'NgayTraDuKien' => '2024-12-31',
                'NgayTraThucTe' => null,
                'TinhTrang' => 'Đang mượn'
            ],
            [
                'DocGiaId' => 1,
                'NgayMuon' => '2024-10-15',
                'NgayTraDuKien' => '2024-11-15',
                'NgayTraThucTe' => null,
                'TinhTrang' => 'Quá hạn'
            ],
            [
                'DocGiaId' => 2,
                'NgayMuon' => '2024-12-10',
                'NgayTraDuKien' => '2025-01-10',
                'NgayTraThucTe' => null,
                'TinhTrang' => 'Đang mượn'
            ],
        ];

        foreach ($borrowRecords as $index => $record) {
            $borrowId = DB::table('PHIEUMUON')->insertGetId([
                'DocGiaId' => $record['DocGiaId'],
                'NgayMuon' => $record['NgayMuon'],
                'NgayTraDuKien' => $record['NgayTraDuKien'],
                'NgayTraThucTe' => $record['NgayTraThucTe'],
                'TinhTrang' => $record['TinhTrang'],
            ]);

            // Thêm chi tiết phiếu mượn
            $bookIds = [1, 2, 3, 4, 5];
            $numBooks = rand(1, 3); // Mỗi phiếu mượn 1-3 sách
            $selectedBooks = array_slice($bookIds, 0, $numBooks);

            foreach ($selectedBooks as $bookId) {
                DB::table('CHITIETPHIEUMUON')->insertOrIgnore([
                    'phieumuon_id' => $borrowId,
                    'sach_id' => $bookId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "✅ Đã tạo dữ liệu test cho báo cáo thành công!\n";
        echo "📊 Dữ liệu bao gồm:\n";
        echo "   - " . count($genres) . " thể loại sách\n";
        echo "   - " . count($authors) . " tác giả\n";
        echo "   - " . count($publishers) . " nhà xuất bản\n";
        echo "   - " . count($books) . " sách\n";
        echo "   - " . count($readers) . " độc giả\n";
        echo "   - " . count($borrowRecords) . " phiếu mượn\n";
        echo "🎯 Bây giờ có thể test báo cáo với dữ liệu thực tế!\n";
    }
}
