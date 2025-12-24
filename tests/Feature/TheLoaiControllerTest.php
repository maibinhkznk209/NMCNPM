<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TheLoai;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\DauSach;

class TheLoaiControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_genre()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/theloai', [
            'TenTheLoai' => 'Tiểu thuyết',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Thêm thể loại thành công'
            ]);

        $this->assertDatabaseHas('THELOAI', [
            'TenTheLoai' => 'Tiểu thuyết',
        ]);
    }

    public function test_cannot_create_genre_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/theloai', [
            'TenTheLoai' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_cannot_create_duplicate_genre()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        $response = $this->postJson('/theloai', [
            'TenTheLoai' => 'Tiểu thuyết',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_update_genre()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        $response = $this->putJson("/theloai/{$theLoai->MaTheLoai}", [
            'TenTheLoai' => 'Tiểu thuyết mới',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật thể loại thành công'
            ]);

        $this->assertDatabaseHas('THELOAI', [
            'MaTheLoai' => $theLoai->MaTheLoai,
            'TenTheLoai' => 'Tiểu thuyết mới',
        ]);
    }

    public function test_cannot_update_genre_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        $response = $this->putJson("/theloai/{$theLoai->MaTheLoai}", [
            'TenTheLoai' => '',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_cannot_update_genre_to_duplicate_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai1 = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);
        TheLoai::factory()->create(['TenTheLoai' => 'Truyện ngắn']);

        $response = $this->putJson("/theloai/{$theLoai1->MaTheLoai}", [
            'TenTheLoai' => 'Truyện ngắn',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_delete_genre_without_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        $response = $this->deleteJson("/theloai/{$theLoai->MaTheLoai}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Xóa thể loại thành công'
            ]);

        $this->assertDatabaseMissing('THELOAI', [
            'MaTheLoai' => $theLoai->MaTheLoai,
        ]);
    }

    public function test_cannot_delete_genre_with_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        DauSach::factory()->create([
            'MaTheLoai' => $theLoai->MaTheLoai,
        ]);

        $response = $this->deleteJson("/theloai/{$theLoai->MaTheLoai}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Không thể xóa thể loại này vì đang có đầu sách thuộc thể loại này'
            ]);

        $this->assertDatabaseHas('THELOAI', [
            'MaTheLoai' => $theLoai->MaTheLoai,
        ]);
    }

    public function test_can_show_genres_page()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->get('/genres');

        $response->assertStatus(200);
        $response->assertViewIs('genres');
    }
}
