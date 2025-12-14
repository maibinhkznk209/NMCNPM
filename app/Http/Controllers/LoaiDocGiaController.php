<?php

namespace App\Http\Controllers;

use App\Models\LoaiDocGia;
use Illuminate\Http\Request;

class LoaiDocGiaController extends Controller
{
    public function index(Request $request)
    {
        $query = LoaiDocGia::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('TenLoaiDocGia', 'like', "%{$search}%");
        }

        // Sort functionality
        if ($request->has('sort') && $request->sort) {
            $sortData = explode('-', $request->sort);
            $sortBy = $sortData[0] ?? 'TenLoaiDocGia';
            $sortOrder = $sortData[1] ?? 'asc';
            
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('TenLoaiDocGia');
        }

        // Get all reader types (no pagination since we removed it from frontend)
        $loaiDocGias = $query->get();
        
        // Add reader count for each type
        foreach ($loaiDocGias as $loai) {
            $loai->readers_count = \App\Models\DocGia::where('loaidocgia_id', $loai->id)->count();
        }
        
        // Get total doc gia count for stats
        $docGiaCount = \App\Models\DocGia::count();
        
        if ($request->expectsJson()) {
            return response()->json($loaiDocGias);
        }
        
        return view('reader-types', compact('loaiDocGias', 'docGiaCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenLoaiDocGia' => 'required|string|max:255|unique:LOAIDOCGIA,TenLoaiDocGia',
        ], [
            'TenLoaiDocGia.required' => 'Tên loại độc giả là bắt buộc',
            'TenLoaiDocGia.unique' => 'Tên loại độc giả đã tồn tại',
            'TenLoaiDocGia.max' => 'Tên loại độc giả không được quá 255 ký tự',
        ]);

        try {
            $loaiDocGia = LoaiDocGia::create([
                'TenLoaiDocGia' => $request->TenLoaiDocGia,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm loại độc giả thành công',
                    'data' => $loaiDocGia
                ]);
            }

            return redirect()->route('reader-types.index')->with('success', 'Thêm loại độc giả thành công');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $loaiDocGia = LoaiDocGia::findOrFail($id);

        $request->validate([
            'TenLoaiDocGia' => 'required|string|max:255|unique:LOAIDOCGIA,TenLoaiDocGia,' . $id,
        ], [
            'TenLoaiDocGia.required' => 'Tên loại độc giả là bắt buộc',
            'TenLoaiDocGia.unique' => 'Tên loại độc giả đã tồn tại',
            'TenLoaiDocGia.max' => 'Tên loại độc giả không được quá 255 ký tự',
        ]);

        try {
            $loaiDocGia->update([
                'TenLoaiDocGia' => $request->TenLoaiDocGia,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật loại độc giả thành công',
                    'data' => $loaiDocGia
                ]);
            }

            return redirect()->route('reader-types.index')->with('success', 'Cập nhật loại độc giả thành công');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $loaiDocGia = LoaiDocGia::findOrFail($id);
            
            // Check if there are readers using this type
            if ($loaiDocGia->docGias()->count() > 0) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa loại độc giả đang được sử dụng'
                    ], 400);
                }
                
                return redirect()->route('reader-types.index')->with('error', 'Không thể xóa loại độc giả đang được sử dụng');
            }

            $loaiDocGia->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa loại độc giả thành công'
                ]);
            }

            return redirect()->route('reader-types.index')->with('success', 'Xóa loại độc giả thành công');
            
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('reader-types.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $loaiDocGia = LoaiDocGia::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $loaiDocGia
        ]);
    }
}
