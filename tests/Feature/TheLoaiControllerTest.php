<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TheLoai;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\Sach;

class TheLoaiControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Force sử dụng SQLite in-memory cho test
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    /** @test */
    public function can_create_genre()
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

    /** @test */
    public function cannot_create_genre_with_missing_name()
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

    /** @test */
    public function cannot_create_duplicate_genre()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        // Tạo thể loại đầu tiên
        TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        // Thử tạo thể loại trùng tên
        $response = $this->postJson('/theloai', [
            'TenTheLoai' => 'Tiểu thuyết',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function can_update_genre()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        $response = $this->putJson("/theloai/{$theLoai->id}", [
            'TenTheLoai' => 'Tiểu thuyết mới',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cập nhật thể loại thành công'
                ]);

        $this->assertDatabaseHas('THELOAI', [
            'id' => $theLoai->id,
            'TenTheLoai' => 'Tiểu thuyết mới',
        ]);
    }

    /** @test */
    public function cannot_update_genre_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        $response = $this->putJson("/theloai/{$theLoai->id}", [
            'TenTheLoai' => '',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function cannot_update_genre_to_duplicate_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai1 = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);
        $theLoai2 = TheLoai::factory()->create(['TenTheLoai' => 'Truyện ngắn']);

        $response = $this->putJson("/theloai/{$theLoai1->id}", [
            'TenTheLoai' => 'Truyện ngắn',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function can_delete_genre_without_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);

        $response = $this->deleteJson("/theloai/{$theLoai->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Xóa thể loại thành công'
                ]);

        $this->assertDatabaseMissing('THELOAI', [
            'id' => $theLoai->id,
        ]);
    }

    /** @test */
    public function cannot_delete_genre_with_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $theLoai = TheLoai::factory()->create(['TenTheLoai' => 'Tiểu thuyết']);
        $sach = Sach::factory()->create();
        $sach->theLoais()->attach($theLoai->id);

        $response = $this->deleteJson("/theloai/{$theLoai->id}");

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Không thể xóa thể loại này vì đang có sách thuộc thể loại này'
                ]);

        $this->assertDatabaseHas('THELOAI', [
            'id' => $theLoai->id,
        ]);
    }

    /** @test */
    public function can_show_genres_page()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->get('/genres');

        $response->assertStatus(200);
        $response->assertViewIs('genres');
    }
}
