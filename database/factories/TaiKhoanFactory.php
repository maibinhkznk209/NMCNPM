<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\VaiTro;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaiKhoan>
 */
class TaiKhoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'HoVaTen' => $this->faker->name,
            'Email' => $this->faker->unique()->safeEmail,
            'MatKhau' => Hash::make('password'),
            'vaitro_id' => VaiTro::factory(),
        ];
    }
}
