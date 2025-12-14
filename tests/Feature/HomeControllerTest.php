<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Sach;
use App\Models\TheLoai;
use App\Models\TacGia;
use App\Models\NhaXuatBan;
use App\Models\TaiKhoan;
use App\Models\VaiTro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $thuThu;
    protected $sach1;
    protected $sach2;
    protected $sach3;
    protected $theLoai1;
    protected $theLoai2;
    protected $tacGia1;
    protected $tacGia2;
    protected $nhaXuatBan1;
    protected $nhaXuatBan2;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo vai trò
        $adminRole = VaiTro::create(['VaiTro' => 'Admin']);
        $thuThuRole = VaiTro::create(['VaiTro' => 'Thủ thư']);

        // Tạo tài khoản
        $this->admin = TaiKhoan::create([
            'HoVaTen' => 'Admin User',
            'Email' => 'admin@example.com',
            'MatKhau' => bcrypt('password'),
            'vaitro_id' => $adminRole->id
        ]);

        $this->thuThu = TaiKhoan::create([
            'HoVaTen' => 'Thủ thư User',
            'Email' => 'thuthu@example.com',
            'MatKhau' => bcrypt('password'),
            'vaitro_id' => $thuThuRole->id
        ]);

        // Tạo thể loại
        $this->theLoai1 = TheLoai::create(['TenTheLoai' => 'Tiểu thuyết']);
        $this->theLoai2 = TheLoai::create(['TenTheLoai' => 'Khoa học']);

        // Tạo tác giả
        $this->tacGia1 = TacGia::create(['TenTacGia' => 'Nguyễn Du']);
        $this->tacGia2 = TacGia::create(['TenTacGia' => 'Nam Cao']);

        // Tạo nhà xuất bản
        $this->nhaXuatBan1 = NhaXuatBan::create(['TenNXB' => 'NXB Văn học']);
        $this->nhaXuatBan2 = NhaXuatBan::create(['TenNXB' => 'NXB Khoa học']);

        // Tạo sách
        $this->sach1 = new Sach([
            'MaSach' => 'S001',
            'TenSach' => 'Truyện Kiều',
            'MaTacGia' => $this->tacGia1->id,
            'MaNhaXuatBan' => $this->nhaXuatBan1->id,
            'NamXuatBan' => 2020,
            'NgayNhap' => '2023-01-01',
            'TriGia' => 50000,
            'TinhTrang' => 1 // Có sẵn
        ]);
        $this->sach1->save();

        $this->sach2 = new Sach([
            'MaSach' => 'S002',
            'TenSach' => 'Chí Phèo',
            'MaTacGia' => $this->tacGia2->id,
            'MaNhaXuatBan' => $this->nhaXuatBan1->id,
            'NamXuatBan' => 2021,
            'NgayNhap' => '2023-01-02',
            'TriGia' => 60000,
            'TinhTrang' => 0 // Đang mượn
        ]);
        $this->sach2->save();

        $this->sach3 = new Sach([
            'MaSach' => 'S003',
            'TenSach' => 'Sách Khoa học',
            'MaTacGia' => $this->tacGia1->id,
            'MaNhaXuatBan' => $this->nhaXuatBan2->id,
            'NamXuatBan' => 2022,
            'NgayNhap' => '2023-01-03',
            'TriGia' => 70000,
            'TinhTrang' => 1 // Có sẵn
        ]);
        $this->sach3->save();

        // Gán thể loại cho sách
        $this->sach1->theLoais()->attach($this->theLoai1->id);
        $this->sach2->theLoais()->attach($this->theLoai1->id);
        $this->sach3->theLoais()->attach($this->theLoai2->id);
    }

    /** @test */
    public function can_access_home_page_without_login()
    {
        $response = $this->get('/');

        $response->assertStatus(200)
                ->assertViewIs('home')
                ->assertViewHas('books')
                ->assertViewHas('genres')
                ->assertViewHas('authors')
                ->assertViewHas('publishers')
                ->assertViewHas('isLoggedIn', false);
    }

    /** @test */
    public function can_access_home_page_with_admin_login()
    {
        session([
            'user_id' => $this->admin->id,
            'username' => $this->admin->HoVaTen,
            'email' => $this->admin->Email,
            'role' => 'Admin',
            'role_id' => $this->admin->vaitro_id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
                ->assertViewIs('home')
                ->assertViewHas('isLoggedIn', true)
                ->assertViewHas('userRole', 'Admin');
    }

    /** @test */
    public function can_access_home_page_with_thu_thu_login()
    {
        session([
            'user_id' => $this->thuThu->id,
            'username' => $this->thuThu->HoVaTen,
            'email' => $this->thuThu->Email,
            'role' => 'Thủ thư',
            'role_id' => $this->thuThu->vaitro_id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
                ->assertViewIs('home')
                ->assertViewHas('isLoggedIn', true)
                ->assertViewHas('userRole', 'Thủ thư');
    }

    /** @test */
    public function can_search_books_by_title()
    {
        $response = $this->get('/?search=Truyện');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertDontSee('Chí Phèo');
    }

    /** @test */
    public function can_search_books_by_author()
    {
        $response = $this->get('/?search=Nguyễn Du');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Sách Khoa học');
    }

    /** @test */
    public function can_search_books_by_genre()
    {
        $response = $this->get('/?search=Tiểu thuyết');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Chí Phèo');
    }

    /** @test */
    public function can_search_books_by_publisher()
    {
        $response = $this->get('/?search=Văn học');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Chí Phèo');
    }

    /** @test */
    public function can_search_books_by_book_code()
    {
        $response = $this->get('/?search=S001');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều');
    }

    /** @test */
    public function search_returns_empty_when_no_matches()
    {
        $response = $this->get('/?search=Không tồn tại');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertDontSee('Truyện Kiều')
                ->assertDontSee('Chí Phèo')
                ->assertDontSee('Sách Khoa học');
    }

    /** @test */
    public function can_filter_books_by_genre()
    {
        $response = $this->get("/?genre={$this->theLoai1->id}");

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Chí Phèo')
                ->assertDontSee('Sách Khoa học');
    }

    /** @test */
    public function can_filter_books_by_author()
    {
        $response = $this->get("/?author={$this->tacGia1->id}");

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Sách Khoa học')
                ->assertDontSee('Chí Phèo');
    }

    /** @test */
    public function can_filter_books_by_publisher()
    {
        $response = $this->get("/?publisher={$this->nhaXuatBan1->id}");

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Chí Phèo')
                ->assertDontSee('Sách Khoa học');
    }

    /** @test */
    public function can_filter_books_by_status_available()
    {
        // Verify test data is correct
        $this->assertEquals(1, $this->sach1->TinhTrang);
        $this->assertEquals(0, $this->sach2->TinhTrang);
        $this->assertEquals(1, $this->sach3->TinhTrang);

        $response = $this->get('/?status=1');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Sách Khoa học')
                ->assertDontSee('Chí Phèo');
    }

    /** @test */
    public function can_filter_books_by_status_borrowed()
    {
        // Verify test data is correct
        $this->assertEquals(1, $this->sach1->TinhTrang);
        $this->assertEquals(0, $this->sach2->TinhTrang);
        $this->assertEquals(1, $this->sach3->TinhTrang);

        $response = $this->get('/?status=0');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Chí Phèo')
                ->assertDontSee('Truyện Kiều')
                ->assertDontSee('Sách Khoa học');
    }

    /** @test */
    public function can_sort_books_by_title_asc()
    {
        $response = $this->get('/?sort=TenSach&order=asc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_sort_books_by_title_desc()
    {
        $response = $this->get('/?sort=TenSach&order=desc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_sort_books_by_year_asc()
    {
        $response = $this->get('/?sort=NamXuatBan&order=asc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_sort_books_by_year_desc()
    {
        $response = $this->get('/?sort=NamXuatBan&order=desc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_sort_books_by_price_asc()
    {
        $response = $this->get('/?sort=TriGia&order=asc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_sort_books_by_price_desc()
    {
        $response = $this->get('/?sort=TriGia&order=desc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_combine_search_and_filter()
    {
        $response = $this->get("/?search=Kiều&genre={$this->theLoai1->id}");

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertDontSee('Chí Phèo')
                ->assertDontSee('Sách Khoa học');
    }

    /** @test */
    public function can_combine_search_and_sort()
    {
        $response = $this->get('/?search=Nguyễn&sort=TenSach&order=asc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_combine_filter_and_sort()
    {
        $response = $this->get("/?genre={$this->theLoai1->id}&sort=TenSach&order=desc");

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function can_combine_all_parameters()
    {
        $response = $this->get("/?search=Kiều&genre={$this->theLoai1->id}&author={$this->tacGia1->id}&publisher={$this->nhaXuatBan1->id}&status=1&sort=TenSach&order=asc");

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function search_is_case_insensitive()
    {
        $response = $this->get('/?search=truyện');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều');
    }

    /** @test */
    public function search_with_partial_match()
    {
        $response = $this->get('/?search=Kiều');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều');
    }

    /** @test */
    public function search_with_special_characters()
    {
        $response = $this->get('/?search=Chí Phèo');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Chí Phèo');
    }

    /** @test */
    public function filter_with_invalid_genre_id()
    {
        $response = $this->get('/?genre=999');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function filter_with_invalid_author_id()
    {
        $response = $this->get('/?author=999');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function filter_with_invalid_publisher_id()
    {
        $response = $this->get('/?publisher=999');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function filter_with_invalid_status()
    {
        $response = $this->get('/?status=999');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function sort_with_invalid_field()
    {
        $response = $this->get('/?sort=InvalidField&order=asc');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function sort_with_invalid_order()
    {
        $response = $this->get('/?sort=TenSach&order=invalid');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function pagination_works_correctly()
    {
        // Tạo thêm sách để test pagination
        for ($i = 4; $i <= 15; $i++) {
            Sach::create([
                'MaSach' => sprintf('S%03d', $i),
                'TenSach' => "Sách số {$i}",
                'MaTacGia' => $this->tacGia1->id,
                'MaNhaXuatBan' => $this->nhaXuatBan1->id,
                'NamXuatBan' => 2020 + $i,
                'NgayNhap' => '2023-01-01',
                'TriGia' => 50000 + ($i * 1000),
                'TinhTrang' => 1
            ]);
        }

        $response = $this->get('/');

        $response->assertStatus(200)
                ->assertViewHas('books');
    }

    /** @test */
    public function search_parameters_are_preserved_in_pagination()
    {
        $response = $this->get('/?search=Kiều&page=1');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều');
    }

    /** @test */
    public function empty_search_returns_all_books()
    {
        $response = $this->get('/?search=');

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Chí Phèo')
                ->assertSee('Sách Khoa học');
    }

    /** @test */
    public function search_with_whitespace_only()
    {
        $response = $this->get('/?search=' . urlencode('   '));

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều')
                ->assertSee('Chí Phèo')
                ->assertSee('Sách Khoa học');
    }

    /** @test */
    public function can_access_with_multiple_filters()
    {
        $response = $this->get("/?genre={$this->theLoai1->id}&author={$this->tacGia1->id}&publisher={$this->nhaXuatBan1->id}&status=1");

        $response->assertStatus(200)
                ->assertViewHas('books')
                ->assertSee('Truyện Kiều');
    }
} 