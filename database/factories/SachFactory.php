<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TacGia;
use App\Models\NhaXuatBan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sach>
 */
class SachFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'MaSach' => 'S' . $this->faker->unique()->numberBetween(1000, 9999),
            'TenSach' => $this->faker->sentence(3),
            'MaTacGia' => TacGia::factory(),
            'MaNhaXuatBan' => NhaXuatBan::factory(),
            'NamXuatBan' => $this->faker->year,
            'NgayNhap' => $this->faker->date(),
            'TriGia' => $this->faker->numberBetween(10000, 200000),
            'TinhTrang' => 1,
        ];
    }
}
