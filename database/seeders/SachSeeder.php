<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sach;
use Carbon\Carbon;

class SachSeeder extends Seeder
{
    public function run()
    {
        // Clear existing books
        Sach::query()->delete();

        $saches = [
            [
                'TenSach' => 'Đắc nhân tâm',
                'MaTacGia' => 6, // Dale Carnegie
                'MaNhaXuatBan' => 2, // NXB Trẻ
                'NamXuatBan' => 2020,
                'NgayNhap' => Carbon::now()->subDays(30),
                'TriGia' => 89000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [6], // Kỹ năng sống
            ],
            [
                'TenSach' => 'Dế mèn phiêu lưu ký',
                'MaTacGia' => 4, // Tô Hoài
                'MaNhaXuatBan' => 3, // NXB Kim Đồng
                'NamXuatBan' => 2019,
                'NgayNhap' => Carbon::now()->subDays(25),
                'TriGia' => 65000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [4], // Thiếu nhi
            ],
            [
                'TenSach' => 'Harry Potter và Hòn đá phù thủy',
                'MaTacGia' => 8, // J.K. Rowling
                'MaNhaXuatBan' => 7, // NXB Thế giới
                'NamXuatBan' => 2018,
                'NgayNhap' => Carbon::now()->subDays(20),
                'TriGia' => 125000,
                'TinhTrang' => 0, // Đang được mượn
                'theLoaiIds' => [7], // Tiểu thuyết
            ],
            [
                'TenSach' => 'Nhà giả kim',
                'MaTacGia' => 7, // Paulo Coelho
                'MaNhaXuatBan' => 2, // NXB Trẻ
                'NamXuatBan' => 2021,
                'NgayNhap' => Carbon::now()->subDays(15),
                'TriGia' => 78000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [7, 5], // Tiểu thuyết, Triết học
            ],
            [
                'TenSach' => 'Lão Hạc',
                'MaTacGia' => 5, // Nam Cao
                'MaNhaXuatBan' => 4, // NXB Văn học
                'NamXuatBan' => 2017,
                'NgayNhap' => Carbon::now()->subDays(40),
                'TriGia' => 55000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [1], // Văn học
            ],
            [
                'TenSach' => 'Nhật ký trong tù',
                'MaTacGia' => 3, // Hồ Chí Minh
                'MaNhaXuatBan' => 5, // NXB Chính trị Quốc gia Sự thật
                'NamXuatBan' => 2019,
                'NgayNhap' => Carbon::now()->subDays(35),
                'TriGia' => 45000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [3, 1], // Lịch sử, Văn học
            ],
            [
                'TenSach' => 'Vũ trụ trong vỏ hạt dẻ',
                'MaTacGia' => 9, // Stephen Hawking
                'MaNhaXuatBan' => 7, // NXB Thế giới
                'NamXuatBan' => 2020,
                'NgayNhap' => Carbon::now()->subDays(10),
                'TriGia' => 145000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [2, 9], // Khoa học, Vật lý
            ],
            [
                'TenSach' => 'Thuyết tương đối',
                'MaTacGia' => 10, // Albert Einstein
                'MaNhaXuatBan' => 1, // NXB Giáo dục Việt Nam
                'NamXuatBan' => 2018,
                'NgayNhap' => Carbon::now()->subDays(50),
                'TriGia' => 120000,
                'TinhTrang' => 0, // Đang được mượn
                'theLoaiIds' => [2, 9], // Khoa học, Vật lý
            ],
            [
                'TenSach' => 'Doraemon - Tập 1',
                'MaTacGia' => 4, // Tô Hoài (demo)
                'MaNhaXuatBan' => 3, // NXB Kim Đồng
                'NamXuatBan' => 2022,
                'NgayNhap' => Carbon::now()->subDays(5),
                'TriGia' => 35000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [8, 4], // Truyện tranh, Thiếu nhi
            ],
            [
                'TenSach' => 'Lịch sử thế giới',
                'MaTacGia' => 3, // Hồ Chí Minh (demo)
                'MaNhaXuatBan' => 1, // NXB Giáo dục Việt Nam
                'NamXuatBan' => 2021,
                'NgayNhap' => Carbon::now()->subDays(60),
                'TriGia' => 98000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [3], // Lịch sử
            ],
            [
                'TenSach' => 'Lịch sử Việt Nam',
                'MaTacGia' => 3, // Hồ Chí Minh
                'MaNhaXuatBan' => 5, // NXB Chính trị Quốc gia Sự thật
                'NamXuatBan' => 2020,
                'NgayNhap' => Carbon::now()->subDays(45),
                'TriGia' => 85000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [3], // Lịch sử
            ],
            [
                'TenSach' => 'Khoa học kỳ thú',
                'MaTacGia' => 9, // Stephen Hawking
                'MaNhaXuatBan' => 2, // NXB Trẻ
                'NamXuatBan' => 2022,
                'NgayNhap' => Carbon::now()->subDays(8),
                'TriGia' => 75000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [2], // Khoa học
            ],
            [
                'TenSach' => 'Truyện cổ tích Việt Nam',
                'MaTacGia' => 4, // Tô Hoài
                'MaNhaXuatBan' => 3, // NXB Kim Đồng
                'NamXuatBan' => 2019,
                'NgayNhap' => Carbon::now()->subDays(70),
                'TriGia' => 48000,
                'TinhTrang' => 0, // Đang được mượn
                'theLoaiIds' => [4, 1], // Thiếu nhi, Văn học
            ],
            [
                'TenSach' => 'Chiến tranh và Hòa bình',
                'MaTacGia' => 1, // Nguyễn Du (demo)
                'MaNhaXuatBan' => 4, // NXB Văn học
                'NamXuatBan' => 2017,
                'NgayNhap' => Carbon::now()->subDays(80),
                'TriGia' => 165000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [1, 7], // Văn học, Tiểu thuyết
            ],
            [
                'TenSach' => 'Tâm lý học đại cương',
                'MaTacGia' => 6, // Dale Carnegie
                'MaNhaXuatBan' => 1, // NXB Giáo dục Việt Nam
                'NamXuatBan' => 2021,
                'NgayNhap' => Carbon::now()->subDays(12),
                'TriGia' => 115000,
                'TinhTrang' => 1, // Có sẵn
                'theLoaiIds' => [10], // Tâm lý học
            ],
        ];

        foreach ($saches as $sachData) {
            $theLoaiIds = $sachData['theLoaiIds'];
            unset($sachData['theLoaiIds']);
            $sachData['MaSach'] = $this->generateMaSach();
            $sach = Sach::create($sachData);
            if (!empty($theLoaiIds)) {
                $sach->theLoais()->attach($theLoaiIds);
            }
        }
    }

    private function generateMaSach()
    {
        do {
            $maSach = 'S' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Sach::where('MaSach', $maSach)->exists());
        return $maSach;
    }
}
