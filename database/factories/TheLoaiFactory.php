<?php

namespace Database\Factories;

use App\Models\TheLoai;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TheLoai>
 */
class TheLoaiFactory extends Factory
{
    protected $model = TheLoai::class;

    public function definition(): array
    {
        return [
            'TenTheLoai' => $this->faker->unique()->word(),
        ];
    }
}
