<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NhaXuatBan;

class NhaXuatBanSeeder extends Seeder
{
    public function run()
    {
        $nhaXuatBans = [
            ['TenNXB' => 'NXB Giáo dục Việt Nam'],
            ['TenNXB' => 'NXB Trẻ'],
            ['TenNXB' => 'NXB Kim Đồng'],
            ['TenNXB' => 'NXB Văn học'],
            ['TenNXB' => 'NXB Chính trị Quốc gia Sự thật'],
            ['TenNXB' => 'NXB Hội Nhà văn'],
            ['TenNXB' => 'NXB Thế giới'],
        ];

        foreach ($nhaXuatBans as $nxb) {
            NhaXuatBan::create($nxb);
        }
    }
}
