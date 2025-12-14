<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sach;
use App\Models\TheLoai;
use App\Models\TacGia;
use App\Models\NhaXuatBan;
use App\Models\TaiKhoan;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Sach::with(['theLoais', 'tacGia', 'nhaXuatBan']);
        
        // Tìm kiếm theo từ khóa
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('TenSach', 'like', "%{$search}%")
                  ->orWhere('MaSach', 'like', "%{$search}%")
                  ->orWhereHas('tacGia', function($q) use ($search) {
                      $q->where('TenTacGia', 'like', "%{$search}%");
                  })
                  ->orWhereHas('theLoais', function($q) use ($search) {
                      $q->where('TenTheLoai', 'like', "%{$search}%");
                  })
                  ->orWhereHas('nhaXuatBan', function($q) use ($search) {
                      $q->where('TenNXB', 'like', "%{$search}%");
                  });
            });
        }
        
        // Lọc theo thể loại
        if ($request->filled('genre')) {
            $query->whereHas('theLoais', function($q) use ($request) {
                $q->where('id', $request->get('genre'));
            });
        }
        
        // Lọc theo tác giả
        if ($request->filled('author')) {
            $query->whereHas('tacGia', function($q) use ($request) {
                $q->where('id', $request->get('author'));
            });
        }
        
        // Lọc theo nhà xuất bản
        if ($request->filled('publisher')) {
            $query->whereHas('nhaXuatBan', function($q) use ($request) {
                $q->where('id', $request->get('publisher'));
            });
        }
        
        // Lọc theo tình trạng sách
        if ($request->filled('status')) {
            $query->where('TinhTrang', $request->get('status'));
        }
        
        // Sắp xếp
        $sortBy = $request->get('sort', 'TenSach');
        $sortOrder = $request->get('order', 'asc');
        
        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'asc';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        // Phân trang với appends để giữ các tham số tìm kiếm
        $books = $query->paginate(12)->appends($request->query());
        
        // Lấy danh sách thể loại, tác giả, nhà xuất bản cho filter
        $genres = TheLoai::orderBy('TenTheLoai')->get();
        $authors = TacGia::orderBy('TenTacGia')->get();
        $publishers = NhaXuatBan::orderBy('TenNXB')->get();
        
        // Lấy thông tin user và role từ Session
        $user = null;
        $userRole = null;
        $isLoggedIn = false;
        
        if (Session::has('user_id')) {
            $isLoggedIn = true;
            $user = TaiKhoan::with('vaiTro')->find(Session::get('user_id'));
            $userRole = Session::get('role');
        }
        
        return view('home', compact('books', 'genres', 'authors', 'publishers', 'user', 'userRole', 'isLoggedIn'));
    }
}
