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

    public function test_store_creates_sach_and_cuonsach_by_soluong(): void
    {
        $payload = [
            'MaDauSach' => 1,
            'MaNXB' => 1,
            'NamXuatBan' => (int)date('Y'),
            'TriGia' => 50000,
            'SoLuong' => 3,
        ];

        $res = $this->postJson('/api/sach', $payload);
        $res->assertStatus(201)->assertJson(['success' => true]);

        $maSach = DB::table('SACH')->where('MaDauSach', 1)->value('MaSach');
        $this->assertNotEmpty($maSach);

        $this->assertDatabaseHas('SACH', [
            'MaSach' => $maSach,
            'SoLuong' => 3,
        ]);

        $countCuon = DB::table('CUONSACH')->where('MaSach', $maSach)->count();
        $this->assertEquals(3, $countCuon);
    }
}
