<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IntakeController extends Controller
{
    public function index(Request $request)
    {
        $theLoais = DB::table('THELOAI')->orderBy('TenTheLoai')->get();
        $tacGias = DB::table('TACGIA')->orderBy('TenTacGia')->get();
        $dauSachs = DB::table('DAUSACH')->orderByDesc('MaDauSach')->get();
        $nhaXuatBans = DB::table('NHAXUATBAN')->orderBy('TenNXB')->get();

        return view('intake', [
            'theLoais' => $theLoais,
            'tacGias' => $tacGias,
            'dauSachs' => $dauSachs,
            'nhaXuatBans' => $nhaXuatBans,
        ]);
    }
}
