<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhieuPhat;
use App\Models\DocGia;
use Carbon\Carbon;

class FineRecordsSeeder extends Seeder
{
    /**
     * Track codes generated in this seeding run to prevent duplicates
     */
    private array $usedCodes = [];

    public function run(): void
    {
        $maDocGiaList = DocGia::query()->pluck('MaDocGia')->values();

        if ($maDocGiaList->isEmpty()) {
            $this->command?->warn('DOCGIA đang rỗng, bỏ qua FineRecordsSeeder.');
            return;
        }

        $dg1 = $maDocGiaList[0];
        $dg2 = $maDocGiaList[1] ?? $dg1;
        $dg3 = $maDocGiaList[2] ?? $dg1;

        $fineRecords = [
            ['MaDocGia' => $dg2, 'SoTienNop' => 11000,  'NgayThu' => Carbon::now()->subDays(3),  'description' => 'Thu tiền phạt trễ hạn 11 ngày'],
            ['MaDocGia' => $dg2, 'SoTienNop' => 4000,   'NgayThu' => Carbon::now()->subDays(1),  'description' => 'Thu tiền phạt trễ hạn 4 ngày'],
            ['MaDocGia' => $dg1, 'SoTienNop' => 50000,  'NgayThu' => Carbon::now()->subDays(2),  'description' => 'Thu tiền đền bù sách bị hỏng'],
            ['MaDocGia' => $dg2, 'SoTienNop' => 120000, 'NgayThu' => Carbon::now()->subDays(4),  'description' => 'Thu tiền đền bù sách bị mất'],
            ['MaDocGia' => $dg3, 'SoTienNop' => 25000,  'NgayThu' => Carbon::now()->subDays(1),  'description' => 'Thu tiền đền bù sách hỏng nhẹ'],
            ['MaDocGia' => $dg1, 'SoTienNop' => 57000,  'NgayThu' => Carbon::now()->subDays(2),  'description' => 'Thu tiền phạt trễ hạn và đền bù sách hỏng'],
            ['MaDocGia' => $dg2, 'SoTienNop' => 136000, 'NgayThu' => Carbon::now()->subDays(4),  'description' => 'Thu tiền phạt trễ hạn và đền bù sách mất'],
            ['MaDocGia' => $dg3, 'SoTienNop' => 15000,  'NgayThu' => Carbon::now()->subDays(1),  'description' => 'Thu tiền phạt một phần (còn nợ)'],
            ['MaDocGia' => $dg1, 'SoTienNop' => 30000,  'NgayThu' => Carbon::now()->subDays(25), 'description' => 'Thu tiền phạt từ tháng trước'],
            ['MaDocGia' => $dg2, 'SoTienNop' => 8000,   'NgayThu' => Carbon::now(),              'description' => 'Thu tiền phạt hôm nay'],
        ];

        foreach ($fineRecords as $record) {
            $docGia = DocGia::query()->where('MaDocGia', $record['MaDocGia'])->first();

            if (!$docGia) {
                $this->command?->warn("Không tìm thấy DOCGIA.MaDocGia = {$record['MaDocGia']}, bỏ qua record.");
                continue;
            }

            $maPhieu = $this->nextUniqueMaPhieuPhat();


            $phieuThu = new PhieuPhat();
            $phieuThu->MaPhieuPhat = $maPhieu;
            $phieuThu->MaDocGia    = $docGia->MaDocGia;
            $phieuThu->SoTienNop   = $record['SoTienNop'];
            $phieuThu->NgayThu     = $record['NgayThu'];
            $phieuThu->save();

            $docGia->TongNo = max(0, (float)$docGia->TongNo - (float)$record['SoTienNop']);
            $docGia->save();

            echo "Đã tạo phiếu thu: {$phieuThu->MaPhieuPhat} - {$record['description']}\n";
        }

        $this->createRandomFineRecords();
    }

    private function createRandomFineRecords(): void
    {
        $docGias = DocGia::query()->get();

        if ($docGias->isEmpty()) {
            $this->command?->warn('DOCGIA đang rỗng, bỏ qua tạo phiếu thu ngẫu nhiên.');
            return;
        }

        $fineAmounts = [5000, 10000, 15000, 20000, 25000, 30000, 35000, 40000, 45000, 50000];

        for ($i = 0; $i < 5; $i++) {
            $docGia  = $docGias->random();
            $amount  = $fineAmounts[array_rand($fineAmounts)];
            $daysAgo = rand(1, 30);

            $maPhieu = $this->nextUniqueMaPhieuPhat();

            $phieuThu = new PhieuPhat();
            $phieuThu->MaPhieuPhat = $maPhieu;
            $phieuThu->MaDocGia    = $docGia->MaDocGia;
            $phieuThu->SoTienNop   = $amount;
            $phieuThu->NgayThu     = Carbon::now()->subDays($daysAgo);
            $phieuThu->save();

            $docGia->TongNo = max(0, (float)$docGia->TongNo - (float)$amount);
            $docGia->save();
        }
    }

    /**
     * Still uses PhieuPhat::generateMaPhieuPhat(), but guarantees uniqueness
     * inside this seeding run (and against existing DB rows).
     */
    private function nextUniqueMaPhieuPhat(): string
    {
        // 1) call your generate method (requirement)
        $code = PhieuPhat::generateMaPhieuPhat();

        // 2) if unique, accept
        if (!$this->isCodeTaken($code)) {
            $this->usedCodes[] = $code;
            return $code;
        }

        // 3) fallback: increment from the latest code we have (or from the generated code)
        $base = !empty($this->usedCodes) ? end($this->usedCodes) : $code;

        $prefix = substr($base, 0, -4);
        $num    = (int)substr($base, -4);

        do {
            $num++;
            $candidate = $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
        } while ($this->isCodeTaken($candidate));

        $this->usedCodes[] = $candidate;
        return $candidate;
    }

    private function isCodeTaken(string $code): bool
    {
        if (in_array($code, $this->usedCodes, true)) return true;

        return PhieuPhat::query()
            ->where('MaPhieuPhat', $code)
            ->exists();
    }
}
