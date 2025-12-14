<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

use App\Models\PhieuMuon;
use App\Models\DocGia;
use App\Models\Sach;
use App\Models\ChiTietPhieuMuon;
use App\Models\QuyDinh;
use App\Models\PhieuThuTienPhat;

class PhieuMuonController extends Controller
{

    private function validateBorrowRequest(Request $request): array
    {
        $data = $request->all();
        if (empty($data) && $request->isJson()) {
            $data = $request->json()->all();
        }

        // Basic validation with more detailed error messages
        if (!isset($data['docgia_id']) || empty($data['docgia_id'])) {
            Log::error('Missing docgia_id', ['data' => $data]);
            throw new Exception('Vui lòng chọn độc giả. (docgia_id missing)');
        }
        
        if (!isset($data['sach_ids']) || !is_array($data['sach_ids']) || empty($data['sach_ids'])) {
            Log::error('Missing or invalid sach_ids', [
                'isset' => isset($data['sach_ids']),
                'is_array' => isset($data['sach_ids']) ? is_array($data['sach_ids']) : false,
                'empty' => isset($data['sach_ids']) ? empty($data['sach_ids']) : true,
                'value' => $data['sach_ids'] ?? 'NOT_SET'
            ]);
            throw new Exception('Vui lòng chọn ít nhất một cuốn sách. (sach_ids missing/invalid)');
        }

        // Validate borrow_date if provided
        $borrowDate = null;
        if (isset($data['borrow_date']) && !empty($data['borrow_date'])) {
            try {
                $borrowDate = Carbon::parse($data['borrow_date']);
                // Check if borrow date is not in the future
                if ($borrowDate->isFuture()) {
                    throw new Exception('Ngày mượn không thể là ngày trong tương lai');
                }
            } catch (Exception $e) {
                throw new Exception('Ngày mượn không hợp lệ: ' . $e->getMessage());
            }
        } else {
            // Use current date if not provided
            $borrowDate = Carbon::now();
        }

        // Convert to proper types
        $docgiaId = (int) $data['docgia_id'];
        $sachIds = array_map('intval', $data['sach_ids']);
        
        if ($docgiaId <= 0) {
            throw new Exception('ID độc giả không hợp lệ');
        }
        
        if (empty($sachIds) || in_array(0, $sachIds)) {
            throw new Exception('Danh sách ID sách không hợp lệ');
        }
        
        // Check if reader exists
        $reader = DocGia::find($docgiaId);
        if (!$reader) {
            throw new Exception('Độc giả không tồn tại trong hệ thống');
        }
        
        // Check if books exist
        foreach ($sachIds as $sachId) {
            $book = Sach::find($sachId);
            if (!$book) {
                throw new Exception("Sách ID {$sachId} không tồn tại trong hệ thống");
            }
        }
        
        return [
            'docgia_id' => $docgiaId,
            'sach_ids' => $sachIds,
            'borrow_date' => $borrowDate
        ];
    }

    /**
     * Validate return request data
     */
    private function validateReturnRequest(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'sach_ids' => 'required|array|min:1',
            'sach_ids.*' => 'integer|exists:SACH,id',
            'book_damages' => 'sometimes|array',
            'book_damages.*' => 'sometimes|numeric|min:0', // Số tiền đền bù (thủ thư nhập)
            'book_statuses' => 'sometimes|array', 
            'book_statuses.*' => 'sometimes|integer|in:1,3,4', // 1=tốt, 3=hỏng, 4=mất
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        return $validator->validated();
    }

    /**
     * Validate extend request data
     */
    private function validateExtendRequest(Request $request): array
    {
        $maxExtendDays = 7; // Giá trị cố định: 7 ngày
        
        $validator = Validator::make($request->all(), [
            'sach_ids' => 'required|array|min:1',
            'sach_ids.*' => 'integer|exists:SACH,id',
            'extend_days' => 'required|integer|min:1|max:' . $maxExtendDays,
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
        }

        return $validator->validated();
    }

    /**
     * Check if reader can borrow books
     */
    private function checkReaderBorrowEligibility(int $docgiaId, array $sachIds): void
    {
        $docGia = DocGia::findOrFail($docgiaId);
        
        // 1. Check reader age
        $minAge = QuyDinh::getMinAge();
        $maxAge = QuyDinh::getMaxAge();
        $age = Carbon::parse($docGia->NgaySinh)->age;
        
        if ($age < $minAge || $age > $maxAge) {
            throw new Exception("Độc giả phải từ {$minAge} đến {$maxAge} tuổi");
        }

        // 2. Check card validity - use NgayHetHan from DocGia table
        $cardExpiry = Carbon::parse($docGia->NgayHetHan);
        $now = Carbon::now();
        
        // Card is expired if current date is AFTER the expiry date
        if ($now->gt($cardExpiry)) {
            throw new Exception('Thẻ độc giả đã hết hạn vào ngày ' . $cardExpiry->format('d/m/Y'));
        }

        // 3. Check for overdue books
        $overdueBooks = ChiTietPhieuMuon::whereHas('phieuMuon', function($query) use ($docgiaId) {
            $query->where('docgia_id', $docgiaId);
        })
        ->whereNull('NgayTra')
        ->whereHas('phieuMuon', function($query) {
            $query->where('NgayHenTra', '<', Carbon::now());
        })
        ->count();

        if ($overdueBooks > 0) {
            throw new Exception("Độc giả có sách mượn quá hạn. Vui lòng trả sách quá hạn trước khi mượn sách mới.");
        }

        // 4. Check current borrowed books count
        $currentBorrowedCount = ChiTietPhieuMuon::whereHas('phieuMuon', function($query) use ($docgiaId) {
            $query->where('docgia_id', $docgiaId);
        })->whereNull('NgayTra')->count();

        $maxBooksPerBorrow = QuyDinh::getMaxBooksPerBorrow();
        $totalAfterBorrow = $currentBorrowedCount + count($sachIds);

        if ($totalAfterBorrow > $maxBooksPerBorrow) {
            throw new Exception("Không thể mượn quá {$maxBooksPerBorrow} cuốn sách cùng lúc");
        }

        // 5. Check borrow duration limit
        $borrowDurationDays = QuyDinh::getBorrowDurationDays();
        $recentBorrows = PhieuMuon::where('docgia_id', $docgiaId)
            ->where('NgayMuon', '>=', Carbon::now()->subDays($borrowDurationDays))
            ->withCount(['chiTietPhieuMuon' => function($query) {
                $query->whereNull('NgayTra');
            }])
            ->get()
            ->sum('chi_tiet_phieu_muon_count');

        $totalBooksInPeriod = $recentBorrows + count($sachIds);
        
        if ($totalBooksInPeriod > $maxBooksPerBorrow) {
            throw new Exception("Trong {$borrowDurationDays} ngày gần đây, độc giả chỉ được mượn tối đa {$maxBooksPerBorrow} cuốn sách");
        }
    }

    /**
     * Check book availability
     */
    private function checkBookAvailability(array $sachIds): void
    {
        $unavailableBooks = [];
        $bookPublicationYears = QuyDinh::getBookPublicationYears();
        $currentYear = Carbon::now()->year;

        foreach ($sachIds as $sachId) {
            $sach = Sach::findOrFail($sachId);

            // 1. Check publication year
            if (($currentYear - $sach->NamXuatBan) > $bookPublicationYears) {
                $unavailableBooks[] = "Sách '{$sach->TenSach}' đã quá {$bookPublicationYears} năm xuất bản";
                continue;
            }

            // 2. Check if book is available (TinhTrang = 1 means available)
            if ($sach->TinhTrang != Sach::TINH_TRANG_CO_SAN) {
                $unavailableBooks[] = "Sách '{$sach->TenSach}' hiện không có sẵn";
                continue;
            }

            // 3. Double-check: Verify no one is currently borrowing this book
            $currentlyBorrowed = ChiTietPhieuMuon::where('sach_id', $sachId)
                ->whereNull('NgayTra')
                ->exists();

            if ($currentlyBorrowed) {
                $unavailableBooks[] = "Sách '{$sach->TenSach}' đang được mượn bởi độc giả khác";
            }
        }

        if (!empty($unavailableBooks)) {
            throw new Exception('Một số sách không thể mượn: ' . implode(', ', $unavailableBooks));
        }
    }

    /**
     * Calculate due date
     */
    private function calculateDueDate(Carbon $borrowDate): Carbon
    {
        $borrowDurationDays = QuyDinh::getBorrowDurationDays();
        return $borrowDate->copy()->addDays($borrowDurationDays);
    }

    /**
     * Calculate fine amount
     */
    private function calculateFineAmount(Carbon $dueDate, Carbon $returnDate): int
    {
        if ($returnDate->lte($dueDate)) {
            return 0;
        }

        $daysLate = (int) $dueDate->diffInDays($returnDate);
        $finePerDay = 1000; // 1,000 VND per day per book

        return $daysLate * $finePerDay;
    }

    /**
     * Calculate compensation for damaged/lost books
     */
    private function calculateCompensation(int $sachId, int $bookStatus): int
    {
        $book = Sach::find($sachId);
        if (!$book) {
            return 0;
        }
        
        $bookValue = $book->TriGia ?? 0;
        
        switch ($bookStatus) {
            case Sach::TINH_TRANG_HONG:
                return (int) ($bookValue * 0.5); // 50% giá trị sách
            case Sach::TINH_TRANG_BI_MAT:
                return (int) $bookValue; // 100% giá trị sách
            default:
                return 0;
        }
    }

    /**
     * Get book status text
     */
    private function getBookStatusText(int $status): string
    {
        switch ($status) {
            case Sach::TINH_TRANG_CO_SAN:
                return 'Có sẵn';
            case Sach::TINH_TRANG_HONG:
                return 'Hỏng';
            case Sach::TINH_TRANG_BI_MAT:
                return 'Bị mất';
            default:
                return 'Không xác định';
        }
    }

    /**
     * ==========================================
     * MAIN BUSINESS LOGIC METHODS
     * ==========================================
     */

    /**
     * Create a new borrow record
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. Validate request
            $validated = $this->validateBorrowRequest($request);
            $docgiaId = $validated['docgia_id'];
            $sachIds = $validated['sach_ids'];
            $borrowDate = $validated['borrow_date'];

            // 2. Business rules validation
            $this->checkReaderBorrowEligibility($docgiaId, $sachIds);
            $this->checkBookAvailability($sachIds);

            // 3. Create PhieuMuon
            $dueDate = $this->calculateDueDate($borrowDate);

            $phieuMuon = PhieuMuon::create([
                'MaPhieu' => PhieuMuon::generateMaPhieu(),
                'docgia_id' => $docgiaId,
                'NgayMuon' => $borrowDate,
                'NgayHenTra' => $dueDate,
            ]);

            // 4. Create ChiTietPhieuMuon records and update book status
            foreach ($sachIds as $sachId) {
                ChiTietPhieuMuon::create([
                    'phieumuon_id' => $phieuMuon->id,
                    'sach_id' => $sachId,
                ]);
                
                // Update book status to borrowed
                Sach::where('id', $sachId)->update(['TinhTrang' => Sach::TINH_TRANG_DANG_MUON]);
            }

            DB::commit();

            // 5. Load relationships for response
            $phieuMuon->load(['docGia', 'chiTietPhieuMuon.sach']);

            return response()->json([
                'success' => true,
                'message' => 'Tạo phiếu mượn thành công',
                'data' => $phieuMuon
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('=== BORROW RECORD CREATION FAILED ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo phiếu mượn: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update an existing borrow record
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            Log::info('=== BORROW RECORD UPDATE START ===');
            Log::info('Updating borrow record ID: ' . $id);
            Log::info('Request data: ', $request->all());

            // 1. Find existing borrow record
            $phieuMuon = PhieuMuon::with(['chiTietPhieuMuon.sach'])->findOrFail($id);
            
            // 2. Check if this is a returned record (all books are returned)
            $allBooksReturned = $phieuMuon->chiTietPhieuMuon->whereNull('NgayTra')->count() === 0;
            
            if ($allBooksReturned) {
                // For returned records, allow updating reader, borrow date, and books (if available)
                Log::info('This is a returned record - updating reader, dates, and books if available');
                
                $validated = $this->validateBorrowRequest($request);
                $docgiaId = $validated['docgia_id'];
                $newSachIds = $validated['sach_ids'];
                
                // Get borrow date from request
                $borrowDate = $request->get('borrow_date');
                if (!$borrowDate) {
                    throw new Exception('Ngày mượn không được để trống');
                }
                
                // Calculate new due date based on borrow date
                $dueDate = $this->calculateDueDate(Carbon::parse($borrowDate));
                
                // Get all books that were in this record (both returned and not returned)
                $allBookIds = $phieuMuon->chiTietPhieuMuon->pluck('sach_id')->toArray();
                
                // Find books to remove (currently in record but not in new selection)
                $booksToRemove = array_diff($allBookIds, $newSachIds);
                
                // Find books to add (in new selection but not currently in record)
                $booksToAdd = array_diff($newSachIds, $allBookIds);
                
                // Remove books that are no longer selected
                foreach ($booksToRemove as $sachId) {
                    $chiTiet = $phieuMuon->chiTietPhieuMuon()
                        ->where('sach_id', $sachId)
                        ->first();
                    
                    if ($chiTiet) {
                        // Delete the chi tiet record completely
                        $chiTiet->delete();
                        
                        // Update book status to available (only if it was returned)
                        if ($chiTiet->NgayTra) {
                            Sach::where('id', $sachId)->update(['TinhTrang' => Sach::TINH_TRANG_CO_SAN]);
                        }
                    }
                }
                
                // Add new books (only if they are available)
                foreach ($booksToAdd as $sachId) {
                    // Check if book is available
                    $book = Sach::find($sachId);
                    if (!$book) {
                        throw new Exception("Sách ID {$sachId} không tồn tại");
                    }
                    
                    // Check if book is not borrowed by others
                    $isBorrowedByOthers = ChiTietPhieuMuon::where('sach_id', $sachId)
                        ->whereNull('NgayTra')
                        ->where('phieumuon_id', '!=', $id)
                        ->exists();
                    
                    if ($isBorrowedByOthers) {
                        throw new Exception("Sách ID {$sachId} đang được mượn bởi phiếu khác");
                    }
                    
                    // Create new chi tiet record with return date (since this is a returned record)
                    ChiTietPhieuMuon::create([
                        'phieumuon_id' => $phieuMuon->id,
                        'sach_id' => $sachId,
                        'NgayTra' => Carbon::now(), // Mark as returned
                        'TienPhat' => 0, // No fine for new books
                        'TienDenBu' => 0, // No compensation for new books
                    ]);
                    
                    // Update book status to available (since it's marked as returned)
                    Sach::where('id', $sachId)->update(['TinhTrang' => Sach::TINH_TRANG_CO_SAN]);
                }
                
                // Update borrow record info
                $phieuMuon->update([
                    'docgia_id' => $docgiaId,
                    'NgayMuon' => $borrowDate,
                    'NgayHenTra' => $dueDate,
                ]);
                
                DB::commit();
                
                // Load updated relationships
                $phieuMuon->load(['docGia', 'chiTietPhieuMuon.sach']);
                
                Log::info('=== RETURNED BORROW RECORD UPDATE SUCCESS ===');
                
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật phiếu mượn đã trả thành công',
                    'data' => $phieuMuon
                ]);
            }
            
            // 3. For active records, proceed with normal update logic
            $validated = $this->validateBorrowRequest($request);
            $docgiaId = $validated['docgia_id'];
            $newSachIds = $validated['sach_ids'];
            
            // Get borrow date from request
            $borrowDate = $request->get('borrow_date');
            if (!$borrowDate) {
                throw new Exception('Ngày mượn không được để trống');
            }
            
            // Calculate new due date based on borrow date
            $dueDate = $this->calculateDueDate(Carbon::parse($borrowDate));
            
            // 4. Get current borrowed books in this record
            $currentBorrowedBookIds = $phieuMuon->chiTietPhieuMuon
                ->whereNull('NgayTra')
                ->pluck('sach_id')
                ->toArray();
            
            // 5. Find books to remove (currently borrowed but not in new selection)
            $booksToRemove = array_diff($currentBorrowedBookIds, $newSachIds);
            
            // 6. Find books to add (in new selection but not currently borrowed)
            $booksToAdd = array_diff($newSachIds, $currentBorrowedBookIds);
            
            // 7. Remove books that are no longer selected
            foreach ($booksToRemove as $sachId) {
                $chiTiet = $phieuMuon->chiTietPhieuMuon()
                    ->where('sach_id', $sachId)
                    ->whereNull('NgayTra')
                    ->first();
                
                if ($chiTiet) {
                    // Delete the chi tiet record completely
                    $chiTiet->delete();
                    
                    // Update book status to available
                    Sach::where('id', $sachId)->update(['TinhTrang' => Sach::TINH_TRANG_CO_SAN]);
                }
            }
            
            // 8. Add new books
            foreach ($booksToAdd as $sachId) {
                // Check if book is available
                $book = Sach::find($sachId);
                if (!$book) {
                    throw new Exception("Sách ID {$sachId} không tồn tại");
                }
                
                // Check if book is not borrowed by others
                $isBorrowedByOthers = ChiTietPhieuMuon::where('sach_id', $sachId)
                    ->whereNull('NgayTra')
                    ->where('phieumuon_id', '!=', $id)
                    ->exists();
                
                if ($isBorrowedByOthers) {
                    throw new Exception("Sách ID {$sachId} đang được mượn bởi phiếu khác");
                }
                
                // Create new chi tiet record
                ChiTietPhieuMuon::create([
                    'phieumuon_id' => $phieuMuon->id,
                    'sach_id' => $sachId,
                ]);
                
                // Update book status to borrowed
                Sach::where('id', $sachId)->update(['TinhTrang' => Sach::TINH_TRANG_DANG_MUON]);
            }
            
            // 9. Update borrow record info
            $phieuMuon->update([
                'docgia_id' => $docgiaId,
                'NgayMuon' => $borrowDate,
                'NgayHenTra' => $dueDate,
            ]);
            
            DB::commit();
            
            // 10. Load updated relationships
            $phieuMuon->load(['docGia', 'chiTietPhieuMuon.sach']);
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật phiếu mượn thành công',
                'data' => $phieuMuon
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('=== BORROW RECORD UPDATE FAILED - NOT FOUND ===');
            Log::error('Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Phiếu mượn không tồn tại'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('=== BORROW RECORD UPDATE FAILED ===');
            Log::error('Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cập nhật phiếu mượn: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Calculate return fines and compensation (preview)
     */
    public function calculateReturnFines(Request $request, int $phieuMuonId): JsonResponse
    {
        try {
            // 1. Validate request
            $validator = Validator::make($request->all(), [
                'sach_ids' => 'required|array|min:1',
                'sach_ids.*' => 'integer|exists:SACH,id',
                'book_statuses' => 'required|array',
                'book_statuses.*' => 'integer|in:1,3,4', // 1=có sẵn, 3=hỏng, 4=mất
            ]);

            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
            }

            $validated = $validator->validated();
            $sachIds = $validated['sach_ids'];
            $bookStatuses = $validated['book_statuses'];

            // 2. Find PhieuMuon
            $phieuMuon = PhieuMuon::with(['chiTietPhieuMuon.sach', 'docGia'])->findOrFail($phieuMuonId);
            
            // 3. Calculate fines and compensation
            $returnDate = Carbon::now();
            $totalLateFine = 0;
            $totalCompensation = 0;
            $bookDetails = [];

            foreach ($sachIds as $sachId) {
                $chiTiet = $phieuMuon->chiTietPhieuMuon()
                                   ->where('sach_id', $sachId)
                                   ->whereNull('NgayTra')
                                   ->first();

                if (!$chiTiet) {
                    throw new Exception("Sách ID {$sachId} không tìm thấy trong phiếu mượn hoặc đã được trả");
                }

                $book = $chiTiet->sach;
                $bookStatus = $bookStatuses[$sachId] ?? Sach::TINH_TRANG_CO_SAN;
                
                // Calculate late fine with details
                $dueDate = Carbon::parse($phieuMuon->NgayHenTra);
                $lateDays = 0;
                $lateFine = 0;
                $lateFineDetails = '';
                
                // Check if return date is after due date
                if ($returnDate->gt($dueDate)) {
                    $lateDays = (int) $dueDate->diffInDays($returnDate); // Ép kiểu thành số nguyên
                    $lateFine = $lateDays * 1000; // 1000 VND per day
                    $lateFineDetails = "Trễ {$lateDays} ngày × 1.000 VNĐ/ngày = " . number_format($lateFine) . " VNĐ";
                } else {
                    $lateFineDetails = "Trả đúng hạn - Không phạt";
                }
                
                // Calculate compensation with details
                $compensation = $this->calculateCompensation($sachId, $bookStatus);
                $compensationDetails = '';
                
                if ($bookStatus == Sach::TINH_TRANG_HONG) {
                    $bookValue = $book ? $book->TriGia : 0;
                    $compensationDetails = "Sách hỏng - Đền bù 50% giá trị = " . number_format($compensation) . " VNĐ";
                } else if ($bookStatus == Sach::TINH_TRANG_BI_MAT) {
                    $bookValue = $book ? $book->TriGia : 0;
                    $compensationDetails = "Sách mất - Đền bù 100% giá trị = " . number_format($compensation) . " VNĐ";
                } else {
                    $compensationDetails = "Sách tốt - Không đền bù";
                }
                
                $totalLateFine += $lateFine;
                $totalCompensation += $compensation;
                
                $bookDetails[] = [
                    'sach_id' => $sachId,
                    'ten_sach' => $book ? $book->TenSach : 'N/A',
                    'gia_tri_sach' => $book ? $book->TriGia : 0,
                    'tinh_trang_moi' => $bookStatus,
                    'tinh_trang_text' => $this->getBookStatusText($bookStatus),
                    'tien_phat_tre' => $lateFine,
                    'tien_den_bu' => $compensation,
                    'tong_tien_phat' => $lateFine + $compensation,
                    'late_days' => $lateDays,
                    'late_fine_details' => $lateFineDetails,
                    'compensation_details' => $compensationDetails,
                ];
            }

            $totalFine = $totalLateFine + $totalCompensation;

            return response()->json([
                'success' => true,
                'data' => [
                    'phieu_muon' => [
                        'id' => $phieuMuon->id,
                        'ma_phieu' => $phieuMuon->MaPhieu,
                        'doc_gia' => $phieuMuon->docGia ? $phieuMuon->docGia->HoTen : 'N/A',
                        'ngay_muon' => $phieuMuon->NgayMuon->format('Y-m-d'),
                        'ngay_hen_tra' => $phieuMuon->NgayHenTra->format('Y-m-d'),
                        'ngay_tra' => $returnDate->format('Y-m-d'),
                    ],
                    'book_details' => $bookDetails,
                    'summary' => [
                        'total_late_fine' => $totalLateFine,
                        'total_compensation' => $totalCompensation,
                        'total_fine' => $totalFine,
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Error calculating return fines - NOT FOUND: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Phiếu mượn không tồn tại'
            ], 404);
        } catch (Exception $e) {
            Log::error('Error calculating return fines: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tính toán phạt: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Return books with fines and compensation
     */
    public function returnBooks(Request $request, int $phieuMuonId): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. Validate request
            $validator = Validator::make($request->all(), [
                'sach_ids' => 'required|array|min:1',
                'sach_ids.*' => 'integer|exists:SACH,id',
                'book_statuses' => 'required|array',
                'book_statuses.*' => 'integer|in:1,3,4', // 1=có sẵn, 3=hỏng, 4=mất
            ]);

            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
            }

            $validated = $validator->validated();
            $sachIds = $validated['sach_ids'];
            $bookStatuses = $validated['book_statuses'];

            // 2. Find PhieuMuon
            $phieuMuon = PhieuMuon::with(['chiTietPhieuMuon.sach', 'docGia'])->findOrFail($phieuMuonId);

            // 3. Process returns
            $returnDate = Carbon::now();
            $totalLateFine = 0;
            $totalCompensation = 0;
            $returnedBooks = [];

            foreach ($sachIds as $sachId) {
                $chiTiet = $phieuMuon->chiTietPhieuMuon()
                                   ->where('sach_id', $sachId)
                                   ->whereNull('NgayTra')
                                   ->first();

                if (!$chiTiet) {
                    throw new Exception("Sách ID {$sachId} không tìm thấy trong phiếu mượn hoặc đã được trả");
                }

                $book = $chiTiet->sach;
                $bookStatus = $bookStatuses[$sachId] ?? Sach::TINH_TRANG_CO_SAN;
                
                // Calculate late fine
                $lateFine = $this->calculateFineAmount(Carbon::parse($phieuMuon->NgayHenTra), $returnDate);
                
                // Calculate compensation
                $compensation = $this->calculateCompensation($sachId, $bookStatus);
                
                $totalLateFine += $lateFine;
                $totalCompensation += $compensation;
                $totalBookFine = $lateFine + $compensation;

                // Update return info
                $chiTiet->update([
                    'NgayTra' => $returnDate,
                    'TienPhat' => (int) $lateFine,        // Phí trễ hạn
                    'TienDenBu' => (int) $compensation,   // Phí đền bù sách hỏng/mất
                ]);
                
                // Update book status based on condition
                // If book is returned in good condition, mark as available
                // If damaged or lost, keep the status as is
                $finalStatus = $bookStatus;
                if ($bookStatus == Sach::TINH_TRANG_CO_SAN) {
                    $finalStatus = Sach::TINH_TRANG_CO_SAN; // Available
                } else if ($bookStatus == Sach::TINH_TRANG_HONG) {
                    $finalStatus = Sach::TINH_TRANG_HONG; // Damaged
                } else if ($bookStatus == Sach::TINH_TRANG_BI_MAT) {
                    $finalStatus = Sach::TINH_TRANG_BI_MAT; // Lost
                }
                
                Sach::where('id', $sachId)->update(['TinhTrang' => $finalStatus]);

                $returnedBooks[] = [
                    'sach_id' => $sachId,
                    'ten_sach' => $book ? $book->TenSach : 'N/A',
                    'gia_tri_sach' => $book ? $book->TriGia : 0,
                    'tinh_trang_moi' => $bookStatus,
                    'tinh_trang_text' => $this->getBookStatusText($bookStatus),
                    'tien_phat_tre' => $lateFine,
                    'tien_den_bu' => $compensation,
                    'tong_tien_phat' => $totalBookFine,
                ];
            }

            $totalFine = $totalLateFine + $totalCompensation;

            // 4. Update reader's debt
            if ($totalFine > 0 && $phieuMuon->docGia) {
                $docGia = $phieuMuon->docGia;
                $currentDebt = (int) ($docGia->TongNo ?? 0);
                $newDebt = $currentDebt + (int) $totalFine;
                
                $docGia->update(['TongNo' => $newDebt]);
                
                Log::info("Updated reader debt", [
                    'reader_id' => $docGia->id,
                    'reader_name' => $docGia->HoTen,
                    'old_debt' => $currentDebt,
                    'new_debt' => $newDebt,
                    'added_fine' => $totalFine
                ]);
            }

            // 5. Removed automatic PhieuThuTienPhat creation
            // Phiếu thu tiền phạt sẽ được tạo thủ công sau khi trả sách
            $phieuThuTienPhat = null;
            // if ($totalFine > 0) {
            //     $phieuThuTienPhat = PhieuThuTienPhat::create([
            //         'MaPhieu' => PhieuThuTienPhat::generateMaPhieu(),
            //         'docgia_id' => $phieuMuon->docgia_id,
            //         'SoTienNop' => (int) $totalFine,
            //     ]);
            // }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trả sách thành công',
                'data' => [
                    'phieu_muon' => [
                        'id' => $phieuMuon->id,
                        'ma_phieu' => $phieuMuon->MaPhieu,
                        'doc_gia' => $phieuMuon->docGia ? $phieuMuon->docGia->HoTen : 'N/A',
                    ],
                    'returned_books' => $returnedBooks,
                    'summary' => [
                        'total_late_fine' => $totalLateFine,
                        'total_compensation' => $totalCompensation,
                        'total_fine' => $totalFine,
                    ],
                    'phieu_thu_tien_phat' => $phieuThuTienPhat,
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Error returning books - NOT FOUND: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Phiếu mượn không tồn tại'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error returning books: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi trả sách: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Extend borrow period
     */
    public function extendBorrow(Request $request, int $phieuMuonId): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. Validate request
            $validated = $this->validateExtendRequest($request);
            $sachIds = $validated['sach_ids'];
            $extendDays = $validated['extend_days'];

            // 2. Find PhieuMuon
            $phieuMuon = PhieuMuon::with(['chiTietPhieuMuon', 'docGia'])->findOrFail($phieuMuonId);

            // 3. Check extend eligibility
            $maxExtendDays = 7; // Giá trị cố định: 7 ngày
            if ($extendDays > $maxExtendDays) {
                throw new Exception("Chỉ được gia hạn tối đa {$maxExtendDays} ngày");
            }

            // 4. Process extensions
            $extendedBooks = [];
            foreach ($sachIds as $sachId) {
                $chiTiet = $phieuMuon->chiTietPhieuMuon()
                                   ->where('sach_id', $sachId)
                                   ->whereNull('NgayTra')
                                   ->first();

                if (!$chiTiet) {
                    throw new Exception("Sách ID {$sachId} không tìm thấy trong phiếu mượn hoặc đã được trả");
                }

                // Check if already extended
                if ($chiTiet->DaGiaHan) {
                    throw new Exception("Sách ID {$sachId} đã được gia hạn trước đó");
                }

                // Check if overdue
                if (Carbon::parse($chiTiet->NgayHenTra)->lt(Carbon::now())) {
                    throw new Exception("Không thể gia hạn sách đã quá hạn");
                }

                // Update due date
                $newDueDate = Carbon::parse($chiTiet->NgayHenTra)->addDays($extendDays);
                $chiTiet->update([
                    'NgayHenTra' => $newDueDate,
                    'DaGiaHan' => true,
                ]);

                $extendedBooks[] = [
                    'sach_id' => $sachId,
                    'new_due_date' => $newDueDate->toDateString(),
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gia hạn thành công',
                'data' => [
                    'extended_books' => $extendedBooks,
                    'extend_days' => $extendDays,
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Error extending borrow - NOT FOUND: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Phiếu mượn không tồn tại'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error extending borrow: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi gia hạn: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Extend entire borrow record
     */
    public function extendAll(Request $request, int $phieuMuonId): JsonResponse
    {
        try {
            DB::beginTransaction();

            // 1. Validate request
            $validator = Validator::make($request->all(), [
                'new_due_date' => 'required|date|after:today',
                'extend_days' => 'required|integer|min:1|max:30',
            ]);

            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . implode(', ', $validator->errors()->all()));
            }

            $validated = $validator->validated();
            $newDueDate = Carbon::parse($validated['new_due_date']);
            $extendDays = $validated['extend_days'];

            // 2. Find PhieuMuon
            $phieuMuon = PhieuMuon::with(['chiTietPhieuMuon.sach'])->findOrFail($phieuMuonId);

            // 3. Check extend eligibility
            $maxExtendDays = 7; // Giá trị cố định: 7 ngày
            if ($extendDays > $maxExtendDays) {
                throw new Exception("Chỉ được gia hạn tối đa {$maxExtendDays} ngày");
            }

            // 4. Check if any book is overdue
            $currentDate = Carbon::now();
            foreach ($phieuMuon->chiTietPhieuMuon as $chiTiet) {
                if (is_null($chiTiet->NgayTra) && Carbon::parse($phieuMuon->NgayHenTra)->lt($currentDate)) {
                    throw new Exception("Không thể gia hạn phiếu mượn đã quá hạn");
                }
            }

            // 5. Update due date for the entire borrow record
            $phieuMuon->update([
                'NgayHenTra' => $newDueDate,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gia hạn phiếu mượn thành công',
                'data' => [
                    'ma_phieu' => $phieuMuon->MaPhieuMuon,
                    'new_due_date' => $newDueDate->toDateString(),
                    'extend_days' => $extendDays,
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Error extending borrow record - NOT FOUND: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Phiếu mượn không tồn tại'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error extending borrow record: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi gia hạn: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * ==========================================
     * API ENDPOINTS
     * ==========================================
     */

    /**
     * Get all borrow records with filtering (no pagination)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search');
            $status = $request->get('status'); // 'active', 'completed', 'overdue'

            $query = PhieuMuon::with(['docGia', 'chiTietPhieuMuon.sach']);

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('MaPhieu', 'like', "%{$search}%")
                      ->orWhereHas('docGia', function($dq) use ($search) {
                          $dq->where('HoTen', 'like', "%{$search}%");
                      });
                });
            }

            // Apply status filter
            if ($status) {
                switch ($status) {
                    case 'active':
                        $query->whereHas('chiTietPhieuMuon', function($q) {
                            $q->whereNull('NgayTra');
                        });
                        break;
                    case 'completed':
                        $query->whereDoesntHave('chiTietPhieuMuon', function($q) {
                            $q->whereNull('NgayTra');
                        });
                        break;
                    case 'overdue':
                        $query->whereHas('chiTietPhieuMuon', function($q) {
                            $q->whereNull('NgayTra')
                              ->where('NgayHenTra', '<', Carbon::now());
                        });
                        break;
                }
            }

            $phieuMuons = $query->latest('id')->get();

            // Format data for frontend
            $formattedData = $phieuMuons->map(function ($phieuMuon) {
                $books = $phieuMuon->chiTietPhieuMuon->map(function ($chiTiet) {
                    $sach = $chiTiet->sach;
                    $tacGiaName = $sach && $sach->tacGia ? $sach->tacGia->TenTacGia : 'Chưa có tác giả';
                    
                    return [
                        'id' => $sach ? $sach->id : null,
                        'code' => $sach ? $sach->MaSach : 'N/A',
                        'title' => $sach ? $sach->TenSach : 'N/A',
                        'author' => $tacGiaName,
                        'year' => $sach ? $sach->NamXuatBan : null,
                        'return_date' => $chiTiet->NgayTra ? $chiTiet->NgayTra->format('Y-m-d') : null,
                        'fine_amount' => $chiTiet->TienPhat ?? 0,
                        'compensation_amount' => $chiTiet->TienDenBu ?? 0,
                        'is_returned' => !is_null($chiTiet->NgayTra),
                    ];
                });
                
                $docGia = $phieuMuon->docGia;
                $totalBorrowedBooks = $phieuMuon->chiTietPhieuMuon->whereNull('NgayTra')->count();
                $returnedBooks = $phieuMuon->chiTietPhieuMuon->whereNotNull('NgayTra')->count();
                $isCompleted = $totalBorrowedBooks === 0; // Completed when no books are currently borrowed
                
                // Check if overdue
                $isOverdue = false;
                if ($phieuMuon->NgayHenTra && !$isCompleted) {
                    $isOverdue = Carbon::now()->gt(Carbon::parse($phieuMuon->NgayHenTra));
                }
                
                return [
                    'id' => $phieuMuon->id,
                    'code' => $phieuMuon->MaPhieu,  // Frontend expects 'code'
                    'ma_phieu' => $phieuMuon->MaPhieu,
                    'docgia_id' => $docGia ? $docGia->id : null,
                    'reader_name' => $docGia ? $docGia->HoTen : 'N/A',  // Frontend expects 'reader_name'
                    'reader_email' => $docGia ? $docGia->Email : 'N/A',  // Frontend expects 'reader_email'
                    'reader' => [
                        'id' => $docGia ? $docGia->id : null,
                        'name' => $docGia ? $docGia->HoTen : 'N/A',
                        'email' => $docGia ? $docGia->Email : 'N/A',
                    ],
                    'books' => $books,
                    'borrow_date' => $phieuMuon->NgayMuon ? $phieuMuon->NgayMuon->format('Y-m-d') : null,
                    'due_date' => $phieuMuon->NgayHenTra ? $phieuMuon->NgayHenTra->format('Y-m-d') : null,
                    'return_date' => null, // Will be filled by books data if needed
                    'total_books' => $totalBorrowedBooks,
                    'returned_books' => $returnedBooks,
                    'status' => $isCompleted ? 'returned' : ($isOverdue ? 'overdue' : 'active'),
                    'is_overdue' => $isOverdue,
                    'is_completed' => $isCompleted,
                    'fine_created' => false, // TODO: Check if fine already created
                    'created_at' => $phieuMuon->id, // Using ID instead of created_at
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching borrow records: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách phiếu mượn: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific borrow record
     */
    public function show(int $id): JsonResponse
    {
        try {
            $phieuMuon = PhieuMuon::with([
                'docGia',
                'chiTietPhieuMuon.sach.tacGia',
                'chiTietPhieuMuon.sach.theLoais'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $phieuMuon
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching borrow record: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy phiếu mượn'
            ], 404);
        }
    }

    /**
     * Get borrow records by reader
     */
    public function getByReader(int $docgiaId): JsonResponse
    {
        try {
            $phieuMuons = PhieuMuon::with([
                'chiTietPhieuMuon.sach'
            ])
            ->where('docgia_id', $docgiaId)
            ->latest('created_at')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $phieuMuons
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching reader borrow records: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy phiếu mượn của độc giả: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue borrow records
     */
    public function getOverdue(): JsonResponse
    {
        try {
            $overdueRecords = PhieuMuon::with(['docGia', 'chiTietPhieuMuon.sach'])
                ->whereHas('chiTietPhieuMuon', function($query) {
                    $query->whereNull('NgayTra')
                          ->where('NgayHenTra', '<', Carbon::now());
                })
                ->latest('id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $overdueRecords
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching overdue records: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách quá hạn: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a borrow record (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $phieuMuon = PhieuMuon::with(['chiTietPhieuMuon.sach'])->findOrFail($id);

            // Check if any books are still borrowed and update their status
            $activeBorrows = $phieuMuon->chiTietPhieuMuon()->whereNull('NgayTra')->get();
            
            if ($activeBorrows->count() > 0) {
                // Update book status to available for all borrowed books
                foreach ($activeBorrows as $chiTiet) {
                    if ($chiTiet->sach) {
                        $chiTiet->sach->markAsAvailable();
                    }
                }
            }

            // Delete the borrow record (cascade will handle details)
            $phieuMuon->delete();

            DB::commit();

            $message = 'Xóa phiếu mượn thành công';
            if ($activeBorrows->count() > 0) {
                $message = 'Xóa phiếu mượn thành công! Tình trạng sách đã được cập nhật về "Có sẵn".';
            } else {
                $message = 'Xóa phiếu mượn đã trả thành công!';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Error deleting borrow record - NOT FOUND: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Phiếu mượn không tồn tại'
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting borrow record: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xóa phiếu mượn: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all readers for edit modal (API) - no filtering
     */
    public function allReadersListApi(): JsonResponse
    {
        try {
            
            // Get all readers without filtering
            $readers = DocGia::select('id', 'HoTen', 'Email', 'NgayHetHan')
                ->orderBy('HoTen')
                ->get();
                
            $mappedReaders = $readers->map(function ($reader) {
                $cardExpiry = Carbon::parse($reader->NgayHetHan);
                $daysUntilExpiry = Carbon::now()->diffInDays($cardExpiry, false);
                $isExpired = $daysUntilExpiry < 0;
                
                $statusText = $isExpired ? 'Thẻ hết hạn' : 'Thẻ còn hạn ' . $daysUntilExpiry . ' ngày';
                
                return [
                    'id' => $reader->id,
                    'name' => $reader->HoTen,
                    'email' => $reader->Email,
                    'card_expiry' => $cardExpiry->format('Y-m-d'),
                    'days_until_expiry' => $daysUntilExpiry,
                    'is_expired' => $isExpired,
                    'display_name' => $reader->HoTen . ' (' . $reader->Email . ') - ' . $statusText
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $mappedReaders
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching all readers list: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách độc giả: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get readers list for dropdown (API) - only eligible readers
     */
    public function readersListApi(): JsonResponse
    {
        try {
            // Get readers with valid cards and no overdue books
            $readers = DocGia::select('id', 'HoTen', 'Email', 'NgayHetHan')
                ->where('NgayHetHan', '>', Carbon::now()) // Card not expired
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                          ->from('CHITIETPHIEUMUON')
                          ->join('PHIEUMUON', 'CHITIETPHIEUMUON.phieumuon_id', '=', 'PHIEUMUON.id')
                          ->whereRaw('PHIEUMUON.docgia_id = DOCGIA.id')
                          ->whereNull('CHITIETPHIEUMUON.NgayTra')
                          ->where('PHIEUMUON.NgayHenTra', '<', Carbon::now()); // Overdue books
                })
                ->orderBy('HoTen')
                ->get();
            
            $mappedReaders = $readers->map(function ($reader) {
                $cardExpiry = Carbon::parse($reader->NgayHetHan);
                $daysUntilExpiry = Carbon::now()->diffInDays($cardExpiry, false);
                
                return [
                    'id' => $reader->id,
                    'name' => $reader->HoTen,
                    'email' => $reader->Email,
                    'card_expiry' => $cardExpiry->format('Y-m-d'),
                    'days_until_expiry' => $daysUntilExpiry,
                    'display_name' => $reader->HoTen . ' (' . $reader->Email . ') - Thẻ còn hạn ' . $daysUntilExpiry . ' ngày'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $mappedReaders
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching readers list: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách độc giả: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get books list for dropdown (API) - only available books
     */
    public function booksListApi(): JsonResponse
    {
        try {
            // Get books that are available and not currently borrowed by anyone
            $books = Sach::with('tacGia')
                        ->select('id', 'MaSach', 'TenSach', 'NamXuatBan', 'TinhTrang', 'MaTacGia')
                        ->where('TinhTrang', Sach::TINH_TRANG_CO_SAN)
                        ->whereNotExists(function ($query) {
                            $query->select(\DB::raw(1))
                                  ->from('CHITIETPHIEUMUON')
                                  ->whereRaw('CHITIETPHIEUMUON.sach_id = SACH.id')
                                  ->whereNull('CHITIETPHIEUMUON.NgayTra');
                        })
                        ->get();
            
            $mappedBooks = $books->map(function ($book) {
                $authorName = $book->tacGia ? $book->tacGia->TenTacGia : 'Không có tác giả';
                
                return [
                    'id' => $book->id,
                    'title' => $book->TenSach,
                    'author' => $authorName,
                    'year' => $book->NamXuatBan,
                    'is_available' => true,
                    'display_name' => $book->TenSach . ' - ' . $authorName . ' (' . $book->NamXuatBan . ') - Có sẵn'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $mappedBooks
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching books list: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách sách: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get books list for edit modal (API) - available books + books borrowed in this record
     */
    public function editBooksListApi(Request $request): JsonResponse
    {
        try {
            $phieuMuonId = $request->get('phieu_muon_id');
            
            if (!$phieuMuonId) {
                throw new Exception('Thiếu ID phiếu mượn');
            }
            
            // Get the borrow record
            $phieuMuon = PhieuMuon::with(['chiTietPhieuMuon.sach.tacGia'])
                ->findOrFail($phieuMuonId);
            
            // Check if this is a returned record (all books are returned)
            $allBooksReturned = $phieuMuon->chiTietPhieuMuon->whereNull('NgayTra')->count() === 0;
            
            if ($allBooksReturned) {
                // For returned records, return all books that were in this record + available books
                $allBookIds = $phieuMuon->chiTietPhieuMuon->pluck('sach_id')->toArray();
                
                // Get books that were in this record
                $recordBooks = Sach::with('tacGia')
                    ->select('id', 'MaSach', 'TenSach', 'NamXuatBan', 'TinhTrang', 'MaTacGia')
                    ->whereIn('id', $allBookIds)
                    ->get();
                
                // Get available books (not borrowed by anyone)
                $availableBooks = Sach::with('tacGia')
                    ->select('id', 'MaSach', 'TenSach', 'NamXuatBan', 'TinhTrang', 'MaTacGia')
                    ->where('TinhTrang', Sach::TINH_TRANG_CO_SAN)
                    ->whereNotExists(function ($query) {
                        $query->select(\DB::raw(1))
                              ->from('CHITIETPHIEUMUON')
                              ->whereRaw('CHITIETPHIEUMUON.sach_id = SACH.id')
                              ->whereNull('CHITIETPHIEUMUON.NgayTra');
                    })
                    ->get();
                
                // Combine record books and available books
                $allBooks = $recordBooks->merge($availableBooks);
                
                $mappedBooks = $allBooks->map(function ($book) use ($allBookIds) {
                    $authorName = $book->tacGia ? $book->tacGia->TenTacGia : 'Không có tác giả';
                    
                    // Check if this book was in the original record
                    $wasInRecord = in_array($book->id, $allBookIds);
                    
                    if ($wasInRecord) {
                        return [
                            'id' => $book->id,
                            'title' => $book->TenSach,
                            'author' => $authorName,
                            'year' => $book->NamXuatBan,
                            'is_available' => false, // Not available for selection (already in record)
                            'is_borrowed_in_this_record' => true, // Was in this record
                            'is_returned_record' => true, // Flag to indicate this is a returned record
                            'display_name' => $book->TenSach . ' - ' . $authorName . ' (' . $book->NamXuatBan . ') - Đã trả (trong phiếu này)'
                        ];
                    } else {
                        return [
                            'id' => $book->id,
                            'title' => $book->TenSach,
                            'author' => $authorName,
                            'year' => $book->NamXuatBan,
                            'is_available' => true, // Available for selection
                            'is_borrowed_in_this_record' => false, // Not in this record
                            'is_returned_record' => true, // Flag to indicate this is a returned record
                            'display_name' => $book->TenSach . ' - ' . $authorName . ' (' . $book->NamXuatBan . ') - Có sẵn (có thể thêm)'
                        ];
                    }
                });

                return response()->json([
                    'success' => true,
                    'data' => $mappedBooks,
                    'is_returned_record' => true
                ]);
            }
            
            // For active records, proceed with normal logic
            // Get currently borrowed books in this record
            $currentBorrowedBookIds = $phieuMuon->chiTietPhieuMuon
                ->whereNull('NgayTra')
                ->pluck('sach_id')
                ->toArray();
            
            // Get available books (not borrowed by anyone)
            $availableBooks = Sach::with('tacGia')
                ->select('id', 'MaSach', 'TenSach', 'NamXuatBan', 'TinhTrang', 'MaTacGia')
                ->where('TinhTrang', Sach::TINH_TRANG_CO_SAN)
                ->whereNotExists(function ($query) {
                    $query->select(\DB::raw(1))
                          ->from('CHITIETPHIEUMUON')
                          ->whereRaw('CHITIETPHIEUMUON.sach_id = SACH.id')
                          ->whereNull('CHITIETPHIEUMUON.NgayTra');
                })
                ->get();
            
            // Get currently borrowed books in this record
            $currentBorrowedBooks = Sach::with('tacGia')
                ->select('id', 'MaSach', 'TenSach', 'NamXuatBan', 'TinhTrang', 'MaTacGia')
                ->whereIn('id', $currentBorrowedBookIds)
                ->get();
            
            // Combine available books and currently borrowed books in this record
            $allBooks = $availableBooks->merge($currentBorrowedBooks);
            
            $mappedBooks = $allBooks->map(function ($book) use ($currentBorrowedBookIds) {
                $authorName = $book->tacGia ? $book->tacGia->TenTacGia : 'Không có tác giả';
                
                // Check if this book is currently borrowed in this record
                $isBorrowedInThisRecord = in_array($book->id, $currentBorrowedBookIds);
                
                $statusText = $isBorrowedInThisRecord ? 'Đang mượn (trong phiếu này)' : 'Có sẵn';
                $isAvailable = !$isBorrowedInThisRecord && $book->TinhTrang == Sach::TINH_TRANG_CO_SAN;
                
                return [
                    'id' => $book->id,
                    'title' => $book->TenSach,
                    'author' => $authorName,
                    'year' => $book->NamXuatBan,
                    'is_available' => $isAvailable,
                    'is_borrowed_in_this_record' => $isBorrowedInThisRecord,
                    'is_returned_record' => false,
                    'display_name' => $book->TenSach . ' - ' . $authorName . ' (' . $book->NamXuatBan . ') - ' . $statusText
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $mappedBooks,
                'is_returned_record' => false
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching edit books list: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách sách: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * WEB VIEW METHODS
     * ==========================================
     */

    /**
     * Show borrow records page
     */
    public function showBorrowRecordsPage()
    {
        // Get regulation values for the view
        $maxBorrowDays = QuyDinh::getBorrowDurationDays();
        $maxBooksPerBorrow = QuyDinh::getMaxBooksPerBorrow();
        
        return view('borrow-records', compact('maxBorrowDays', 'maxBooksPerBorrow'));
    }
}
