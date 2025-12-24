<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TheLoai;
use Illuminate\Http\JsonResponse;

class TheLoaiController extends Controller
{
    public function index()
    {
        $theLoais = TheLoai::all();
        return view('genres', compact('theLoais'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'TenTheLoai' => 'required|string|max:255|unique:THELOAI,TenTheLoai',
            ], [
                'TenTheLoai.required' => 'Tên thể loại là bắt buộc',
                'TenTheLoai.unique' => 'Thể loại này đã tồn tại',
                'TenTheLoai.max' => 'Tên thể loại không được quá 255 ký tự',
            ]);

            $theLoai = TheLoai::create([
                'TenTheLoai' => $request->TenTheLoai,
            ]);

            return response()->json([
                'success' => true,
                'data' => $theLoai,
                'message' => 'Thêm thể loại thành công'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenTheLoai'][0] ?? 'Lỗi khi lưu thể loại',
                'errors' => $e->errors()
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $theLoai = TheLoai::query()->where('MaTheLoai', $id)->firstOrFail();

            $request->validate([
                'TenTheLoai' => 'required|string|max:255|unique:THELOAI,TenTheLoai,' . $theLoai->MaTheLoai . ',MaTheLoai',
            ], [
                'TenTheLoai.required' => 'Tên thể loại là bắt buộc',
                'TenTheLoai.unique' => 'Thể loại này đã tồn tại',
                'TenTheLoai.max' => 'Tên thể loại không được quá 255 ký tự',
            ]);

            $theLoai->update([
                'TenTheLoai' => $request->TenTheLoai,
            ]);

            return response()->json([
                'success' => true,
                'data' => $theLoai,
                'message' => 'Cập nhật thể loại thành công'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenTheLoai'][0] ?? 'Lỗi khi cập nhật thể loại',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thể loại'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $theLoai = TheLoai::query()->where('MaTheLoai', $id)->firstOrFail();

            if ($theLoai->dauSaches()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa thể loại này vì đang có đầu sách thuộc thể loại này'
                ], 400);
            }

            $theLoai->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa thể loại thành công'
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thể loại'
            ], 404);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa thể loại'
            ], 500);
        }
    }
}
