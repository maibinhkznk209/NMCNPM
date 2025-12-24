<?php

namespace App\Http\Controllers;

use App\Models\CuonSach;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuonSachController extends Controller
{
    /**
     * BM6 - Tra cứu cuốn sách (CUONSACH join SACH/DAUSACH/...)
     */
    public function index(Request $request)
    {
        $driver = DB::getDriverName();
        $authorAgg = $driver === 'mysql'
            ? "GROUP_CONCAT(DISTINCT tg.TenTacGia ORDER BY tg.TenTacGia SEPARATOR ', ')"
            : "GROUP_CONCAT(DISTINCT tg.TenTacGia)";

        $q = DB::table('CUONSACH as cs')
            ->join('SACH as s', 's.MaSach', '=', 'cs.MaSach')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->join('THELOAI as tl', 'tl.MaTheLoai', '=', 'ds.MaTheLoai')
            ->leftJoin('NHAXUATBAN as nxb', 'nxb.MaNXB', '=', 's.MaNXB')
            ->leftJoin('CT_TACGIA as cttg', 'cttg.MaDauSach', '=', 'ds.MaDauSach')
            ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'cttg.MaTacGia')
            ->select([
                'cs.MaCuonSach',
                'cs.MaSach',
                'cs.NgayNhap',
                'cs.TinhTrang',
                's.MaDauSach',
                's.MaNXB',
                's.NamXuatBan',
                's.TriGia',
                's.SoLuong',
                'ds.TenDauSach',
                'ds.MaTheLoai',
                'tl.TenTheLoai',
                'nxb.TenNXB',
                DB::raw("$authorAgg as TenTacGia"),
            ])
            ->groupBy([
                'cs.MaCuonSach', 'cs.MaSach', 'cs.NgayNhap', 'cs.TinhTrang',
                's.MaDauSach', 's.MaNXB', 's.NamXuatBan', 's.TriGia', 's.SoLuong',
                'ds.TenDauSach', 'ds.MaTheLoai',
                'tl.TenTheLoai',
                'nxb.TenNXB',
            ]);

        if ($kw = trim((string)$request->get('q', ''))) {
            $q->where('ds.TenDauSach', 'LIKE', '%' . $kw . '%');
        }

        if ($maSach = $request->get('MaSach')) {
            $q->where('cs.MaSach', '=', $maSach);
        }

        if (!is_null($request->get('TinhTrang'))) {
            $q->where('cs.TinhTrang', '=', (int)$request->get('TinhTrang'));
        }

        $items = $q->orderByDesc('cs.MaCuonSach')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($items);
        }

        return view('cuonsach.index', ['items' => $items]);
    }

 
    public function store(Request $request)
    {
        $request->validate([
            'MaSach' => 'required|exists:SACH,MaSach',
            'SoLuong' => 'required|integer|min:1|max:1000',
        ]);

        $maSach = $request->MaSach;
        $qty = (int)$request->SoLuong;
        $now = Carbon::now();

        DB::beginTransaction();
        try {
            for ($i = 0; $i < $qty; $i++) {
                CuonSach::create([
                    'MaSach' => $maSach,
                    'NgayNhap' => $now,
                    'TinhTrang' => CuonSach::TINH_TRANG_CO_SAN,
                ]);
            }

            DB::table('SACH')->where('MaSach', $maSach)->increment('SoLuong', $qty);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'MaSach' => $maSach,
                    'SoLuongThem' => $qty,
                ], 201);
            }

            return redirect()->back()->with('success', 'Đã thêm ' . $qty . ' cuốn');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * BM10 - Cập nhật tình trạng cuốn sách
     */
    public function updateTinhTrang(Request $request, $maCuonSach)
    {
        $request->validate([
            'TinhTrang' => 'required|integer|in:0,1,2,3'
        ], [
            'TinhTrang.required' => 'Vui lòng chọn tình trạng',
            'TinhTrang.in' => 'Tình trạng không hợp lệ'
        ]);

        $cuonSach = CuonSach::where('MaCuonSach', $maCuonSach)->firstOrFail();
        $cuonSach->TinhTrang = (int)$request->TinhTrang;
        $cuonSach->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'MaCuonSach' => $cuonSach->MaCuonSach,
                'TinhTrang' => $cuonSach->TinhTrang,
            ]);
        }

        return redirect()->back()->with('success', 'Đã cập nhật tình trạng cuốn sách');
    }
}
