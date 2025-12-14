<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_show_login_form()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('login');
    }

    /** @test */
    public function can_login_with_valid_credentials()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'Email' => 'test@example.com',
            'MatKhau' => Hash::make('password123'),
            'vaitro_id' => $role->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('user_id', $user->id);
        $response->assertSessionHas('username', $user->HoVaTen);
        $response->assertSessionHas('email', $user->Email);
        $response->assertSessionHas('role', 'Thủ thư');
    }

    /** @test */
    public function cannot_login_with_invalid_email()
    {
        $response = $this->post('/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function cannot_login_with_missing_fields()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /** @test */
    public function cannot_login_with_wrong_password()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'Email' => 'test@example.com',
            'MatKhau' => Hash::make('password123'),
            'vaitro_id' => $role->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionMissing('user_id');
    }

    /** @test */
    public function cannot_login_with_nonexistent_email()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionMissing('user_id');
    }

    /** @test */
    public function can_logout_successfully()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'vaitro_id' => $role->id,
        ]);
        
        // Đăng nhập trước
        session(['user_id' => $user->id, 'username' => $user->HoVaTen, 'role' => 'Thủ thư']);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionMissing('user_id');
        $response->assertSessionMissing('username');
        $response->assertSessionMissing('role');
    }

    /** @test */
    public function can_check_auth_status_when_logged_in()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Admin']);
        $user = TaiKhoan::factory()->create([
            'vaitro_id' => $role->id,
        ]);
        
        session(['user_id' => $user->id, 'username' => $user->HoVaTen, 'role' => 'Admin']);

        $response = $this->getJson('/api/check-auth');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'authenticated' => true,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->HoVaTen,
                        'role' => 'Admin',
                    ]
                ]);
    }

    /** @test */
    public function can_check_auth_status_when_not_logged_in()
    {
        $response = $this->getJson('/api/check-auth');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'authenticated' => false,
                    'user' => null
                ]);
    }

    /** @test */
    public function can_get_current_user_when_logged_in()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'vaitro_id' => $role->id,
        ]);
        
        session(['user_id' => $user->id, 'username' => $user->HoVaTen, 'role' => 'Thủ thư']);

        $response = $this->getJson('/api/current-user');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'user' => [
                        'id' => $user->id,
                        'username' => $user->HoVaTen,
                        'role' => 'Thủ thư',
                    ]
                ]);
    }

    /** @test */
    public function cannot_get_current_user_when_not_logged_in()
    {
        $response = $this->getJson('/api/current-user');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Chưa đăng nhập'
                ]);
    }

    /** @test */
    public function redirects_to_home_if_already_logged_in()
    {
        $role = VaiTro::factory()->create(['VaiTro' => 'Thủ thư']);
        $user = TaiKhoan::factory()->create([
            'vaitro_id' => $role->id,
        ]);
        
        session(['user_id' => $user->id]);

        $response = $this->get('/login');

        $response->assertRedirect('/');
    }
}
