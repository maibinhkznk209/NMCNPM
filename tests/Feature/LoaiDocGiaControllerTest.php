<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\LoaiDocGia;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\DocGia;

class LoaiDocGiaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_create_reader_type()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/api/loaidocgia', [
            'TenLoaiDocGia' => 'Sinh viên',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Thêm loại độc giả thành công'
                ]);

        $this->assertDatabaseHas('LOAIDOCGIA', [
            'TenLoaiDocGia' => 'Sinh viên',
        ]);
    }

    /** @test */
    public function cannot_create_reader_type_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/api/loaidocgia', [
            'TenLoaiDocGia' => '',
        ]);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'message' => 'Tên loại độc giả là bắt buộc',
                ]);
    }

    /** @test */
    public function cannot_create_duplicate_reader_type()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Sinh viên']);

        $response = $this->postJson('/api/loaidocgia', [
            'TenLoaiDocGia' => 'Sinh viên',
        ]);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'message' => 'Tên loại độc giả đã tồn tại',
                ]);
    }

    /** @test */
    public function can_update_reader_type()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loaiDocGia = LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Sinh viên']);

        $response = $this->putJson("/api/loaidocgia/{$loaiDocGia->MaLoaiDocGia}", [
            'TenLoaiDocGia' => 'Giảng viên',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cập nhật loại độc giả thành công'
                ]);

        $this->assertDatabaseHas('LOAIDOCGIA', [
            'MaLoaiDocGia' => $loaiDocGia->MaLoaiDocGia,
            'TenLoaiDocGia' => 'Giảng viên',
        ]);
    }

    /** @test */
    public function cannot_update_reader_type_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loaiDocGia = LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Sinh viên']);

        $response = $this->putJson("/api/loaidocgia/{$loaiDocGia->MaLoaiDocGia}", [
            'TenLoaiDocGia' => '',
        ]);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'message' => 'Tên loại độc giả là bắt buộc',
                ]);
    }

    /** @test */
    public function cannot_update_reader_type_to_duplicate_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loaiDocGia1 = LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Sinh viên']);
        $loaiDocGia2 = LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Giảng viên']);

        $response = $this->putJson("/api/loaidocgia/{$loaiDocGia1->MaLoaiDocGia}", [
            'TenLoaiDocGia' => 'Giảng viên',
        ]);

        $response->assertStatus(422)
                ->assertJsonFragment([
                    'message' => 'Tên loại độc giả đã tồn tại',
                ]);
    }

    /** @test */
    public function can_delete_reader_type_without_readers()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loaiDocGia = LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Sinh viên']);

        $response = $this->deleteJson("/api/loaidocgia/{$loaiDocGia->MaLoaiDocGia}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Xóa loại độc giả thành công'
                ]);

        $this->assertDatabaseMissing('LOAIDOCGIA', [
            'MaLoaiDocGia' => $loaiDocGia->MaLoaiDocGia,
        ]);
    }

    /** @test */
    public function cannot_delete_reader_type_with_readers()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loaiDocGia = LoaiDocGia::factory()->create(['TenLoaiDocGia' => 'Sinh viên']);
        $docGia = DocGia::factory()->create(['MaLoaiDocGia' => $loaiDocGia->MaLoaiDocGia]);

        $response = $this->deleteJson("/api/loaidocgia/{$loaiDocGia->MaLoaiDocGia}");

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                ]);

        $this->assertDatabaseHas('LOAIDOCGIA', [
            'MaLoaiDocGia' => $loaiDocGia->MaLoaiDocGia,
        ]);
    }

    /** @test */
    public function can_show_reader_types_page()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->get('/reader-types');

        $response->assertStatus(200);
        $response->assertViewIs('reader-types');
    }
}
