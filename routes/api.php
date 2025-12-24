<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhieuMuonController;
use App\Http\Controllers\PhieuPhatController;
use App\Http\Controllers\DocGiaController;
use App\Http\Controllers\LoaiDocGiaController;
use App\Http\Controllers\NhaXuatBanController;
use App\Http\Controllers\TacGiaController;
use App\Http\Controllers\QuyDinhController;

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