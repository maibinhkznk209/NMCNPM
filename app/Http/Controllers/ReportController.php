<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Report API is working',
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
                'tl.MaTheLoai as MaTheLoai',
                'tl.TenTheLoai as TenTheLoai',
                DB::raw('COUNT(*) as SoLuotMuon'),
            ])
            ->orderByDesc('SoLuotMuon')
            ->get();

        $genres = $rows->map(function ($r) {
            return [
                'MaTheLoai' => $r->MaTheLoai,
                'TenTheLoai' => $r->TenTheLoai,
                'SoLuotMuon' => (int)$r->SoLuotMuon,
            ];
        })->values()->all();

        $total = (int) $rows->sum('SoLuotMuon');

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
            $date = \Carbon\Carbon::createFromFormat('Y-m-d', $dateStr)->startOfDay();
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Định dạng ngày không hợp lệ',
            ], 400);
        }

        $rows = \Illuminate\Support\Facades\DB::table('PHIEUMUON as pm')
            ->join('CT_PHIEUMUON as ct', 'ct.MaPhieuMuon', '=', 'pm.MaPhieuMuon')
            ->whereDate('pm.NgayHenTra', '<', $date->toDateString())
            ->where(function ($q) use ($date) {
                $q->whereNull('ct.NgayTra')
                ->orWhereDate('ct.NgayTra', '>', $date->toDateString());
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date->toDateString(),
                'total_overdue' => $rows->count(),
                'overdue_books' => $rows,
            ],
        ], 200);
    }


  
    public function exportGenreStatistics(Request $request)
    {
        $res = $this->genreStatistics($request);
        $payload = $res->getData(true);

        $content = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="genre-statistics.xlsx"',
        ]);
    }

   
    public function exportOverdueBooks(Request $request)
    {
        $res = $this->overdueBooks($request);
        $payload = $res->getData(true);

        $content = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="overdue-books.xlsx"',
        ]);
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
}
