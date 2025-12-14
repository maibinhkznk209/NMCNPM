<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\SachController;
use App\Http\Controllers\TheLoaiController;
use App\Http\Controllers\TacGiaController;
use App\Http\Controllers\NhaXuatBanController;
use App\Http\Controllers\DocGiaController;
use App\Http\Controllers\LoaiDocGiaController;
use App\Http\Controllers\PhieuMuonController;
use App\Http\Controllers\PhieuThuTienPhatController;
use App\Http\Controllers\QuyDinhController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaiKhoanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Routes cho Thủ thư và Admin
Route::middleware(['role:Thủ thư,Admin'])->group(function () {
    Route::resource('books', SachController::class);
    Route::resource('genres', TheLoaiController::class);
    Route::resource('authors', TacGiaController::class);
    Route::resource('publishers', NhaXuatBanController::class);
    Route::resource('readers', DocGiaController::class);
    Route::resource('reader-types', LoaiDocGiaController::class);
        
    // Borrow records - separate web view and API routes
    Route::get('borrow-records', [PhieuMuonController::class, 'showBorrowRecordsPage'])->name('borrow-records.index');
    Route::post('borrow-records', [PhieuMuonController::class, 'store'])->name('borrow-records.store');
    Route::put('borrow-records/{id}', [PhieuMuonController::class, 'update'])->name('borrow-records.update');
    Route::delete('borrow-records/{id}', [PhieuMuonController::class, 'destroy'])->name('borrow-records.destroy');
        
    // Fine Payments routes (chỉ index, store, destroy - không có update)
    Route::get('fine-payments', [PhieuThuTienPhatController::class, 'index'])->name('fine-payments.index');
    Route::post('fine-payments', [PhieuThuTienPhatController::class, 'store'])->name('fine-payments.store');
    Route::delete('fine-payments/{phieuThuTienPhat}', [PhieuThuTienPhatController::class, 'destroy'])->name('fine-payments.destroy');
});

// Routes chỉ cho Admin
Route::middleware(['role:Admin'])->group(function () {
Route::resource('accounts', TaiKhoanController::class);
    Route::get('/regulations', [QuyDinhController::class, 'index'])->name('regulations.index');
    Route::get('/regulations/{id}', [QuyDinhController::class, 'show'])->name('regulations.show');
    Route::put('/regulations/{id}', [QuyDinhController::class, 'update'])->name('regulations.update');
});

// API routes for AJAX (cho Thủ thư và Admin)
Route::middleware(['role:Thủ thư,Admin'])->group(function () {
    Route::post('/theloai', [TheLoaiController::class, 'store'])->name('theloai.store');
    Route::put('/theloai/{id}', [TheLoaiController::class, 'update'])->name('theloai.update');
    Route::delete('/theloai/{id}', [TheLoaiController::class, 'destroy'])->name('theloai.destroy');

    // API routes for TacGia
    Route::get('/api/tacgia', [TacGiaController::class, 'index'])->name('api.tacgia.index');
    Route::post('/api/tacgia', [TacGiaController::class, 'store'])->name('api.tacgia.store');
    Route::put('/api/tacgia/{id}', [TacGiaController::class, 'update'])->name('api.tacgia.update');
    Route::delete('/api/tacgia/{id}', [TacGiaController::class, 'destroy'])->name('api.tacgia.destroy');

    // API routes for NhaXuatBan
    Route::get('/api/nhaxuatban', [NhaXuatBanController::class, 'index'])->name('api.nxb.index');
    Route::post('/api/nhaxuatban', [NhaXuatBanController::class, 'store'])->name('api.nxb.store');
    Route::put('/api/nhaxuatban/{id}', [NhaXuatBanController::class, 'update'])->name('api.nxb.update');
    Route::delete('/api/nhaxuatban/{id}', [NhaXuatBanController::class, 'destroy'])->name('api.nxb.destroy');

    // API routes for DocGia
    Route::get('/api/docgia', [DocGiaController::class, 'index'])->name('api.docgia.index');
    Route::post('/api/docgia', [DocGiaController::class, 'store'])->name('api.docgia.store');
    Route::put('/api/docgia/{id}', [DocGiaController::class, 'update'])->name('api.docgia.update');
    Route::delete('/api/docgia/{id}', [DocGiaController::class, 'destroy'])->name('api.docgia.destroy');

    // API routes for LoaiDocGia
    Route::get('/api/loaidocgia', [LoaiDocGiaController::class, 'index'])->name('api.loaidocgia.index');
    Route::post('/api/loaidocgia', [LoaiDocGiaController::class, 'store'])->name('api.loaidocgia.store');
    Route::put('/api/loaidocgia/{id}', [LoaiDocGiaController::class, 'update'])->name('api.loaidocgia.update');
    Route::delete('/api/loaidocgia/{id}', [LoaiDocGiaController::class, 'destroy'])->name('api.loaidocgia.destroy');
    });

    // API routes for Fine Records (cho Thủ thư và Admin)
    Route::middleware(['role:Thủ thư,Admin'])->group(function () {
    // API to get overdue borrow records for fine creation
    Route::get('/api/overdue-borrow-records', [PhieuMuonController::class, 'apiIndex'])->name('api.overdue-borrow-records');

    // API routes for Fine Payments
    Route::get('/api/fine-payments', [PhieuThuTienPhatController::class, 'index'])->name('api.fine-payments.index');
    Route::post('/api/fine-payments', [PhieuThuTienPhatController::class, 'store'])->name('api.fine-payments.store');
    Route::get('/api/fine-payments/{phieuThuTienPhat}', [PhieuThuTienPhatController::class, 'show'])->name('api.fine-payments.show');
    Route::delete('/api/fine-payments/{phieuThuTienPhat}', [PhieuThuTienPhatController::class, 'destroy'])->name('api.fine-payments.destroy');
    Route::get('/api/fine-payments/reader-debt/{docgia_id}', [PhieuThuTienPhatController::class, 'getReaderDebt'])->name('api.fine-payments.reader-debt');
});

// API routes for Regulations (chỉ Admin)
Route::middleware(['role:Admin'])->group(function () {
    Route::get('/api/regulations', [QuyDinhController::class, 'index'])->name('api.regulations.index');
    Route::get('/api/regulations/{id}', [QuyDinhController::class, 'show'])->name('api.regulations.show');
    Route::put('/api/regulations/{id}', [QuyDinhController::class, 'update'])->name('api.regulations.update');
    Route::get('/api/regulations/{id}/validation-info', [QuyDinhController::class, 'getValidationInfo'])->name('api.regulations.validation-info');
});

// Reports routes (cho Thủ thư và Admin)
Route::middleware(['role:Thủ thư,Admin'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // API routes for Reports
    Route::get('/api/reports/genre-statistics', [ReportController::class, 'genreStatistics'])->name('api.reports.genre-statistics');
    Route::get('/api/reports/overdue-books', [ReportController::class, 'overdueBooks'])->name('api.reports.overdue-books');
    Route::get('/api/reports/debug-overdue-books', [ReportController::class, 'debugOverdueBooks'])->name('api.reports.debug-overdue-books');
    Route::get('/api/reports/compare-overdue', [ReportController::class, 'compareOverdueResults'])->name('api.reports.compare-overdue');
    Route::get('/api/reports/export-genre-statistics', [ReportController::class, 'exportGenreStatistics'])->name('api.reports.export-genre-statistics');
    Route::get('/api/reports/export-overdue-books', [ReportController::class, 'exportOverdueBooks'])->name('api.reports.export-overdue-books');
});

// API routes for fixing negative fines (cho Thủ thư và Admin)
Route::middleware(['role:Thủ thư,Admin'])->group(function () {
    Route::get('/api/fix-negative-fines/check', [ReportController::class, 'checkNegativeFines'])->name('api.fix-negative-fines.check');
    Route::post('/api/fix-negative-fines/fix', [ReportController::class, 'fixNegativeFines'])->name('api.fix-negative-fines.fix');
    Route::post('/api/fix-negative-fines/recalculate', [ReportController::class, 'recalculateAllFines'])->name('api.fix-negative-fines.recalculate');
});

// API CRUD cho tài khoản (chỉ Admin)
Route::middleware(['role:Admin'])->group(function () {
    Route::get('/api/tai-khoan', [TaiKhoanController::class, 'getAllAccounts']);
    Route::post('/api/tai-khoan', [TaiKhoanController::class, 'store']);
    Route::get('/api/tai-khoan/{id}', [TaiKhoanController::class, 'show']);
    Route::put('/api/tai-khoan/{id}', [TaiKhoanController::class, 'update']);
    Route::delete('/api/tai-khoan/{id}', [TaiKhoanController::class, 'destroy']);
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// API routes for authentication
Route::post('/api/login', [AuthController::class, 'login'])->name('login.api');
Route::get('/api/check-auth', [AuthController::class, 'checkAuth'])->name('api.check-auth');
Route::get('/api/current-user', [AuthController::class, 'getCurrentUser'])->name('api.current-user');
// Phieu Muon API routes (cho Thủ thư và Admin)
Route::middleware(['role:Thủ thư,Admin'])->group(function () {
    Route::get('/api/borrow-records', [PhieuMuonController::class, 'index'])->name('api.borrow-records.index');
    Route::get('/api/borrow-records/{id}', [PhieuMuonController::class, 'show'])->name('api.borrow-records.show');
    Route::post('/api/phieu-muon', [PhieuMuonController::class, 'store'])->name('api.phieu-muon.store');
    Route::post('/api/borrow-records/{id}/calculate-fines', [PhieuMuonController::class, 'calculateReturnFines'])->name('api.borrow-records.calculate-fines');
    Route::post('/api/borrow-records/{id}/return', [PhieuMuonController::class, 'returnBooks'])->name('api.borrow-records.return');
    Route::post('/api/borrow-records/{id}/extend', [PhieuMuonController::class, 'extendBorrow'])->name('api.borrow-records.extend');
    Route::post('/api/borrow-records/{id}/extend-all', [PhieuMuonController::class, 'extendAll'])->name('api.borrow-records.extend-all');
    Route::delete('/api/borrow-records/{id}', [PhieuMuonController::class, 'destroy'])->name('api.borrow-records.destroy');
    
    // Additional API routes for borrow records functionality
    Route::get('/api/borrow-records/readers/list', [PhieuMuonController::class, 'readersListApi'])->name('api.borrow-records.readers-list');
    Route::get('/api/borrow-records/books/list', [PhieuMuonController::class, 'booksListApi'])->name('api.borrow-records.books-list');
    Route::get('/api/borrow-records/books/edit-list', [PhieuMuonController::class, 'editBooksListApi'])->name('api.borrow-records.edit-books-list');
});

