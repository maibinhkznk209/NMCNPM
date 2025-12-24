<?php

namespace Database\Factories;

use App\Models\DauSach;
use App\Models\NhaXuatBan;
use App\Models\Sach;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sach> */
class SachFactory extends Factory
{
    protected $model = Sach::class;

    public function definition(): array
    {
        return [
            'MaDauSach' => DauSach::factory(),
            'MaNXB' => NhaXuatBan::factory(),
            'NamXuatBan' => $this->faker->numberBetween(1990, (int) date('Y')),
            'TriGia' => $this->faker->numberBetween(10000, 200000),
            'SoLuong' => $this->faker->numberBetween(1, 10),
        ];
    }
}
