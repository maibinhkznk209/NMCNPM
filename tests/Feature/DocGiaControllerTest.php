<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\DocGia;
use App\Models\LoaiDocGia;
use App\Models\TaiKhoan;
use App\Models\VaiTro;

class DocGiaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
    }

    private function validData($overrides = [])
    {
        $loai = LoaiDocGia::factory()->create();
        return array_merge([
            'HoTen' => 'Nguyễn Văn A',
            'loaidocgia_id' => $loai->id,
            'NgaySinh' => '2000-01-01',
            'DiaChi' => 'Hà Nội',
            'Email' => 'vana@example.com',
            'NgayLapThe' => '2024-01-01',
            'NgayHetHan' => '2025-01-01',
            'TongNo' => 0,
        ], $overrides);
    }

    /** @test */
    public function can_create_reader()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $data = $this->validData();
        $response = $this->postJson('/api/docgia', $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Thêm độc giả thành công'
            ]);
        $this->assertDatabaseHas('DOCGIA', [
            'HoTen' => $data['HoTen'],
            'Email' => $data['Email'],
        ]);
    }

    /** @test */
    public function cannot_create_reader_with_missing_required_fields()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $data = $this->validData(['HoTen' => '']);
        $response = $this->postJson('/api/docgia', $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Họ tên là bắt buộc']);
    }

    /** @test */
    public function cannot_create_reader_with_invalid_email()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $data = $this->validData(['Email' => 'not-an-email']);
        $response = $this->postJson('/api/docgia', $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Email không hợp lệ']);
    }

    /** @test */
    public function cannot_create_reader_with_duplicate_email()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $data = $this->validData();
        DocGia::factory()->create(['Email' => $data['Email']]);
        $response = $this->postJson('/api/docgia', $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Email đã tồn tại']);
    }

    /** @test */
    public function cannot_create_reader_with_future_birthday()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $data = $this->validData(['NgaySinh' => now()->addYear()->format('Y-m-d')]);
        $response = $this->postJson('/api/docgia', $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Ngày sinh không hợp lệ']);
    }

    /** @test */
    public function cannot_create_reader_with_duplicate_email_case_insensitive()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $data = $this->validData(['Email' => 'test@example.com']);
        DocGia::factory()->create(['Email' => 'TEST@EXAMPLE.COM']);
        $response = $this->postJson('/api/docgia', $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Email đã tồn tại']);
    }

    /** @test */
    public function can_update_reader()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loai = LoaiDocGia::factory()->create();
        $docGia = DocGia::factory()->create(['loaidocgia_id' => $loai->id]);
        $data = $this->validData(['Email' => 'newemail@example.com', 'loaidocgia_id' => $loai->id]);
        $response = $this->putJson("/api/docgia/{$docGia->id}", $data);
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật độc giả thành công'
            ]);
        $this->assertDatabaseHas('DOCGIA', [
            'id' => $docGia->id,
            'Email' => 'newemail@example.com',
        ]);
    }

    /** @test */
    public function cannot_update_reader_with_invalid_email()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loai = LoaiDocGia::factory()->create();
        $docGia = DocGia::factory()->create(['loaidocgia_id' => $loai->id]);
        $data = $this->validData(['Email' => 'not-an-email', 'loaidocgia_id' => $loai->id]);
        $response = $this->putJson("/api/docgia/{$docGia->id}", $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Email không hợp lệ']);
    }

    /** @test */
    public function cannot_update_reader_with_expiry_before_issue_date()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loai = LoaiDocGia::factory()->create();
        $docGia = DocGia::factory()->create(['loaidocgia_id' => $loai->id]);
        $data = $this->validData([
            'NgayLapThe' => '2024-01-01',
            'NgayHetHan' => '2023-12-31',
            'loaidocgia_id' => $loai->id
        ]);
        $response = $this->putJson("/api/docgia/{$docGia->id}", $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Ngày hết hạn phải sau ngày lập thẻ']);
    }

    /** @test */
    public function cannot_create_reader_with_negative_debt()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $data = $this->validData(['TongNo' => -1000]);
        $response = $this->postJson('/api/docgia', $data);
        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Tổng nợ không hợp lệ']);
    }

    /** @test */
    public function can_delete_reader()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $loai = LoaiDocGia::factory()->create();
        $docGia = DocGia::factory()->create(['loaidocgia_id' => $loai->id]);
        $response = $this->deleteJson("/api/docgia/{$docGia->id}");
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Xóa độc giả thành công'
            ]);
        $this->assertDatabaseMissing('DOCGIA', [
            'id' => $docGia->id,
        ]);
    }

    /** @test */
    public function can_show_readers_page()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create(['vaitro_id' => $role->id]);
        session(['user_id' => $user->id, 'role' => 'Thủ thư']);

        $response = $this->get('/readers');
        $response->assertStatus(200);
        $response->assertViewIs('readers');
    }
}
