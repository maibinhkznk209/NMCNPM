<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoaiDocGia;
use App\Models\TacGia;
use App\Models\TheLoai;
use App\Models\NhaXuatBan;
use App\Models\QuyDinh;
use App\Models\DocGia;
use App\Models\Sach;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    private const READER_TYPES = [
        'Sinh viên',
        'Giảng viên',
        'Cán bộ nhân viên',
        'Độc giả bên ngoài',
        'Học sinh',
        'Nghiên cứu sinh',
    ];

    private const AUTHORS = [
        'Nguyễn Du',
        'Dale Carnegie',
        'Paulo Coelho',
        'Stephen Hawking',
        'Tô Hoài',
        'Nam Cao',
        'Vũ Trọng Phụng',
        'Thạch Lam',
        'Nguyễn Tuân',
        'Hồ Chí Minh',
    ];

    private const GENRES = [
        'Văn học',
        'Khoa học',
        'Thiếu nhi',
        'Kỹ năng sống',
        'Lịch sử',
        'Kinh tế',
        'Công nghệ',
        'Y học',
        'Nghệ thuật',
        'Du lịch',
    ];

    private const PUBLISHERS = [
        'NXB Trẻ',
        'NXB Kim Đồng',
        'NXB Văn học',
        'NXB Khoa học tự nhiên và Công nghệ',
        'NXB Giáo dục',
        'NXB Chính trị Quốc gia',
        'NXB Thông tin và Truyền thông',
        'NXB Lao động',
        'NXB Thanh niên',
        'NXB Phụ nữ',
    ];

    private const REGULATIONS = [
        ['TenThamSo' => 'TuoiToiThieu', 'GiaTri' => '18'],
        ['TenThamSo' => 'TuoiToiDa', 'GiaTri' => '55'],
        ['TenThamSo' => 'ThoiHanThe', 'GiaTri' => '6'],
        ['TenThamSo' => 'SoSachToiDa', 'GiaTri' => '5'],
        ['TenThamSo' => 'NgayMuonToiDa', 'GiaTri' => '14'],
        ['TenThamSo' => 'SoNamXuatBan', 'GiaTri' => '8'],
    ];

    private const READERS = [
        [
            'HoTen' => 'Nguyễn Văn An',
            'LoaiDocGia' => 'Sinh viên',
            'NgaySinh' => '2000-05-15',
            'DiaChi' => 'Hà Nội',
            'Email' => 'an@email.com',
            'NgayLapThe' => '2024-01-15',
            'NgayHetHan' => '2024-07-15',
            'TongNo' => 15000,
        ],
        [
            'HoTen' => 'Trần Thị Bình',
            'LoaiDocGia' => 'Sinh viên',
            'NgaySinh' => '2001-08-20',
            'DiaChi' => 'TP.HCM',
            'Email' => 'binh@email.com',
            'NgayLapThe' => '2024-02-10',
            'NgayHetHan' => '2024-08-10',
            'TongNo' => 25000,
        ],
        [
            'HoTen' => 'Lê Minh Châu',
            'LoaiDocGia' => 'Giảng viên',
            'NgaySinh' => '1985-12-10',
            'DiaChi' => 'Đà Nẵng',
            'Email' => 'chau@email.com',
            'NgayLapThe' => '2024-03-05',
            'NgayHetHan' => '2024-09-05',
            'TongNo' => 0,
        ],
        [
            'HoTen' => 'Phạm Văn Dũng',
            'LoaiDocGia' => 'Sinh viên',
            'NgaySinh' => '2002-03-25',
            'DiaChi' => 'Hải Phòng',
            'Email' => 'dung@email.com',
            'NgayLapThe' => '2024-04-01',
            'NgayHetHan' => '2024-10-01',
            'TongNo' => 5000,
        ],
        [
            'HoTen' => 'Hoàng Thị Em',
            'LoaiDocGia' => 'Cán bộ nhân viên',
            'NgaySinh' => '1990-07-18',
            'DiaChi' => 'Cần Thơ',
            'Email' => 'em@email.com',
            'NgayLapThe' => '2024-05-12',
            'NgayHetHan' => '2024-11-12',
            'TongNo' => 0,
        ],
    ];

    private const BOOKS = [
        [
            'MaSach' => 'S001',
            'TenSach' => 'Đắc nhân tâm',
            'TacGia' => 'Dale Carnegie',
            'NhaXuatBan' => 'NXB Trẻ',
            'NamXuatBan' => 2020,
            'NgayNhap' => '2024-01-15',
            'TriGia' => 89000,
            'TinhTrang' => 1,
            'TheLoai' => ['Kỹ năng sống'],
        ],
        [
            'MaSach' => 'S002',
            'TenSach' => 'Nhà giả kim',
            'TacGia' => 'Paulo Coelho',
            'NhaXuatBan' => 'NXB Trẻ',
            'NamXuatBan' => 2019,
            'NgayNhap' => '2024-01-20',
            'TriGia' => 79000,
            'TinhTrang' => 1,
            'TheLoai' => ['Văn học'],
        ],
        [
            'MaSach' => 'S003',
            'TenSach' => 'Vũ trụ trong vỏ hạt dẻ',
            'TacGia' => 'Stephen Hawking',
            'NhaXuatBan' => 'NXB Khoa học tự nhiên và Công nghệ',
            'NamXuatBan' => 2018,
            'NgayNhap' => '2024-02-01',
            'TriGia' => 120000,
            'TinhTrang' => 1,
            'TheLoai' => ['Khoa học'],
        ],
        [
            'MaSach' => 'S004',
            'TenSach' => 'Dế Mèn phiêu lưu ký',
            'TacGia' => 'Tô Hoài',
            'NhaXuatBan' => 'NXB Kim Đồng',
            'NamXuatBan' => 2021,
            'NgayNhap' => '2024-02-10',
            'TriGia' => 65000,
            'TinhTrang' => 1,
            'TheLoai' => ['Thiếu nhi'],
        ],
        [
            'MaSach' => 'S005',
            'TenSach' => 'Truyện Kiều',
            'TacGia' => 'Nguyễn Du',
            'NhaXuatBan' => 'NXB Văn học',
            'NamXuatBan' => 2020,
            'NgayNhap' => '2024-02-15',
            'TriGia' => 55000,
            'TinhTrang' => 1,
            'TheLoai' => ['Văn học'],
        ],
        [
            'MaSach' => 'S006',
            'TenSach' => 'Chí Phèo',
            'TacGia' => 'Nam Cao',
            'NhaXuatBan' => 'NXB Văn học',
            'NamXuatBan' => 2021,
            'NgayNhap' => '2024-03-01',
            'TriGia' => 45000,
            'TinhTrang' => 1,
            'TheLoai' => ['Văn học'],
        ],
        [
            'MaSach' => 'S007',
            'TenSach' => 'Số đỏ',
            'TacGia' => 'Vũ Trọng Phụng',
            'NhaXuatBan' => 'NXB Văn học',
            'NamXuatBan' => 2020,
            'NgayNhap' => '2024-03-05',
            'TriGia' => 68000,
            'TinhTrang' => 1,
            'TheLoai' => ['Văn học'],
        ],
        [
            'MaSach' => 'S008',
            'TenSach' => 'Hai đứa trẻ',
            'TacGia' => 'Thạch Lam',
            'NhaXuatBan' => 'NXB Văn học',
            'NamXuatBan' => 2021,
            'NgayNhap' => '2024-03-10',
            'TriGia' => 35000,
            'TinhTrang' => 1,
            'TheLoai' => ['Văn học'],
        ],
        [
            'MaSach' => 'S009',
            'TenSach' => 'Vang bóng một thời',
            'TacGia' => 'Nguyễn Tuân',
            'NhaXuatBan' => 'NXB Văn học',
            'NamXuatBan' => 2020,
            'NgayNhap' => '2024-03-15',
            'TriGia' => 75000,
            'TinhTrang' => 1,
            'TheLoai' => ['Văn học'],
        ],
        [
            'MaSach' => 'S010',
            'TenSach' => 'Nhật ký trong tù',
            'TacGia' => 'Hồ Chí Minh',
            'NhaXuatBan' => 'NXB Giáo dục',
            'NamXuatBan' => 2021,
            'NgayNhap' => '2024-03-20',
            'TriGia' => 42000,
            'TinhTrang' => 1,
            'TheLoai' => ['Lịch sử', 'Văn học'],
        ],
    ];

    public function run(): void
    {
        echo "Bắt đầu tạo dữ liệu mẫu...\n";

        $this->createReaderTypes();
        $this->createAuthors();
        $this->createGenres();
        $this->createPublishers();
        $this->createRegulations();
        $this->createReaders();
        $this->createBooks();
        $this->createBorrowRecords();
        $this->createFineRecords();

        echo "Hoàn thành tạo dữ liệu mẫu!\n";
    }

    private function createReaderTypes(): void
    {
        echo "Tạo loại độc giả...\n";

        foreach (self::READER_TYPES as $type) {
            LoaiDocGia::firstOrCreate(['TenLoaiDocGia' => $type]);
        }
    }

    private function createAuthors(): void
    {
        echo "Tạo tác giả...\n";

        foreach (self::AUTHORS as $author) {
            TacGia::firstOrCreate(['TenTacGia' => $author]);
        }
    }

    private function createGenres(): void
    {
        echo "Tạo thể loại...\n";

        foreach (self::GENRES as $genre) {
            TheLoai::firstOrCreate(['TenTheLoai' => $genre]);
        }
    }

    private function createPublishers(): void
    {
        echo "Tạo nhà xuất bản...\n";

        foreach (self::PUBLISHERS as $publisher) {
            NhaXuatBan::firstOrCreate(['TenNXB' => $publisher]);
        }
    }

    private function createRegulations(): void
    {
        echo "Tạo tham số...\n";

        foreach (self::REGULATIONS as $parameter) {
            QuyDinh::updateOrCreate(
                ['TenThamSo' => $parameter['TenThamSo']],
                ['GiaTri' => $parameter['GiaTri']]
            );
        }
    }

    private function createReaders(): void
    {
        echo "Tạo độc giả...\n";

        $readerTypes = LoaiDocGia::pluck('id', 'TenLoaiDocGia');

        foreach (self::READERS as $reader) {
            if (!$readerTypes->has($reader['LoaiDocGia'])) {
                continue;
            }

            DocGia::updateOrCreate(
                ['Email' => $reader['Email']],
                [
                    'HoTen' => $reader['HoTen'],
                    'loaidocgia_id' => $readerTypes[$reader['LoaiDocGia']],
                    'NgaySinh' => Carbon::parse($reader['NgaySinh']),
                    'DiaChi' => $reader['DiaChi'],
                    'NgayLapThe' => Carbon::parse($reader['NgayLapThe']),
                    'NgayHetHan' => Carbon::parse($reader['NgayHetHan']),
                    'TongNo' => $reader['TongNo'],
                ]
            );
        }
    }

    private function createBooks(): void
    {
        echo "Tạo sách...\n";

        $authorIds = TacGia::pluck('id', 'TenTacGia');
        $publisherIds = NhaXuatBan::pluck('id', 'TenNXB');
        $genreIds = TheLoai::pluck('id', 'TenTheLoai');

        foreach (self::BOOKS as $book) {
            if (!$authorIds->has($book['TacGia']) || !$publisherIds->has($book['NhaXuatBan'])) {
                continue;
            }

            $sach = Sach::query()->updateOrCreate(
                ['MaSach' => $book['MaSach']],
                [
                    'TenSach' => $book['TenSach'],
                    'MaTacGia' => $authorIds[$book['TacGia']],
                    'MaNhaXuatBan' => $publisherIds[$book['NhaXuatBan']],
                    'NamXuatBan' => $book['NamXuatBan'],
                    'NgayNhap' => Carbon::parse($book['NgayNhap']),
                    'TriGia' => $book['TriGia'],
                    'TinhTrang' => $book['TinhTrang'],
                ]
            );

            $attachedGenres = collect($book['TheLoai'] ?? [])
                ->map(fn (string $name) => $genreIds[$name] ?? null)
                ->filter()
                ->all();

            if (!empty($attachedGenres)) {
                $sach->theLoais()->syncWithoutDetaching($attachedGenres);
            }
        }
    }

    private function createBorrowRecords(): void
    {
        echo "Tạo phiếu mượn...\n";

        $this->call(PhieuMuonSeeder::class);
    }

    private function createFineRecords(): void
    {
        echo "Tạo phiếu thu tiền phạt...\n";

        $this->call(FineRecordsSeeder::class);
    }
}
