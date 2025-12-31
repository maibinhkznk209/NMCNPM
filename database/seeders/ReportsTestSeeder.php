<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class ReportsTestSeeder extends Seeder
{
    public function run(): void
    {
        // THELOAI
        $maTheLoai = DB::table('THELOAI')->insertGetId([
            'TenTheLoai' => 'Văn học'
        ], 'MaTheLoai');

        // NHAXUATBAN
        $maNXB = DB::table('NHAXUATBAN')->insertGetId([
            'TenNXB' => 'NXB Test'
        ], 'MaNXB');

        $books = [
            ['TenDauSach' => 'Đắc nhân tâm', 'NamXuatBan' => 2020, 'SoLuong' => 10, 'TriGia' => 89000],
            ['TenDauSach' => 'Nhà giả kim', 'NamXuatBan' => 2019, 'SoLuong' => 8, 'TriGia' => 79000],
            ['TenDauSach' => 'Vũ trụ trong vỏ hạt dẻ', 'NamXuatBan' => 2018, 'SoLuong' => 5, 'TriGia' => 120000],
        ];

        foreach ($books as $b) {
            $maDauSach = DB::table('DAUSACH')->insertGetId([
                'TenDauSach' => $b['TenDauSach'],
                'MaTheLoai' => $maTheLoai,
                'NgayNhap' => Carbon::now(),
            ], 'MaDauSach');

            DB::table('SACH')->insert([
                'MaDauSach' => $maDauSach,
                'MaNXB' => $maNXB,
                'NamXuatBan' => $b['NamXuatBan'],
                'TriGia' => $b['TriGia'],
                'SoLuong' => $b['SoLuong'],
            ]);
        }
    }
}
