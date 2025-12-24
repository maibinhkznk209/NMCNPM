<?php

namespace Tests\Feature;

use App\Models\CuonSach;
use App\Models\DauSach;
use App\Models\NhaXuatBan;
use App\Models\Sach;
use App\Models\TacGia;
use App\Models\TheLoai;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $books;
    protected $genres;
    protected $authors;
    protected $publishers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestData();
    }

    private function createTestData()
    {
        $this->genres = collect([
            TheLoai::factory()->create(['TenTheLoai' => 'Văn học']),
            TheLoai::factory()->create(['TenTheLoai' => 'Khoa học']),
            TheLoai::factory()->create(['TenTheLoai' => 'Lịch sử']),
        ]);

        $this->authors = collect([
            TacGia::factory()->create(['TenTacGia' => 'Nguyễn Du']),
            TacGia::factory()->create(['TenTacGia' => 'Albert Einstein']),
        ]);

        $this->publishers = collect([
            NhaXuatBan::factory()->create(['TenNXB' => 'NXB Văn học']),
            NhaXuatBan::factory()->create(['TenNXB' => 'NXB Khoa học']),
        ]);

        $this->books = collect([
            $this->createBook(
                'Truyện Kiều',
                $this->genres[0],
                [$this->authors[0]],
                $this->publishers[0],
                2020,
                50000,
                5,
                CuonSach::TINH_TRANG_CO_SAN
            ),
            $this->createBook(
                'Thuyết tương đối',
                $this->genres[1],
                [$this->authors[1]],
                $this->publishers[1],
                2021,
                75000,
                3,
                CuonSach::TINH_TRANG_DANG_MUON
            ),
            $this->createBook(
                'Lịch sử Việt Nam',
                $this->genres[2],
                [$this->authors[0]],
                $this->publishers[0],
                2019,
                60000,
                2,
                CuonSach::TINH_TRANG_HONG
            ),
        ]);
    }

    private function createBook(
        string $tenDauSach,
        TheLoai $theLoai,
        array $tacGias,
        NhaXuatBan $nxb,
        int $namXuatBan,
        int $triGia,
        int $soLuong,
        int $tinhTrangCuon
    ): Sach {
        $dauSach = DauSach::factory()->create([
            'TenDauSach' => $tenDauSach,
            'MaTheLoai' => $theLoai->MaTheLoai,
            'NgayNhap' => Carbon::now(),
        ]);

        $dauSach->DS_TG()->attach(collect($tacGias)->pluck('MaTacGia')->toArray());

        $sach = Sach::factory()->create([
            'MaDauSach' => $dauSach->MaDauSach,
            'MaNXB' => $nxb->MaNXB,
            'NamXuatBan' => $namXuatBan,
            'TriGia' => $triGia,
            'SoLuong' => $soLuong,
        ]);

        // CuonSach::create() trong codebase đang unset TinhTrang, nên set sau khi tạo
        $cuon = CuonSach::create([
            'MaSach' => $sach->MaSach,
            'NgayNhap' => Carbon::now(),
        ]);
        $cuon->TinhTrang = $tinhTrangCuon;
        $cuon->save();

        return $sach;
    }

    /** @test */
    public function can_access_home_page_without_login()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHas(['books', 'genres', 'authors', 'publishers']);
    }

    /** @test */
    public function can_search_books_by_title()
    {
        $response = $this->get('/?search=Truyện');

        $response->assertStatus(200);
        $response->assertSee('Truyện Kiều');
    }

    /** @test */
    public function can_search_books_by_author()
    {
        $response = $this->get('/?search=Einstein');

        $response->assertStatus(200);
        $response->assertSee('Thuyết tương đối');
    }

    /** @test */
    public function can_search_books_by_genre()
    {
        $response = $this->get('/?search=Khoa');

        $response->assertStatus(200);
        $response->assertSee('Thuyết tương đối');
    }

    /** @test */
    public function can_filter_books_by_genre()
    {
        $genreId = $this->genres[0]->MaTheLoai;
        $response = $this->get("/?genre={$genreId}");

        $response->assertStatus(200);
        $response->assertSee('Truyện Kiều');
        $response->assertDontSee('Thuyết tương đối');
    }

    /** @test */
    public function can_filter_books_by_author()
    {
        $authorId = $this->authors[1]->MaTacGia;
        $response = $this->get("/?author={$authorId}");

        $response->assertStatus(200);
        $response->assertSee('Thuyết tương đối');
        $response->assertDontSee('Truyện Kiều');
    }

    /** @test */
    public function can_filter_books_by_publisher()
    {
        $publisherId = $this->publishers[0]->MaNXB;
        $response = $this->get("/?publisher={$publisherId}");

        $response->assertStatus(200);
        $response->assertSee('Truyện Kiều');
        $response->assertSee('Lịch sử Việt Nam');
        $response->assertDontSee('Thuyết tương đối');
    }

    /** @test */
    public function can_filter_books_by_status()
    {
        $response = $this->get('/?status=' . CuonSach::TINH_TRANG_DANG_MUON);

        $response->assertStatus(200);
        $response->assertSee('Thuyết tương đối');
        $response->assertDontSee('Truyện Kiều');
    }

    /** @test */
    public function can_sort_books_by_year_desc()
    {
        $response = $this->get('/?sort=NamXuatBan&order=desc');
        $response->assertStatus(200);
    }

    /** @test */
    public function can_sort_books_by_price_asc()
    {
        $response = $this->get('/?sort=TriGia&order=asc');
        $response->assertStatus(200);
    }

    /** @test */
    public function can_handle_invalid_sort_parameters()
    {
        $response = $this->get('/?sort=invalid&order=invalid');
        $response->assertStatus(200);
    }

    /** @test */
    public function can_handle_empty_search()
    {
        $response = $this->get('/?search=');

        $response->assertStatus(200);
        $response->assertSee('Truyện Kiều');
        $response->assertSee('Thuyết tương đối');
    }
}
