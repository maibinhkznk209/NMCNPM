<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\LoaiDocGia;

class LoaiDocGiaFactory extends Factory
{
    protected $model = LoaiDocGia::class;

    public function definition(): array
    {
        return [
            'MaLoaiDocGia' => $this->faker->unique()->bothify('LDG###'),
            'TenLoaiDocGia' => $this->faker->randomElement(['Sinh viên', 'Người lớn', 'Trẻ em']),
        ];
    }
}
