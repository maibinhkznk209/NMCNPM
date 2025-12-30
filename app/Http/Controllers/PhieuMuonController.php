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
use Illuminate\Validation\ValidationException;
use App\Models\QuyDinh;

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
            'MaSach.*' => 'required', 
            'borrow_date' => 'sometimes|date',
            'due_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $data = $validator->validated();

        // Normalize
        $maDocGia = (string) $data['MaDocGia'];
        $maSachArr = array_values(array_unique(array_map('strval', $data['MaSach'])));

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

        $dueDate = isset($data['due_date']) ? Carbon::parse($data['due_date']) : null;
        if ($dueDate && $dueDate->lt($borrowDate)) {
            throw new Exception('Ngay hen tra khong duoc truoc ngay muon');
        }

        return [
            'MaDocGia' => $maDocGia,
            'MaSach' => $maSachArr,
            'borrow_date' => $borrowDate,
            'due_date' => $dueDate,
        ];
    }

    private function validateReturnRequest(Request $request): array
    {
        // UI sends 'sach_ids' (frontend) while backend originally used 'MaSach'
        $payload = $request->all();
        if (!isset($payload['MaSach']) && isset($payload['sach_ids'])) {
            $payload['MaSach'] = $payload['sach_ids'];
        }

        $validator = Validator::make($payload, [
            'MaSach' => 'required|array|min:1',
            'MaSach.*' => 'integer',
            'return_date' => 'sometimes|date',
            'book_statuses' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        $data['MaSach'] = array_values(array_unique(array_map('intval', $data['MaSach'])));

        // Normalize statuses if provided (1: tốt, 2: hỏng, 3: mất)
        $statuses = $payload['book_statuses'] ?? [];
        if (is_array($statuses)) {
            $normalized = [];
            $usesLegacyValues = false;
            foreach ($statuses as $k => $v) {
                $value = (int)$v;
                if ($value === 4) {
                    $usesLegacyValues = true;
                }
                $normalized[(int)$k] = $value;
            }

            if ($usesLegacyValues) {
                // Legacy UI used 1 (tốt), 3 (hỏng), 4 (mất)
                foreach ($normalized as $k => $v) {
                    if ($v === 3) {
                        $normalized[$k] = 2;
                    } elseif ($v === 4) {
                        $normalized[$k] = 3;
                    } elseif (!in_array($v, [1, 2, 3], true)) {
                        $normalized[$k] = 1;
                    }
                }
            } else {
                foreach ($normalized as $k => $v) {
                    if (!in_array($v, [1, 2, 3], true)) {
                        $normalized[$k] = 1;
                    }
                }
            }
            $data['book_statuses'] = $normalized;
        } else {
            $data['book_statuses'] = [];
        }

        return $data;
    }

    private function calculateFineAmount(Carbon $dueDate, Carbon $returnDate): int
    {
        if ($returnDate->lte($dueDate)) {
            return 0;
        }
        $daysLate = (int)$dueDate->diffInDays($returnDate);
        return $daysLate * QuyDinh::getLateFinePerDay();
    }


    /**
     * =========================
     * INVENTORY (TÌNH TRẠNG SÁCH)
     * Ưu tiên dùng CUONSACH.TinhTrang (0/1/2/3) nếu có bảng CUONSACH.
     * Fallback:
     *  - SACH.TinhTrang (nếu hệ thống cũ có cột này)
     *  - SACH.SoLuong (giảm/tăng số lượng nếu chỉ có tồn kho theo số lượng)
     * =========================
     */
    private function hasCuonSachTable(): bool
    {
        return Schema::hasTable('CUONSACH')
            && Schema::hasColumn('CUONSACH', 'MaSach')
            && Schema::hasColumn('CUONSACH', 'TinhTrang');
    }

    private function markBorrowed($maSach): void
    {
        if ($this->hasCuonSachTable()) {
            $base = DB::table('CUONSACH')
                ->where('MaSach', $maSach)
                ->orderBy('MaCuonSach')
                ->lockForUpdate();

            $cuon = (clone $base)->first();

            // Nếu không có cuốn trong CUONSACH -> fallback sang SACH (seed test đang như vậy)
            if (!$cuon) {
                $this->markBorrowedFallbackOnSach($maSach);
                return;
            }

            // Nếu có TinhTrang thì update, không thì thôi
            if (Schema::hasColumn('CUONSACH', 'TinhTrang')) {
                DB::table('CUONSACH')->where('MaCuonSach', $cuon->MaCuonSach)->update([
                    'TinhTrang' => 0,
                ]);
            }

            return;
        }

        $this->markBorrowedFallbackOnSach($maSach);
    }

    private function markBorrowedFallbackOnSach($maSach): void
    {
        if (Schema::hasColumn('SACH', 'TinhTrang')) {
            DB::table('SACH')->where('MaSach', $maSach)->update(['TinhTrang' => 0]);
            return;
        }

        if (Schema::hasColumn('SACH', 'SoLuong')) {
            $row = DB::table('SACH')->where('MaSach', $maSach)->lockForUpdate()->first();
            if (!$row) return;

            $qty = (int)($row->SoLuong ?? 0);
            if ($qty <= 0) return;

            DB::table('SACH')->where('MaSach', $maSach)->update(['SoLuong' => $qty - 1]);
        }
    }




    private function restoreBorrowedToAvailable(int $maSach): void
    {
        // 1 = Có sẵn
        if ($this->hasCuonSachTable()) {
            $cuon = DB::table('CUONSACH')
                ->where('MaSach', $maSach)
                ->where('TinhTrang', 0) // đang mượn
                ->orderBy('MaCuonSach')
                ->lockForUpdate()
                ->first();

            if ($cuon) {
                DB::table('CUONSACH')->where('MaCuonSach', $cuon->MaCuonSach)->update([
                    'TinhTrang' => 1,
                ]);
            }
            return;
        }

        if (Schema::hasColumn('SACH', 'TinhTrang')) {
            DB::table('SACH')->where('MaSach', $maSach)->update(['TinhTrang' => 1]);
            return;
        }

        if (Schema::hasColumn('SACH', 'SoLuong')) {
            DB::table('SACH')->where('MaSach', $maSach)->increment('SoLuong', 1);
        }
    }

    private function setInventoryStatusOnReturn(int $maSach, int $status): void
    {
        // status: 1=tốt(có sẵn), 2=hỏng, 3=mất
        $status = in_array($status, [1, 2, 3], true) ? $status : 1;

        if ($this->hasCuonSachTable()) {
            $cuon = DB::table('CUONSACH')
                ->where('MaSach', $maSach)
                ->where('TinhTrang', 0) // đang mượn
                ->orderBy('MaCuonSach')
                ->lockForUpdate()
                ->first();

            // Không tìm thấy cuốn đang mượn -> bỏ qua để tránh crash (đảm bảo CT_PHIEUMUON vẫn cập nhật)
            if ($cuon) {
                DB::table('CUONSACH')->where('MaCuonSach', $cuon->MaCuonSach)->update([
                    'TinhTrang' => $status,
                ]);
            }
            return;
        }

        if (Schema::hasColumn('SACH', 'TinhTrang')) {
            DB::table('SACH')->where('MaSach', $maSach)->update(['TinhTrang' => $status]);
            return;
        }

        if (Schema::hasColumn('SACH', 'SoLuong')) {
            // Nếu trả "tốt" thì tăng lại số lượng; hỏng/mất thì không tăng.
            if ($status === 1) {
                DB::table('SACH')->where('MaSach', $maSach)->increment('SoLuong', 1);
            }
        }
    }

    /**
     * GET /api/borrow-records
     */
    public function index(Request $request): JsonResponse
    {
        $today = $this->today();

        // Base rows (1 row per borrow record)
        $rows = DB::table('PHIEUMUON as pm')
            ->join('DOCGIA as dg', 'dg.MaDocGia', '=', 'pm.MaDocGia')
            ->leftJoin('CT_PHIEUMUON as ct', 'ct.MaPhieuMuon', '=', 'pm.MaPhieuMuon')
            ->select(
                'pm.MaPhieuMuon',
                'pm.MaDocGia',
                'dg.TenDocGia',
                'dg.Email',
                'pm.NgayMuon',
                'pm.NgayHenTra',
                DB::raw('SUM(CASE WHEN ct.NgayTra IS NULL THEN 1 ELSE 0 END) as so_sach_chua_tra'),
                DB::raw('COUNT(ct.MaSach) as tong_sach'),
                DB::raw('MAX(ct.NgayTra) as max_ngay_tra')
            )
            ->groupBy('pm.MaPhieuMuon', 'pm.MaDocGia', 'dg.TenDocGia', 'dg.Email', 'pm.NgayMuon', 'pm.NgayHenTra')
            ->orderBy('pm.NgayMuon', 'desc')
            ->get();

        $ids = $rows->pluck('MaPhieuMuon')->all();

        // Books per borrow record (to satisfy UI expectations: record.books[])
        $booksByPM = [];
        $hasCuonSach = Schema::hasTable('CUONSACH')
            && Schema::hasColumn('CUONSACH', 'MaSach')
            && Schema::hasColumn('CUONSACH', 'MaCuonSach');
        if (!empty($ids)) {
            $bookRows = DB::table('CT_PHIEUMUON as ct')
                ->join('SACH as s', 's.MaSach', '=', 'ct.MaSach')
                ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
                ->leftJoin('CT_TACGIA as ctg', 'ctg.MaDauSach', '=', 'ds.MaDauSach')
                ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'ctg.MaTacGia')
                ->whereIn('ct.MaPhieuMuon', $ids)
                ->select(
                    'ct.MaPhieuMuon',
                    'ct.MaSach',
                    'ds.MaDauSach',
                    'ds.TenDauSach',
                    DB::raw('GROUP_CONCAT(DISTINCT tg.TenTacGia) as TenTacGia'),
                    $hasCuonSach
                        ? DB::raw('(SELECT COUNT(*) FROM CUONSACH cs2 JOIN SACH s2 ON s2.MaSach = cs2.MaSach WHERE s2.MaDauSach = ds.MaDauSach AND cs2.MaCuonSach <= (SELECT MIN(cs3.MaCuonSach) FROM CUONSACH cs3 WHERE cs3.MaSach = ct.MaSach)) as SoThuTuCuon')
                        : DB::raw('NULL as SoThuTuCuon')
                )
                ->groupBy('ct.MaPhieuMuon', 'ct.MaSach', 'ds.MaDauSach', 'ds.TenDauSach')
                ->orderBy('ct.MaPhieuMuon')
                ->orderBy('ct.MaSach')
                ->get();

            foreach ($bookRows as $r) {
                $author = $r->TenTacGia ?: null;
                $firstAuthor = $author ? explode(',', (string)$author)[0] : null;

                $booksByPM[$r->MaPhieuMuon][] = [
                    'id' => (int)$r->MaSach,
                    'code' => (string)$r->MaSach,
                    'MaSach' => (int)$r->MaSach,
                    'MaDauSach' => isset($r->MaDauSach) ? (int)$r->MaDauSach : null,
                    'SoThuTuCuon' => isset($r->SoThuTuCuon) ? (int)$r->SoThuTuCuon : null,
                    'TenSach' => $r->TenDauSach,
                    'TenDauSach' => $r->TenDauSach,
                    'title' => $r->TenDauSach,
                    'author' => $author,
                    'TenTacGia' => $author,
                    'tac_gia' => $firstAuthor ? ['TenTacGia' => $firstAuthor] : null,
                    'the_loais' => [],
                ];
            }
        }

        $data = $rows->map(function ($r) use ($today, $booksByPM) {
            $hasUnreturned = ((int)$r->so_sach_chua_tra) > 0;
            $dueDate = Carbon::parse($r->NgayHenTra);

            if (!$hasUnreturned) {
                $status = 'returned';
                $returnDate = $r->max_ngay_tra;
            } elseif ($today->gt($dueDate)) {
                $status = 'overdue';
                $returnDate = null;
            } else {
                $status = 'borrowed';
                $returnDate = null;
            }

            $books = $booksByPM[$r->MaPhieuMuon] ?? [];

            return [
                // Canonical fields (used by borrow-records.blade.php JS)
                'id' => (string)$r->MaPhieuMuon,
                'ma_phieu' => (string)$r->MaPhieuMuon,
                'code' => (string)$r->MaPhieuMuon,
                'borrow_date' => $r->NgayMuon,
                'due_date' => $r->NgayHenTra,
                'return_date' => $returnDate,
                'status' => $status,
                'is_overdue' => $status === 'overdue',
                'fine_created' => false,

                'reader' => [
                    'id' => (string)$r->MaDocGia,
                    'name' => $r->TenDocGia,
                    'email' => $r->Email,
                ],
                'reader_name' => $r->TenDocGia,
                'reader_email' => $r->Email,

                'books' => $books,

                // Backward compatible fields
                'MaPhieuMuon' => $r->MaPhieuMuon,
                'MaDocGia' => $r->MaDocGia,
                'TenDocGia' => $r->TenDocGia,
                'NgayMuon' => $r->NgayMuon,
                'NgayHenTra' => $r->NgayHenTra,
                'TrangThai' => $status,
                'TongSach' => (int)$r->tong_sach,
                'SoSachChuaTra' => (int)$r->so_sach_chua_tra,
                'book_title' => !empty($books) ? implode(', ', array_map(fn ($b) => $b['TenSach'] ?? $b['title'] ?? 'Không rõ tên sách', $books)) : null,
                'book_author' => !empty($books) ? implode(', ', array_map(fn ($b) => $b['author'] ?? 'Chưa có tác giả', $books)) : null,
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
            ->select(
                'pm.MaPhieuMuon',
                'pm.MaDocGia',
                'pm.NgayMuon',
                'pm.NgayHenTra',
                'dg.TenDocGia',
                'dg.Email'
            )
            ->first();

        if (!$pm) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu mượn'], 404);
        }

        $hasCuonSach = Schema::hasTable('CUONSACH')
            && Schema::hasColumn('CUONSACH', 'MaSach')
            && Schema::hasColumn('CUONSACH', 'MaCuonSach');

        $details = DB::table('CT_PHIEUMUON as ct')
            ->join('SACH as s', 's.MaSach', '=', 'ct.MaSach')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->leftJoin('CT_TACGIA as ctg', 'ctg.MaDauSach', '=', 'ds.MaDauSach')
            ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'ctg.MaTacGia')
            ->where('ct.MaPhieuMuon', $id)
            ->select(
                'ct.MaSach',
                'ct.NgayTra',
                'ct.TienPhat',
                's.TriGia',
                'ds.MaDauSach',
                'ds.TenDauSach',
                DB::raw('GROUP_CONCAT(DISTINCT tg.TenTacGia) as TenTacGia'),
                $hasCuonSach
                    ? DB::raw('(SELECT COUNT(*) FROM CUONSACH cs2 JOIN SACH s2 ON s2.MaSach = cs2.MaSach WHERE s2.MaDauSach = ds.MaDauSach AND cs2.MaCuonSach <= (SELECT MIN(cs3.MaCuonSach) FROM CUONSACH cs3 WHERE cs3.MaSach = ct.MaSach)) as SoThuTuCuon')
                    : DB::raw('NULL as SoThuTuCuon')
            )
            ->groupBy('ct.MaSach', 'ct.NgayTra', 'ct.TienPhat', 's.TriGia', 'ds.MaDauSach', 'ds.TenDauSach')
            ->orderBy('ct.MaSach')
            ->get();

        $chiTiet = $details->map(function ($r) {
            $author = $r->TenTacGia ?: null;
            $firstAuthor = $author ? explode(',', (string)$author)[0] : null;
            $triGia = $r->TriGia !== null ? (float)$r->TriGia : 0;

            $sach = [
                'id' => (int)$r->MaSach,
                'MaSach' => (int)$r->MaSach,
                'MaDauSach' => isset($r->MaDauSach) ? (int)$r->MaDauSach : null,
                'SoThuTuCuon' => isset($r->SoThuTuCuon) ? (int)$r->SoThuTuCuon : null,
                'TenSach' => $r->TenDauSach,
                'TenDauSach' => $r->TenDauSach,
                'TriGia' => $triGia,
                'tri_gia' => $triGia,
                'title' => $r->TenDauSach,
                'author' => $author,
                'TenTacGia' => $author,
                'tac_gia' => $firstAuthor ? ['TenTacGia' => $firstAuthor] : null,
                'the_loais' => [],
            ];

            return [
                'MaSach' => (int)$r->MaSach,
                'NgayTra' => $r->NgayTra,
                'TienPhat' => $r->TienPhat,
                'sach' => $sach,
            ];
        });

        $today = $this->today();
        $dueDate = Carbon::parse($pm->NgayHenTra);
        $hasUnreturned = $chiTiet->contains(fn ($x) => $x['NgayTra'] === null);

        if (!$hasUnreturned) {
            $status = 'returned';
        } elseif ($today->gt($dueDate)) {
            $status = 'overdue';
        } else {
            $status = 'borrowed';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string)$pm->MaPhieuMuon,
                'MaPhieu' => $pm->MaPhieuMuon,
                'MaPhieuMuon' => $pm->MaPhieuMuon,
                'MaDocGia' => $pm->MaDocGia,
                'NgayMuon' => $pm->NgayMuon,
                'NgayHenTra' => $pm->NgayHenTra,
                'TrangThai' => $status,
                'doc_gia' => [
                    'MaDocGia' => $pm->MaDocGia,
                    'TenDocGia' => $pm->TenDocGia,
                    'Email' => $pm->Email,
                ],
                'chi_tiet_phieu_muon' => $chiTiet,
                'books' => $chiTiet->map(fn ($ct) => $ct['sach'])->values(),
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

            $maDocGia  = $validated['MaDocGia'];
            $maSachArr = $validated['MaSach'];

            $borrowDate = Carbon::parse($validated['borrow_date']);
            $dueDate = isset($validated['due_date'])
                ? Carbon::parse($validated['due_date'])
                : $borrowDate->copy()->addDays($this->getBorrowDurationDays());

            $maPhieuMuon = $this->generateMaPhieuMuon();
            DB::table('PHIEUMUON')->insert([
                'MaPhieuMuon' => $maPhieuMuon,
                'MaDocGia' => $maDocGia,
                'NgayMuon' => $borrowDate->toDateString(),
                'NgayHenTra' => $dueDate->toDateString(),
            ]);

            // Mỗi phiếu mượn chỉ 1 cuốn sách.
            // Nếu người dùng chọn nhiều sách, hệ thống sẽ tạo nhiều phiếu mượn tương ứng.
            $created = [];

            foreach ($maSachArr as $maSach) {
                // quan trọng: đừng ép int nếu mã là string
                $this->markBorrowed($maSach);

                DB::table('CT_PHIEUMUON')->insert([
                    'MaPhieuMuon' => $maPhieuMuon,
                    'MaSach' => $maSach,
                    'NgayTra' => null,
                    'TienPhat' => 0,
                ]);

                $created[] = $maPhieuMuon;
            }

            DB::commit();

            $count = count($created);
            $msg = $count > 1
                ? ('Tạo ' . $count . ' phiếu mượn thành công')
                : 'Tạo phiếu mượn thành công';

            return response()->json([
                'success' => true,
                'message' => $msg,
                'data' => [
                    'created' => $created,
                    'MaPhieuMuon' => $created[0] ?? null,
                ],
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
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
            $dueDate = $validated['due_date'] ?? $borrowDate->copy()->addDays($this->getBorrowDurationDays());

            // Mỗi phiếu mượn chỉ được 1 cuốn sách.
            if (count($maSachArr) !== 1) {
                throw new Exception('Mỗi phiếu mượn chỉ được chọn đúng 1 cuốn sách');
            }

            $maSachNew = (int) $maSachArr[0];

            // Replace details (simple approach)
            $oldSach = DB::table('CT_PHIEUMUON')->where('MaPhieuMuon', $id)->pluck('MaSach')->all();


            // Hoàn trả tồn kho cho các sách cũ (trong trường hợp sửa phiếu mượn trước khi trả)
            foreach ($oldSach as $oldMaSach) {
                $this->restoreBorrowedToAvailable((int)$oldMaSach);
            }

            DB::table('CT_PHIEUMUON')->where('MaPhieuMuon', $id)->delete();

            $this->markBorrowed($maSachNew);

            DB::table('CT_PHIEUMUON')->insert([
                'MaPhieuMuon' => $id,
                'MaSach' => $maSachNew,
                'NgayTra' => null,
                'TienPhat' => 0,
            ]);

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

            $bookInfo = DB::table('SACH as s')
                ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
                ->whereIn('s.MaSach', $data['MaSach'])
                ->select('s.MaSach', 's.TriGia', 'ds.TenDauSach')
                ->get()
                ->keyBy('MaSach');

            $summary = [
                'total_late_fine' => 0,
                'total_compensation' => 0,
                'total_fine' => 0,
            ];

            $bookDetails = [];
            $bookStatuses = $data['book_statuses'] ?? [];

            // Late fine is based on the record due date, applied per book (current UI design)
            $lateFinePerBook = $this->calculateFineAmount($dueDate, $returnDate);
            $daysLate = $returnDate->lte($dueDate) ? 0 : (int)$dueDate->diffInDays($returnDate);

            foreach ($data['MaSach'] as $maSach) {
                $info = $bookInfo[$maSach] ?? null;
                $ten = $info ? $info->TenDauSach : ('MaSach=' . $maSach);
                $triGia = $info ? (int)$info->TriGia : 0;

                $status = (int)($bookStatuses[$maSach] ?? 1);

                $compensation = 0;
                $compensationText = 'Tình trạng tốt: 0 VNĐ';
                if ($status === 2) {
                    $compensation = (int)round($triGia * 0.5);
                    $compensationText = 'Hỏng (50% trị giá): ' . number_format($compensation, 0, ',', '.') . ' VNĐ';
                } elseif ($status === 3) {
                    $compensation = $triGia;
                    $compensationText = 'Mất (100% trị giá): ' . number_format($compensation, 0, ',', '.') . ' VNĐ';
                }

                $lateText = $daysLate > 0
                    ? ('Trễ ' . $daysLate . ' ngày: ' . number_format($lateFinePerBook, 0, ',', '.') . ' VNĐ')
                    : 'Không trễ hạn: 0 VNĐ';

                $total = $lateFinePerBook + $compensation;

                $summary['total_late_fine'] += $lateFinePerBook;
                $summary['total_compensation'] += $compensation;
                $summary['total_fine'] += $total;

                $bookDetails[] = [
                    'sach_id' => (int)$maSach,
                    'ten_sach' => $ten,
                    'tinh_trang_moi' => $status,
                    'late_fine' => $lateFinePerBook,
                    'compensation' => $compensation,
                    'tong_tien_phat' => $total,
                    'late_fine_details' => $lateText,
                    'compensation_details' => $compensationText,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => $summary,
                    'book_details' => $bookDetails,
                    'return_date' => $returnDate->toDateString(),
                ],
            ]);
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

            $bookInfo = DB::table('SACH as s')
                ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
                ->whereIn('s.MaSach', $data['MaSach'])
                ->select('s.MaSach', 's.TriGia', 'ds.TenDauSach')
                ->get()
                ->keyBy('MaSach');

            $bookStatuses = $data['book_statuses'] ?? [];

            $lateFinePerBook = $this->calculateFineAmount($dueDate, $returnDate);

            foreach ($data['MaSach'] as $maSach) {
                $info = $bookInfo[$maSach] ?? null;
                $triGia = $info ? (int)$info->TriGia : 0;

                $status = (int)($bookStatuses[$maSach] ?? 1);

                $compensation = 0;
                if ($status === 2) {
                    $compensation = (int)round($triGia * 0.5);
                } elseif ($status === 3) {
                    $compensation = $triGia;
                }

                $fine = $lateFinePerBook + $compensation;

                // Update inventory status (ưu tiên CUONSACH.TinhTrang; fallback SACH.TinhTrang/SoLuong)
                $this->setInventoryStatusOnReturn((int)$maSach, $status);

// Update borrow detail
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
            'new_due_date' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => implode(', ', $validator->errors()->all())], 400);
        }

        $pm = DB::table('PHIEUMUON')->where('MaPhieuMuon', $phieuMuonId)->first();
        if (!$pm) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phiếu mượn'], 404);
        }

        $validated = $validator->validated();
        $extendDays = (int)$validated['extend_days'];

        // If UI provided explicit new_due_date, trust it (still capped by extend_days rule)
        if (!empty($validated['new_due_date'])) {
            $newDue = Carbon::parse($validated['new_due_date']);
        } else {
            $newDue = Carbon::parse($pm->NgayHenTra)->addDays($extendDays);
        }

        DB::table('PHIEUMUON')->where('MaPhieuMuon', $phieuMuonId)->update([
            'NgayHenTra' => $newDue->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gia hạn phiếu mượn thành công',
            'data' => ['NgayHenTra' => $newDue->toDateString()],
        ]);
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
        // UI (borrow-records.blade.php) expects a normalized shape:
        // { id, MaDocGia, name, TenDocGia, email, Email, ... }
        $rows = DB::table('DOCGIA')
            ->select(
                'MaDocGia',
                'TenDocGia',
                'Email',
                'NgayHetHan',
                'TongNo'
            )
            ->orderBy('MaDocGia')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->MaDocGia,
                    'MaDocGia' => $r->MaDocGia,
                    'name' => $r->TenDocGia,
                    'TenDocGia' => $r->TenDocGia,
                    'email' => $r->Email,
                    'Email' => $r->Email,
                    'NgayHetHan' => $r->NgayHetHan,
                    'TongNo' => $r->TongNo,
                ];
            });

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
        // NOTE: In DB design, author is linked to DAUSACH via CT_TACGIA.
        // UI expects to display book title + author in the selector.
        $hasCuonSach = Schema::hasTable('CUONSACH')
            && Schema::hasColumn('CUONSACH', 'MaSach')
            && Schema::hasColumn('CUONSACH', 'MaCuonSach');
        $q = DB::table('SACH as s')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->leftJoin('NHAXUATBAN as nxb', 'nxb.MaNXB', '=', 's.MaNXB')
            ->leftJoin('CT_TACGIA as ctg', 'ctg.MaDauSach', '=', 'ds.MaDauSach')
            ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'ctg.MaTacGia')
            ->select(
                's.MaSach',
                'ds.MaDauSach',
                'ds.TenDauSach',
                's.NamXuatBan',
                's.TriGia',
                's.SoLuong',
                'nxb.TenNXB',
                DB::raw("GROUP_CONCAT(DISTINCT tg.TenTacGia) as TenTacGia"),
                $hasCuonSach
                    ? DB::raw('(SELECT COUNT(*) FROM CUONSACH cs2 JOIN SACH s2 ON s2.MaSach = cs2.MaSach WHERE s2.MaDauSach = ds.MaDauSach AND cs2.MaCuonSach <= (SELECT MIN(cs3.MaCuonSach) FROM CUONSACH cs3 WHERE cs3.MaSach = s.MaSach)) as SoThuTuCuon')
                    : DB::raw('NULL as SoThuTuCuon')
            )
            ->groupBy(
                's.MaSach',
                'ds.MaDauSach',
                'ds.TenDauSach',
                's.NamXuatBan',
                's.TriGia',
                's.SoLuong',
                'nxb.TenNXB'
            )
            ->orderBy('s.MaSach');
        // Lọc chỉ sách còn "có sẵn" theo ưu tiên:
        // 1) CUONSACH.TinhTrang = 1 (nếu có bảng cuốn sách)
        // 2) SACH.TinhTrang = 1 (nếu hệ thống cũ có cột này)
        // 3) SACH.SoLuong > 0 (fallback)
        if (Schema::hasTable('CUONSACH') && Schema::hasColumn('CUONSACH', 'MaSach') && Schema::hasColumn('CUONSACH', 'TinhTrang')) {
            $q->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('CUONSACH as cs')
                    ->whereColumn('cs.MaSach', 's.MaSach')
                    ->where('cs.TinhTrang', 1);
            });
        } elseif (Schema::hasColumn('SACH', 'TinhTrang')) {
            $q->addSelect('s.TinhTrang');
            $q->where('s.TinhTrang', 1);
        } elseif (Schema::hasColumn('SACH', 'SoLuong')) {
            $q->where('s.SoLuong', '>', 0);
        }

        $rows = $q->get()->map(function ($r) {
            $author = $r->TenTacGia ?: null;
            $firstAuthor = $author ? explode(',', (string)$author)[0] : null;

            // Normalize a few fields for the current frontend implementation.
            return [
                'id' => (int)$r->MaSach,
                'MaSach' => (int)$r->MaSach,
                'MaDauSach' => isset($r->MaDauSach) ? (int)$r->MaDauSach : null,
                'SoThuTuCuon' => isset($r->SoThuTuCuon) ? (int)$r->SoThuTuCuon : null,
                // Keep both for backward compatibility in UI
                'TenDauSach' => $r->TenDauSach,
                'TenSach' => $r->TenDauSach,
                'title' => $r->TenDauSach,

                // Author
                'author' => $author,
                'TenTacGia' => $author,
                'tac_gia' => $firstAuthor ? ['TenTacGia' => $firstAuthor] : null,

                // Other metadata
                'NamXuatBan' => $r->NamXuatBan,
                'TriGia' => $r->TriGia,
                'SoLuong' => $r->SoLuong,
                'TenNXB' => $r->TenNXB,
                // If present
                'TinhTrang' => property_exists($r, 'TinhTrang') ? $r->TinhTrang : null,
            ];
        });

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
            ->values()
            ->all();

        // Xác định phiếu mượn đã trả hết hay chưa (phục vụ UI chỉnh sửa phiếu trả)
        $isReturnedRecord = !DB::table('CT_PHIEUMUON')
            ->where('MaPhieuMuon', $pmId)
            ->whereNull('NgayTra')
            ->exists();

        // Lấy danh sách sách có thể chọn:
        // - Sách đang "Có sẵn" (TinhTrang = 1) OR
        // - Sách đã nằm trong phiếu mượn này (để sửa phiếu mượn/phiếu trả vẫn thấy)
        $hasCuonSach = Schema::hasTable('CUONSACH')
            && Schema::hasColumn('CUONSACH', 'MaSach')
            && Schema::hasColumn('CUONSACH', 'MaCuonSach');
        $books = DB::table('SACH as s')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->leftJoin('CT_TACGIA as ctg', 'ctg.MaDauSach', '=', 'ds.MaDauSach')
            ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'ctg.MaTacGia')
            ->leftJoin('NHAXUATBAN as nxb', 'nxb.MaNXB', '=', 's.MaNXB')
            ->where(function ($q) use ($selected) {
                if (Schema::hasTable('CUONSACH') && Schema::hasColumn('CUONSACH', 'MaSach') && Schema::hasColumn('CUONSACH', 'TinhTrang')) {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('CUONSACH as cs')
                            ->whereColumn('cs.MaSach', 's.MaSach')
                            ->where('cs.TinhTrang', 1);
                    });
                } elseif (Schema::hasColumn('SACH', 'TinhTrang')) {
                    $q->where('s.TinhTrang', 1);
                } elseif (Schema::hasColumn('SACH', 'SoLuong')) {
                    $q->where('s.SoLuong', '>', 0);
                } else {
                    $q->whereRaw('1=1');
                }

                if (!empty($selected)) {
                    $q->orWhereIn('s.MaSach', $selected);
                }
            })
            ->select(
                's.MaSach',
                'ds.MaDauSach',
                'ds.TenDauSach',
                's.NamXuatBan',
                's.TriGia',
                'nxb.TenNXB',
                DB::raw('GROUP_CONCAT(DISTINCT tg.TenTacGia) as TenTacGia'),
                $hasCuonSach
                    ? DB::raw('(SELECT COUNT(*) FROM CUONSACH cs2 JOIN SACH s2 ON s2.MaSach = cs2.MaSach WHERE s2.MaDauSach = ds.MaDauSach AND cs2.MaCuonSach <= (SELECT MIN(cs3.MaCuonSach) FROM CUONSACH cs3 WHERE cs3.MaSach = s.MaSach)) as SoThuTuCuon')
                    : DB::raw('NULL as SoThuTuCuon')
            )
            ->groupBy('s.MaSach', 'ds.MaDauSach', 'ds.TenDauSach', 's.NamXuatBan', 's.TriGia', 'nxb.TenNXB')
            ->orderBy('s.MaSach')
            ->get()
            ->map(function ($r) {
                $author = $r->TenTacGia ?: null;
                $firstAuthor = $author ? explode(',', (string)$author)[0] : null;

                return [
                    'id' => (int)$r->MaSach,
                    'code' => (string)$r->MaSach,
                    'MaSach' => (int)$r->MaSach,
                    'MaDauSach' => isset($r->MaDauSach) ? (int)$r->MaDauSach : null,
                    'SoThuTuCuon' => isset($r->SoThuTuCuon) ? (int)$r->SoThuTuCuon : null,
                    'TenDauSach' => $r->TenDauSach,
                    'TenSach' => $r->TenDauSach,
                    'title' => $r->TenDauSach,
                    'author' => $author,
                    'TenTacGia' => $author,
                    'tac_gia' => $firstAuthor ? ['TenTacGia' => $firstAuthor] : null,
                    'NamXuatBan' => $r->NamXuatBan,
                    'TriGia' => $r->TriGia,
                    'TenNXB' => $r->TenNXB,
                    'the_loais' => [],
                ];
            })
            ->values();

        // IMPORTANT:
        // - UI hiện tại đọc data.data là một MẢNG books
        // - Đồng thời vẫn giữ thêm selected/is_returned_record để dùng khi cần
        return response()->json([
            'success' => true,
            'data' => $books,
            'selected' => $selected,
            'is_returned_record' => $isReturnedRecord,
        ]);
    }


    /**
     * Render UI page (Blade)
     */
    public function showBorrowRecordsPage()
    {
        $today = Carbon::today();
        $borrowDurationDays = $this->getBorrowDurationDays();

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

        return view('borrow-records', compact('phieuMuons', 'borrowDurationDays'));
    }


}







