<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use App\Models\TaiKhoan;
use App\Models\VaiTro;

class AuthController extends Controller
{

    public function showLoginForm()
    {
        if (Session::has('user_id')) {
            return redirect()->route('home');
        }
        
        return view('login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email là bắt buộc',
            'email.email' => 'Email không hợp lệ',
            'password.required' => 'Mật khẩu là bắt buộc',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email hoặc mật khẩu không chính xác',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $taiKhoan = TaiKhoan::with('vaiTro')->where('Email', $request->email)->first();

        if (!$taiKhoan || !Hash::check($request->password, $taiKhoan->MatKhau)) {
            $errorMessage = 'Email hoặc mật khẩu không đúng';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 401);
            }
            
            return back()
                ->withErrors(['email' => $errorMessage])
                ->withInput($request->only('email'));
        }

        Session::put('user_id', $taiKhoan->id);
        Session::put('username', $taiKhoan->HoVaTen);
        Session::put('email', $taiKhoan->Email);
        Session::put('role', $taiKhoan->vaiTro->VaiTro);
        Session::put('role_id', $taiKhoan->vaitro_id);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'user' => [
                    'id' => $taiKhoan->id,
                    'username' => $taiKhoan->HoVaTen,
                    'email' => $taiKhoan->Email,
                    'role' => $taiKhoan->vaiTro->VaiTro
                ]
            ]);
        }

        return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
    }

    public function logout(Request $request)
    {
        Session::flush();
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đăng xuất thành công'
            ]);
        }

        return redirect()->route('login')->with('success', 'Đã đăng xuất thành công');
    }


    public function checkAuth(Request $request): JsonResponse
    {
        if (Session::has('user_id')) {
            return response()->json([
                'success' => true,
                'authenticated' => true,
                'user' => [
                    'id' => Session::get('user_id'),
                    'username' => Session::get('username'),
                    'email' => Session::get('email'),
                    'role' => Session::get('role'),
                    'role_id' => Session::get('role_id')
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'authenticated' => false,
            'user' => null
        ]);
    }

    public function getCurrentUser(): JsonResponse
    {
        if (!Session::has('user_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => Session::get('user_id'),
                'username' => Session::get('username'),
                'email' => Session::get('email'),
                'role' => Session::get('role'),
                'role_id' => Session::get('role_id')
            ]
        ]);
    }


}
