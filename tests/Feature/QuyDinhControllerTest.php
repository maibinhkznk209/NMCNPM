<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\QuyDinh;
use App\Models\User;
use App\Models\VaiTro;
use App\Models\TaiKhoan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuyDinhControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $thuThu;
    protected $quyDinh;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo vai trò Admin và Thủ thư
        $adminRole = VaiTro::create(['VaiTro' => 'Admin']);
        $thuThuRole = VaiTro::create(['VaiTro' => 'Thủ thư']);

        // Tạo tài khoản Admin
        $this->admin = TaiKhoan::create([
            'HoVaTen' => 'Admin User',
            'Email' => 'admin@example.com',
            'MatKhau' => bcrypt('password'),
            'vaitro_id' => $adminRole->id
        ]);

        // Tạo tài khoản Thủ thư
        $this->thuThu = TaiKhoan::create([
            'HoVaTen' => 'Thủ thư User',
            'Email' => 'thuthu@example.com',
            'MatKhau' => bcrypt('password'),
            'vaitro_id' => $thuThuRole->id
        ]);

        // Tạo quy định mẫu
        $this->quyDinh = QuyDinh::create([
            'TenThamSo' => 'TuoiToiThieu',
            'GiaTri' => '18'
        ]);

        // Đăng nhập với Admin (mô phỏng session)
        session([
            'user_id' => $this->admin->id,
            'username' => $this->admin->HoVaTen,
            'email' => $this->admin->Email,
            'role' => 'Admin',
            'role_id' => $this->admin->vaitro_id,
        ]);
    }

    /** @test */
    public function admin_can_view_regulations_list()
    {
        $response = $this->getJson('/api/regulations');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        [
                            'id' => $this->quyDinh->id,
                            'TenThamSo' => 'TuoiToiThieu',
                            'GiaTri' => '18'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function admin_can_view_specific_regulation()
    {
        $response = $this->getJson("/api/regulations/{$this->quyDinh->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->quyDinh->id,
                        'TenThamSo' => 'TuoiToiThieu',
                        'GiaTri' => '18'
                    ]
                ]);
    }

    /** @test */
    public function admin_cannot_view_nonexistent_regulation()
    {
        $response = $this->getJson('/api/regulations/999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Không tìm thấy quy định'
                ]);
    }

    /** @test */
    public function admin_can_update_regulation_with_valid_data()
    {
        $response = $this->putJson("/api/regulations/{$this->quyDinh->id}", [
            'GiaTri' => '20'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Cập nhật quy định thành công',
                    'data' => [
                        'id' => $this->quyDinh->id,
                        'TenThamSo' => 'TuoiToiThieu',
                        'GiaTri' => '20'
                    ]
                ]);

        $this->assertDatabaseHas('THAMSO', [
            'id' => $this->quyDinh->id,
            'TenThamSo' => 'TuoiToiThieu',
            'GiaTri' => '20'
        ]);
    }

    /** @test */
    public function admin_cannot_update_regulation_without_value()
    {
        $response = $this->putJson("/api/regulations/{$this->quyDinh->id}", []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['GiaTri']);
    }

    /** @test */
    public function admin_cannot_update_regulation_with_non_numeric_value()
    {
        $response = $this->putJson("/api/regulations/{$this->quyDinh->id}", [
            'GiaTri' => 'abc'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['GiaTri']);
    }

    /** @test */
    public function admin_cannot_update_regulation_with_negative_value()
    {
        $response = $this->putJson("/api/regulations/{$this->quyDinh->id}", [
            'GiaTri' => '-5'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['GiaTri']);
    }

    /** @test */
    public function admin_cannot_update_age_related_regulation_with_invalid_range()
    {
        $ageRegulation = QuyDinh::create([
            'TenThamSo' => 'TuoiToiDa',
            'GiaTri' => '55'
        ]);

        $response = $this->putJson("/api/regulations/{$ageRegulation->id}", [
            'GiaTri' => '150'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['GiaTri']);
    }

    /** @test */
    public function admin_cannot_update_duration_related_regulation_with_invalid_range()
    {
        $durationRegulation = QuyDinh::create([
            'TenThamSo' => 'ThoiHanThe',
            'GiaTri' => '6'
        ]);

        $response = $this->putJson("/api/regulations/{$durationRegulation->id}", [
            'GiaTri' => '150'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['GiaTri']);
    }

    /** @test */
    public function thu_thu_cannot_access_regulations_list()
    {
        // Đăng nhập với Thủ thư
        session([
            'user_id' => $this->thuThu->id,
            'username' => $this->thuThu->HoVaTen,
            'email' => $this->thuThu->Email,
            'role' => 'Thủ thư',
            'role_id' => $this->thuThu->vaitro_id,
        ]);

        $response = $this->getJson('/api/regulations');

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_regulations()
    {
        // Xóa session để mô phỏng chưa đăng nhập
        session()->flush();

        $response = $this->getJson('/api/regulations');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_cannot_create_new_regulation()
    {
        $response = $this->postJson('/api/regulations', [
            'TenThamSo' => 'NewRegulation',
            'GiaTri' => '10'
        ]);

        $response->assertStatus(405); // Method Not Allowed
    }

    /** @test */
    public function admin_cannot_delete_regulation()
    {
        $response = $this->deleteJson("/api/regulations/{$this->quyDinh->id}");

        $response->assertStatus(405); // Method Not Allowed
    }
} 