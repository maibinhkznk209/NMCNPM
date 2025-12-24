<?php

namespace Database\Factories;

use App\Models\NhaXuatBan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NhaXuatBan>
 */
class NhaXuatBanFactory extends Factory
{
    protected $model = NhaXuatBan::class;

    public function definition(): array
    {
        return [
            'TenNXB' => $this->faker->company(),
        ];
    }
}
