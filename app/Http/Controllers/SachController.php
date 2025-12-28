<?php

namespace App\Http\Controllers;

use App\Models\CuonSach;
use App\Models\Sach;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SachController extends Controller
{
    private function generateMaPhieuNhanSach(): string
    {
        $prefix = 'PNS';
        $year = date('Y');

        $latest = DB::table('PHIEUNHANSACH')
            ->where('MaPhieuNhanSach', 'like', $prefix . $year . '-%')
            ->orderBy('MaPhieuNhanSach', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest && isset($latest->MaPhieuNhanSach)) {
            $lastNumber = (int)substr((string)$latest->MaPhieuNhanSach, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . $year . '-' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function mapTinhTrangIntToText(?int $v): string
    {
        return match ($v) {
            CuonSach::TINH_TRANG_CO_SAN => 'Có sẵn',
            CuonSach::TINH_TRANG_DANG_MUON => 'Đã cho mượn',
            CuonSach::TINH_TRANG_HONG => 'Hỏng',
            CuonSach::TINH_TRANG_BI_MAT => 'Mất',
            default => 'Không xác định',
        };
    }

    private function mapTinhTrangTextToInt(string $v): ?int
    {
        $v = trim(mb_strtolower($v));
        return match ($v) {
            'có sẵn', 'co san', 'sẵn có', 'san co' => CuonSach::TINH_TRANG_CO_SAN,       // 1
            'đã cho mượn', 'da cho muon', 'đang được mượn', 'dang duoc muon' => CuonSach::TINH_TRANG_DANG_MUON,  // 0
            'hỏng', 'hong' => CuonSach::TINH_TRANG_HONG,                                 // 2
            'mất', 'mat', 'bị mất', 'bi mat' => CuonSach::TINH_TRANG_BI_MAT,             // 3
            default => null,
        };
    }

    public function index(Request $request)
    {
        $q = DB::table('CUONSACH as cs')
            ->join('SACH as s', 's.MaSach', '=', 'cs.MaSach')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->leftJoin('THELOAI as tl', 'tl.MaTheLoai', '=', 'ds.MaTheLoai')
            ->leftJoin('CT_TACGIA as ctg', 'ctg.MaDauSach', '=', 'ds.MaDauSach')
            ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'ctg.MaTacGia')
            ->select([
                'cs.MaCuonSach',
                'cs.MaSach',
                'cs.TinhTrang',
                'ds.TenDauSach',
                'tl.TenTheLoai',
                DB::raw('GROUP_CONCAT(DISTINCT tg.TenTacGia) as TenTacGia'),
            ])
            ->groupBy('cs.MaCuonSach', 'cs.MaSach', 'cs.TinhTrang', 'ds.TenDauSach', 'tl.TenTheLoai')
            ->orderByDesc('cs.MaCuonSach');

        $search = trim((string)$request->get('search', ''));
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('cs.MaCuonSach', 'like', '%' . $search . '%')
                    ->orWhere('cs.MaSach', 'like', '%' . $search . '%')
                    ->orWhere('ds.TenDauSach', 'like', '%' . $search . '%')
                    ->orWhere('tg.TenTacGia', 'like', '%' . $search . '%');
            });
        }

        $tinhTrangText = trim((string)$request->get('tinhtrang', ''));
        if ($tinhTrangText !== '') {
            $tinhTrang = $this->mapTinhTrangTextToInt($tinhTrangText);
            if ($tinhTrang !== null) {
                $q->where('cs.TinhTrang', '=', $tinhTrang);
            }
        }

        $rows = $q->paginate(20)->withQueryString();

        $mapped = collect($rows->items())->map(function ($r) {
            $r->TinhTrang = $this->mapTinhTrangIntToText(isset($r->TinhTrang) ? (int)$r->TinhTrang : null);
            return $r;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $mapped,
                'meta' => [
                    'current_page' => $rows->currentPage(),
                    'last_page' => $rows->lastPage(),
                    'per_page' => $rows->perPage(),
                    'total' => $rows->total(),
                ],
            ]);
        }

        return view('books', ['danhSachSach' => $mapped]);
    }

    public function updateTinhTrangCuonSach(Request $request, int $maCuonSach)
    {
        \Log::info('Update status called', [
            'maCuonSach' => $maCuonSach,
            'tinhTrang' => $request->input('TinhTrang'),
            'is_json' => $request->expectsJson()
        ]);

        $request->validate([
            'TinhTrang' => 'required|string',
        ]);

        $status = $this->mapTinhTrangTextToInt((string)$request->input('TinhTrang'));
        if ($status === null) {
            \Log::error('Invalid status', ['tinhTrang' => $request->input('TinhTrang')]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Tình trạng không hợp lệ'], 422);
            }
            return back()->with('error', 'Tình trạng không hợp lệ');
        }

        $exists = DB::table('CUONSACH')->where('MaCuonSach', $maCuonSach)->exists();
        if (!$exists) {
            \Log::error('Record not found', ['maCuonSach' => $maCuonSach]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Không tìm thấy cuốn sách'], 404);
            }
            return back()->with('error', 'Không tìm thấy cuốn sách');
        }

        try {
            \Log::info('Updating status', ['maCuonSach' => $maCuonSach, 'status' => $status]);
            $updated = DB::table('CUONSACH')->where('MaCuonSach', $maCuonSach)->update([
                'TinhTrang' => $status,
            ]);

            \Log::info('Update result', ['affected_rows' => $updated]);

            if ($updated === 0) {
                \Log::warning('No rows updated', ['maCuonSach' => $maCuonSach]);
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Không thể cập nhật tình trạng'], 400);
                }
                return back()->with('error', 'Không thể cập nhật tình trạng');
            }

            \Log::info('Status updated successfully', ['maCuonSach' => $maCuonSach, 'status' => $status]);
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Đã cập nhật tình trạng'], 200);
            }
            return back()->with('success', 'Đã cập nhật tình trạng');
        } catch (\Throwable $e) {
            \Log::error('Update failed', ['maCuonSach' => $maCuonSach, 'error' => $e->getMessage()]);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Lỗi: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

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
            'NgayNhap' => 'required|date',
        ]);

        $qty = (int)$request->SoLuong;
        $ngayNhap = Carbon::parse($request->NgayNhap);

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
                DB::table('CUONSACH')->insert([
                    'MaSach' => $sach->MaSach,
                    'NgayNhap' => $ngayNhap->toDateString(),
                ]);
            }

            DB::table('PHIEUNHANSACH')->insert([
                'MaPhieuNhanSach' => $this->generateMaPhieuNhanSach(),
                'MaSach' => $sach->MaSach,
                'SoLuong' => $qty,
                'NgayNhap' => $ngayNhap->toDateString(),
            ]);

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
