<?php

namespace Database\Seeders;

use App\Models\DauSach;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DauSachSeeder extends Seeder
{
    public function run(): void
    {
        // master data fallback (nếu project đã có seeders khác thì bạn có thể xoá phần này)
        if (DB::table('THELOAI')->count() === 0) {
            DB::table('THELOAI')->insert([
                ['MaTheLoai' => 1, 'TenTheLoai' => 'Khoa học'],
                ['MaTheLoai' => 2, 'TenTheLoai' => 'Văn học'],
                ['MaTheLoai' => 3, 'TenTheLoai' => 'CNTT'],
            ]);
        }

        if (DB::table('TACGIA')->count() === 0) {
            DB::table('TACGIA')->insert([
                ['MaTacGia' => 1, 'TenTacGia' => 'Tác giả A'],
                ['MaTacGia' => 2, 'TenTacGia' => 'Tác giả B'],
                ['MaTacGia' => 3, 'TenTacGia' => 'Tác giả C'],
            ]);
        }

        $now = Carbon::now();

        $items = [
            ['TenDauSach' => 'Nhập môn Công nghệ phần mềm', 'MaTheLoai' => 3, 'tacGias' => [1, 2]],
            ['TenDauSach' => 'Cơ sở dữ liệu', 'MaTheLoai' => 3, 'tacGias' => [2]],
            ['TenDauSach' => 'Kỹ năng mềm', 'MaTheLoai' => 1, 'tacGias' => [3]],
            ['TenDauSach' => 'Văn học Việt Nam', 'MaTheLoai' => 2, 'tacGias' => [1]],
        ];

        foreach ($items as $it) {
            $dauSach = DauSach::create([
                'TenDauSach' => $it['TenDauSach'],
                'MaTheLoai' => $it['MaTheLoai'],
                'NgayNhap' => $now,
            ]);

            $uniqueTacGia = array_values(array_unique($it['tacGias']));

            if (method_exists($dauSach, 'DS_TG')) {
                $dauSach->DS_TG()->sync($uniqueTacGia);
            } elseif (method_exists($dauSach, 'tacGias')) {
                $dauSach->tacGias()->sync($uniqueTacGia);
            } else {
                foreach ($uniqueTacGia as $maTG) {
                    DB::table('CT_TACGIA')->insert([
                        'MaDauSach' => $dauSach->MaDauSach,
                        'MaTacGia' => $maTG,
                    ]);
                }
            }
        }
    }
}
