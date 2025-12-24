<?php

namespace Database\Factories;

use App\Models\DauSach;
use App\Models\TheLoai;
use App\Models\TacGia;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DauSach> */
class DauSachFactory extends Factory
{
    protected $model = DauSach::class;

    public function definition(): array
    {
        return [
            'TenDauSach' => $this->faker->sentence(3),
            'MaTheLoai' => TheLoai::factory(),
            'NgayNhap' => $this->faker->date(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (DauSach $dauSach) {
            $tacGias = TacGia::factory()->count($this->faker->numberBetween(1, 2))->create();
            $dauSach->tacGias()->sync($tacGias->pluck('MaTacGia')->all());
        });
    }
}
