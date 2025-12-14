<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TacGia;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\Sach;

class TacGiaControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_author()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/api/tacgia', [
            'TenTacGia' => 'Nguyễn Nhật Ánh',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Thêm tác giả thành công'
                ]);

        $this->assertDatabaseHas('TACGIA', [
            'TenTacGia' => 'Nguyễn Nhật Ánh',
        ]);
    }

    /** @test */
    public function cannot_create_author_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/api/tacgia', [
            'TenTacGia' => '',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function cannot_create_duplicate_author()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        TacGia::factory()->create(['TenTacGia' => 'Nguyễn Nhật Ánh']);

        $response = $this->postJson('/api/tacgia', [
            'TenTacGia' => 'Nguyễn Nhật Ánh',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function can_update_author()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $tacGia = TacGia::factory()->create(['TenTacGia' => 'Nguyễn Nhật Ánh']);

        $response = $this->putJson("/api/tacgia/{$tacGia->id}", [
            'TenTacGia' => 'Tô Hoài',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cập nhật tác giả thành công'
                ]);

        $this->assertDatabaseHas('TACGIA', [
            'id' => $tacGia->id,
            'TenTacGia' => 'Tô Hoài',
        ]);
    }

    /** @test */
    public function cannot_update_author_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $tacGia = TacGia::factory()->create(['TenTacGia' => 'Nguyễn Nhật Ánh']);

        $response = $this->putJson("/api/tacgia/{$tacGia->id}", [
            'TenTacGia' => '',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function cannot_update_author_to_duplicate_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $tacGia1 = TacGia::factory()->create(['TenTacGia' => 'Nguyễn Nhật Ánh']);
        $tacGia2 = TacGia::factory()->create(['TenTacGia' => 'Tô Hoài']);

        $response = $this->putJson("/api/tacgia/{$tacGia1->id}", [
            'TenTacGia' => 'Tô Hoài',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function can_delete_author_without_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $tacGia = TacGia::factory()->create(['TenTacGia' => 'Nguyễn Nhật Ánh']);

        $response = $this->deleteJson("/api/tacgia/{$tacGia->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Xóa tác giả thành công'
                ]);

        $this->assertDatabaseMissing('TACGIA', [
            'id' => $tacGia->id,
        ]);
    }

    /** @test */
    public function cannot_delete_author_with_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $tacGia = TacGia::factory()->create(['TenTacGia' => 'Nguyễn Nhật Ánh']);
        $sach = Sach::factory()->create(['MaTacGia' => $tacGia->id]);

        $response = $this->deleteJson("/api/tacgia/{$tacGia->id}");

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                ]);

        $this->assertDatabaseHas('TACGIA', [
            'id' => $tacGia->id,
        ]);
    }

    /** @test */
    public function can_show_authors_page()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->get('/authors');

        $response->assertStatus(200);
        $response->assertViewIs('authors');
    }
}
