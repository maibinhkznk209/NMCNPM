<?php

namespace Database\Factories;

use App\Models\CT_PHIEUMUON;
use App\Models\PhieuMuon;
use App\Models\Sach;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class CT_PHIEUMUONFactory extends Factory
{
    protected $model = CT_PHIEUMUON::class;

    public function definition()
    {
        return [
            'MaPhieuMuon' => PhieuMuon::factory(),
            'MaSach' => Sach::factory(),
            'NgayTra' => null,
            'TienPhat' => 0,
        ];
    }

    public function returned()
    {
        return $this->state(function (array $attributes) {
            return [
                'NgayTra' => Carbon::now(),
                'TienPhat' => $this->faker->numberBetween(0, 50000),
            ];
        });
    }


} 