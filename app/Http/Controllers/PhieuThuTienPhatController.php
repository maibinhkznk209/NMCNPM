<?php

namespace App\Http\Controllers;

use App\Models\PhieuThuTienPhat;
use App\Models\DocGia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhieuThuTienPhatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $phieuThus = PhieuThuTienPhat::with('docGia')->orderBy('id', 'desc')->get();
            $docGias = DocGia::orderBy('HoTen')->get();
            
            return view('fine-payments', compact('phieuThus', 'docGias'));
        } catch (\Exception $e) {
            Log::error('Error in PhieuThuTienPhatController@index: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải dữ liệu.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'docgia_id' => 'required|exists:DOCGIA,id',
                'SoTienNop' => 'required|numeric|min:0',
            ], [
                'docgia_id.required' => 'Vui lòng chọn độc giả',
                'docgia_id.exists' => 'Độc giả không tồn tại',
                'SoTienNop.required' => 'Vui lòng nhập số tiền nộp',
                'SoTienNop.numeric' => 'Số tiền nộp phải là số',
                'SoTienNop.min' => 'Số tiền nộp phải lớn hơn 0',
            ]);

            // Lấy thông tin độc giả
            $docGia = DocGia::findOrFail($request->docgia_id);
            
            // Kiểm tra số tiền nộp không vượt quá tổng nợ
            if ($request->SoTienNop > $docGia->TongNo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số tiền nộp không được vượt quá tổng nợ (' . number_format($docGia->TongNo, 0, ',', '.') . 'đ)'
                ], 422);
            }

            DB::beginTransaction();

            // Tạo phiếu thu tiền phạt
            $phieuThu = PhieuThuTienPhat::create([
                'MaPhieu' => PhieuThuTienPhat::generateMaPhieu(),
                'docgia_id' => $request->docgia_id,
                'SoTienNop' => $request->SoTienNop,
                'NgayThu' => now()->toDateString(),
            ]);

            // Cập nhật tổng nợ của độc giả
            $docGia->TongNo -= $request->SoTienNop;
            $docGia->save();

            DB::commit();

            $phieuThu->load('docGia');

            return response()->json([
                'success' => true,
                'message' => 'Tạo phiếu thu tiền phạt thành công!',
                'data' => $phieuThu
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in PhieuThuTienPhatController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo phiếu thu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PhieuThuTienPhat $phieuThuTienPhat)
    {
        try {
            $phieuThuTienPhat->load('docGia');
            return response()->json([
                'success' => true,
                'data' => $phieuThuTienPhat
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhieuThuTienPhatController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải dữ liệu.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhieuThuTienPhat $phieuThuTienPhat)
    {
        try {
            DB::beginTransaction();

            // Hoàn lại số tiền cho độc giả
            $docGia = DocGia::findOrFail($phieuThuTienPhat->docgia_id);
            $docGia->TongNo += $phieuThuTienPhat->SoTienNop;
            $docGia->save();

            // Xóa phiếu thu
            $phieuThuTienPhat->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa phiếu thu tiền phạt thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in PhieuThuTienPhatController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phiếu thu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reader debt information
     */
    public function getReaderDebt($docgia_id)
    {
        try {
            $docGia = DocGia::findOrFail($docgia_id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'TongNo' => $docGia->TongNo,
                    'TongNoFormatted' => number_format($docGia->TongNo, 0, ',', '.') . 'đ'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhieuThuTienPhatController@getReaderDebt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin nợ.'
            ], 500);
        }
    }
}
