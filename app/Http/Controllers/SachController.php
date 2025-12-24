<?php

namespace App\Http\Controllers;

use App\Models\CuonSach;
use App\Models\Sach;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SachController extends Controller
{
    public function index(Request $request)
    {
        $q = DB::table('SACH as s')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->leftJoin('NHAXUATBAN as nxb', 'nxb.MaNXB', '=', 's.MaNXB')
            ->select([
                's.MaSach',
                's.MaDauSach',
                's.MaNXB',
                's.NamXuatBan',
                's.TriGia',
                's.SoLuong',
                'ds.TenDauSach',
                'nxb.TenNXB',
            ]);

        if ($maDauSach = $request->get('MaDauSach')) {
            $q->where('s.MaDauSach', '=', $maDauSach);
        }

        if ($maNXB = $request->get('MaNXB')) {
            $q->where('s.MaNXB', '=', $maNXB);
        }

        if ($nam = $request->get('NamXuatBan')) {
            $q->where('s.NamXuatBan', '=', (int)$nam);
        }

        $items = $q->orderByDesc('s.MaSach')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($items);
        }

        return view('sach.index', ['items' => $items]);
    }

    /**
     * BM5 - Tiếp nhận sách mới theo ấn bản (SACH) và sinh CUONSACH theo SoLuong
     */
    public function store(Request $request)
    {
        $currentYear = (int)date('Y');
        $bookPublicationYears = $this->getBookPublicationYears();
        $minPublicationYear = $currentYear - $bookPublicationYears;

        $request->validate([
            'MaDauSach' => 'required|exists:DAUSACH,MaDauSach',
            'MaNXB' => 'required|integer|exists:NHAXUATBAN,MaNXB',
            'NamXuatBan' => ['required', 'integer', 'min:' . $minPublicationYear, 'max:' . $currentYear],
            'TriGia' => 'required|numeric|min:0|max:999999999.99',
            'SoLuong' => 'required|integer|min:1|max:1000',
        ]);

        $now = Carbon::now();
        $qty = (int)$request->SoLuong;

        DB::beginTransaction();
        try {
            $sach = Sach::create([
                'MaDauSach' => $request->MaDauSach,
                'MaNXB' => (int)$request->MaNXB,
                'NamXuatBan' => (int)$request->NamXuatBan,
                'TriGia' => $request->TriGia,
                'SoLuong' => $qty,
            ]);

            for ($i = 0; $i < $qty; $i++) {
                CuonSach::create([
                    'MaSach' => $sach->MaSach,
                    'NgayNhap' => $now,
                    'TinhTrang' => CuonSach::TINH_TRANG_CO_SAN,
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'MaSach' => $sach->MaSach,
                    'SoLuong' => $qty,
                ], 201);
            }

            return redirect()->back()->with('success', 'Đã nhập ' . $qty . ' cuốn');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function getBookPublicationYears(): int
    {
        $default = 8;

        try {
            $row = DB::table('THAMSO')
                ->where('TenThamSo', 'LIKE', '%NamXuatBan%')
                ->orderBy('MaThamSo')
                ->first();

            if ($row && isset($row->GiaTri) && is_numeric($row->GiaTri)) {
                return (int)$row->GiaTri;
            }
        } catch (\Throwable $e) {
        }

        return $default;
    }
}
