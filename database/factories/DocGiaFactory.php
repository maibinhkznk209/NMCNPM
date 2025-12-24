<?php

namespace Database\Factories;

use App\Models\DocGia;
use App\Models\LoaiDocGia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DocGia>
 */
class DocGiaFactory extends Factory
{
    protected $model = DocGia::class;

    public function definition(): array
    {
        $ngayLapThe = $this->faker->dateTimeBetween('-1 year', 'now');
        $loai = LoaiDocGia::factory()->create();

        // Mặc định thẻ có hạn 6 tháng (các bài test có thể ghi đè bằng ThamSo).
        $ngayHetHan = (clone $ngayLapThe)->modify('+180 days');

        return [
            'MaDocGia' => strtoupper($this->faker->bothify('DG######')),
            'TenDocGia' => $this->faker->name(),
            'MaLoaiDocGia' => LoaiDocGia::factory(),
            'NgaySinh' => $this->faker->date('Y-m-d', '-18 years'),
            'DiaChi' => $this->faker->address(),
            'Email' => $this->faker->unique()->safeEmail(),
            'NgayLapThe' => $ngayLapThe->format('Y-m-d'),
            'NgayHetHan' => $ngayHetHan->format('Y-m-d'),
            'TongNo' => 0,
        ];
    }
}
