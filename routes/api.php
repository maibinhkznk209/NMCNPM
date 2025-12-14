<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhieuMuonController;
use App\Http\Controllers\PhieuThuTienPhatController;
use App\Http\Controllers\DocGiaController;
use App\Http\Controllers\LoaiDocGiaController;
use App\Http\Controllers\NhaXuatBanController;
use App\Http\Controllers\TacGiaController;
use App\Http\Controllers\QuyDinhController;

// Borrow Records API
Route::get('/borrow-records', [PhieuMuonController::class, 'index']);
Route::get('/borrow-records/{id}', [PhieuMuonController::class, 'show']);
Route::post('/borrow-records', [PhieuMuonController::class, 'store']);
Route::put('/borrow-records/{id}', [PhieuMuonController::class, 'update']);
Route::delete('/borrow-records/{id}', [PhieuMuonController::class, 'destroy']);
Route::post('/borrow-records/{id}/calculate-fines', [PhieuMuonController::class, 'calculateReturnFines']);
Route::post('/borrow-records/{id}/return', [PhieuMuonController::class, 'returnBooks']);
Route::put('/borrow-records/{id}/extend', [PhieuMuonController::class, 'extendAll']);

// Readers and Books for dropdowns
Route::get('/readers-list', [PhieuMuonController::class, 'readersListApi']);
Route::get('/all-readers-list', [PhieuMuonController::class, 'allReadersListApi']);
Route::get('/books-list', [PhieuMuonController::class, 'booksListApi']);
Route::get('/edit-books-list', [PhieuMuonController::class, 'editBooksListApi']);

// Fine Payments API
Route::get('/fine-payments', [PhieuThuTienPhatController::class, 'index']);
Route::post('/fine-payments', [PhieuThuTienPhatController::class, 'store']);
Route::get('/fine-payments/{phieuThuTienPhat}', [PhieuThuTienPhatController::class, 'show']);
Route::delete('/fine-payments/{phieuThuTienPhat}', [PhieuThuTienPhatController::class, 'destroy']);
Route::get('/fine-payments/reader-debt/{docgia_id}', [PhieuThuTienPhatController::class, 'getReaderDebt']);

// Add other API routes as needed (DocGia, LoaiDocGia, NhaXuatBan, TacGia, QuyDinh, PhieuPhat, ...) 