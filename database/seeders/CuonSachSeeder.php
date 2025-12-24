<?php

namespace Database\Seeders;

use App\Models\CuonSach;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CuonSachSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('SACH')->count() === 0) {
            $this->call(SachSeeder::class);
        }

        $now = Carbon::now();
        $maSachList = DB::table('SACH')->pluck('MaSach')->toArray();

        foreach ($maSachList as $maSach) {
            $add = rand(0, 3);
            if ($add <= 0) continue;

            DB::beginTransaction();
            try {
                for ($i = 0; $i < $add; $i++) {
                    DB::table('CUONSACH')->insert([
                        'MaSach' => $maSach,
                        'NgayNhap' => $now,
                        'TinhTrang' => CuonSach::TINH_TRANG_CO_SAN,
                    ]);
                }

                DB::table('SACH')->where('MaSach', $maSach)->increment('SoLuong', $add);
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }
}
