<?php

namespace App\Http\Controllers;

use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(private readonly ExcelExportService $excelExport)
    {
    }
    
    
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type');

        if ($type === 'genre') {
            return $this->genreStatistics($request);
        }

        if ($type === 'overdue') {
            return $this->overdueBooks($request);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report API is working',
            'hint' => [
                'genre' => '/api/reports?type=genre&month=12&year=2025',
                'overdue' => '/api/reports?type=overdue&date=2025-12-25',
            ],
        ]);
    }


    
    public function genreStatistics(Request $request): JsonResponse
    {
        $month = $request->query('month');
        $year  = $request->query('year');

        if ($month === null || $year === null || $month === '' || $year === '') {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn tháng và năm để tạo báo cáo',
            ], 400);
        }

        $m = (int) $month;
        $y = (int) $year;

        if ($m <= 0 || $y <= 0) {
            return response()->json([
                'success' => true,
                'data' => [
                    'month' => (string)$month,
                    'year'  => (string)$year,
                    'total_borrows' => 0,
                    'genres' => [],
                ],
            ]);
        }

        $rows = DB::table('CT_PHIEUMUON as ct')
            ->join('PHIEUMUON as pm', 'pm.MaPhieuMuon', '=', 'ct.MaPhieuMuon')
            ->join('SACH as s', 's.MaSach', '=', 'ct.MaSach')
            ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
            ->join('THELOAI as tl', 'tl.MaTheLoai', '=', 'ds.MaTheLoai')
            ->whereMonth('pm.NgayMuon', $m)
            ->whereYear('pm.NgayMuon', $y)
            ->groupBy('tl.MaTheLoai', 'tl.TenTheLoai')
            ->select([
                'tl.MaTheLoai as genre_id',
                'tl.TenTheLoai as genre_name',
                DB::raw('COUNT(*) as borrow_count'),
                DB::raw('GROUP_CONCAT(ds.TenDauSach) as books'),
            ])
            ->orderByDesc('borrow_count')
            ->get();

        $total = (int) $rows->sum('borrow_count');

        $genres = $rows->map(function ($r) use ($total) {
            $borrowCount = (int) ($r->borrow_count ?? 0);
            $percentage = $total > 0 ? ($borrowCount / $total) * 100 : 0;

            $books = [];
            if (!empty($r->books)) {
                $books = array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $r->books)))));
            }

            return [
                'id' => $r->genre_id,
                'name' => $r->genre_name,
                'borrow_count' => $borrowCount,
                'percentage' => $percentage,
                'books' => $books,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'month' => (string)$month,
                'year'  => (string)$year,
                'total_borrows' => $total,
                'genres' => $genres,
            ],
        ]);
    }

    public function overdueBooks(Request $request): JsonResponse
    {
        $dateStr = $request->query('date');

        if ($dateStr === null || $dateStr === '') {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ngày để tạo báo cáo',
            ], 400);
        }

        try {
            $reportDate = Carbon::createFromFormat('Y-m-d', $dateStr)->startOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Định dạng ngày không hợp lệ',
            ], 400);
        }

        // Báo cáo trả trễ theo ngày: liệt kê các sách được trả trong ngày được chọn nhưng trả sau hạn.
        $rows = DB::table('CT_PHIEUMUON as ct')
        ->join('PHIEUMUON as pm', 'pm.MaPhieuMuon', '=', 'ct.MaPhieuMuon')
        ->join('DOCGIA as dg', 'dg.MaDocGia', '=', 'pm.MaDocGia')
        ->join('SACH as s', 's.MaSach', '=', 'ct.MaSach')
        ->join('DAUSACH as ds', 'ds.MaDauSach', '=', 's.MaDauSach')
        // quá hạn tính tới reportDate: hạn trả < reportDate
        ->whereDate('pm.NgayHenTra', '<', $reportDate->toDateString())
        // và (chưa trả) hoặc (đã trả nhưng trả trễ)
        ->where(function ($q) {
            $q->whereNull('ct.NgayTra')
            ->orWhereColumn('ct.NgayTra', '>', 'pm.NgayHenTra');
        })
        ->select([
            'ds.TenDauSach as book_title',
            'dg.TenDocGia as reader_name',
            'pm.NgayMuon as borrow_date',
            'pm.NgayHenTra as due_date',
            'ct.NgayTra as return_date',
            'pm.MaPhieuMuon as borrow_id',
        ])
        ->orderBy('pm.NgayHenTra')
        ->orderBy('pm.MaPhieuMuon')
        ->get();

    $finePerDay = 1000;

    $overdueBooks = $rows->map(function ($r) use ($reportDate, $finePerDay) {
        $dueDate = Carbon::parse($r->due_date)->startOfDay();

        // Nếu đã trả thì tính theo ngày trả, chưa trả thì tính tới reportDate
        $endDate = !empty($r->return_date)
            ? Carbon::parse($r->return_date)->startOfDay()
            : $reportDate;

        $overdueDays = 0;
        if ($endDate->gt($dueDate)) {
            $overdueDays = $dueDate->diffInDays($endDate);
        }

        $status = empty($r->return_date)
            ? 'Chưa trả'
            : ('Đã trả ngày ' . Carbon::parse($r->return_date)->format('d/m/Y'));

        return [
            'book_title' => (string) ($r->book_title ?? ''),
            'reader_name' => (string) ($r->reader_name ?? ''),
            'borrow_date' => $r->borrow_date ? Carbon::parse($r->borrow_date)->toDateString() : '',
            'overdue_days' => (int) $overdueDays,
            'status' => $status,
            'fine_amount' => (int) ($overdueDays * $finePerDay),
        ];
    })->values();

    $totalFine = (int) $overdueBooks->sum('fine_amount');

    return response()->json([
        'success' => true,
        'data' => [
            'date' => $reportDate->toDateString(),
            'total_overdue' => $overdueBooks->count(),
            'total_fine' => $totalFine,
            'overdue_books' => $overdueBooks->all(),
        ],
    ], 200);
    }

    public function exportGenreStatistics(Request $request)
    {
        $res = $this->genreStatistics($request);
        $payload = $res->getData(true);

        if (!($payload['success'] ?? false)) {
            return $res;
        }

        $month = (int) $request->query('month');
        $year = (int) $request->query('year');

        return $this->excelExport->exportGenreStatistics($payload['data'], $month, $year);
    }

   
    public function exportOverdueBooks(Request $request)
    {
        $res = $this->overdueBooks($request);
        $payload = $res->getData(true);

        if (!($payload['success'] ?? false)) {
            return $res;
        }

        $dateStr = (string) $request->query('date');

        return $this->excelExport->exportOverdueBooks($payload['data'], $dateStr);
    }

 
    public function debugOverdueBooks(Request $request): JsonResponse
    {
        $dateStr = $request->query('date');
        $reportDate = $this->parseDateOrNull($dateStr);

        return response()->json([
            'success' => true,
            'debug' => [
                'input_date' => $dateStr,
                'parsed_date' => $reportDate?->toDateString(),
                'borrow_days' => $this->getBorrowDaysDefault30(),
            ],
        ]);
    }

    
    public function compareOverdueResults(Request $request): JsonResponse
    {
        $current = $this->overdueBooks($request)->getData(true);

        return response()->json([
            'success' => true,
            'data' => [
                'current' => $current['data'] ?? [],
                'legacy' => [], 
            ],
        ]);
    }

    public function checkNegativeFines(): JsonResponse
    {
        $rows = DB::table('CT_PHIEUMUON')
            ->whereNotNull('TienPhat')
            ->whereRaw('CAST(TienPhat AS DECIMAL(15,2)) < 0')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $rows->count(),
                'records' => $rows,
            ],
        ]);
    }

    public function fixNegativeFines(): JsonResponse
    {
        $updated = DB::table('CT_PHIEUMUON')
            ->whereNotNull('TienPhat')
            ->whereRaw('CAST(TienPhat AS DECIMAL(15,2)) < 0')
            ->update(['TienPhat' => 0]);

        return response()->json([
            'success' => true,
            'message' => 'Đã sửa tiền phạt âm về 0',
            'updated' => $updated,
        ]);
    }

    public function recalculateAllFines(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Endpoint recalculateAllFines đã sẵn sàng (chưa bật tính lại tự động).',
        ]);
    }

  
    private function parseDateOrNull($value): ?Carbon
    {
        if ($value === null) return null;

        $s = (string)$value;
        $s = trim($s);
        if ($s === '') return null;

        try {
            return Carbon::parse($s)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getBorrowDaysDefault30(): int
    {
        $val = DB::table('THAMSO')
            ->where('TenThamSo', 'NgayMuonToiDa')
            ->value('GiaTri');

        $days = (int) $val;
        return $days > 0 ? $days : 30;
    }

    private function toNumber($value): float
    {
        if ($value === null) return 0.0;

        if (is_string($value)) {
            $v = trim($value);
            if ($v === '') return 0.0;
            if (is_numeric($v)) return (float)$v;
            return 0.0;
        }

        if (is_numeric($value)) return (float)$value;

        return 0.0;
    }

    public function page()
    {
        return view('reports');
    }
}
