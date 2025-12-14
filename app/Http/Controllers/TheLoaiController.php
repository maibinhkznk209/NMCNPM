<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TheLoai;

class TheLoaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $theLoais = TheLoai::all();
        return view('genres', compact('theLoais'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
        $theLoai = TheLoai::findOrFail($id);
        
        $request->validate([
            'TenTheLoai' => 'required|string|max:255|unique:THELOAI,TenTheLoai,' . $theLoai->id . ',id',
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
        ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()['TenTheLoai'][0] ?? 'Lỗi khi cập nhật thể loại',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $theLoai = TheLoai::findOrFail($id);
            
            // Kiểm tra xem thể loại có đang được sử dụng không
            if ($theLoai->saches()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa thể loại này vì đang có sách thuộc thể loại này'
                ], 400);
            }
            
            $theLoai->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa thể loại thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa thể loại'
            ], 500);
        }
    }
}
