<?php

namespace App\Http\Controllers;

use App\Models\TacGia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TacGiaController extends Controller
{
    public function index()
    {
        if (request()->expectsJson()) {
            $tacGias = TacGia::orderBy('TenTacGia')->get();
            return response()->json($tacGias);
        }

        $tacGias = TacGia::orderBy('TenTacGia')->get();
        return view('authors', compact('tacGias'));
    }

    public function store(Request $request): JsonResponse
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
                'data' => $tacGia,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenTacGia'][0] ?? 'Lỗi khi lưu tác giả',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $tacGia = TacGia::query()->where('MaTacGia', $id)->first();

        if (!$tacGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tác giả',
            ], 404);
        }

        return response()->json($tacGia, 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $tacGia = TacGia::query()->where('MaTacGia', $id)->first();

            if (!$tacGia) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tác giả',
                ], 404);
            }

            $request->validate([
                'TenTacGia' => 'required|string|max:255|unique:TACGIA,TenTacGia,' . $id . ',MaTacGia',
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
                'data' => $tacGia,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenTacGia'][0] ?? 'Lỗi khi cập nhật tác giả',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $tacGia = TacGia::query()->where('MaTacGia', $id)->first();

        if (!$tacGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tác giả',
            ], 404);
        }

        try {
            $hasDauSach = DB::table('CT_TACGIA')
                ->where('MaTacGia', $tacGia->MaTacGia)
                ->exists();

            if ($hasDauSach) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa tác giả vì đang có đầu sách liên quan',
                ], 400);
            }

            $tacGia->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa tác giả thành công',
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }
}
