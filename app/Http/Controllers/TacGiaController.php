<?php

namespace App\Http\Controllers;

use App\Models\TacGia;
use Illuminate\Http\Request;

class TacGiaController extends Controller
{
    public function index()
    {
        // Nếu là AJAX request, trả về JSON
        if (request()->expectsJson()) {
            $tacGias = TacGia::orderBy('TenTacGia')->get();
            return response()->json($tacGias);
        }
        
        // Nếu không, trả về view
        $tacGias = TacGia::orderBy('TenTacGia')->get();
        return view('authors', compact('tacGias'));
    }

    public function store(Request $request)
    {
        try {
        $request->validate([
            'TenTacGia' => 'required|string|max:255|unique:TACGIA,TenTacGia',
        ], [
            'TenTacGia.required' => 'Tên tác giả là bắt buộc',
            'TenTacGia.unique' => 'Tên tác giả đã tồn tại',
            'TenTacGia.max' => 'Tên tác giả không được quá 255 ký tự',
        ]);

            $tacGia = TacGia::create([
                'TenTacGia' => $request->TenTacGia,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thêm tác giả thành công',
                'data' => $tacGia
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenTacGia'][0] ?? 'Lỗi khi lưu tác giả',
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
        $tacGia = TacGia::find($id);
        if (!$tacGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tác giả'
            ], 404);
        }
        return response()->json($tacGia);
    }

    public function update(Request $request, $id)
    {
        try {
        $tacGia = TacGia::find($id);
        if (!$tacGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tác giả'
            ], 404);
        }

        $request->validate([
            'TenTacGia' => 'required|string|max:255|unique:TACGIA,TenTacGia,' . $id,
        ], [
            'TenTacGia.required' => 'Tên tác giả là bắt buộc',
            'TenTacGia.unique' => 'Tên tác giả đã tồn tại',
            'TenTacGia.max' => 'Tên tác giả không được quá 255 ký tự',
        ]);

            $tacGia->update([
                'TenTacGia' => $request->TenTacGia,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tác giả thành công',
                'data' => $tacGia
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenTacGia'][0] ?? 'Lỗi khi cập nhật tác giả',
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
        $tacGia = TacGia::find($id);
        if (!$tacGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tác giả'
            ], 404);
        }

        try {
            // Kiểm tra xem tác giả có đang được sử dụng không
            $sachCount = $tacGia->sachs()->count();
            if ($sachCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể xóa tác giả này vì có {$sachCount} sách đang sử dụng."
                ], 400);
            }

            $tacGia->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa tác giả thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
