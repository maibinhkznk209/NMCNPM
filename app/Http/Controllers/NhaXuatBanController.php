<?php

namespace App\Http\Controllers;

use App\Models\NhaXuatBan;
use Illuminate\Http\Request;
use App\Models\Sach;

class NhaXuatBanController extends Controller
{
    public function index()
    {
        // Nếu là AJAX request, trả về JSON
        if (request()->expectsJson()) {
            $nhaXuatBans = NhaXuatBan::orderBy('TenNXB')->get();
            return response()->json($nhaXuatBans);
        }
        
        // Nếu không, trả về view
        $nhaXuatBans = NhaXuatBan::orderBy('TenNXB')->get();
        return view('publishers', compact('nhaXuatBans'));
    }

    public function store(Request $request)
    {
        try {
        $request->validate([
            'TenNXB' => 'required|string|max:255|unique:NHAXUATBAN,TenNXB',
        ], [
            'TenNXB.required' => 'Tên nhà xuất bản là bắt buộc',
            'TenNXB.unique' => 'Tên nhà xuất bản đã tồn tại',
            'TenNXB.max' => 'Tên nhà xuất bản không được quá 255 ký tự',
        ]);

            $nhaXuatBan = NhaXuatBan::create([
                'TenNXB' => $request->TenNXB,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm nhà xuất bản thành công',
                'data' => $nhaXuatBan
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenNXB'][0] ?? 'Lỗi khi lưu nhà xuất bản',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $nhaXuatBan = NhaXuatBan::find($id);
        if (!$nhaXuatBan) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhà xuất bản'
            ], 404);
        }
        return response()->json($nhaXuatBan);
    }

    public function update(Request $request, $id)
    {
        try {
        $nhaXuatBan = NhaXuatBan::find($id);
        if (!$nhaXuatBan) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhà xuất bản'
            ], 404);
        }

        $request->validate([
            'TenNXB' => 'required|string|max:255|unique:NHAXUATBAN,TenNXB,' . $id . ',MaNXB',
        ], [
            'TenNXB.required' => 'Tên nhà xuất bản là bắt buộc',
            'TenNXB.unique' => 'Tên nhà xuất bản đã tồn tại',
            'TenNXB.max' => 'Tên nhà xuất bản không được quá 255 ký tự',
        ]);

            $nhaXuatBan->update([
                'TenNXB' => $request->TenNXB,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật nhà xuất bản thành công',
                'data' => $nhaXuatBan
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenNXB'][0] ?? 'Lỗi khi cập nhật nhà xuất bản',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $nhaXuatBan = NhaXuatBan::find($id);
        if (!$nhaXuatBan) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nhà xuất bản'
            ], 404);
        }

        try {
            $sachCount = $nhaXuatBan->NXB_S()->count();
            if ($sachCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể xóa nhà xuất bản này vì có {$sachCount} sách đang sử dụng."
                ], 400);
            }

            $nhaXuatBan->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa nhà xuất bản thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
