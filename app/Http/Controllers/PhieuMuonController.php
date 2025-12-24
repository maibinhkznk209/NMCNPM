<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;


class PhieuMuonController extends Controller
{
    private function today(): Carbon
    {
        return Carbon::today();
    }

    private function getBorrowDurationDays(): int
    {
        // Prefer THAMSO.NgayMuonToiDa if exists, else fallback 14
        try {
            $row = DB::table('THAMSO')->where('TenThamSo', 'NgayMuonToiDa')->first();
            if ($row && isset($row->GiaTri) && is_numeric($row->GiaTri)) {
                return (int)$row->GiaTri;
            }
        } catch (Exception $e) {
            // ignore
        }
        return 14;
    }

    private function generateMaPhieuMuon(): string
    {
        $prefix = 'PM';
        $year = date('Y');

        $latest = DB::table('PHIEUMUON')
            ->where('MaPhieuMuon', 'like', $prefix . $year . '-%')
            ->orderBy('MaPhieuMuon', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest && isset($latest->MaPhieuMuon)) {
            $lastNumber = (int)substr((string)$latest->MaPhieuMuon, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . $year . '-' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function tenDauSachFromMaSach(int $maSach): ?string
    {
        $row = DB::table('SACH as s')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->where('s.MaSach', $maSach)
            ->select('ds.TenDauSach')
            ->first();

        return $row?->TenDauSach;
    }

    private function validateBorrowRequest(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'MaDocGia' => 'required|string',
            'MaSach' => 'required|array|min:1',
            'MaSach.*' => 'integer',
            'borrow_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $data = $validator->validated();

        // Normalize
        $maDocGia = (string)$data['MaDocGia'];
        $maSachArr = array_values(array_unique(array_map('intval', $data['MaSach'])));

        // Existence checks (schema-aligned)
        $docGiaExists = DB::table('DOCGIA')->where('MaDocGia', $maDocGia)->exists();
        if (!$docGiaExists) {
            throw new Exception('Độc giả không tồn tại trong hệ thống');
        }

        foreach ($maSachArr as $maSach) {
            if (!DB::table('SACH')->where('MaSach', $maSach)->exists()) {
                throw new Exception("Sách (MaSach={$maSach}) không tồn tại trong hệ thống");
            }
        }

        $borrowDate = isset($data['borrow_date']) ? Carbon::parse($data['borrow_date']) : Carbon::now();
        if ($borrowDate->isFuture()) {
            throw new Exception('Ngày mượn không thể là ngày trong tương lai');
        }

        return [
            'MaDocGia' => $maDocGia,
            'MaSach' => $maSachArr,
            'borrow_date' => $borrowDate,
        ];
    }

    private function validateReturnRequest(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'MaSach' => 'required|array|min:1',
            'MaSach.*' => 'integer',
            'return_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        $data['MaSach'] = array_values(array_unique(array_map('intval', $data['MaSach'])));
        return $data;
    }

    private function calculateFineAmount(Carbon $dueDate, Carbon $returnDate): int
    {
        if ($returnDate->lte($dueDate)) {
            return 0;
        }
        $daysLate = (int)$dueDate->diffInDays($returnDate);
        return $daysLate * 1000;
    }

    /**
     * GET /api/borrow-records
     */
    public function index(Request $request): JsonResponse
    {
        $today = $this->today();

        $rows = DB::table('PHIEUMUON as pm')
            ->join('DOCGIA as dg', 'dg.MaDocGia', '=', 'pm.MaDocGia')
            ->leftJoin('CT_PHIEUMUON as ct', 'ct.MaPhieuMuon', '=', 'pm.MaPhieuMuon')
            ->leftJoin('SACH as s', 's.MaSach', '=', 'ct.MaSach')
            ->leftJoin('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->select(
                'pm.MaPhieuMuon',
                'pm.MaDocGia',
                'dg.TenDocGia',
                'pm.NgayMuon',
                'pm.NgayHenTra',
                DB::raw('SUM(CASE WHEN ct.NgayTra IS NULL THEN 1 ELSE 0 END) as so_sach_chua_tra'),
                DB::raw('COUNT(ct.MaSach) as tong_sach'),
                DB::raw('GROUP_CONCAT(DISTINCT ds.TenDauSach) as danh_sach_ten_dau_sach')
            )
            ->groupBy('pm.MaPhieuMuon', 'pm.MaDocGia', 'dg.TenDocGia', 'pm.NgayMuon', 'pm.NgayHenTra')
            ->orderBy('pm.NgayMuon', 'desc')
            ->get();

        $data = $rows->map(function ($r) use ($today) {
            $status = 'active';
            $ngayHenTra = $r->NgayHenTra ? Carbon::parse($r->NgayHenTra) : null;

            if ((int)$r->tong_sach > 0 && (int)$r->so_sach_chua_tra === 0) {
                $status = 'returned';
            } elseif ($ngayHenTra && $ngayHenTra->lt($today)) {
                $status = 'overdue';
            }

            return [
                'MaPhieuMuon' => $r->MaPhieuMuon,
                'MaDocGia' => $r->MaDocGia,
                'TenDocGia' => $r->TenDocGia,
                'NgayMuon' => $r->NgayMuon,
                'NgayHenTra' => $r->NgayHenTra,
                'TrangThai' => $status,
                'TongSach' => (int)$r->tong_sach,
                'SoSachChuaTra' => (int)$r->so_sach_chua_tra,
                'TenDauSach' => $r->danh_sach_ten_dau_sach ? explode(',', (string)$r->danh_sach_ten_dau_sach) : [],
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }


    /**
     * GET /api/borrow-records/{MaPhieuMuon}
     */
    public function show(string $id): JsonResponse
    {
        $pm = DB::table('PHIEUMUON as pm')
            ->join('DOCGIA as dg', 'dg.MaDocGia', '=', 'pm.MaDocGia')
            ->where('pm.MaPhieuMuon', $id)
            ->select('pm.*', 'dg.TenDocGia')
            ->first();

        if (!$pm) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu mượn'], 404);
        }

        $details = DB::table('CT_PHIEUMUON as ct')
            ->join('SACH as s', 's.MaSach', '=', 'ct.MaSach')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->where('ct.MaPhieuMuon', $id)
            ->select(
                'ct.MaSach',
                'ds.TenDauSach',
                'ct.NgayTra',
                'ct.TienPhat',
                'pm.NgayHenTra'
            )
            ->addSelect(DB::raw('CASE WHEN ct.NgayTra IS NULL THEN 1 ELSE 0 END as chua_tra'))
            ->join('PHIEUMUON as pm', 'pm.MaPhieuMuon', '=', 'ct.MaPhieuMuon')
            ->orderBy('ct.MaSach')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'MaPhieuMuon' => $pm->MaPhieuMuon,
                'MaDocGia' => $pm->MaDocGia,
                'TenDocGia' => $pm->TenDocGia,
                'NgayMuon' => $pm->NgayMuon,
                'NgayHenTra' => $pm->NgayHenTra,
                'chi_tiet' => $details,
            ],
        ]);
    }

    /**
     * GET /api/borrow-records/doc-gia/{MaDocGia}
     */
    public function getByReader(string $docgiaId): JsonResponse
    {
        $exists = DB::table('DOCGIA')->where('MaDocGia', $docgiaId)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy độc giả'], 404);
        }

        $rows = DB::table('PHIEUMUON as pm')
            ->leftJoin('CT_PHIEUMUON as ct', 'ct.MaPhieuMuon', '=', 'pm.MaPhieuMuon')
            ->leftJoin('SACH as s', 's.MaSach', '=', 'ct.MaSach')
            ->leftJoin('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->where('pm.MaDocGia', $docgiaId)
            ->select(
                'pm.MaPhieuMuon',
                'pm.NgayMuon',
                'pm.NgayHenTra',
                DB::raw('SUM(CASE WHEN ct.NgayTra IS NULL THEN 1 ELSE 0 END) as so_sach_chua_tra'),
                DB::raw('COUNT(ct.MaSach) as tong_sach'),
                DB::raw('GROUP_CONCAT(DISTINCT ds.TenDauSach) as danh_sach_ten_dau_sach')
            )
            ->groupBy('pm.MaPhieuMuon', 'pm.NgayMuon', 'pm.NgayHenTra')
            ->orderBy('pm.NgayMuon', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * POST /api/borrow-records (or /api/phieu-muon depending on routes)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $this->validateBorrowRequest($request);
            $maDocGia = $validated['MaDocGia'];
            $maSachArr = $validated['MaSach'];
            $borrowDate = $validated['borrow_date'];

            $dueDate = $borrowDate->copy()->addDays($this->getBorrowDurationDays());
            $maPhieuMuon = $this->generateMaPhieuMuon();

            DB::table('PHIEUMUON')->insert([
                'MaPhieuMuon' => $maPhieuMuon,
                'MaDocGia' => $maDocGia,
                'NgayMuon' => $borrowDate->toDateString(),
                'NgayHenTra' => $dueDate->toDateString(),
            ]);

            foreach ($maSachArr as $maSach) {
                DB::table('CT_PHIEUMUON')->insert([
                    'MaPhieuMuon' => $maPhieuMuon,
                    'MaSach' => $maSach,
                    'NgayTra' => null,
                    'TienPhat' => 0,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo phiếu mượn thành công',
                'data' => ['MaPhieuMuon' => $maPhieuMuon],
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Create borrow record failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * PUT/PATCH /api/borrow-records/{MaPhieuMuon}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $pm = DB::table('PHIEUMUON')->where('MaPhieuMuon', $id)->first();
            if (!$pm) {
                throw new Exception('Không tìm thấy phiếu mượn');
            }

            $validated = $this->validateBorrowRequest($request);
            $maDocGia = $validated['MaDocGia'];
            $maSachArr = $validated['MaSach'];
            $borrowDate = $validated['borrow_date'];
            $dueDate = $borrowDate->copy()->addDays($this->getBorrowDurationDays());

            // Replace details (simple approach)
            DB::table('CT_PHIEUMUON')->where('MaPhieuMuon', $id)->delete();

            foreach ($maSachArr as $maSach) {
                DB::table('CT_PHIEUMUON')->insert([
                    'MaPhieuMuon' => $id,
                    'MaSach' => $maSach,
                    'NgayTra' => null,
                    'TienPhat' => 0,
                ]);
            }

            DB::table('PHIEUMUON')->where('MaPhieuMuon', $id)->update([
                'MaDocGia' => $maDocGia,
                'NgayMuon' => $borrowDate->toDateString(),
                'NgayHenTra' => $dueDate->toDateString(),
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Cập nhật phiếu mượn thành công']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/borrow-records/{MaPhieuMuon}/calculate-fines
     */
    public function calculateReturnFines(Request $request, string $phieuMuonId): JsonResponse
    {
        try {
            $data = $this->validateReturnRequest($request);
            $returnDate = isset($data['return_date']) ? Carbon::parse($data['return_date']) : Carbon::now();

            $pm = DB::table('PHIEUMUON')->where('MaPhieuMuon', $phieuMuonId)->first();
            if (!$pm) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu mượn'], 404);
            }

            $dueDate = Carbon::parse($pm->NgayHenTra);

            $items = [];
            $total = 0;

            foreach ($data['MaSach'] as $maSach) {
                $ten = $this->tenDauSachFromMaSach($maSach) ?? ('MaSach=' . $maSach);
                $fine = $this->calculateFineAmount($dueDate, $returnDate);
                $items[] = ['MaSach' => $maSach, 'TenDauSach' => $ten, 'TienPhat' => $fine];
                $total += $fine;
            }

            return response()->json(['success' => true, 'data' => ['items' => $items, 'total' => $total]]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/borrow-records/{MaPhieuMuon}/return
     */
    public function returnBooks(Request $request, string $phieuMuonId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $data = $this->validateReturnRequest($request);
            $returnDate = isset($data['return_date']) ? Carbon::parse($data['return_date']) : Carbon::now();

            $pm = DB::table('PHIEUMUON')->where('MaPhieuMuon', $phieuMuonId)->first();
            if (!$pm) {
                throw new Exception('Không tìm thấy phiếu mượn');
            }

            $dueDate = Carbon::parse($pm->NgayHenTra);

            foreach ($data['MaSach'] as $maSach) {
                $exists = DB::table('CT_PHIEUMUON')
                    ->where('MaPhieuMuon', $phieuMuonId)
                    ->where('MaSach', $maSach)
                    ->exists();

                if (!$exists) {
                    throw new Exception("Sách (MaSach={$maSach}) không thuộc phiếu mượn này");
                }

                $fine = $this->calculateFineAmount($dueDate, $returnDate);

                DB::table('CT_PHIEUMUON')
                    ->where('MaPhieuMuon', $phieuMuonId)
                    ->where('MaSach', $maSach)
                    ->update([
                        'NgayTra' => $returnDate->toDateString(),
                        'TienPhat' => $fine,
                    ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Trả sách thành công']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/borrow-records/{MaPhieuMuon}/extend
     */
    public function extendBorrow(Request $request, string $phieuMuonId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'extend_days' => 'required|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => implode(', ', $validator->errors()->all())], 400);
        }

        $pm = DB::table('PHIEUMUON')->where('MaPhieuMuon', $phieuMuonId)->first();
        if (!$pm) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu mượn'], 404);
        }

        $extendDays = (int)$validator->validated()['extend_days'];
        $newDue = Carbon::parse($pm->NgayHenTra)->addDays($extendDays);

        DB::table('PHIEUMUON')->where('MaPhieuMuon', $phieuMuonId)->update([
            'NgayHenTra' => $newDue->toDateString(),
        ]);

        return response()->json(['success' => true, 'message' => 'Gia hạn thành công', 'data' => ['NgayHenTra' => $newDue->toDateString()]]);
    }

    /**
     * Aliases kept for UI compatibility
     */
    public function extendAll(Request $request, string $phieuMuonId): JsonResponse
    {
        return $this->extendBorrow($request, $phieuMuonId);
    }

    public function getOverdue(): JsonResponse
    {
        $today = $this->today()->toDateString();

        $rows = DB::table('PHIEUMUON as pm')
            ->join('DOCGIA as dg', 'dg.MaDocGia', '=', 'pm.MaDocGia')
            ->where('pm.NgayHenTra', '<', $today)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('CT_PHIEUMUON as ct')
                    ->whereColumn('ct.MaPhieuMuon', 'pm.MaPhieuMuon')
                    ->whereNull('ct.NgayTra');
            })
            ->select('pm.*', 'dg.TenDocGia')
            ->orderBy('pm.NgayHenTra', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    public function destroy(string $id): JsonResponse
    {
        $exists = DB::table('PHIEUMUON')->where('MaPhieuMuon', $id)->exists();
        if (!$exists) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu mượn'], 404);
        }

        DB::table('PHIEUMUON')->where('MaPhieuMuon', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Xoá phiếu mượn thành công']);
    }

    /**
     * GET /api/readers-list
     */
    public function readersListApi(): JsonResponse
    {
        $rows = DB::table('DOCGIA')
            ->select('MaDocGia', 'TenDocGia', 'Email', 'NgayHetHan', 'TongNo')
            ->orderBy('MaDocGia')
            ->get();

        return response()->json(['success' => true, 'data' => $rows]);
    }

    /**
     * GET /api/all-readers-list
     */
    public function allReadersListApi(): JsonResponse
    {
        return $this->readersListApi();
    }

    /**
     * GET /api/books-list
     */
    public function booksListApi(): JsonResponse
    {
        $q = DB::table('SACH as s')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->leftJoin('NHAXUATBAN as nxb', 'nxb.MaNXB', '=', 's.MaNXB')
            ->select(
                's.MaSach',
                'ds.TenDauSach',
                's.NamXuatBan',
                's.TriGia',
                's.SoLuong',
                'nxb.TenNXB'
            )
            ->orderBy('s.MaSach');

        // If TinhTrang exists, prefer only available
        if (Schema::hasColumn('SACH', 'TinhTrang')) {
            $q->addSelect('s.TinhTrang');
            $q->where('s.TinhTrang', 1);
        }

        $rows = $q->get();
        return response()->json(['success' => true, 'data' => $rows]);
    }

   
    public function editBooksListApi(Request $request): JsonResponse
    {
        $pmId = $request->query('MaPhieuMuon')
            ?? $request->query('phieu_muon_id');

        if (!$pmId) {
            return response()->json([
                'success' => false,
                'message' => 'Thiếu MaPhieuMuon'
            ], 400);
        }

        $selected = DB::table('CT_PHIEUMUON')
            ->where('MaPhieuMuon', $pmId)
            ->pluck('MaSach')
            ->map(fn ($v) => (int)$v)
            ->toArray();

        $books = DB::table('SACH as s')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->leftJoin('NHAXUATBAN as nxb', 'nxb.MaNXB', '=', 's.MaNXB')
            ->select(
                's.MaSach',
                'ds.TenDauSach',
                's.NamXuatBan',
                's.TriGia',
                'nxb.TenNXB'
            )
            ->orderBy('s.MaSach')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'selected' => $selected,
                'books' => $books
            ]
        ]);
    }


    /**
     * Render UI page (Blade)
     */
    public function showBorrowRecordsPage()
    {
        $today = Carbon::today();

        $phieuMuons = DB::table('PHIEUMUON as pm')
            ->join('DOCGIA as dg', 'dg.MaDocGia', '=', 'pm.MaDocGia')
            ->leftJoin('CT_PHIEUMUON as ct', 'ct.MaPhieuMuon', '=', 'pm.MaPhieuMuon')
            ->select(
                'pm.MaPhieuMuon',
                'pm.MaDocGia',
                'dg.TenDocGia',
                'pm.NgayMuon',
                'pm.NgayHenTra',
                DB::raw('SUM(CASE WHEN ct.NgayTra IS NULL THEN 1 ELSE 0 END) as so_sach_chua_tra'),
                DB::raw('COUNT(ct.MaSach) as tong_sach')
            )
            ->groupBy('pm.MaPhieuMuon', 'pm.MaDocGia', 'dg.TenDocGia', 'pm.NgayMuon', 'pm.NgayHenTra')
            ->orderBy('pm.NgayMuon', 'desc')
            ->get()
            ->map(function ($r) use ($today) {
                $ngayHenTra = $r->NgayHenTra ? Carbon::parse($r->NgayHenTra) : null;

                $status = 'active';
                if ((int)$r->tong_sach > 0 && (int)$r->so_sach_chua_tra === 0) {
                    $status = 'returned';
                } elseif ($ngayHenTra && $ngayHenTra->lt($today)) {
                    $status = 'overdue';
                }

                $r->TrangThai = $status;
                return $r;
            });

        return view('borrow-records', compact('phieuMuons'));
    }


}
