<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {

        if (!Session::has('user_id')) {

            if ($request->routeIs('home')) {
                return $next($request);
            }
            

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa đăng nhập'
                ], 401);
            }
            

            return redirect()->route('login');
        }

        $userRole = Session::get('role');
        

        if ($userRole) {

            if (empty($roles) || in_array($userRole, $roles)) {
                return $next($request);
            }
        }


        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập'
            ], 403);
        }
        

        return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
    }
}
