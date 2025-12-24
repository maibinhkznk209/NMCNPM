<?php

namespace Tests\Feature;

use App\Http\Controllers\DauSachController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DauSachControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // API routes for tests (avoid CSRF)
        if (!Route::has('test.api.dau-sach.store')) {
            Route::middleware('api')->post('/api/dau-sach', [DauSachController::class, 'store'])->name('test.api.dau-sach.store');
            Route::middleware('api')->get('/api/dau-sach', [DauSachController::class, 'index'])->name('test.api.dau-sach.index');
        }

        // seed minimal master data
        DB::table('THELOAI')->insert([
            ['MaTheLoai' => 1, 'TenTheLoai' => 'Khoa học'],
        ]);

        DB::table('TACGIA')->insert([
            ['MaTacGia' => 1, 'TenTacGia' => 'Tác giả A'],
            ['MaTacGia' => 2, 'TenTacGia' => 'Tác giả B'],
        ]);
    }

    public function test_store_creates_dausach_and_ct_tacgia_rows(): void
    {
        $payload = [
            'TenDauSach' => 'Lập trình Laravel',
            'MaTheLoai' => 1,
            'tacGias' => [1, 2],
        ];

        $res = $this->postJson('/api/dau-sach', $payload);
        $res->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('DAUSACH', [
            'TenDauSach' => 'Lập trình Laravel',
            'MaTheLoai' => 1,
        ]);

        $maDauSach = DB::table('DAUSACH')->where('TenDauSach', 'Lập trình Laravel')->value('MaDauSach');
        $this->assertNotEmpty($maDauSach);

        $this->assertDatabaseHas('CT_TACGIA', ['MaDauSach' => $maDauSach, 'MaTacGia' => 1]);
        $this->assertDatabaseHas('CT_TACGIA', ['MaDauSach' => $maDauSach, 'MaTacGia' => 2]);
    }
}
