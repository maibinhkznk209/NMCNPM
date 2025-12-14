<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TacGia;

class TacGiaSeeder extends Seeder
{
    public function run()
    {
        $tacGias = [
            ['TenTacGia' => 'Nguyễn Du'],
            ['TenTacGia' => 'Nguyễn Khuyến'],
            ['TenTacGia' => 'Hồ Chí Minh'],
            ['TenTacGia' => 'Tô Hoài'],
            ['TenTacGia' => 'Nam Cao'],
            ['TenTacGia' => 'Dale Carnegie'],
            ['TenTacGia' => 'Paulo Coelho'],
            ['TenTacGia' => 'J.K. Rowling'],
            ['TenTacGia' => 'Stephen Hawking'],
            ['TenTacGia' => 'Albert Einstein'],
        ];

        foreach ($tacGias as $tacGia) {
            TacGia::firstOrCreate($tacGia);
        }
    }
}
