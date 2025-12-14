<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Sach;
use App\Models\TacGia;
use App\Models\TheLoai;
use App\Models\NhaXuatBan;
use App\Models\User;
use App\Models\TaiKhoan;
use App\Models\VaiTro;

class SachControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_book()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'vaitro_id' => $role->id,
        ]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);
        $tacGia = TacGia::factory()->create();
        $theLoai = TheLoai::factory()->create();
        $nxb = NhaXuatBan::factory()->create();

        $response = $this->post('/books', [
            'TenSach' => 'Sách test',
            'NamXuatBan' => 2024,
            'TriGia' => 50000,
            'SoLuong' => 1,
            'tacGias' => $tacGia->id,
            'theLoais' => json_encode([$theLoai->id]),
            'nhaXuatBans' => $nxb->id,
        ]);

        $response->assertRedirect('/books');
        $this->assertDatabaseHas('SACH', [
            'TenSach' => 'Sách test',
            'TriGia' => 50000,
        ]);
    }

    /** @test */
    public function it_can_update_a_book()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'vaitro_id' => $role->id,
        ]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);
        $tacGia = TacGia::factory()->create();
        $theLoai = TheLoai::factory()->create();
        $nxb = NhaXuatBan::factory()->create();
        $sach = Sach::factory()->create([
            'MaTacGia' => $tacGia->id,
            'MaNhaXuatBan' => $nxb->id,
        ]);
        $sach->theLoais()->attach($theLoai->id);

        $response = $this->put("/books/{$sach->id}", [
            'TenSach' => 'Sách đã sửa',
            'NamXuatBan' => 2024,
            'TriGia' => 60000,
            'tacGias' => $tacGia->id,
            'theLoais' => json_encode([$theLoai->id]),
            'nhaXuatBans' => $nxb->id,
            'TinhTrang' => 1,
        ]);

        $response->assertRedirect('/books');
        $this->assertDatabaseHas('SACH', [
            'id' => $sach->id,
            'TenSach' => 'Sách đã sửa',
            'TriGia' => 60000,
        ]);
    }

    /** @test */
    public function it_can_delete_a_book()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'vaitro_id' => $role->id,
        ]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);
        $tacGia = TacGia::factory()->create();
        $nxb = NhaXuatBan::factory()->create();
        $sach = Sach::factory()->create([
            'MaTacGia' => $tacGia->id,
            'MaNhaXuatBan' => $nxb->id,
        ]);

        $response = $this->delete("/books/{$sach->id}");
        $response->assertRedirect('/books');
        $this->assertDatabaseMissing('SACH', [
            'id' => $sach->id,
        ]);
    }

    /** @test */
    public function cannot_create_book_with_missing_required_fields()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->post('/books', [
            // Thiếu TenSach, NamXuatBan, TriGia, tacGias, theLoais, nhaXuatBans
        ]);
        $response->assertSessionHasErrors(['TenSach', 'NamXuatBan', 'TriGia', 'tacGias', 'theLoais', 'nhaXuatBans']);
    }

    /** @test */
    public function cannot_create_book_with_invalid_year()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);
        $tacGia = TacGia::factory()->create();
        $theLoai = TheLoai::factory()->create();
        $nxb = NhaXuatBan::factory()->create();
        $response = $this->post('/books', [
            'TenSach' => 'Sách test',
            'NamXuatBan' => 1900, // Quá cũ
            'TriGia' => 50000,
            'SoLuong' => 1,
            'tacGias' => $tacGia->id,
            'theLoais' => json_encode([$theLoai->id]),
            'nhaXuatBans' => $nxb->id,
        ]);
        $response->assertSessionHasErrors(['NamXuatBan']);
    }

    /** @test */
    public function cannot_create_book_with_negative_price()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);
        $tacGia = TacGia::factory()->create();
        $theLoai = TheLoai::factory()->create();
        $nxb = NhaXuatBan::factory()->create();
        $response = $this->post('/books', [
            'TenSach' => 'Sách test',
            'NamXuatBan' => 2024,
            'TriGia' => -10000, // Giá âm
            'SoLuong' => 1,
            'tacGias' => $tacGia->id,
            'theLoais' => json_encode([$theLoai->id]),
            'nhaXuatBans' => $nxb->id,
        ]);
        $response->assertSessionHasErrors(['TriGia']);
    }

    /** @test */
    public function cannot_update_book_with_invalid_data()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);
        $tacGia = TacGia::factory()->create();
        $theLoai = TheLoai::factory()->create();
        $nxb = NhaXuatBan::factory()->create();
        $sach = Sach::factory()->create([
            'MaTacGia' => $tacGia->id,
            'MaNhaXuatBan' => $nxb->id,
        ]);
        $sach->theLoais()->attach($theLoai->id);
        $response = $this->put("/books/{$sach->id}", [
            'TenSach' => '', // Thiếu tên sách
            'NamXuatBan' => 2024,
            'TriGia' => 60000,
            'tacGias' => $tacGia->id,
            'theLoais' => json_encode([$theLoai->id]),
            'nhaXuatBans' => $nxb->id,
            'TinhTrang' => 1,
        ]);
        $response->assertSessionHasErrors(['TenSach']);
    }
}
