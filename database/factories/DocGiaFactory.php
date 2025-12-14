<?php

namespace Database\Factories;

use App\Models\DocGia;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocGiaFactory extends Factory
{
    protected $model = DocGia::class;

    public function definition()
    {
        return [
            'MaDocGia' => 'DG' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'HoTen' => $this->faker->name,
            'MaLoaiDocGia' => LoaiDocGia::inRandomOrder()->first()->MaLoaiDocGia, 
            'NgaySinh' => $this->faker->date('Y-m-d', '-18 years'),
            'DiaChi' => $this->faker->address,
            'Email' => $this->faker->unique()->safeEmail,
            'NgayLapThe' => $this->faker->date('Y-m-d', '-1 year'),
            'NgayHetHan' => $this->faker->date('Y-m-d', '+1 year'),
            'TongNo' => 0,
        ];
    }
} 