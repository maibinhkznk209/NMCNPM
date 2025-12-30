<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuyDinhSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $thamSoData = [
            [
                'TenThamSo' => 'TuoiToiThieu',
                'GiaTri' => '18',
            ],
            [
                'TenThamSo' => 'TuoiToiDa',
                'GiaTri' => '55',
            ],
            [
                'TenThamSo' => 'ThoiHanThe',
                'GiaTri' => '6',
            ],
            [
                'TenThamSo' => 'SoSachToiDa',
                'GiaTri' => '5',
            ],
            [
                'TenThamSo' => 'NgayMuonToiDa',
                'GiaTri' => '14',
            ],
            [
                'TenThamSo' => 'SoNamXuatBan',
                'GiaTri' => '8',
            ],
            [
                'TenThamSo' => 'TienPhatTreNgay',
                'GiaTri' => '1000',
            ],
        ];

        DB::table('THAMSO')->insert($thamSoData);
    }
}
