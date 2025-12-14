<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VaiTro;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Hash;

class VaiTroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed vai trò mặc định
        $roles = [
            ['VaiTro' => 'Admin'],
            ['VaiTro' => 'Thủ thư'],
        ];

        foreach ($roles as $role) {
            VaiTro::firstOrCreate(['VaiTro' => $role['VaiTro']], $role);
        }

        // Tạo tài khoản admin mặc định
        $adminRole = VaiTro::where('VaiTro', 'Admin')->first();
        if ($adminRole) {
            TaiKhoan::firstOrCreate(
                ['Email' => 'admin@library.com'],
                [
                    'HoVaTen' => 'Mai Thái Bình',
                    'Email' => 'admin@library.com',
                    'MatKhau' => Hash::make('123456'),
                    'vaitro_id' => $adminRole->id,
                ]
            );
        }

        // Tạo tài khoản thủ thư mặc định
        $librarianRole = VaiTro::where('VaiTro', 'Thủ thư')->first();
        if ($librarianRole) {
            TaiKhoan::firstOrCreate(
                ['Email' => 'librarian@library.com'],
                [
                    'HoVaTen' => 'Vũ Việt Hoàng',
                    'Email' => 'librarian@library.com',
                    'MatKhau' => Hash::make('123456'),
                    'vaitro_id' => $librarianRole->id,
                ]
            );
        }
    }
}
