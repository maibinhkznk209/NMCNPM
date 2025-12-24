<?php

namespace Tests\Feature;

use App\Http\Controllers\CuonSachController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CuonSachControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Route::has('test.api.cuonsach.store')) {
            Route::middleware('api')->post('/api/cuon-sach', [CuonSachController::class, 'store'])->name('test.api.cuonsach.store');
            Route::middleware('api')->patch('/api/cuon-sach/{maCuonSach}/tinh-trang', [CuonSachController::class, 'updateTinhTrang'])->name('test.api.cuonsach.updateTinhTrang');
            Route::middleware('api')->get('/api/cuon-sach', [CuonSachController::class, 'index'])->name('test.api.cuonsach.index');
        }

        DB::table('THELOAI')->insert([['MaTheLoai' => 1, 'TenTheLoai' => 'Khoa học']]);
        DB::table('DAUSACH')->insert([
            'MaDauSach' => 1,
            'TenDauSach' => 'Nhập môn CNPM',
            'MaTheLoai' => 1,
            'NgayNhap' => now(),
        ]);
        DB::table('NHAXUATBAN')->insert([['MaNXB' => 1, 'TenNXB' => 'NXB Trẻ']]);

        DB::table('SACH')->insert([
            'MaSach' => 1,
            'MaDauSach' => 1,
            'MaNXB' => 1,
            'NamXuatBan' => (int)date('Y'),
            'TriGia' => 50000,
            'SoLuong' => 1,
        ]);

        DB::table('CUONSACH')->insert([
            'MaCuonSach' => 1,
            'MaSach' => 1,
            'NgayNhap' => now(),
            'TinhTrang' => 0,
        ]);
    }

    public function test_store_adds_copies_and_increments_sach_soluong(): void
    {
        $res = $this->postJson('/api/cuon-sach', [
            'MaSach' => 1,
            'SoLuong' => 2,
        ]);

        $res->assertStatus(201)->assertJson(['success' => true]);

        $this->assertEquals(3, (int)DB::table('SACH')->where('MaSach', 1)->value('SoLuong'));
        $this->assertEquals(3, DB::table('CUONSACH')->where('MaSach', 1)->count());
    }

    public function test_update_tinh_trang(): void
    {
        $res = $this->patchJson('/api/cuon-sach/1/tinh-trang', [
            'TinhTrang' => 3,
        ]);

        $res->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('CUONSACH', ['MaCuonSach' => 1, 'TinhTrang' => 3]);
    }
}
