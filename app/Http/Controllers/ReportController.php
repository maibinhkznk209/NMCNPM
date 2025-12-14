<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\ExcelExportService;

class ReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    /**
     * Display the reports page
     */
    public function index()
    {
        return view('reports');
    }

    /**
     * Generate genre statistics report
     */
    public function genreStatistics(Request $request)
    {
        try {
            $month = $request->input('month');
            $year = $request->input('year');
            
            if (!$month || !$year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn tháng và năm để tạo báo cáo'
                ], 400);
            }

            // Get borrow statistics by genre for the specified month
            $genreStats = DB::table('CHITIETPHIEUMUON as ctpm')
                ->join('PHIEUMUON as pm', 'ctpm.phieumuon_id', '=', 'pm.id')
                ->join('SACH as s', 'ctpm.sach_id', '=', 's.id')
                ->join('SACH_THELOAI as st', 's.id', '=', 'st.sach_id')
                ->join('THELOAI as tl', 'st.theloai_id', '=', 'tl.id')
                ->whereYear('pm.NgayMuon', $year)
                ->whereMonth('pm.NgayMuon', $month)
                ->select(
                    'tl.id as genre_id',
                    'tl.TenTheLoai as genre_name',
                    's.TenSach as book_name',
                    DB::raw('COUNT(ctpm.id) as borrow_count')
                )
                ->groupBy('tl.id', 'tl.TenTheLoai', 's.id', 's.TenSach')
                ->orderBy('borrow_count', 'desc')
                ->get();

            // Group by genre and calculate totals
            $groupedStats = $genreStats->groupBy('genre_name')->map(function ($books, $genreName) {
                $totalBorrows = $books->sum('borrow_count');
                return [
                    'genre_name' => $genreName,
                    'borrow_count' => $totalBorrows,
                    'books' => $books->pluck('book_name')->unique()->values()->toArray()
                ];
            })->values()->sortByDesc('borrow_count')->values();

            // Calculate total borrows
            $totalBorrows = $groupedStats->sum('borrow_count');

            // Add percentage to each genre
            $genres = $groupedStats->map(function ($genre) use ($totalBorrows) {
                return [
                    'name' => $genre['genre_name'],
                    'borrow_count' => $genre['borrow_count'],
                    'percentage' => $totalBorrows > 0 ? round(($genre['borrow_count'] / $totalBorrows) * 100, 2) : 0,
                    'books' => $genre['books']
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'total_borrows' => $totalBorrows,
                    'genres' => $genres
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate overdue books report
     */
    public function overdueBooks(Request $request)
    {
        try {
            $date = $request->input('date'); // Format: YYYY-MM-DD
            
            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ngày để tạo báo cáo'
                ], 400);
            }

            $reportDate = Carbon::createFromFormat('Y-m-d', $date);

            // Get default borrow period (30 days if not found in THAMSO)
            $maxBorrowDays = 30;
            try {
                $quyDinh = DB::table('THAMSO')
                    ->where('TenThamSo', 'NgayMuonToiDa')
                    ->first();
                if ($quyDinh) {
                    $maxBorrowDays = (int) $quyDinh->GiaTri;
                }
            } catch (\Exception $e) {
                // Use default if THAMSO table doesn't exist or has issues
            }

            // Get all borrow records for processing
            $allBorrowRecords = DB::table('CHITIETPHIEUMUON as ctpm')
                ->join('PHIEUMUON as pm', 'ctpm.phieumuon_id', '=', 'pm.id')
                ->join('SACH as s', 'ctpm.sach_id', '=', 's.id')
                ->join('DOCGIA as dg', 'pm.docgia_id', '=', 'dg.id')
                ->select(
                    's.TenSach as book_title',
                    'dg.HoTen as reader_name',
                    'pm.NgayMuon as borrow_date',
                    'ctpm.NgayTra as return_date',
                    'ctpm.TienPhat as fine_amount'
                )
                ->get();

            // Process and filter overdue books
            $processedBooks = [];
            foreach ($allBorrowRecords as $book) {
                $borrowDate = Carbon::parse($book->borrow_date);
                $dueDate = $borrowDate->copy()->addDays($maxBorrowDays);
                
                $overdueDays = 0;
                $status = '';
                
                if ($book->return_date) {
                    $returnDate = Carbon::parse($book->return_date);
                    if ($returnDate->gt($dueDate)) {
                        $overdueDays = (int) $dueDate->diffInDays($returnDate); // Làm tròn thành số nguyên
                        $status = 'Đã trả (trễ)';
                        
                        // Chỉ hiển thị nếu ngày trả <= ngày báo cáo
                        if (!$returnDate->lte($reportDate)) {
                            $overdueDays = 0; // Không hiển thị nếu trả sau ngày báo cáo
                            $status = '';
                        }
                    }
                } else {
                    if ($reportDate->gt($dueDate)) {
                        $overdueDays = (int) $dueDate->diffInDays($reportDate); // Làm tròn thành số nguyên
                        $status = 'Chưa trả (quá hạn)';
                    }
                }
                
                if ($overdueDays > 0) {
                    // Tính tiền phạt dựa trên số ngày trễ (1000 đồng/ngày)
                    $calculatedFine = $overdueDays * 1000;
                    $actualFine = (float) ($book->fine_amount ?? 0);
                    
                    // Sử dụng tiền phạt tính toán nếu dữ liệu sai hoặc âm
                    $finalFine = ($actualFine > 0) ? $actualFine : $calculatedFine;
                    
                    $processedBooks[] = [
                        'book_title' => $book->book_title,
                        'reader_name' => $book->reader_name,
                        'borrow_date' => $book->borrow_date,
                        'overdue_days' => $overdueDays,
                        'status' => $status,
                        'fine_amount' => round($finalFine) // Làm tròn tiền phạt
                    ];
                }
            }

            // Sort by overdue days descending
            usort($processedBooks, function($a, $b) {
                return $b['overdue_days'] - $a['overdue_days'];
            });

            $totalFine = array_sum(array_column($processedBooks, 'fine_amount'));

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'overdue_books' => $processedBooks,
                    'total_overdue' => count($processedBooks),
                    'total_fine' => $totalFine
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export genre statistics to Excel
     */
    public function exportGenreStatistics(Request $request)
    {
        try {
            $month = $request->input('month');
            $year = $request->input('year');
            
            if (!$month || !$year) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn tháng và năm để xuất báo cáo'
                ], 400);
            }

            // Get the data first
            $response = $this->genreStatistics($request);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return $response;
            }

            $reportData = $data['data'];

            // Use Excel export service
            return $this->excelExportService->exportGenreStatistics($reportData, $month, $year);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export overdue books to Excel
     */
    public function exportOverdueBooks(Request $request)
    {
        try {
            $date = $request->input('date');
            
            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ngày để xuất báo cáo'
                ], 400);
            }

            // Get the data first
            $response = $this->overdueBooks($request);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return $response;
            }

            $reportData = $data['data'];

            // Use Excel export service
            return $this->excelExportService->exportOverdueBooks($reportData, $date);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xuất báo cáo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug function to understand overdue books logic
     */
    public function debugOverdueBooks(Request $request)
    {
        try {
            $date = $request->input('date', '2025-07-04'); // Current date as default
            $reportDate = Carbon::createFromFormat('Y-m-d', $date);

            // Get borrow period
            $maxBorrowDays = 30;
            $quyDinh = DB::table('THAMSO')
                ->where('TenThamSo', 'NgayMuonToiDa')
                ->first();
            if ($quyDinh) {
                $maxBorrowDays = (int) $quyDinh->GiaTri;
            }

            // Get all borrow records for debugging
            $allBooks = DB::table('CHITIETPHIEUMUON as ctpm')
                ->join('PHIEUMUON as pm', 'ctpm.phieumuon_id', '=', 'pm.id')
                ->join('SACH as s', 'ctpm.sach_id', '=', 's.id')
                ->join('DOCGIA as dg', 'pm.docgia_id', '=', 'dg.id')
                ->select(
                    's.TenSach as book_title',
                    'dg.HoTen as reader_name',
                    'pm.NgayMuon as borrow_date',
                    'ctpm.NgayTra as return_date',
                    'ctpm.TienPhat as fine_amount',
                    'pm.id as borrow_id',
                    'ctpm.id as detail_id'
                )
                ->get();

            $debugInfo = [];
            $overdueBooks = [];

            foreach ($allBooks as $book) {
                $borrowDate = Carbon::parse($book->borrow_date);
                $dueDate = $borrowDate->copy()->addDays($maxBorrowDays);
                
                $debug = [
                    'book_title' => $book->book_title,
                    'reader_name' => $book->reader_name,
                    'borrow_date' => $book->borrow_date,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'return_date' => $book->return_date,
                    'report_date' => $reportDate->format('Y-m-d'),
                    'max_borrow_days' => $maxBorrowDays,
                    'is_returned' => !is_null($book->return_date),
                    'status' => '',
                    'overdue_days' => 0,
                    'should_show' => false,
                    'original_fine' => (float) ($book->fine_amount ?? 0),
                    'calculated_fine' => 0,
                    'final_fine' => 0
                ];

                if ($book->return_date) {
                    $returnDate = Carbon::parse($book->return_date);
                    $debug['return_date_parsed'] = $returnDate->format('Y-m-d');
                    
                    if ($returnDate->gt($dueDate)) {
                        $debug['overdue_days'] = (int) $dueDate->diffInDays($returnDate); // Sửa thứ tự và làm tròn
                        $debug['status'] = 'Đã trả (trễ)';
                        
                        // Tính tiền phạt
                        $debug['calculated_fine'] = $debug['overdue_days'] * 1000;
                        $debug['final_fine'] = ($debug['original_fine'] > 0) ? $debug['original_fine'] : $debug['calculated_fine'];
                        $debug['final_fine'] = round($debug['final_fine']);
                        
                        // Chỉ hiển thị nếu ngày trả <= ngày báo cáo
                        if ($returnDate->lte($reportDate)) {
                            $debug['should_show'] = true;
                            $overdueBooks[] = $debug;
                        } else {
                            $debug['note'] = 'Trả trễ nhưng ngày trả > ngày báo cáo';
                        }
                    } else {
                        $debug['status'] = 'Đã trả (đúng hạn)';
                        $debug['note'] = 'Trả đúng hạn hoặc sớm';
                    }
                } else {
                    // Chưa trả
                    if ($reportDate->gt($dueDate)) {
                        $debug['overdue_days'] = (int) $dueDate->diffInDays($reportDate); // Sửa thứ tự và làm tròn
                        $debug['status'] = 'Chưa trả (quá hạn)';
                        
                        // Tính tiền phạt
                        $debug['calculated_fine'] = $debug['overdue_days'] * 1000;
                        $debug['final_fine'] = ($debug['original_fine'] > 0) ? $debug['original_fine'] : $debug['calculated_fine'];
                        $debug['final_fine'] = round($debug['final_fine']);
                        
                        $debug['should_show'] = true;
                        $overdueBooks[] = $debug;
                    } else {
                        $debug['status'] = 'Chưa trả (chưa quá hạn)';
                        $debug['note'] = 'Chưa quá hạn vào ngày báo cáo';
                    }
                }
                
                $debugInfo[] = $debug;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'report_date' => $date,
                    'max_borrow_days' => $maxBorrowDays,
                    'total_books' => count($allBooks),
                    'overdue_books_count' => count($overdueBooks),
                    'all_books_debug' => $debugInfo,
                    'overdue_books' => $overdueBooks,
                    'logic_explanation' => [
                        'Sách hiển thị trong báo cáo khi:',
                        '1. Đã trả + Ngày trả > Ngày hết hạn + Ngày trả <= Ngày báo cáo',
                        '2. Chưa trả + Ngày báo cáo > Ngày hết hạn',
                        'Ngày hết hạn = Ngày mượn + ' . $maxBorrowDays . ' ngày'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi debug: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compare overdue books results for debugging
     */
    public function compareOverdueResults(Request $request)
    {
        try {
            $date = $request->input('date', '2025-07-04');
            
            // Get results from main overdueBooks function
            $mainResponse = $this->overdueBooks($request);
            $mainData = json_decode($mainResponse->getContent(), true);
            
            // Get results from debug function
            $debugResponse = $this->debugOverdueBooks($request);
            $debugData = json_decode($debugResponse->getContent(), true);
            
            return response()->json([
                'success' => true,
                'date' => $date,
                'main_function_count' => $mainData['success'] ? count($mainData['data']['overdue_books']) : 0,
                'debug_function_count' => $debugData['success'] ? count($debugData['data']['overdue_books']) : 0,
                'main_results' => $mainData['success'] ? $mainData['data']['overdue_books'] : [],
                'debug_results' => $debugData['success'] ? $debugData['data']['overdue_books'] : [],
                'main_response' => $mainData,
                'debug_response' => $debugData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi so sánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for negative fines in database
     */
    public function checkNegativeFines()
    {
        try {
            // Find all records with negative fines
            $negativeFines = DB::table('CHITIETPHIEUMUON as ctpm')
                ->join('PHIEUMUON as pm', 'ctpm.phieumuon_id', '=', 'pm.id')
                ->join('SACH as s', 'ctpm.sach_id', '=', 's.id')
                ->where('ctpm.TienPhat', '<', 0)
                ->select(
                    'ctpm.id',
                    'pm.MaPhieu as phieu_code',
                    's.TenSach as book_title',
                    'ctpm.NgayTra as return_date',
                    'ctpm.TienPhat as old_fine',
                    'pm.NgayMuon as borrow_date'
                )
                ->get();

            $summary = [
                'total_records' => DB::table('CHITIETPHIEUMUON')->count(),
                'problematic_records' => $negativeFines->count(),
                'negative_fines_count' => $negativeFines->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'negative_fines' => $negativeFines,
                    'summary' => $summary
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix negative fines in database
     */
    public function fixNegativeFines()
    {
        try {
            DB::beginTransaction();

            // Get borrow duration
            $maxBorrowDays = 30;
            $quyDinh = DB::table('THAMSO')
                ->where('TenThamSo', 'NgayMuonToiDa')
                ->first();
            if ($quyDinh) {
                $maxBorrowDays = (int) $quyDinh->GiaTri;
            }

            // Find all records with negative fines
            $negativeFines = DB::table('CHITIETPHIEUMUON as ctpm')
                ->join('PHIEUMUON as pm', 'ctpm.phieumuon_id', '=', 'pm.id')
                ->join('SACH as s', 'ctpm.sach_id', '=', 's.id')
                ->where('ctpm.TienPhat', '<', 0)
                ->select(
                    'ctpm.id',
                    'pm.MaPhieu as phieu_code',
                    's.TenSach as book_title',
                    'ctpm.NgayTra as return_date',
                    'ctpm.TienPhat as old_fine',
                    'pm.NgayMuon as borrow_date'
                )
                ->get();

            $fixedRecords = [];
            $fixedCount = 0;

            foreach ($negativeFines as $record) {
                $newFine = 0;

                if ($record->return_date) {
                    // Calculate correct fine
                    $borrowDate = Carbon::parse($record->borrow_date);
                    $returnDate = Carbon::parse($record->return_date);
                    $dueDate = $borrowDate->copy()->addDays($maxBorrowDays);

                    if ($returnDate->gt($dueDate)) {
                        $overdueDays = $dueDate->diffInDays($returnDate);
                        $newFine = $overdueDays * 1000;
                    }
                }

                // Update the record
                DB::table('CHITIETPHIEUMUON')
                    ->where('id', $record->id)
                    ->update(['TienPhat' => $newFine]);

                $fixedRecords[] = [
                    'id' => $record->id,
                    'phieu_code' => $record->phieu_code,
                    'book_title' => $record->book_title,
                    'return_date' => $record->return_date,
                    'old_fine' => $record->old_fine,
                    'new_fine' => $newFine
                ];

                $fixedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã sửa {$fixedCount} bản ghi tiền phạt âm",
                'data' => [
                    'negative_fines' => $fixedRecords,
                    'summary' => [
                        'total_records' => DB::table('CHITIETPHIEUMUON')->count(),
                        'problematic_records' => $negativeFines->count(),
                        'fixed_records' => $fixedCount
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi sửa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate all fines
     */
    public function recalculateAllFines()
    {
        try {
            DB::beginTransaction();

            // Get borrow duration
            $maxBorrowDays = 30;
            $quyDinh = DB::table('THAMSO')
                ->where('TenThamSo', 'NgayMuonToiDa')
                ->first();
            if ($quyDinh) {
                $maxBorrowDays = (int) $quyDinh->GiaTri;
            }

            // Get all returned books
            $returnedBooks = DB::table('CHITIETPHIEUMUON as ctpm')
                ->join('PHIEUMUON as pm', 'ctpm.phieumuon_id', '=', 'pm.id')
                ->whereNotNull('ctpm.NgayTra')
                ->select(
                    'ctpm.id',
                    'pm.NgayMuon as borrow_date',
                    'ctpm.NgayTra as return_date',
                    'ctpm.TienPhat as old_fine'
                )
                ->get();

            $updatedCount = 0;
            $updatedRecords = [];

            foreach ($returnedBooks as $record) {
                $borrowDate = Carbon::parse($record->borrow_date);
                $returnDate = Carbon::parse($record->return_date);
                $dueDate = $borrowDate->copy()->addDays($maxBorrowDays);

                $newFine = 0;
                if ($returnDate->gt($dueDate)) {
                    $overdueDays = $dueDate->diffInDays($returnDate);
                    $newFine = $overdueDays * 1000;
                }

                // Only update if fine changed
                if ($newFine != $record->old_fine) {
                    DB::table('CHITIETPHIEUMUON')
                        ->where('id', $record->id)
                        ->update(['TienPhat' => $newFine]);

                    $updatedRecords[] = [
                        'id' => $record->id,
                        'old_fine' => $record->old_fine,
                        'new_fine' => $newFine
                    ];

                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã tính lại {$updatedCount} bản ghi tiền phạt",
                'data' => [
                    'summary' => [
                        'total_returned_books' => $returnedBooks->count(),
                        'updated_records' => $updatedCount,
                        'max_borrow_days' => $maxBorrowDays
                    ],
                    'updated_records' => array_slice($updatedRecords, 0, 10) // Show first 10 for display
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tính lại: ' . $e->getMessage()
            ], 500);
        }
    }
}
