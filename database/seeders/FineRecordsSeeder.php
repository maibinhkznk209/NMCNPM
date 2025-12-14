<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhieuThuTienPhat;
use App\Models\DocGia;
use App\Models\ChiTietPhieuMuon;
use Carbon\Carbon;

class FineRecordsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo dữ liệu test cho phiếu thu tiền phạt
        $fineRecords = [
            // 1. Thu tiền phạt trễ hạn
            [
                'docgia_id' => 2,
                'SoTienNop' => 11000,
                'NgayThu' => Carbon::now()->subDays(3),
                'description' => 'Thu tiền phạt trễ hạn 11 ngày',
            ],
            
            // 2. Thu tiền phạt trễ hạn nhẹ
            [
                'docgia_id' => 2,
                'SoTienNop' => 4000,
                'NgayThu' => Carbon::now()->subDays(1),
                'description' => 'Thu tiền phạt trễ hạn 4 ngày',
            ],
            
            // 3. Thu tiền đền bù sách hỏng
            [
                'docgia_id' => 1,
                'SoTienNop' => 50000,
                'NgayThu' => Carbon::now()->subDays(2),
                'description' => 'Thu tiền đền bù sách bị hỏng',
            ],
            
            // 4. Thu tiền đền bù sách mất
            [
                'docgia_id' => 2,
                'SoTienNop' => 120000,
                'NgayThu' => Carbon::now()->subDays(4),
                'description' => 'Thu tiền đền bù sách bị mất',
            ],
            
            // 5. Thu tiền đền bù sách hỏng nhẹ
            [
                'docgia_id' => 3,
                'SoTienNop' => 25000,
                'NgayThu' => Carbon::now()->subDays(1),
                'description' => 'Thu tiền đền bù sách hỏng nhẹ',
            ],
            
            // 6. Thu tiền phạt trễ hạn + đền bù sách hỏng
            [
                'docgia_id' => 1,
                'SoTienNop' => 57000, // 7000 phạt + 50000 đền bù
                'NgayThu' => Carbon::now()->subDays(2),
                'description' => 'Thu tiền phạt trễ hạn và đền bù sách hỏng',
            ],
            
            // 7. Thu tiền phạt trễ hạn + đền bù sách mất
            [
                'docgia_id' => 2,
                'SoTienNop' => 136000, // 16000 phạt + 120000 đền bù
                'NgayThu' => Carbon::now()->subDays(4),
                'description' => 'Thu tiền phạt trễ hạn và đền bù sách mất',
            ],
            
            // 8. Thu tiền phạt một phần (còn nợ)
            [
                'docgia_id' => 3,
                'SoTienNop' => 15000,
                'NgayThu' => Carbon::now()->subDays(1),
                'description' => 'Thu tiền phạt một phần (còn nợ)',
            ],
            
            // 9. Thu tiền phạt cũ (từ tháng trước)
            [
                'docgia_id' => 1,
                'SoTienNop' => 30000,
                'NgayThu' => Carbon::now()->subDays(25),
                'description' => 'Thu tiền phạt từ tháng trước',
            ],
            
            // 10. Thu tiền phạt hôm nay
            [
                'docgia_id' => 2,
                'SoTienNop' => 8000,
                'NgayThu' => Carbon::now(),
                'description' => 'Thu tiền phạt hôm nay',
            ],
        ];

        foreach ($fineRecords as $record) {
            // Kiểm tra độc giả có tồn tại không
            $docGia = DocGia::find($record['docgia_id']);
            
            if ($docGia) {
                // Tạo phiếu thu tiền phạt
                $phieuThu = PhieuThuTienPhat::create([
                    'MaPhieu' => PhieuThuTienPhat::generateMaPhieu(),
                    'docgia_id' => $record['docgia_id'],
                    'SoTienNop' => $record['SoTienNop'],
                    'NgayThu' => $record['NgayThu'],
                ]);
                
                // Cập nhật tổng nợ của độc giả (giảm đi số tiền đã thu)
                $docGia->TongNo = max(0, $docGia->TongNo - $record['SoTienNop']);
                $docGia->save();
                
                echo "Đã tạo phiếu thu: {$phieuThu->MaPhieu} - {$record['description']}\n";
            }
        }
        
        // Tạo thêm một số phiếu thu ngẫu nhiên
        $this->createRandomFineRecords();
    }
    
    /**
     * Tạo thêm phiếu thu ngẫu nhiên
     */
    private function createRandomFineRecords()
    {
        $docGias = DocGia::all();
        $fineAmounts = [5000, 10000, 15000, 20000, 25000, 30000, 35000, 40000, 45000, 50000];
        
        for ($i = 0; $i < 5; $i++) {
            $docGia = $docGias->random();
            $amount = $fineAmounts[array_rand($fineAmounts)];
            $daysAgo = rand(1, 30);
            
            PhieuThuTienPhat::create([
                'MaPhieu' => PhieuThuTienPhat::generateMaPhieu(),
                'docgia_id' => $docGia->id,
                'SoTienNop' => $amount,
                'NgayThu' => Carbon::now()->subDays($daysAgo),
            ]);
            
            // Cập nhật tổng nợ
            $docGia->TongNo = max(0, $docGia->TongNo - $amount);
            $docGia->save();
        }
    }
}
