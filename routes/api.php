<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhieuMuonController;
use App\Http\Controllers\PhieuPhatController;
use App\Http\Controllers\DocGiaController;
use App\Http\Controllers\LoaiDocGiaController;
use App\Http\Controllers\NhaXuatBanController;
use App\Http\Controllers\TacGiaController;
use App\Http\Controllers\QuyDinhController;
use App\Http\Controllers\ReportController;

Route::get('borrow-records/doc-gia/{MaDocGia}', [PhieuMuonController::class, 'getByReader']);
Route::apiResource('borrow-records', PhieuMuonController::class);
Route::post('borrow-records/{MaPhieuMuon}/calculate-fines', [PhieuMuonController::class, 'calculateReturnFines']);
Route::post('borrow-records/{MaPhieuMuon}/return', [PhieuMuonController::class, 'returnBooks']);
Route::put('borrow-records/{MaPhieuMuon}/extend', [PhieuMuonController::class, 'extendAll']);
Route::patch('borrow-records/{MaPhieuMuon}/extend', [PhieuMuonController::class, 'extendAll']);

Route::get('readers-list', [PhieuMuonController::class, 'readersListApi']);
Route::get('all-readers-list', [PhieuMuonController::class, 'allReadersListApi']);
Route::get('books-list', [PhieuMuonController::class, 'booksListApi']);
Route::get('edit-books-list', [PhieuMuonController::class, 'editBooksListApi']);

Route::get('fine-payments', [PhieuPhatController::class, 'index']);
Route::post('fine-payments', [PhieuPhatController::class, 'store']);
Route::get('fine-payments/{PhieuPhat}', [PhieuPhatController::class, 'show']);
Route::delete('fine-payments/{PhieuPhat}', [PhieuPhatController::class, 'destroy']);
Route::get('fine-payments/reader-debt/{MaDocGia}', [PhieuPhatController::class, 'getReaderDebt']);

Route::apiResource('readers', DocGiaController::class);
Route::apiResource('reader-types', LoaiDocGiaController::class);
Route::apiResource('publishers', NhaXuatBanController::class);
Route::apiResource('authors', TacGiaController::class);

Route::get('regulations', [QuyDinhController::class, 'index']);
Route::get('regulations/{MaThamSo}', [QuyDinhController::class, 'show']);
Route::put('regulations/{MaThamSo}', [QuyDinhController::class, 'update']);
Route::patch('regulations/{MaThamSo}', [QuyDinhController::class, 'update']);
Route::get('regulations/{MaThamSo}/validation-info', [QuyDinhController::class, 'getValidationInfo']);

Route::prefix('reports')->controller(ReportController::class)->group(function () {
    Route::get('/', 'index');

    Route::get('/genre-statistics', 'genreStatistics');
    Route::get('/overdue-books', 'overdueBooks');

    Route::get('/export/genre-statistics', 'exportGenreStatistics');
    Route::get('/export/overdue-books', 'exportOverdueBooks');

    Route::get('/debug/overdue-books', 'debugOverdueBooks');
    Route::get('/compare/overdue-books', 'compareOverdueResults');

    Route::get('/check-negative-fines', 'checkNegativeFines');
    Route::post('/fix-negative-fines', 'fixNegativeFines');
    Route::post('/recalculate-all-fines', 'recalculateAllFines');
});