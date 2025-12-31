<?php

namespace App\Http\Controllers;

use App\Models\QuyDinh;
use Illuminate\Http\Request;

class QuyDinhController extends Controller
{
    /**
     */
    private function quyDinhDto(QuyDinh $qd): array
    {
        return [
            'id' => $qd->getKey(),               // = MaThamSo theo QuyDinh.php
            'TenThamSo' => $qd->TenThamSo,
            'GiaTri' => (string) $qd->GiaTri,
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        QuyDinh::firstOrCreate(
            ['TenThamSo' => QuyDinh::LATE_FINE_PER_DAY],
            ['GiaTri' => 1000]
        );
        $quyDinhs = QuyDinh::orderBy('TenThamSo')->get();


        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $quyDinhs->map(fn ($qd) => $this->quyDinhDto($qd))->values(),
            ]);
        }

        return view('regulations', compact('quyDinhs'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $quyDinh = QuyDinh::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $this->quyDinhDto($quyDinh),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy quy định',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $quyDinh = QuyDinh::findOrFail($id);

        $request->validate([
            'GiaTri' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($quyDinh) {
                    $tenThamSo = $quyDinh->TenThamSo;

                    if (str_contains($tenThamSo, 'Tuoi')) {
                        if ($value < 1 || $value > 100) $fail('Tuổi phải từ 1 đến 100');
                    } elseif (str_contains($tenThamSo, 'ThoiHan')) {
                        if ($value < 1 || $value > 120) $fail('Số tháng phải từ 1 đến 120');
                    } elseif (str_contains($tenThamSo, 'TienPhat')) {
                        if ($value < 1000 || $value > 1000000) $fail('Số tiền phạt phải từ 1000 đến 1000000');
                    } elseif (str_contains($tenThamSo, 'Ngay')) {
                        if ($value < 1 || $value > 365) $fail('Số ngày phải từ 1 đến 365');
                    } elseif (str_contains($tenThamSo, 'Sach')) {
                        if ($value < 1 || $value > 50) $fail('Số sách phải từ 1 đến 50');
                    } elseif (str_contains($tenThamSo, 'Nam')) {
                        if ($value < 1 || $value > 50) $fail('Số năm phải từ 1 đến 50');
                    }
                },
            ],
        ], [
            'GiaTri.required' => 'Giá trị quy định là bắt buộc',
            'GiaTri.numeric' => 'Giá trị phải là số',
            'GiaTri.min' => 'Giá trị phải lớn hơn 0',
        ]);

        try {
            $quyDinh->update([
                'GiaTri' => $request->GiaTri,
            ]);

            if ($request->expectsJson()) {
                $qd = $quyDinh->fresh();

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật quy định thành công',
                    'data' => $this->quyDinhDto($qd),
                ]);
            }

            return redirect()
                ->route('regulations.index')
                ->with('success', 'Cập nhật quy định thành công');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->route('regulations.index')
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Get validation info for frontend
     */
    public function getValidationInfo($id)
    {
        $quyDinh = QuyDinh::findOrFail($id);
        $tenThamSo = $quyDinh->TenThamSo;

        $validationInfo = [
            'min' => 1,
            'max' => 100,
            'unit' => '',
            'description' => '',
        ];

        if (str_contains($tenThamSo, 'Tuoi')) {
            $validationInfo['max'] = 100;
            $validationInfo['unit'] = 'tuoi';
            $validationInfo['description'] = 'Tuoi hop le (1-100)';
        } elseif (str_contains($tenThamSo, 'ThoiHan')) {
            $validationInfo['max'] = 120;
            $validationInfo['unit'] = 'thang';
            $validationInfo['description'] = 'So thang hop le (1-120)';
        } elseif (str_contains($tenThamSo, 'Ngay')) {
            $validationInfo['max'] = 365;
            $validationInfo['unit'] = 'ngay';
            $validationInfo['description'] = 'So ngay hop le (1-365)';
        } elseif (str_contains($tenThamSo, 'TienPhat')) {
            $validationInfo['min'] = 1000;
            $validationInfo['max'] = 1000000;
            $validationInfo['unit'] = 'VND/ngay';
            $validationInfo['description'] = 'So tien phat moi ngay tre (0-1,000,000)';
        } elseif (str_contains($tenThamSo, 'Sach')) {
            $validationInfo['max'] = 50;
            $validationInfo['unit'] = 'cuon';
            $validationInfo['description'] = 'So sach hop le (1-50)';
        } elseif (str_contains($tenThamSo, 'Nam')) {
            $validationInfo['max'] = 50;
            $validationInfo['unit'] = 'nam';
            $validationInfo['description'] = 'So nam hop le (1-50)';
        }
        return response()->json([
            'success' => true,
            'data' => $validationInfo,
        ]);
    }
}
