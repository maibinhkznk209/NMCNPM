<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IntakeController extends Controller
{
    public function index(Request $request)
    {
        $theLoais = DB::table('THELOAI')->orderBy('TenTheLoai')->get();
        $tacGias = DB::table('TACGIA')->orderBy('TenTacGia')->get();
        $dauSachs = DB::table('DAUSACH')->orderByDesc('MaDauSach')->get();
        $nhaXuatBans = DB::table('NHAXUATBAN')->orderBy('TenNXB')->get();
        $driver = DB::getDriverName();
        $authorAgg = $driver === 'mysql'
            ? "GROUP_CONCAT(DISTINCT tg.TenTacGia ORDER BY tg.TenTacGia SEPARATOR ', ')"
            : "GROUP_CONCAT(DISTINCT tg.TenTacGia)";

        $dauSachItems = DB::table('DAUSACH as ds')
            ->join('THELOAI as tl', 'tl.MaTheLoai', '=', 'ds.MaTheLoai')
            ->leftJoin('CT_TACGIA as cttg', 'cttg.MaDauSach', '=', 'ds.MaDauSach')
            ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'cttg.MaTacGia')
            ->leftJoin('SACH as s', 's.MaDauSach', '=', 'ds.MaDauSach')
            ->select([
                'ds.MaDauSach',
                'ds.TenDauSach',
                'tl.TenTheLoai',
                DB::raw("$authorAgg as TenTacGia"),
                DB::raw('COUNT(DISTINCT s.MaSach) as SoSach'),
            ])
            ->groupBy([
                'ds.MaDauSach',
                'ds.TenDauSach',
                'tl.TenTheLoai',
            ])
            ->orderByDesc('ds.MaDauSach')
            ->get();

        $phieuNhanDauSachItems = collect();
        $phieuNhanSachItems = collect();

        if (Schema::hasTable('PHIEUNHANDAUSACH')) {
            $phieuNhanDauSachItems = DB::table('PHIEUNHANDAUSACH as p')
                ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 'p.MaDauSach')
                ->select([
                    'p.MaPhieuNhanDauSach',
                    'p.NgayNhap',
                    'ds.MaDauSach',
                    'ds.TenDauSach',
                ])
                ->orderByDesc('p.NgayNhap')
                ->orderByDesc('p.MaPhieuNhanDauSach')
                ->limit(20)
                ->get();
        }

        if (Schema::hasTable('PHIEUNHANSACH')) {
            $phieuNhanSachItems = DB::table('PHIEUNHANSACH as p')
                ->join('SACH as s', 's.MaSach', '=', 'p.MaSach')
                ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
                ->leftJoin('NHAXUATBAN as nxb', 'nxb.MaNXB', '=', 's.MaNXB')
                ->select([
                    'p.MaPhieuNhanSach',
                    'p.NgayNhap',
                    'p.SoLuong',
                    's.MaSach',
                    'ds.MaDauSach',
                    'ds.TenDauSach',
                    'nxb.TenNXB',
                    's.NamXuatBan',
                    's.TriGia',
                ])
                ->orderByDesc('p.NgayNhap')
                ->orderByDesc('p.MaPhieuNhanSach')
                ->limit(20)
                ->get();
        }


        return view('intake', [
            'theLoais' => $theLoais,
            'tacGias' => $tacGias,
            'dauSachs' => $dauSachs,
            'nhaXuatBans' => $nhaXuatBans,
            'dauSachItems' => $dauSachItems,
            'phieuNhanDauSachItems' => $phieuNhanDauSachItems,
            'phieuNhanSachItems' => $phieuNhanSachItems,
        ]);
    }
}
