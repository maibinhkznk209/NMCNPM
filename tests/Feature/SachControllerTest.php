<?php

namespace Tests\Feature;

use App\Http\Controllers\SachController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SachControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Route::has('test.api.sach.store')) {
            Route::middleware('api')->post('/api/sach', [SachController::class, 'store'])->name('test.api.sach.store');
            Route::middleware('api')->get('/api/sach', [SachController::class, 'index'])->name('test.api.sach.index');
        }

        // master data
        DB::table('THELOAI')->insert([['MaTheLoai' => 1, 'TenTheLoai' => 'Khoa học']]);
        DB::table('NHAXUATBAN')->insert([['MaNXB' => 1, 'TenNXB' => 'NXB Trẻ']]);

        // create DauSach directly
        DB::table('DAUSACH')->insert([
            'MaDauSach' => 1,
            'TenDauSach' => 'Nhập môn CNPM',
            'MaTheLoai' => 1,
            'NgayNhap' => now(),
        ]);

        // THAMSO for year constraint (optional)
        DB::table('THAMSO')->insert([
            'MaThamSo' => 1,
            'TenThamSo' => 'SoNamXuatBanToiDa',
            'GiaTri' => 8,
        ]);
    }


}
