<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocGia;
use App\Models\LoaiDocGia;
use Carbon\Carbon;

class DocGiaSeeder extends Seeder
{
    public function run(): void
    {
        $loaiDocGias = LoaiDocGia::all();
        
        if ($loaiDocGias->isEmpty()) {
            $this->command->warn('Không có loại độc giả nào. Hãy chạy LoaiDocGiaSeeder trước.');
            return;
        }

        $docGias = [
            [
                'TenDocGia' => 'Nguyễn Văn An',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Sinh viên')->first()->MaLoaiDocGia,
                'NgaySinh' => '2000-05-15',
                'DiaChi' => '123 Đường ABC, Quận 1, TP.HCM',
                'Email' => 'nguyenvanan@email.com',
                'NgayLapThe' => '2024-01-15',
                'NgayHetHan' => '2025-01-15',
                'TongNo' => 0.00
            ],
            [
                'TenDocGia' => 'Trần Thị Bình',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Giảng viên')->first()->MaLoaiDocGia,
                'NgaySinh' => '1985-03-20',
                'DiaChi' => '456 Đường XYZ, Quận 3, TP.HCM',
                'Email' => 'tranthibinh@email.com',
                'NgayLapThe' => '2024-02-01',
                'NgayHetHan' => '2026-02-01',
                'TongNo' => 25000.00
            ],
            [
                'TenDocGia' => 'Lê Minh Cường',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Sinh viên')->first()->MaLoaiDocGia,
                'NgaySinh' => '1999-12-10',
                'DiaChi' => '789 Đường DEF, Quận 7, TP.HCM',
                'Email' => 'leminhcuong@email.com',
                'NgayLapThe' => '2024-03-10',
                'NgayHetHan' => '2025-03-10',
                'TongNo' => 0.00
            ],
            [
                'TenDocGia' => 'Phạm Thị Dung',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Cán bộ nhân viên')->first()->MaLoaiDocGia,
                'NgaySinh' => '1990-08-25',
                'DiaChi' => '321 Đường GHI, Quận 5, TP.HCM',
                'Email' => 'phamthidung@email.com',
                'NgayLapThe' => '2024-01-20',
                'NgayHetHan' => '2025-01-20',
                'TongNo' => 50000.00
            ],
            [
                'TenDocGia' => 'Hoàng Văn Em',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Độc giả bên ngoài')->first()->MaLoaiDocGia,
                'NgaySinh' => '1995-11-05',
                'DiaChi' => '654 Đường JKL, Quận 2, TP.HCM',
                'Email' => 'hoangvanem@email.com',
                'NgayLapThe' => '2023-12-01',
                'NgayHetHan' => '2024-12-01',
                'TongNo' => 0.00
            ],
            [
                'TenDocGia' => 'Vũ Thị An',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Nghiên cứu sinh')->first()->MaLoaiDocGia,
                'NgaySinh' => '1992-04-18',
                'DiaChi' => '987 Đường MNO, Quận 4, TP.HCM',
                'Email' => 'vuthifan@email.com',
                'NgayLapThe' => '2024-04-01',
                'NgayHetHan' => '2026-04-01',
                'TongNo' => 15000.00
            ],
            [
                'TenDocGia' => 'Đặng Minh Giang',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Học sinh')->first()->MaLoaiDocGia,
                'NgaySinh' => '2005-09-12',
                'DiaChi' => '159 Đường PQR, Quận 6, TP.HCM',
                'Email' => 'dangminhgiang@email.com',
                'NgayLapThe' => '2024-05-15',
                'NgayHetHan' => '2025-05-15'
            ],
            [
                'TenDocGia' => 'Bùi Thị Hạnh',
                'MaLoaiDocGia' => $loaiDocGias->where('TenLoaiDocGia', 'Sinh viên')->first()->MaLoaiDocGia,
                'NgaySinh' => '2001-07-30',
                'DiaChi' => '753 Đường STU, Quận 8, TP.HCM',
                'Email' => 'buithihanh@email.com',
                'NgayLapThe' => '2024-06-01',
                'NgayHetHan' => '2025-06-01'
            ]
        ];

        foreach ($docGias as $docGia) {
            $docGia['NgayHetHan'] = Carbon::parse($docGia['NgayLapThe'])
                ->addMonths(6)
                ->toDateString();
            DocGia::create($docGia);
        }
    }
}
