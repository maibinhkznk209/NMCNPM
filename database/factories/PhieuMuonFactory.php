<?php

namespace Database\Factories;

use App\Models\PhieuMuon;
use App\Models\DocGia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PhieuMuonFactory extends Factory
{
    protected $model = PhieuMuon::class;

    public function definition()
    {
        return [
            'MaPhieu' => PhieuMuon::generateMaPhieu(),
            'docgia_id' => DocGia::factory(),
            'NgayMuon' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'NgayHenTra' => function (array $attributes) {
                return Carbon::parse($attributes['NgayMuon'])->addDays(14);
            },
        ];
    }
} 