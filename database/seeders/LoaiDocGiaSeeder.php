<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoaiDocGiaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('LOAIDOCGIA')->truncate();

        $loaiDocGias = [
            ['MaLoaiDocGia' => 'LDG01', 'TenLoaiDocGia' => 'Sinh viên'],
            ['MaLoaiDocGia' => 'LDG02', 'TenLoaiDocGia' => 'Giảng viên'],
            ['MaLoaiDocGia' => 'LDG03', 'TenLoaiDocGia' => 'Cán bộ nhân viên'],
            ['MaLoaiDocGia' => 'LDG04', 'TenLoaiDocGia' => 'Độc giả bên ngoài'],
            ['MaLoaiDocGia' => 'LDG05', 'TenLoaiDocGia' => 'Học sinh'],
            ['MaLoaiDocGia' => 'LDG06', 'TenLoaiDocGia' => 'Nghiên cứu sinh'],
        ];

        DB::table('LOAIDOCGIA')->insert($loaiDocGias);
    }
}
