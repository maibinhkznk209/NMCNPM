<?php

namespace Database\Factories;

use App\Models\PhieuThuTienPhat;
use App\Models\DocGia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhieuThuTienPhat>
 */
class PhieuThuTienPhatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'MaPhieu' => 'PTP' . date('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'docgia_id' => DocGia::factory(),
            'SoTienNop' => $this->faker->randomFloat(2, 10000, 500000),
            'NgayThu' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the payment is for a specific amount.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'SoTienNop' => $amount,
        ]);
    }

    /**
     * Indicate that the payment is for a specific reader.
     */
    public function forReader(DocGia $docGia): static
    {
        return $this->state(fn (array $attributes) => [
            'docgia_id' => $docGia->id,
        ]);
    }

    /**
     * Indicate that the payment is recent.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'NgayThu' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the payment is for this month.
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'NgayThu' => $this->faker->dateTimeBetween(
                now()->startOfMonth(),
                now()->endOfMonth()
            ),
        ]);
    }
} 