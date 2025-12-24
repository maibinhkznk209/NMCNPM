<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PhieuMuonControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $maDocGia;
    private int $maSach1;
    private int $maSach2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->seedMinimalData();
    }

    private function seedMinimalData(): void
    {
        // THELOAI
        $maTheLoai = DB::table('THELOAI')->insertGetId([
            'TenTheLoai' => 'Test Genre'
        ], 'MaTheLoai');

        // NHAXUATBAN
        $maNXB = DB::table('NHAXUATBAN')->insertGetId([
            'TenNXB' => 'Test Publisher'
        ], 'MaNXB');

        // DAUSACH
        $maDauSach1 = DB::table('DAUSACH')->insertGetId([
            'TenDauSach' => 'Test Book 1',
            'MaTheLoai' => $maTheLoai,
            'NgayNhap' => now(),
        ], 'MaDauSach');

        $maDauSach2 = DB::table('DAUSACH')->insertGetId([
            'TenDauSach' => 'Test Book 2',
            'MaTheLoai' => $maTheLoai,
            'NgayNhap' => now(),
        ], 'MaDauSach');

        // SACH (cuốn sách/edition)
        $this->maSach1 = DB::table('SACH')->insertGetId([
            'MaDauSach' => $maDauSach1,
            'MaNXB' => $maNXB,
            'NamXuatBan' => 2020,
            'TriGia' => 50000,
            'SoLuong' => 1,
        ], 'MaSach');

        $this->maSach2 = DB::table('SACH')->insertGetId([
            'MaDauSach' => $maDauSach2,
            'MaNXB' => $maNXB,
            'NamXuatBan' => 2021,
            'TriGia' => 60000,
            'SoLuong' => 1,
        ], 'MaSach');

        // LOAIDOCGIA (string PK in your design)
        $maLoaiDocGia = 'LDG001';
        DB::table('LOAIDOCGIA')->insert([
            'MaLoaiDocGia' => $maLoaiDocGia,
            'TenLoaiDocGia' => 'Sinh viên'
        ]);

        // DOCGIA (string PK MaDocGia)
        $this->maDocGia = 'DG001';
        DB::table('DOCGIA')->insert([
            'MaDocGia' => $this->maDocGia,
            'TenDocGia' => 'Test Reader',
            'MaLoaiDocGia' => $maLoaiDocGia,
            'NgaySinh' => '1990-01-01',
            'DiaChi' => 'Test Address',
            'Email' => 'reader@test.com',
            'NgayLapThe' => '2024-01-01',
            'NgayHetHan' => '2026-01-01',
            'TongNo' => 0,
        ]);
    }

    /** @test */
    public function can_create_borrow_record(): void
    {
        $resp = $this->postJson('/api/borrow-records', [
            'MaDocGia' => $this->maDocGia,
            'MaSach' => [$this->maSach1],
            'borrow_date' => now()->toDateString(),
        ]);

        $resp->assertStatus(201)
            ->assertJsonPath('success', true);

        $maPhieuMuon = $resp->json('data.MaPhieuMuon');
        $this->assertNotEmpty($maPhieuMuon);

        $this->assertDatabaseHas('PHIEUMUON', [
            'MaPhieuMuon' => $maPhieuMuon,
            'MaDocGia' => $this->maDocGia,
        ]);

        $this->assertDatabaseHas('CT_PHIEUMUON', [
            'MaPhieuMuon' => $maPhieuMuon,
            'MaSach' => $this->maSach1,
        ]);
    }

    /** @test */
    public function can_get_reader_borrow_history(): void
    {
        // Create a borrow record first
        $resp = $this->postJson('/api/borrow-records', [
            'MaDocGia' => $this->maDocGia,
            'MaSach' => [$this->maSach1, $this->maSach2],
            'borrow_date' => now()->toDateString(),
        ]);

        $resp->assertStatus(201);

        $history = $this->getJson('/api/borrow-records/doc-gia/' . $this->maDocGia);
        $history->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
