<?php

namespace App\Http\Controllers;

use App\Models\DocGia;
use App\Models\LoaiDocGia;
use App\Models\QuyDinh;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocGiaController extends Controller
{
    public function index(Request $request)
    {
        $query = DocGia::with('loaiDocGia');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('TenDocGia', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%")
                  ->orWhere('DiaChi', 'like', "%{$search}%");
            });
        }

        $query->orderBy('TenDocGia', 'asc');

        $docGias = $query->get();
        $loaiDocGias = LoaiDocGia::orderBy('TenLoaiDocGia')->get();
        $borrowDurationDays = $this->getBorrowDurationDays();

        // Nếu là AJAX request, trả về JSON
        if ($request->expectsJson()) {
            return response()->json([
                'docGias' => $docGias,
                'loaiDocGias' => $loaiDocGias,
                'borrowDurationDays' => $borrowDurationDays,
            ]);
        }

        return view('readers', compact('docGias', 'loaiDocGias', 'borrowDurationDays'));
    }

    private function getBorrowDurationDays(): int
    {
        try {
            $row = DB::table('THAMSO')->where('TenThamSo', 'NgayMuonToiDa')->first();
            if ($row && isset($row->GiaTri) && is_numeric($row->GiaTri)) {
                return (int) $row->GiaTri;
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        return 14;
    }

    public function store(Request $request)
    {
        try {
        $request->validate([
            'TenDocGia' => 'required|string|max:255',
            'MaLoaiDocGia' => 'required|exists:LOAIDOCGIA,MaLoaiDocGia',
            'NgaySinh' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!strtotime($value) || Carbon::parse($value)->isFuture()) {
                        $fail('Ngày sinh không hợp lệ');
                        return;
                    }
                    $birthDate = Carbon::parse($value);
                    $age = $birthDate->diffInYears(Carbon::now());
                    
                    $minAge = QuyDinh::getMinAge();
                    $maxAge = QuyDinh::getMaxAge();
                    
                    if ($age < $minAge) {
                        $fail("Độc giả phải từ {$minAge} tuổi trở lên (hiện tại: {$age} tuổi)");
                    }
                    
                    if ($age > $maxAge) {
                        $fail("Độc giả không được quá {$maxAge} tuổi (hiện tại: {$age} tuổi)");
                    }
                }
            ],
            'DiaChi' => 'required|string|max:500',
            'Email' => [
                'bail',
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    $exists = DocGia::whereRaw('LOWER(Email) = ?', [mb_strtolower($value)])->exists();
                    if ($exists) {
                        $fail('Email đã tồn tại');
                    }
                }
            ],
            'NgayLapThe' => 'required|date',
            'NgayHetHan' => 'required|date|after:NgayLapThe',
            'TongNo' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!is_null($value) && $value < 0) {
                        $fail('Tổng nợ không hợp lệ');
                    }
                },
                'max:999999999'
            ],
        ], [
            'TenDocGia.required' => 'Họ tên là bắt buộc',
            'TenDocGia.max' => 'Họ tên không được quá 255 ký tự',
            'MaLoaiDocGia.required' => 'Loại độc giả là bắt buộc',
            'MaLoaiDocGia.exists' => 'Loại độc giả không hợp lệ',
            'NgaySinh.required' => 'Ngày sinh là bắt buộc',
            'NgaySinh.date' => 'Ngày sinh không hợp lệ',
            'DiaChi.required' => 'Địa chỉ là bắt buộc',
            'DiaChi.max' => 'Địa chỉ không được quá 500 ký tự',
            'Email.required' => 'Email là bắt buộc',
            'Email.email' => 'Email không hợp lệ',
            'NgayLapThe.required' => 'Ngày lập thẻ là bắt buộc',
            'NgayLapThe.date' => 'Ngày lập thẻ không hợp lệ',
            'NgayHetHan.required' => 'Ngày hết hạn là bắt buộc',
            'NgayHetHan.date' => 'Ngày hết hạn không hợp lệ',
            'NgayHetHan.after' => 'Ngày hết hạn phải sau ngày lập thẻ',
            'TongNo.integer' => 'Tổng nợ phải là số nguyên',
            'TongNo.max' => 'Tổng nợ quá lớn',
        ]);

            $docGia = DocGia::create([
                'TenDocGia' => $request->TenDocGia,
                'MaLoaiDocGia' => $request->MaLoaiDocGia,
                'NgaySinh' => $request->NgaySinh,
                'DiaChi' => $request->DiaChi,
                'Email' => $request->Email,
                'NgayLapThe' => $request->NgayLapThe,
                'NgayHetHan' => $request->NgayHetHan,
                'TongNo' => $request->TongNo ?? 0,
            ]);

            // Load relationship
            $docGia->load('loaiDocGia');

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thêm độc giả thành công',
                    'data' => $docGia
                ]);
            }

            return redirect()->route('readers.index')->with('success', 'Thêm độc giả thành công');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                // Lấy thông báo lỗi đầu tiên từ validation errors
                $firstError = collect($e->errors())->first();
                $errorMessage = is_array($firstError) ? $firstError[0] : $firstError;
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Lỗi khi lưu độc giả',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('readers.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
        $docGia = DocGia::findOrFail($id);

        $request->validate([
            'TenDocGia' => 'required|string|max:255',
            'MaLoaiDocGia' => 'required|exists:LOAIDOCGIA,MaLoaiDocGia',
            'NgaySinh' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (!strtotime($value) || Carbon::parse($value)->isFuture()) {
                        $fail('Ngày sinh không hợp lệ');
                        return;
                    }
                    $birthDate = Carbon::parse($value);
                    $age = $birthDate->diffInYears(Carbon::now());
                    
                    $minAge = QuyDinh::getMinAge();
                    $maxAge = QuyDinh::getMaxAge();
                    
                    if ($age < $minAge) {
                        $fail("Độc giả phải từ {$minAge} tuổi trở lên (hiện tại: {$age} tuổi)");
                    }
                    
                    if ($age > $maxAge) {
                        $fail("Độc giả không được quá {$maxAge} tuổi (hiện tại: {$age} tuổi)");
                    }
                }
            ],
            'DiaChi' => 'required|string|max:500',
            'Email' => [
                'bail',
                'required',
                'email',
                function ($attribute, $value, $fail) use ($id) {
                    $exists = DocGia::whereRaw('LOWER(Email) = ?', [mb_strtolower($value)])
                        ->where('MaDocGia', '!=', $id)
                        ->exists();

                    if ($exists) {
                        $fail('Email đã tồn tại');
                    }
                }
            ],
            'NgayLapThe' => 'required|date',
            'NgayHetHan' => 'required|date|after:NgayLapThe',
            'TongNo' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if (!is_null($value) && $value < 0) {
                        $fail('Tổng nợ không hợp lệ');
                    }
                },
                'max:999999999'
            ],
        ], [
            'TenDocGia.required' => 'Họ tên là bắt buộc',
            'TenDocGia.max' => 'Họ tên không được quá 255 ký tự',
            'MaLoaiDocGia.required' => 'Loại độc giả là bắt buộc',
            'MaLoaiDocGia.exists' => 'Loại độc giả không hợp lệ',
            'NgaySinh.required' => 'Ngày sinh là bắt buộc',
            'NgaySinh.date' => 'Ngày sinh không hợp lệ',
            'DiaChi.required' => 'Địa chỉ là bắt buộc',
            'DiaChi.max' => 'Địa chỉ không được quá 500 ký tự',
            'Email.required' => 'Email là bắt buộc',
            'Email.email' => 'Email không hợp lệ',
            'NgayLapThe.required' => 'Ngày lập thẻ là bắt buộc',
            'NgayLapThe.date' => 'Ngày lập thẻ không hợp lệ',
            'NgayHetHan.required' => 'Ngày hết hạn là bắt buộc',
            'NgayHetHan.date' => 'Ngày hết hạn không hợp lệ',
            'NgayHetHan.after' => 'Ngày hết hạn phải sau ngày lập thẻ',
            'TongNo.integer' => 'Tổng nợ phải là số nguyên',
            'TongNo.max' => 'Tổng nợ quá lớn',
        ]);

            $docGia->update([
                'TenDocGia' => $request->TenDocGia,
                'MaLoaiDocGia' => $request->MaLoaiDocGia,
                'NgaySinh' => $request->NgaySinh,
                'DiaChi' => $request->DiaChi,
                'Email' => $request->Email,
                'NgayLapThe' => $request->NgayLapThe,
                'NgayHetHan' => $request->NgayHetHan,
                'TongNo' => $request->TongNo ?? 0,
            ]);

            // Load relationship
            $docGia->load('loaiDocGia');

            // Check if it's an AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật độc giả thành công',
                    'data' => $docGia
                ]);
            }

            // Traditional form submission - redirect with success message
            return redirect()->route('readers.index')->with('success', 'Cập nhật độc giả thành công');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson() || $request->ajax()) {
                // Lấy thông báo lỗi đầu tiên từ validation errors
                $firstError = collect($e->errors())->first();
                $errorMessage = is_array($firstError) ? $firstError[0] : $firstError;
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Lỗi khi cập nhật độc giả',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('readers.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $docGia = DocGia::findOrFail($id);
            
            // Check if reader has any active borrows
            $activeBorrows = \App\Models\PhieuMuon::where('MaDocGia', $docGia->MaDocGia)
                ->whereHas('PM_S', function($query) {
                    $query->whereNull('NgayTra');
                })->count();
            
            if ($activeBorrows > 0) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa độc giả còn sách đang mượn'
                    ], 400);
                }
                
                return redirect()->route('readers.index')->with('error', 'Không thể xóa độc giả còn sách đang mượn');
            }

            $docGia->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa độc giả thành công'
                ]);
            }

            return redirect()->route('readers.index')->with('success', 'Xóa độc giả thành công');
            
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('readers.index')->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $docGia = DocGia::with('loaiDocGia')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $docGia
        ]);
    }
}
