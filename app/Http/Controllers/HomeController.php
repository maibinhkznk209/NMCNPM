<?php

namespace App\Http\Controllers;

use App\Models\CuonSach;
use App\Models\NhaXuatBan;
use App\Models\Sach;
use App\Models\TacGia;
use App\Models\TaiKhoan;
use App\Models\TheLoai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index(Request $request)
    {

        $query = Sach::with(['dauSach', 'dauSach.theLoai', 'dauSach.tacGias', 'nhaXuatBan']);


        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('MaSach', 'like', "%{$search}%")
                        ->orWhereHas('dauSach', function ($q) use ($search) {
                            $q->where('TenDauSach', 'like', "%{$search}%");
                        })
                        ->orWhereHas('dauSach.theLoai', function ($q) use ($search) {
                            $q->where('TenTheLoai', 'like', "%{$search}%");
                        })
                        ->orWhereHas('nhaXuatBan', function ($q) use ($search) {
                            $q->where('TenNXB', 'like', "%{$search}%");
                        })
                        ->orWhereHas('dauSach.tacGias', function ($q) use ($search) {
                            $q->where('TenTacGia', 'like', "%{$search}%");
                        });
                });
            }
        }


        if ($request->filled('genre') && $request->get('genre') !== 'all') {
            $genre = $request->get('genre');
            $query->whereHas('dauSach', function ($q) use ($genre) {
                $q->where('MaTheLoai', $genre);
            });
        }


        if ($request->filled('author') && $request->get('author') !== 'all') {
            $author = $request->get('author');
            $query->whereHas('dauSach.tacGias', function ($q) use ($author) {
                $q->where('TACGIA.MaTacGia', $author);
            });
        }

        // Filter NXB
        if ($request->filled('publisher') && $request->get('publisher') !== 'all') {
            $publisher = $request->get('publisher');
            $query->where('MaNXB', $publisher);
        }


        if ($request->filled('status') && $request->get('status') !== 'all') {
            $status = (int) $request->get('status');
            $query->whereHas('cuonSachs', function ($q) use ($status) {
                $q->where('TinhTrang', $status);
            });
        }


        $sortBy = $request->get('sort', 'MaSach');
        $sortOrder = $request->get('order', 'asc');

        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'asc';
        }


        if (!in_array($sortBy, ['MaSach', 'NamXuatBan', 'TriGia', 'SoLuong'], true)) {
            $sortBy = 'MaSach';
        }

        $query->orderBy($sortBy, $sortOrder);

        $books = $query->paginate(12)->appends($request->query());

        $genres = TheLoai::orderBy('TenTheLoai')->get();
        $authors = TacGia::orderBy('TenTacGia')->get();
        $publishers = NhaXuatBan::orderBy('TenNXB')->get();

        $user = null;
        $userRole = null;
        $isLoggedIn = false;

        if (Session::has('user_id')) {
            $isLoggedIn = true;
            $user = TaiKhoan::with('vaiTro')->find(Session::get('user_id'));
            $userRole = Session::get('role');
        }

        return view('home', compact(
            'books',
            'genres',
            'authors',
            'publishers',
            'user',
            'userRole',
            'isLoggedIn'
        ));
    }
}
