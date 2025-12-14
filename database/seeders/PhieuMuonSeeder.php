<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhieuMuon;
use App\Models\ChiTietPhieuMuon;
use App\Models\DocGia;
use App\Models\Sach;
use Carbon\Carbon;

class PhieuMuonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample borrow records với các trường hợp khác nhau
        $borrowRecords = [
            // 1. Đang mượn - chưa trả
            [
                'docgia_id' => 1,
                'sach_id' => 1,
                'NgayMuon' => Carbon::now()->subDays(5),
                'NgayTra' => null,
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => null, // Chưa trả
            ],
            
            // 2. Trễ hạn - đã trả
            [
                'docgia_id' => 2,
                'sach_id' => 2,
                'NgayMuon' => Carbon::now()->subDays(25),
                'NgayTra' => Carbon::now()->subDays(2),
                'TienPhat' => 11000, // 11 ngày trễ * 1000đ
                'TienDenBu' => 0,
                'TinhTrangSach' => 1, // Có sẵn
            ],
            
            // 3. Đúng hạn - đã trả
            [
                'docgia_id' => 3,
                'sach_id' => 3,
                'NgayMuon' => Carbon::now()->subDays(14),
                'NgayTra' => Carbon::now()->subDays(1),
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => 1, // Có sẵn
            ],
            
            // 4. Làm hỏng sách - đã trả
            [
                'docgia_id' => 1,
                'sach_id' => 4,
                'NgayMuon' => Carbon::now()->subDays(20),
                'NgayTra' => Carbon::now()->subDays(3),
                'TienPhat' => 7000, // 7 ngày trễ
                'TienDenBu' => 50000, // Tiền đền bù sách hỏng
                'TinhTrangSach' => 3, // Hỏng
            ],
            
            // 5. Mất sách - đã trả
            [
                'docgia_id' => 2,
                'sach_id' => 5,
                'NgayMuon' => Carbon::now()->subDays(30),
                'NgayTra' => Carbon::now()->subDays(5),
                'TienPhat' => 16000, // 16 ngày trễ
                'TienDenBu' => 120000, // Tiền đền bù sách mất
                'TinhTrangSach' => 4, // Bị mất
            ],
            
            // 6. Quá hạn - chưa trả
            [
                'docgia_id' => 3,
                'sach_id' => 1,
                'NgayMuon' => Carbon::now()->subDays(20),
                'NgayTra' => null,
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => null, // Chưa trả
            ],
            
            // 7. Trả sớm - đã trả
            [
                'docgia_id' => 1,
                'sach_id' => 2,
                'NgayMuon' => Carbon::now()->subDays(10),
                'NgayTra' => Carbon::now()->subDays(5),
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => 1, // Có sẵn
            ],
            
            // 8. Trễ hạn nhẹ - đã trả
            [
                'docgia_id' => 2,
                'sach_id' => 3,
                'NgayMuon' => Carbon::now()->subDays(18),
                'NgayTra' => Carbon::now()->subDays(1),
                'TienPhat' => 4000, // 4 ngày trễ
                'TienDenBu' => 0,
                'TinhTrangSach' => 1, // Có sẵn
            ],
            
            // 9. Làm hỏng nhẹ - đã trả
            [
                'docgia_id' => 3,
                'sach_id' => 4,
                'NgayMuon' => Carbon::now()->subDays(15),
                'NgayTra' => Carbon::now()->subDays(2),
                'TienPhat' => 0,
                'TienDenBu' => 25000, // Tiền đền bù sách hỏng nhẹ
                'TinhTrangSach' => 3, // Hỏng
            ],
            
            // 10. Mượn nhiều sách - một số đã trả, một số chưa
            [
                'docgia_id' => 1,
                'sach_id' => 5,
                'NgayMuon' => Carbon::now()->subDays(8),
                'NgayTra' => null,
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => null, // Chưa trả
            ],
        ];

        foreach ($borrowRecords as $record) {
            // Check if reader and book exist
            $reader = DocGia::find($record['docgia_id']);
            $book = Sach::find($record['sach_id']);
            
            if ($reader && $book) {
                // Tạo phiếu mượn
                $phieuMuon = new PhieuMuon([
                    'docgia_id' => $record['docgia_id'],
                    'NgayMuon' => $record['NgayMuon'],
                    'NgayHenTra' => Carbon::parse($record['NgayMuon'])->addDays(14), // 14 ngày mượn
                ]);
                $phieuMuon->MaPhieu = $phieuMuon->generateMaPhieu();
                $phieuMuon->save();

                // Tạo chi tiết phiếu mượn
                ChiTietPhieuMuon::create([
                    'phieumuon_id' => $phieuMuon->id,
                    'sach_id' => $record['sach_id'],
                    'NgayTra' => $record['NgayTra'],
                    'TienPhat' => $record['TienPhat'],
                    'TienDenBu' => $record['TienDenBu'],
                ]);
                
                // Cập nhật trạng thái sách
                if ($record['NgayTra']) {
                    // Đã trả - cập nhật trạng thái sách
                    $book->TinhTrang = $record['TinhTrangSach'];
                    $book->save();
                } else {
                    // Chưa trả - đánh dấu đang mượn
                    $book->TinhTrang = 0; // Đang mượn
                    $book->save();
                }
            }
        }
    }
}
