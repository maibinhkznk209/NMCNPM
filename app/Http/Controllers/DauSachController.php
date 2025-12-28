<?php

namespace App\Http\Controllers;

use App\Models\DauSach;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DauSachController extends Controller
{
    private function generateMaPhieuNhanDauSach(): string
    {
        $prefix = 'PNDS';
        $year = date('Y');

        $latest = DB::table('PHIEUNHANDAUSACH')
            ->where('MaPhieuNhanDauSach', 'like', $prefix . $year . '-%')
            ->orderBy('MaPhieuNhanDauSach', 'desc')
            ->first();

        $nextNumber = 1;
        if ($latest && isset($latest->MaPhieuNhanDauSach)) {
            $lastNumber = (int)substr((string)$latest->MaPhieuNhanDauSach, -4);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . $year . '-' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $driver = DB::getDriverName();
        $authorAgg = $driver === 'mysql'
            ? "GROUP_CONCAT(DISTINCT tg.TenTacGia ORDER BY tg.TenTacGia SEPARATOR ', ')"
            : "GROUP_CONCAT(DISTINCT tg.TenTacGia)";

        $q = DB::table('DAUSACH as ds')
            ->join('THELOAI as tl', 'tl.MaTheLoai', '=', 'ds.MaTheLoai')
            ->leftJoin('CT_TACGIA as cttg', 'cttg.MaDauSach', '=', 'ds.MaDauSach')
            ->leftJoin('TACGIA as tg', 'tg.MaTacGia', '=', 'cttg.MaTacGia')
            ->select([
                'ds.MaDauSach',
                'ds.TenDauSach',
                'ds.MaTheLoai',
                'ds.NgayNhap',
                'tl.TenTheLoai',
                DB::raw("$authorAgg as TenTacGia"),
            ])
            ->groupBy([
                'ds.MaDauSach',
                'ds.TenDauSach',
                'ds.MaTheLoai',
                'ds.NgayNhap',
                'tl.TenTheLoai',
            ]);

        if ($kw = trim((string)$request->get('q', ''))) {
            $q->where('ds.TenDauSach', 'LIKE', '%' . $kw . '%');
        }

        if ($maTheLoai = $this->normalizeSingleId($request->get('MaTheLoai', $request->get('theLoais')))) {
            $q->where('ds.MaTheLoai', '=', $maTheLoai);
        }

        if ($maTacGia = $this->normalizeSingleId($request->get('MaTacGia', $request->get('tacGia')))) {
            $q->whereExists(function ($sub) use ($maTacGia) {
                $sub->select(DB::raw(1))
                    ->from('CT_TACGIA as x')
                    ->whereColumn('x.MaDauSach', 'ds.MaDauSach')
                    ->where('x.MaTacGia', '=', $maTacGia);
            });
        }

        $items = $q->orderByDesc('ds.MaDauSach')->paginate(20);

        if ($request->expectsJson()) {
            return response()->json($items);
        }

        return view('dausach.index', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'TenDauSach' => 'required|string|max:255',
            'MaTheLoai' => 'required',
            'tacGias' => 'nullable',
            'MaTacGia' => 'nullable',
            'MaTacGia.*' => 'nullable',
            'NgayNhap' => 'required|date',
        ], [
            'TenDauSach.required' => 'Vui lòng nhập tên đầu sách',
            'MaTheLoai.required' => 'Vui lòng chọn thể loại',
        ]);

        $ten = trim((string)$request->get('TenDauSach'));
        if ($ten === '') {
            return back()->withInput()->with('error', 'Vui lòng nhập tên đầu sách');
        }

        $maTheLoai = $this->normalizeSingleId($request->get('MaTheLoai'));
        if (!$maTheLoai) {
            return back()->withInput()->with('error', 'Vui lòng chọn thể loại');
        }

        $maTacGiaList = $this->normalizeIdList($request->get('tacGias', $request->get('MaTacGia')));
        if (count($maTacGiaList) === 0) {
            return back()->withInput()->with('error', 'Vui lòng chọn ít nhất 1 tác giả');
        }

        if (!DB::table('THELOAI')->where('MaTheLoai', $maTheLoai)->exists()) {
            return back()->withInput()->with('error', 'Thể loại không tồn tại');
        }

        $uniqueTacGia = array_values(array_unique($maTacGiaList));
        $countTg = DB::table('TACGIA')->whereIn('MaTacGia', $uniqueTacGia)->count();
        if ($countTg !== count($uniqueTacGia)) {
            return back()->withInput()->with('error', 'Có tác giả không tồn tại');
        }

        $ngayNhap = Carbon::parse((string)$request->get('NgayNhap'));

        DB::beginTransaction();
        try {
            $dauSach = DauSach::create([
                'TenDauSach' => $ten,
                'MaTheLoai' => $maTheLoai,
                'NgayNhap' => $ngayNhap,
            ]);
            DB::table('PHIEUNHANDAUSACH')->insert([
                'MaPhieuNhanDauSach' => $this->generateMaPhieuNhanDauSach(),
                'MaDauSach' => $dauSach->MaDauSach,
                'NgayNhap' => $ngayNhap->toDateString(),
            ]);


            if (method_exists($dauSach, 'DS_TG')) {
                $dauSach->DS_TG()->sync($uniqueTacGia);
            } elseif (method_exists($dauSach, 'tacGias')) {
                $dauSach->tacGias()->sync($uniqueTacGia);
            } else {
                foreach ($uniqueTacGia as $maTG) {
                    DB::table('CT_TACGIA')->insert([
                        'MaDauSach' => $dauSach->MaDauSach,
                        'MaTacGia' => $maTG,
                    ]);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'MaDauSach' => $dauSach->MaDauSach,
                    'TenDauSach' => $dauSach->TenDauSach,
                ], 201);
            }

            return redirect()->back()->with('success', 'Đã tạo đầu sách');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'TenDauSach' => 'required|string|max:255',
        ], [
            'TenDauSach.required' => 'Vui lAýng nh §-p tA¦n Ž` §u sA­ch',
        ]);

        $ten = trim((string)$request->get('TenDauSach'));
        if ($ten === '') {
            return back()->withInput()->with('error', 'Vui lAýng nh §-p tA¦n Ž` §u sA­ch');
        }

        $exists = DB::table('DAUSACH')->where('MaDauSach', $id)->exists();
        if (!$exists) {
            return back()->with('error', 'KhA'ng tAªm th §y Ž` §u sA­ch');
        }

        DB::table('DAUSACH')->where('MaDauSach', $id)->update([
            'TenDauSach' => $ten,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Ž?Aœ c §-p nh §-t tA¦n Ž` §u sA­ch');
    }

    public function destroy(Request $request, int $id)
    {
        $exists = DB::table('DAUSACH')->where('MaDauSach', $id)->exists();
        if (!$exists) {
            return back()->with('error', 'KhA'ng tAªm th §y Ž` §u sA­ch');
        }

        $hasSach = DB::table('SACH')->where('MaDauSach', $id)->exists();
        if ($hasSach) {
            return back()->with('error', 'KhA'ng th ¯Ÿ xA3a Ž` §u sA­ch vA¼ nA3y vA¼ nA3y Ž`ang cA3 sA­ch liAªn quan');
        }

        DB::beginTransaction();
        try {
            DB::table('CT_TACGIA')->where('MaDauSach', $id)->delete();
            DB::table('DAUSACH')->where('MaDauSach', $id)->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'CA3 l ¯-i x §œy ra: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Ž?Aœ xA3a Ž` §u sA­ch');
    }

    private function normalizeIdList($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map([$this, 'normalizeSingleId'], $value)));
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value !== '' && ($value[0] === '[' || $value[0] === '{')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map([$this, 'normalizeSingleId'], $decoded)));
                }
            }

            if (str_contains($value, ',')) {
                $parts = array_map('trim', explode(',', $value));
                return array_values(array_filter(array_map([$this, 'normalizeSingleId'], $parts)));
            }

            $single = $this->normalizeSingleId($value);
            return $single ? [$single] : [];
        }

        $single = $this->normalizeSingleId($value);
        return $single ? [$single] : [];
    }

    private function normalizeSingleId($value): ?int
    {
        if (is_null($value)) return null;
        if (is_int($value)) return $value;

        if (is_string($value)) {
            $value = trim($value);

            if ($value !== '' && ($value[0] === '[' || $value[0] === '{')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    return isset($decoded[0]) ? (int)$decoded[0] : null;
                }
            }

            if (ctype_digit($value)) return (int)$value;
        }

        if (is_numeric($value)) return (int)$value;

        return null;
    }
}
