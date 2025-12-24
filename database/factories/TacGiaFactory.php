<?php

namespace Database\Factories;

use App\Models\TacGia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TacGia>
 */
class TacGiaFactory extends Factory
{
    protected $model = TacGia::class;

    public function definition(): array
    {
        return [
            'TenTacGia' => $this->faker->name(),
        ];
    }
}
