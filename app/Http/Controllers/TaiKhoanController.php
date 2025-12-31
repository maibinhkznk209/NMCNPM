<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

use App\Models\TaiKhoan;
use App\Models\VaiTro;

class TaiKhoanController extends Controller
{
    /**
     * Display the account management page
     */
    public function index()
    {
        try {
            $taiKhoans = TaiKhoan::with('vaiTro')->orderBy('id', 'desc')->get();
            $vaiTros = VaiTro::all();
            
            return view('accounts', compact('taiKhoans', 'vaiTros'));
        } catch (Exception $e) {
            Log::error('Error loading accounts page: ' . $e->getMessage());
            return back()->with('error', 'Lỗi tải trang quản lý tài khoản');
        }
    }

    /**
     * Get all accounts (API)
     */
    public function getAllAccounts(): JsonResponse
    {
        try {
            $taiKhoans = TaiKhoan::with('vaiTro')->orderBy('id', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $taiKhoans
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching accounts: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy danh sách tài khoản'
            ], 500);
        }
    }

    /**
     * Store a new account
     */
    public function store(Request $request): JsonResponse
    {
        try {


            $defaultRoleId = DB::table('VAITRO')->value('id') ?? 1;

            $validator = Validator::make($request->all(), [
                'HoVaTen' => 'required|string|max:100',
                'Email' => 'required|email|max:150|unique:TAIKHOAN,Email',
                'MatKhau' => 'required|string|min:6',
                'VaiTroId' => 'nullable|exists:VAITRO,id',
            ], [
                'HoVaTen.required' => 'Họ và tên là bắt buộc',
                'HoVaTen.max' => 'Họ và tên không được quá 100 ký tự',
                'Email.required' => 'Email là bắt buộc',
                'Email.email' => 'Email không hợp lệ',
                'Email.unique' => 'Email đã tồn tại',
                'Email.max' => 'Email không được quá 150 ký tự',
                'MatKhau.required' => 'Mật khẩu là bắt buộc',
                'MatKhau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
                'VaiTroId.exists' => 'Vai trò không hợp lệ',
            ]);

            if ($validator->fails()) {
                $firstError = collect($validator->errors())->first();
                $errorMessage = is_array($firstError) ? $firstError[0] : $firstError;

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $vaiTroId = $request->input('VaiTroId', $defaultRoleId);

            $taiKhoan = TaiKhoan::create([
                'HoVaTen' => $request->HoVaTen,
                'Email' => $request->Email,
                'MatKhau' => Hash::make($request->MatKhau),
                'vaitro_id' => $vaiTroId,
            ]);

            $taiKhoan->load('vaiTro');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo tài khoản thành công',
                'data' => $taiKhoan
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating account: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo tài khoản: ' . $e->getMessage()
            ], 400);
        }
    }


    /**
     * Get a specific account
     */
    public function show(int $id): JsonResponse
    {
        try {
            $taiKhoan = TaiKhoan::with('vaiTro')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $taiKhoan
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching account: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản'
            ], 404);
        }
    }

    /**
     * Update an account
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $taiKhoan = TaiKhoan::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'HoVaTen' => 'required|string|max:100',
                'Email' => 'required|email|max:150|unique:TAIKHOAN,Email,' . $id,
                'MatKhau' => 'nullable|string|min:6',
            ], [
                'HoVaTen.required' => 'Họ và tên là bắt buộc',
                'HoVaTen.max' => 'Họ và tên không được quá 100 ký tự',
                'Email.required' => 'Email là bắt buộc',
                'Email.email' => 'Email không hợp lệ',
                'Email.unique' => 'Email đã tồn tại',
                'Email.max' => 'Email không được quá 150 ký tự',
                'MatKhau.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            ]);

            if ($validator->fails()) {

                $firstError = collect($validator->errors())->first();
                $errorMessage = is_array($firstError) ? $firstError[0] : $firstError;
                
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage ?? 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $updateData = [
                'HoVaTen' => $request->HoVaTen,
                'Email' => $request->Email,
            ];

            // Only update password if provided
            if ($request->MatKhau) {
                $updateData['MatKhau'] = Hash::make($request->MatKhau);
            }

            $taiKhoan->update($updateData);

            // Set default VaiTroId if not provided
            $taiKhoan->VaiTroId = $request->input('VaiTroId') ?? 2;

            // Load relationship for response
            $taiKhoan->load('vaiTro');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tài khoản thành công',
                'data' => $taiKhoan
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating account: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cập nhật tài khoản: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete an account
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $taiKhoan = TaiKhoan::findOrFail($id);

            DB::beginTransaction();

            $taiKhoan->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa tài khoản thành công'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting account: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xóa tài khoản: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get account statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $totalAccounts = TaiKhoan::count();
            $accountsByRole = TaiKhoan::select('vaitro_id')
                ->with('vaiTro:id,VaiTro')
                ->get()
                ->groupBy('vaiTro.VaiTro')
                ->map(function ($group) {
                    return $group->count();
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_accounts' => $totalAccounts,
                    'accounts_by_role' => $accountsByRole
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching account stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi lấy thống kê tài khoản'
            ], 500);
        }
    }
}
