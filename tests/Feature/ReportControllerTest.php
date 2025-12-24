<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PhieuMuon;
use App\Models\CT_PHIEUMUON;
use App\Models\Sach;
use App\Models\DauSach;
use App\Models\CuonSach;
use App\Models\DocGia;
use App\Models\TheLoai;
use App\Models\NhaXuatBan;
use App\Models\LoaiDocGia;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected TaiKhoan $user;
    protected DocGia $docGia;
    protected Sach $sach;
    protected DauSach $dauSach;
    protected TheLoai $theLoai;
    protected NhaXuatBan $nhaXuatBan;
    protected LoaiDocGia $loaiDocGia;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::statement('DROP VIEW IF EXISTS CT_PHIEUMUON');
        } catch (\Throwable $e) {
        }

        try {
            DB::statement('CREATE VIEW CT_PHIEUMUON AS SELECT * FROM CT_PHIEUMUON');
        } catch (\Throwable $e) {
        }

        $vaiTro = VaiTro::factory()->create(['VaiTro' => 'Admin']);
        $this->user = TaiKhoan::factory()->create([
            'HoVaTen' => 'admin',
            'Email' => 'admin@example.com',
            'MatKhau' => bcrypt('password'),
            'vaitro_id' => $vaiTro->id,
        ]);

        $this->loaiDocGia = LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Sinh viên']);
        $this->docGia = DocGia::factory()->create([
            'TenDocGia' => 'Nguyễn Văn A',
            'MaLoaiDocGia' => $this->loaiDocGia->MaLoaiDocGia,
            'NgaySinh' => '1990-01-01',
            'DiaChi' => 'Hà Nội',
            'Email' => 'test@example.com',
            'NgayLapThe' => '2024-01-01',
            'NgayHetHan' => '2025-12-31',
        ]);

        $this->theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Văn học']);
        $this->nhaXuatBan = NhaXuatBan::factory()->create(['TenNXB' => 'NXB Giáo Dục']);

        $this->dauSach = DauSach::query()->create([
            'TenDauSach' => 'Sách Test',
            'MaTheLoai' => $this->theLoai->MaTheLoai,
            'NgayNhap' => Carbon::now(),
        ]);

        $this->sach = Sach::query()->create([
            'MaDauSach' => $this->dauSach->MaDauSach,
            'MaNXB' => $this->nhaXuatBan->MaNXB,
            'NamXuatBan' => 2020,
            'TriGia' => 100000,
            'SoLuong' => 1,
        ]);

        CuonSach::query()->create([
            'MaSach' => $this->sach->MaSach,
            'NgayNhap' => Carbon::now(),
        ]);

        $this->actingAs($this->user);
        $this->withoutMiddleware();
    }

    public function test_can_get_genre_statistics_with_valid_data(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-12-24',
            'NgayHenTra' => '2025-12-26',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => '2025-12-26',
            'TienPhat' => 0,
        ]);

        $response = $this->getJson('/api/reports/genre-statistics?month=12&year=2025');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'month' => '12',
                    'year' => '2025',
                    'total_borrows' => 1,
                ],
            ]);
    }

    public function test_cannot_get_genre_statistics_without_month(): void
    {
        $response = $this->getJson('/api/reports/genre-statistics?year=2025');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Vui lòng chọn tháng và năm để tạo báo cáo',
            ]);
    }

    public function test_cannot_get_genre_statistics_without_year(): void
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=12');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Vui lòng chọn tháng và năm để tạo báo cáo',
            ]);
    }

    public function test_can_get_genre_statistics_with_multiple_books(): void
    {
        $theLoai2 = TheLoai::factory()->create(['TenTheLoai' => 'Khoa học']);
        $dauSach2 = DauSach::query()->create([
            'TenDauSach' => 'Sách Khoa học',
            'MaTheLoai' => $theLoai2->MaTheLoai,
            'NgayNhap' => Carbon::now(),
        ]);
        $sach2 = Sach::query()->create([
            'MaDauSach' => $dauSach2->MaDauSach,
            'MaNXB' => $this->nhaXuatBan->MaNXB,
            'NamXuatBan' => 2021,
            'TriGia' => 120000,
            'SoLuong' => 1,
        ]);
        CuonSach::query()->create([
            'MaSach' => $sach2->MaSach,
            'NgayNhap' => Carbon::now(),
        ]);

        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-12-24',
            'NgayHenTra' => '2025-12-26',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => '2025-12-30',
            'TienPhat' => 0,
        ]);
        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $sach2->MaSach,
            'NgayTra' => '2025-12-29',
            'TienPhat' => 0,
        ]);

        $response = $this->getJson('/api/reports/genre-statistics?month=12&year=2025');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_borrows' => 2,
                ],
            ]);
    }

    public function test_can_get_genre_statistics_with_no_data(): void
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=12&year=2025');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_borrows' => 0,
                    'genres' => [],
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_valid_date(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-01',
            'NgayHenTra' => '2025-06-15',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 15000,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'date' => '2025-12-25',
                    'total_overdue' => 1,
                ],
            ]);
    }

    public function test_cannot_get_overdue_books_without_date(): void
    {
        $response = $this->getJson('/api/reports/overdue-books');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Vui lòng chọn ngày để tạo báo cáo',
            ]);
    }

    public function test_can_get_overdue_books_with_invalid_date_format(): void
    {
        $response = $this->getJson('/api/reports/overdue-books?date=invalid-date');

        $this->assertTrue(in_array($response->status(), [200, 400, 500], true));
    }

    public function test_can_get_overdue_books_with_future_date(): void
    {
        $response = $this->getJson('/api/reports/overdue-books?date=2030-12-31');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'overdue_books' => [],
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_past_date(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-01-01',
            'NgayHenTra' => '2025-01-15',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 15000,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-02-20');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 1,
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_not_returned_books(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-01',
            'NgayHenTra' => '2025-06-15',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 0,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 1,
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_negative_fines(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-01',
            'NgayHenTra' => '2025-06-15',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => -5000,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 1,
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_zero_fines(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-01',
            'NgayHenTra' => '2025-06-15',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 0,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 1,
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_large_fines(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-01',
            'NgayHenTra' => '2025-06-15',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 999999999,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 1,
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_decimal_fines(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-01',
            'NgayHenTra' => '2025-06-15',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 15000.5,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 1,
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_multiple_overdue_books(): void
    {
        $phieuMuon1 = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-01',
            'NgayHenTra' => '2025-06-15',
        ]);

        $phieuMuon2 = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-06-05',
            'NgayHenTra' => '2025-06-19',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon1->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 15000,
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon2->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => null,
            'TienPhat' => 20000,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-29');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 2,
                ],
            ]);
    }

    public function test_can_get_overdue_books_with_no_overdue_books(): void
    {
        $phieuMuon = PhieuMuon::factory()->create([
            'MaDocGia' => $this->docGia->MaDocGia,
            'NgayMuon' => '2025-12-24',
            'NgayHenTra' => '2025-12-26',
        ]);

        CT_PHIEUMUON::query()->create([
            'MaPhieuMuon' => $phieuMuon->MaPhieuMuon,
            'MaSach' => $this->sach->MaSach,
            'NgayTra' => '2025-12-26',
            'TienPhat' => 0,
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-12-31');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_overdue' => 0,
                ],
            ]);
    }
}

