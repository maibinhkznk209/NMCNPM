<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PhieuMuon;
use App\Models\CT_PHIEUMUON;
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

        $borrowRecords = [

            [
                'MaDocGia' => 1,
                'MaSach' => 1,
                'NgayMuon' => Carbon::now()->subDays(5),
                'NgayTra' => null,
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => null,
            ],
            

            [
                'MaDocGia' => 2,
                'MaSach' => 2,
                'NgayMuon' => Carbon::now()->subDays(25),
                'NgayTra' => Carbon::now()->subDays(2),
                'TienPhat' => 11000,
                'TienDenBu' => 0,
                'TinhTrangSach' => 1,
            ],
            

            [
                'MaDocGia' => 3,
                'MaSach' => 3,
                'NgayMuon' => Carbon::now()->subDays(14),
                'NgayTra' => Carbon::now()->subDays(1),
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => 1,
            ],
            

            [
                'MaDocGia' => 1,
                'MaSach' => 4,
                'NgayMuon' => Carbon::now()->subDays(20),
                'NgayTra' => Carbon::now()->subDays(3),
                'TienPhat' => 7000,
                'TienDenBu' => 50000,
                'TinhTrangSach' => 3,
            ],
            

            [
                'MaDocGia' => 2,
                'MaSach' => 5,
                'NgayMuon' => Carbon::now()->subDays(30),
                'NgayTra' => Carbon::now()->subDays(5),
                'TienPhat' => 16000,
                'TienDenBu' => 120000,
                'TinhTrangSach' => 4,
            ],
            

            [
                'MaDocGia' => 3,
                'MaSach' => 1,
                'NgayMuon' => Carbon::now()->subDays(20),
                'NgayTra' => null,
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => null,
            ],
            

            [
                'MaDocGia' => 1,
                'MaSach' => 2,
                'NgayMuon' => Carbon::now()->subDays(10),
                'NgayTra' => Carbon::now()->subDays(5),
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => 1,
            ],
            

            [
                'MaDocGia' => 2,
                'MaSach' => 3,
                'NgayMuon' => Carbon::now()->subDays(18),
                'NgayTra' => Carbon::now()->subDays(1),
                'TienPhat' => 4000,
                'TienDenBu' => 0,
                'TinhTrangSach' => 1,
            ],
            

            [
                'MaDocGia' => 3,
                'MaSach' => 4,
                'NgayMuon' => Carbon::now()->subDays(15),
                'NgayTra' => Carbon::now()->subDays(2),
                'TienPhat' => 0,
                'TienDenBu' => 25000,
                'TinhTrangSach' => 3,
            ],
            

            [
                'MaDocGia' => 1,
                'MaSach' => 5,
                'NgayMuon' => Carbon::now()->subDays(8),
                'NgayTra' => null,
                'TienPhat' => 0,
                'TienDenBu' => 0,
                'TinhTrangSach' => null,
            ],
        ];

        foreach ($borrowRecords as $record) {
            // Check if reader and book exist
            $reader = DocGia::find($record['MaDocGia']);
            $book = Sach::find($record['MaSach']);
            
            if ($reader && $book) {

                $phieuMuon = new PhieuMuon([
                    'MaDocGia' => $record['MaDocGia'],
                    'NgayMuon' => $record['NgayMuon'],
                    'NgayHenTra' => Carbon::parse($record['NgayMuon'])->addDays(14),
                ]);
                $phieuMuon->MaPhieuMuon = $phieuMuon->generateMaPhieu();
                $phieuMuon->save();


                CT_PHIEUMUON::create([
                    'MaPhieuMuon' => $phieuMuon->id,
                    'MaSach' => $record['MaSach'],
                    'NgayTra' => $record['NgayTra'],
                    'TienPhat' => $record['TienPhat'],
                    'TienDenBu' => $record['TienDenBu'],
                ]);
                

                if ($record['NgayTra']) {

                    $book->TinhTrang = $record['TinhTrangSach'];
                    $book->save();
                } else {

                    $book->TinhTrang = 0;
                    $book->save();
                }
            }
        }
    }
}
