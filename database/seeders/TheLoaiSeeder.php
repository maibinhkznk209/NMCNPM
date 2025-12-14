<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TheLoaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('THELOAI')->delete();
        DB::table('THELOAI')->insert([
            ['TenTheLoai'=>'Văn học'],
            ['TenTheLoai'=>'Khoa học'],
            ['TenTheLoai'=>'Lịch sử'],
            ['TenTheLoai'=>'Thiếu nhi'],
            ['TenTheLoai'=>'Triết học'],
            ['TenTheLoai'=>'Kỹ năng sống'],
            ['TenTheLoai'=>'Tiểu thuyết'],
            ['TenTheLoai'=>'Truyện tranh'],
            ['TenTheLoai'=>'Viễn tưởng'],
            ['TenTheLoai'=>'Tâm lý học'],
        ]);
    }
}
