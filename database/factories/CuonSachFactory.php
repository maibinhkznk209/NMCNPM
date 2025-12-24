<?php

namespace Database\Factories;

use App\Models\CuonSach;
use App\Models\Sach;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CuonSach> */
class CuonSachFactory extends Factory
{
    protected $model = CuonSach::class;

    public function definition(): array
    {
        return [
            'MaSach' => Sach::factory(),
            'NgayNhap' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'TinhTrang' => $this->faker->randomElement(['Còn', 'Đang mượn', 'Hư hỏng', 'Mất']),
        ];
    }
}
