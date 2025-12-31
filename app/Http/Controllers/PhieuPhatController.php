<?php

namespace App\Http\Controllers;

use App\Models\PhieuPhat;
use App\Models\DocGia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhieuPhatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $phieuThus = PhieuPhat::with('docGia')->orderBy('NgayThu', 'desc')->get();
            $docGias = DocGia::orderBy('TenDocGia', 'asc')->get();

            // IMPORTANT: API path must always return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'data' => $phieuThus,
                    'doc_gias' => $docGias,
                ]);
            }

            return view('fine-payments', compact('phieuThus', 'docGias'));
        } catch (\Exception $e) {
            Log::error('Error in PhieuPhatController@index: ' . $e->getMessage());

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Co loi xay ra khi tai danh sach phieu thu.',
                ], 500);
            }

            return redirect()->route('home')->with('error', 'Co loi xay ra khi tai danh sach phieu thu.');
        }
    }
    public function store(Request $request)
    {
        try {
            $request->validate([
                'MaDocGia' => 'required|exists:DOCGIA,MaDocGia',
                'SoTienNop' => 'required|numeric|min:0',
            ], [
                'MaDocGia.required' => 'Vui lòng chọn độc giả',
                'MaDocGia.exists' => 'Độc giả không tồn tại',
                'SoTienNop.required' => 'Vui lòng nhập số tiền nộp',
                'SoTienNop.numeric' => 'Số tiền nộp phải là số',
                'SoTienNop.min' => 'Số tiền nộp phải lớn hơn 0',
            ]);


            $docGia = DocGia::findOrFail($request->MaDocGia);
            

            if ($request->SoTienNop > $docGia->TongNo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số tiền nộp không được vượt quá tổng nợ (' . number_format($docGia->TongNo, 0, ',', '.') . 'đ)'
                ], 422);
            }

            DB::beginTransaction();


            $phieuThu = PhieuPhat::create([
                'MaPhieuPhat' => PhieuPhat::generateMaPhieuPhat(),
                'MaDocGia' => $request->MaDocGia,
                'SoTienNop' => $request->SoTienNop,
                'NgayThu' => now()->toDateString(),
            ]);


            $docGia->TongNo -= $request->SoTienNop;
            $docGia->save();

            DB::commit();

            $phieuThu->load('PP_DG');

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
            Log::error('Error in PhieuPhatController@store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo phiếu thu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PhieuPhat $PhieuPhat)
    {
        try {
            $PhieuPhat->load('PP_DG');
            return response()->json([
                'success' => true,
                'data' => $PhieuPhat
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhieuPhatController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải dữ liệu.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhieuPhat $PhieuPhat)
    {
        try {
            DB::beginTransaction();


            $docGia = DocGia::findOrFail($PhieuPhat->MaDocGia);
            $docGia->TongNo += $PhieuPhat->SoTienNop;
            $docGia->save();


            $PhieuPhat->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa phiếu thu tiền phạt thành công!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in PhieuPhatController@destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phiếu thu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reader debt information
     */
    public function getReaderDebt($MaDocGia)
    {
        try {
            $docGia = DocGia::findOrFail($MaDocGia);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'TongNo' => $docGia->TongNo,
                    'TongNoFormatted' => number_format($docGia->TongNo, 0, ',', '.') . 'đ'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in PhieuPhatController@getReaderDebt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thông tin nợ.'
            ], 500);
        }
    }
}
