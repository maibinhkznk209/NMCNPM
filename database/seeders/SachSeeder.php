<?php

namespace Database\Seeders;

use App\Models\CuonSach;
use App\Models\Sach;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SachSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('DAUSACH')->count() === 0) {
            $this->call(DauSachSeeder::class);
        }

        if (DB::table('NHAXUATBAN')->count() === 0) {
            DB::table('NHAXUATBAN')->insert([
                ['MaNXB' => 1, 'TenNXB' => 'NXB Trẻ'],
                ['MaNXB' => 2, 'TenNXB' => 'NXB Giáo dục'],
            ]);
        }

        $now = Carbon::now();
        $year = (int)date('Y');

        $dauSachIds = DB::table('DAUSACH')->pluck('MaDauSach')->toArray();
        $nxbIds = DB::table('NHAXUATBAN')->pluck('MaNXB')->toArray();

        // tạo ấn bản SACH + sinh CUONSACH theo SoLuong
        for ($i = 0; $i < 6; $i++) {
            $maDauSach = $dauSachIds[array_rand($dauSachIds)];
            $maNXB = $nxbIds[array_rand($nxbIds)];
            $qty = rand(1, 5);

            DB::beginTransaction();
            try {
                $sach = Sach::create([
                    'MaDauSach' => $maDauSach,
                    'MaNXB' => $maNXB,
                    'NamXuatBan' => $year,
                    'TriGia' => rand(30000, 150000),
                    'SoLuong' => $qty,
                ]);

                for ($k = 0; $k < $qty; $k++) {
                    CuonSach::create([
                        'MaSach' => $sach->MaSach,
                        'NgayNhap' => $now,
                        'TinhTrang' => CuonSach::TINH_TRANG_CO_SAN,
                    ]);
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }
}
