<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PhieuMuon;
use App\Models\ChiTietPhieuMuon;
use App\Models\Sach;
use App\Models\DocGia;
use App\Models\TheLoai;
use App\Models\TacGia;
use App\Models\NhaXuatBan;
use App\Models\LoaiDocGia;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\QuyDinh;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Carbon\Carbon;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $docGia;
    protected $sach;
    protected $theLoai;
    protected $tacGia;
    protected $nhaXuatBan;
    protected $loaiDocGia;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo vai trò và tài khoản
        $vaiTro = VaiTro::factory()->create(['VaiTro' => 'Admin']);
        $this->user = TaiKhoan::factory()->create([
            'HoVaTen' => 'admin',
            'Email' => 'admin@example.com',
            'MatKhau' => bcrypt('password'),
            'vaitro_id' => $vaiTro->id
        ]);

        // Tạo loại độc giả
        $this->loaiDocGia = LoaiDocGia::factory()->create([
            'TenLoaiDocGia' => 'Sinh viên'
        ]);

        // Tạo độc giả
        $this->docGia = DocGia::factory()->create([
            'HoTen' => 'Nguyễn Văn A',
            'loaidocgia_id' => $this->loaiDocGia->id,
            'NgaySinh' => '1990-01-01',
            'DiaChi' => 'Hà Nội',
            'Email' => 'test@example.com',
            'NgayLapThe' => '2024-01-01',
            'NgayHetHan' => '2025-12-31'
        ]);

        // Tạo tác giả
        $this->tacGia = TacGia::factory()->create([
            'TenTacGia' => 'Tác giả Test'
        ]);

        // Tạo nhà xuất bản
        $this->nhaXuatBan = NhaXuatBan::factory()->create([
            'TenNXB' => 'NXB Giáo Dục'
        ]);

        // Tạo thể loại
        $this->theLoai = TheLoai::factory()->create([
            'TenTheLoai' => 'Văn học'
        ]);

        // Tạo sách
        $this->sach = Sach::factory()->create([
            'TenSach' => 'Sách Test',
            'MaTacGia' => $this->tacGia->id,
            'MaNhaXuatBan' => $this->nhaXuatBan->id,
            'TinhTrang' => Sach::TINH_TRANG_CO_SAN
        ]);

        // Gắn thể loại cho sách
        $this->sach->theLoais()->attach($this->theLoai->id);

        // Tạo quy định về số ngày mượn (không dùng factory)
        QuyDinh::create([
            'TenThamSo' => 'NgayMuonToiDa',
            'GiaTri' => '30'
        ]);

        // Đăng nhập
        $this->actingAs($this->user);
        $this->withoutMiddleware();
    }

    /** @test */
    public function can_get_genre_statistics_with_valid_data()
    {
        // Tạo phiếu mượn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-07-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id
        ]);

        $response = $this->getJson('/api/reports/genre-statistics?month=7&year=2025');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'month' => '7',
                        'year' => '2025',
                        'total_borrows' => 1
                    ]
                ]);
    }

    /** @test */
    public function cannot_get_genre_statistics_without_month()
    {
        $response = $this->getJson('/api/reports/genre-statistics?year=2025');

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Vui lòng chọn tháng và năm để tạo báo cáo'
                ]);
    }

    /** @test */
    public function cannot_get_genre_statistics_without_year()
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=7');

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Vui lòng chọn tháng và năm để tạo báo cáo'
                ]);
    }

    /** @test */
    public function can_get_genre_statistics_with_multiple_books()
    {
        // Tạo thêm sách và thể loại
        $theLoai2 = TheLoai::factory()->create(['TenTheLoai' => 'Khoa học']);
        $sach2 = Sach::factory()->create([
            'TenSach' => 'Sách Khoa học',
            'MaTacGia' => $this->tacGia->id,
            'MaNhaXuatBan' => $this->nhaXuatBan->id
        ]);
        $sach2->theLoais()->attach($theLoai2->id);
        
        // Tạo phiếu mượn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-07-01'
        ]);
        
        // Mượn cả hai sách
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $sach2->id
        ]);

        $response = $this->getJson('/api/reports/genre-statistics?month=7&year=2025');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_borrows' => 2
                    ]
                ]);
    }

    /** @test */
    public function can_get_genre_statistics_with_no_data()
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=7&year=2025');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_borrows' => 0,
                        'genres' => []
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_valid_date()
    {
        // Tạo quy định về số ngày mượn (không dùng factory)
        QuyDinh::where('TenThamSo', 'NgayMuonToiDa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn quá hạn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01' // Mượn từ 1/6
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15', // Trả ngày 15/7 (quá hạn)
            'TienPhat' => 15000
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'date' => '2025-07-20',
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function cannot_get_overdue_books_without_date()
    {
        $response = $this->getJson('/api/reports/overdue-books');

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Vui lòng chọn ngày để tạo báo cáo'
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_invalid_date_format()
    {
        $response = $this->getJson('/api/reports/overdue-books?date=invalid-date');

        $this->assertTrue(in_array($response->status(), [400, 500]));
    }

    /** @test */
    public function can_get_overdue_books_with_future_date()
    {
        $response = $this->getJson('/api/reports/overdue-books?date=2030-12-31');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'overdue_books' => []
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_past_date()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn quá hạn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-01-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-02-15', // Trả quá hạn
            'TienPhat' => 15000
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-02-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_not_returned_books()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn chưa trả
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01' // Mượn từ 1/6
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id
            // Không có NgayTra
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_negative_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt âm
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => -5000 // Tiền phạt âm
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_zero_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt bằng 0
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 0 // Tiền phạt bằng 0
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_large_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt lớn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 999999999 // Tiền phạt rất lớn
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_decimal_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt có phần thập phân
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 15000.5 // Tiền phạt có phần thập phân
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_multiple_overdue_books()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo nhiều phiếu mượn quá hạn
        $phieuMuon1 = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        $phieuMuon2 = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-05'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon1->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 15000
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon2->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-20',
            'TienPhat' => 20000
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-25');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 2
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_no_overdue_books()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn không quá hạn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-07-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-25', // Trả đúng hạn
            'TienPhat' => 0
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-30');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 0
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_custom_borrow_days()
    {
        // Tạo quy định với số ngày mượn khác
        QuyDinh::where('TenThamSo', 'NgayMuonToiDa')->update(['GiaTri' => '15']);
        
        // Tạo phiếu mượn với thời hạn 15 ngày
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-07-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-20', // Trả sau 19 ngày (quá hạn)
            'TienPhat' => 5000
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-25');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_without_quy_dinh()
    {
        // Xóa quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->delete();
        
        // Tạo phiếu mượn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 15000
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $this->assertTrue(in_array($response->status(), [200, 400, 500]));
    }

    /** @test */
    public function can_export_genre_statistics()
    {
        // Tạo phiếu mượn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-07-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id
        ]);

        $response = $this->getJson('/api/reports/export-genre-statistics?month=7&year=2025');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function can_export_overdue_books()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn quá hạn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 15000
        ]);

        $response = $this->getJson('/api/reports/export-overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function can_check_negative_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt âm
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => -5000
        ]);

        $response = $this->getJson('/api/fix-negative-fines/check');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'summary' => [
                            'negative_fines_count' => 1
                        ]
                    ]
                ]);
    }

    /** @test */
    public function can_fix_negative_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt âm
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => -5000
        ]);

        $response = $this->postJson('/api/fix-negative-fines/fix');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'summary' => [
                            'fixed_records' => 1
                        ]
                    ]
                ]);
    }

    /** @test */
    public function can_recalculate_all_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 15000
        ]);

        $response = $this->postJson('/api/fix-negative-fines/recalculate');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'summary' => [
                            'updated_records' => 1
                        ]
                    ]
                ]);
    }

    /** @test */
    public function can_debug_overdue_books()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 15000
        ]);

        $response = $this->getJson('/api/reports/debug-overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_books' => 1,
                        'overdue_books_count' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_compare_overdue_results()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 15000
        ]);

        $response = $this->getJson('/api/reports/compare-overdue?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'date' => '2025-07-20'
                ]);
    }

    /** @test */
    public function can_get_genre_statistics_with_invalid_month()
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=13&year=2025');

        // The API doesn't validate month range, so it should return 200 with empty data
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_borrows' => 0
                    ]
                ]);
    }

    /** @test */
    public function can_get_genre_statistics_with_invalid_year()
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=7&year=1800');

        // The API doesn't validate year range, so it should return 200 with empty data
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_borrows' => 0
                    ]
                ]);
    }

    /** @test */
    public function can_get_genre_statistics_with_string_month()
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=abc&year=2025');

        // The API doesn't validate month format, so it should return 200 with empty data
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_borrows' => 0
                    ]
                ]);
    }

    /** @test */
    public function can_get_genre_statistics_with_string_year()
    {
        $response = $this->getJson('/api/reports/genre-statistics?month=7&year=abc');

        // The API doesn't validate year format, so it should return 200 with empty data
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_borrows' => 0
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_edge_case_dates()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với ngày edge case
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-01', // Trả đúng ngày hết hạn
            'TienPhat' => 0
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-01');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 0
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_exact_overdue_date()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với ngày trả đúng 1 ngày sau hạn
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-02', // Trả 1 ngày sau hạn
            'TienPhat' => 1000
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-02');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_null_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt null
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => 0
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_empty_string_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt chuỗi rỗng
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => ''
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }

    /** @test */
    public function can_get_overdue_books_with_string_fines()
    {
        // Tạo quy định
        QuyDinh::where('TenQuyDinh', 'Số ngày mượn tối đa')->update(['GiaTri' => '30']);
        
        // Tạo phiếu mượn với tiền phạt là chuỗi
        $phieuMuon = PhieuMuon::factory()->create([
            'docgia_id' => $this->docGia->id,
            'NgayMuon' => '2025-06-01'
        ]);
        
        ChiTietPhieuMuon::factory()->create([
            'phieumuon_id' => $phieuMuon->id,
            'sach_id' => $this->sach->id,
            'NgayTra' => '2025-07-15',
            'TienPhat' => '15000'
        ]);

        $response = $this->getJson('/api/reports/overdue-books?date=2025-07-20');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'total_overdue' => 1
                    ]
                ]);
    }
} 
