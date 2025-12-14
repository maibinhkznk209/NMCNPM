<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\NhaXuatBan;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use App\Models\Sach;

class NhaXuatBanControllerTest extends TestCase
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
    public function can_create_publisher()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/api/nhaxuatban', [
            'TenNXB' => 'NXB Trẻ',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Thêm nhà xuất bản thành công'
                ]);

        $this->assertDatabaseHas('NHAXUATBAN', [
            'TenNXB' => 'NXB Trẻ',
        ]);
    }

    /** @test */
    public function cannot_create_publisher_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->postJson('/api/nhaxuatban', [
            'TenNXB' => '',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function cannot_create_duplicate_publisher()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        NhaXuatBan::factory()->create(['TenNXB' => 'NXB Trẻ']);

        $response = $this->postJson('/api/nhaxuatban', [
            'TenNXB' => 'NXB Trẻ',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function can_update_publisher()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $nhaXuatBan = NhaXuatBan::factory()->create(['TenNXB' => 'NXB Trẻ']);

        $response = $this->putJson("/api/nhaxuatban/{$nhaXuatBan->id}", [
            'TenNXB' => 'NXB Kim Đồng',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cập nhật nhà xuất bản thành công'
                ]);

        $this->assertDatabaseHas('NHAXUATBAN', [
            'id' => $nhaXuatBan->id,
            'TenNXB' => 'NXB Kim Đồng',
        ]);
    }

    /** @test */
    public function cannot_update_publisher_with_missing_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $nhaXuatBan = NhaXuatBan::factory()->create(['TenNXB' => 'NXB Trẻ']);

        $response = $this->putJson("/api/nhaxuatban/{$nhaXuatBan->id}", [
            'TenNXB' => '',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function cannot_update_publisher_to_duplicate_name()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $nhaXuatBan1 = NhaXuatBan::factory()->create(['TenNXB' => 'NXB Trẻ']);
        $nhaXuatBan2 = NhaXuatBan::factory()->create(['TenNXB' => 'NXB Kim Đồng']);

        $response = $this->putJson("/api/nhaxuatban/{$nhaXuatBan1->id}", [
            'TenNXB' => 'NXB Kim Đồng',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                ]);
    }

    /** @test */
    public function can_delete_publisher_without_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $nhaXuatBan = NhaXuatBan::factory()->create(['TenNXB' => 'NXB Trẻ']);

        $response = $this->deleteJson("/api/nhaxuatban/{$nhaXuatBan->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Xóa nhà xuất bản thành công'
                ]);

        $this->assertDatabaseMissing('NHAXUATBAN', [
            'id' => $nhaXuatBan->id,
        ]);
    }

    /** @test */
    public function cannot_delete_publisher_with_books()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $nhaXuatBan = NhaXuatBan::factory()->create(['TenNXB' => 'NXB Trẻ']);
        $sach = Sach::factory()->create(['MaNhaXuatBan' => $nhaXuatBan->id]);

        $response = $this->deleteJson("/api/nhaxuatban/{$nhaXuatBan->id}");

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                ]);

        $this->assertDatabaseHas('NHAXUATBAN', [
            'id' => $nhaXuatBan->id,
        ]);
    }

    /** @test */
    public function can_show_publishers_page()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->get('/publishers');

        $response->assertStatus(200);
        $response->assertViewIs('publishers');
    }
}
