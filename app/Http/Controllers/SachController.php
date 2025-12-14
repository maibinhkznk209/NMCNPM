<?php

namespace App\Http\Controllers;

use App\Models\Sach;
use App\Models\CuonSach;
use App\Models\DauSach;
use App\Models\TacGia;
use App\Models\TheLoai;
use App\Models\NhaXuatBan;
use App\Models\QuyDinh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SachController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Tra cứu CUỐN SÁCH (thay cho tra cứu SÁCH)
            $query = CuonSach::query()
                ->select('CUONSACH.*')
                ->join('SACH', 'CUONSACH.MaSach', '=', 'SACH.MaSach')
                ->join('DAUSACH', 'SACH.MaDauSach', '=', 'DAUSACH.MaDauSach')
                ->leftJoin('CT_TACGIA', 'DAUSACH.MaDauSach', '=', 'CT_TACGIA.MaDauSach')
                ->leftJoin('TACGIA', 'CT_TACGIA.MaTacGia', '=', 'TACGIA.id')
                // Quan hệ theo đúng tên trong Models
                ->with(['CS_S.S_NXB']);

            // Tìm kiếm theo: Mã cuốn, mã sách, tên đầu sách, tên tác giả
            if ($request->filled('search')) {
                $searchTerm = trim($request->search);

                $query->where(function ($q) use ($searchTerm) {
                    $q->where('CUONSACH.MaCuonSach', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('CUONSACH.MaSach', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('DAUSACH.TenDauSach', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('TACGIA.TenTacGia', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Filter theo tình trạng cuốn sách
            if ($request->filled('tinh_trang')) {
                $query->where('CUONSACH.TinhTrang', (int)$request->tinh_trang);
            }

            // Filter theo thể loại (1 đầu sách thuộc 1 thể loại)
            if ($request->filled('ma_the_loai')) {
                $query->where('DAUSACH.MaTheLoai', (int)$request->ma_the_loai);
            }

            // Filter theo NXB
            if ($request->filled('ma_nxb')) {
                $query->where('SACH.MaNXB', (int)$request->ma_nxb);
            }

            // Tránh duplicate do join N-N tác giả
            $query->distinct('CUONSACH.MaCuonSach');

            // Phân trang
            $danhSachCuonSach = $query->paginate(10)->withQueryString();
        } catch (\Exception $e) {
            $danhSachCuonSach = collect([]);
        }

        // Thống kê (theo cuốn sách)
        try {
            $totalCopies = CuonSach::count();
            $totalTitles = DauSach::count();
            $totalAuthors = TacGia::count();
            $totalProblemCopies = CuonSach::whereIn('TinhTrang', [CuonSach::TINH_TRANG_HONG, CuonSach::TINH_TRANG_BI_MAT])->count();
        } catch (\Exception $e) {
            $totalCopies = 0;
            $totalTitles = 0;
            $totalAuthors = 0;
            $totalProblemCopies = 0;
        }

        // Danh sách cho filter
        try {
            $tacGias = TacGia::all();
            $theLoais = TheLoai::all();
            $nhaXuatBans = NhaXuatBan::all();
        } catch (\Exception $e) {
            $tacGias = collect([]);
            $theLoais = collect([]);
            $nhaXuatBans = collect([]);
        }

        // Giữ tên biến cũ để hạn chế ảnh hưởng view hiện tại (nếu view vẫn dùng $danhSachSach)
        $danhSachSach = $danhSachCuonSach;

        return view('books', compact(
            'danhSachSach',
            'totalCopies',
            'totalTitles',
            'totalAuthors',
            'totalProblemCopies',
            'tacGias',
            'theLoais',
            'nhaXuatBans'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentYear = (int)date('Y');
        $bookPublicationYears = QuyDinh::getBookPublicationYears();
        $minPublicationYear = $currentYear - (int)$bookPublicationYears;

        // tacGias: cho phép gửi 1 id hoặc mảng id
        $tacGiaInput = $request->input('tacGias');
        if (!is_array($tacGiaInput) && $tacGiaInput !== null) {
            $request->merge(['tacGias' => [$tacGiaInput]]);
        }

        $request->validate([
            'TenSach' => 'required|string|max:255',
            'NamXuatBan' => 'required|integer|min:' . $minPublicationYear . '|max:' . $currentYear,
            'TriGia' => 'required|numeric|min:0|max:999999999.99',
            'SoLuong' => 'required|integer|min:1|max:1000',

            // 1 đầu sách thuộc 1 thể loại
            'theLoais' => 'required|integer|exists:THELOAI,MaTheLoai',

            // 1 đầu sách có nhiều tác giả
            'tacGias' => 'required|array|min:1',
            'tacGias.*' => 'integer|exists:TACGIA,id',

            // NXB theo PK MaNXB
            'nhaXuatBans' => 'required|integer|exists:NHAXUATBAN,MaNXB',
        ]);

        DB::beginTransaction();
        try {
            // 1) Tạo ĐẦU SÁCH
            $dauSach = DauSach::create([
                'TenDauSach' => $request->TenSach,
                'MaTheLoai'  => (int)$request->theLoais,
                'NgayNhap'   => Carbon::now()->format('Y-m-d'),
            ]);

            // 2) Gán tác giả cho đầu sách (N-N)
            $dauSach->DS_TG()->sync($request->tacGias);

            // 3) Tạo SÁCH (ấn bản)
            $maSach = $this->generateMaSach();
            $sach = Sach::create([
                'MaSach'     => $maSach,
                'MaDauSach'  => $dauSach->MaDauSach,
                'MaNXB'      => (int)$request->nhaXuatBans,
                'NamXuatBan' => (int)$request->NamXuatBan,
                'TriGia'     => $request->TriGia,
                'SoLuong'    => (int)$request->SoLuong,
            ]);

            // 4) Tạo các CUỐN SÁCH tương ứng với số lượng
            for ($i = 0; $i < (int)$request->SoLuong; $i++) {
                CuonSach::create([
                    'MaSach'    => $sach->MaSach,
                    'NgayNhap'  => Carbon::now()->format('Y-m-d'),
                    'TinhTrang' => CuonSach::TINH_TRANG_CO_SAN,
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm sách thành công',
                    'data' => [
                        'sach' => $sach,
                        'dauSach' => $dauSach,
                    ]
                ]);
            }

            return redirect()->route('books.index')->with('success', 'Thêm sách thành công');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi thêm sách: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('books.index')->with('error', 'Có lỗi xảy ra khi thêm sách: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique book code
     */
    private function generateMaSach()
    {
        $prefix = 'S';
        $year = date('Y');
        
        // Lấy số thứ tự cao nhất trong năm hiện tại
        $latestBook = Sach::where('MaSach', 'LIKE', $prefix . $year . '%')
                         ->orderBy('MaSach', 'desc')
                         ->first();
        
        if ($latestBook) {
            // Lấy số thứ tự từ mã sách cuối cùng
            $lastNumber = (int)substr($latestBook->MaSach, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: S2025-0001, S2025-0002, ...
        return $prefix . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $id ở đây là MaSach
        $sach = Sach::with(['S_NXB'])->findOrFail($id);
        $dauSach = DauSach::with(['DS_TG', 'DS_TL'])->findOrFail($sach->MaDauSach);

        return response()->json([
            'MaSach' => $sach->MaSach,
            'TenSach' => $dauSach->TenDauSach,
            'NamXuatBan' => $sach->NamXuatBan,
            'TriGia' => $sach->TriGia,
            'SoLuong' => $sach->SoLuong,

            // 1 thể loại
            'theLoais' => $dauSach->MaTheLoai,

            // nhiều tác giả
            'tacGias' => $dauSach->DS_TG->pluck('id')->toArray(),

            // NXB
            'nhaXuatBans' => $sach->MaNXB,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $currentYear = (int)date('Y');
        $bookPublicationYears = QuyDinh::getBookPublicationYears();
        $minPublicationYear = $currentYear - (int)$bookPublicationYears;

        $tacGiaInput = $request->input('tacGias');
        if (!is_array($tacGiaInput) && $tacGiaInput !== null) {
            $request->merge(['tacGias' => [$tacGiaInput]]);
        }

        $request->validate([
            'TenSach' => 'required|string|max:255',
            'NamXuatBan' => 'required|integer|min:' . $minPublicationYear . '|max:' . $currentYear,
            'TriGia' => 'required|numeric|min:0|max:999999999.99',
            'SoLuong' => 'required|integer|min:1|max:1000',

            'theLoais' => 'required|integer|exists:THELOAI,MaTheLoai',

            'tacGias' => 'required|array|min:1',
            'tacGias.*' => 'integer|exists:TACGIA,id',

            'nhaXuatBans' => 'required|integer|exists:NHAXUATBAN,MaNXB',
        ]);

        DB::beginTransaction();
        try {
            $sach = Sach::findOrFail($id);
            $dauSach = DauSach::findOrFail($sach->MaDauSach);

            // Cập nhật đầu sách
            $dauSach->TenDauSach = $request->TenSach;
            $dauSach->MaTheLoai  = (int)$request->theLoais;
            $dauSach->save();

            // Sync tác giả cho đầu sách
            $dauSach->DS_TG()->sync($request->tacGias);

            // Cập nhật sách (ấn bản)
            $oldSoLuong = (int)$sach->SoLuong;
            $newSoLuong = (int)$request->SoLuong;

            $sach->MaNXB      = (int)$request->nhaXuatBans;
            $sach->NamXuatBan = (int)$request->NamXuatBan;
            $sach->TriGia     = $request->TriGia;
            $sach->SoLuong    = $newSoLuong;
            $sach->save();

            // Đồng bộ số lượng CUỐN SÁCH
            $currentCopies = CuonSach::where('MaSach', $sach->MaSach)->count();

            if ($newSoLuong > $currentCopies) {
                $needAdd = $newSoLuong - $currentCopies;
                for ($i = 0; $i < $needAdd; $i++) {
                    CuonSach::create([
                        'MaSach'    => $sach->MaSach,
                        'NgayNhap'  => Carbon::now()->format('Y-m-d'),
                        'TinhTrang' => CuonSach::TINH_TRANG_CO_SAN,
                    ]);
                }
            } elseif ($newSoLuong < $currentCopies) {
                $needRemove = $currentCopies - $newSoLuong;

                // chỉ xóa các cuốn đang "Có sẵn"
                $available = CuonSach::where('MaSach', $sach->MaSach)
                    ->where('TinhTrang', CuonSach::TINH_TRANG_CO_SAN)
                    ->orderByDesc('MaCuonSach')
                    ->take($needRemove)
                    ->get();

                if ($available->count() < $needRemove) {
                    throw new \Exception('Không thể giảm số lượng vì có cuốn sách đang mượn/hỏng/mất.');
                }

                foreach ($available as $cuon) {
                    $cuon->delete();
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật sách thành công',
                ]);
            }

            return redirect()->route('books.index')->with('success', 'Cập nhật sách thành công');
        } catch (\Exception $e) {
            DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật sách: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('books.index')->with('error', 'Có lỗi xảy ra khi cập nhật sách: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();
            
            $sach = Sach::findOrFail($id);
            
            // Kiểm tra xem sách có bị hỏng hoặc mất không
            if ($sach->isDamaged()) {
                DB::rollback();
                $errorMessage = 'Không thể xóa sách đã hỏng! Sách này cần được xử lý riêng.';
                
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                
                return redirect()->route('books.index')->with('error', $errorMessage);
            }
            
            if ($sach->isLost()) {
                DB::rollback();
                $errorMessage = 'Không thể xóa sách đã mất! Sách này cần được xử lý riêng.';
                
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 400);
                }
                
                return redirect()->route('books.index')->with('error', $errorMessage);
            }
            
            // Nếu sách đang được mượn, cập nhật tình trạng về "Có sẵn" trước khi xóa
            if ($sach->isBorrowed()) {
                $sach->markAsAvailable();
            }
            
            // Kiểm tra xem sách có trong phiếu mượn nào không (kể cả đã trả)
            $hasActiveLoans = DB::table('CHITIETPHIEUMUON')
                ->where('sach_id', $id)
                ->exists();
                
            if ($hasActiveLoans) {
                DB::rollback();
                
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa sách đã từng được mượn! Sách này có lịch sử mượn trả.'
                    ], 400);
                }
                
                return redirect()->route('books.index')->with('error', 'Không thể xóa sách đã từng được mượn! Sách này có lịch sử mượn trả.');
            }
            
            // Chỉ cần xóa quan hệ many-to-many với thể loại
            $sach->theLoais()->detach();
            
            // Xóa sách (các quan hệ one-to-many tự động null)
            $sach->delete();
            
            DB::commit();
            
            $successMessage = 'Xóa sách thành công!';
            if ($sach->isBorrowed()) {
                $successMessage = 'Xóa sách thành công! Tình trạng sách đã được cập nhật về "Có sẵn" trước khi xóa.';
            }
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage
                ]);
            }
            
            return redirect()->route('books.index')->with('success', $successMessage);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi xóa sách: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('books.index')->with('error', 'Có lỗi xảy ra khi xóa sách: ' . $e->getMessage());
        }
    }


    /**
     * Cập nhật tình trạng CUỐN SÁCH
     * - Hỗ trợ cả web và API (expectsJson)
     */
    public function updateTinhTrangCuonSach(Request $request, $maCuonSach)
    {
        $request->validate([
            'TinhTrang' => 'required|integer|in:' .
                CuonSach::TINH_TRANG_DANG_MUON . ',' .
                CuonSach::TINH_TRANG_CO_SAN . ',' .
                CuonSach::TINH_TRANG_HONG . ',' .
                CuonSach::TINH_TRANG_BI_MAT,
        ]);

        try {
            $cuonSach = CuonSach::findOrFail($maCuonSach);
            $cuonSach->TinhTrang = (int)$request->TinhTrang;
            $cuonSach->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật tình trạng cuốn sách thành công',
                    'data' => $cuonSach
                ]);
            }

            return redirect()->route('books.index')->with('success', 'Cập nhật tình trạng cuốn sách thành công');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật tình trạng cuốn sách: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('books.index')->with('error', 'Có lỗi xảy ra khi cập nhật tình trạng cuốn sách: ' . $e->getMessage());
        }
    }
}
