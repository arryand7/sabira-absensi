<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSuspendedUser
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->status === 'suspended' || $user->status === 'nonaktif') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akun Anda sedang ditangguhkan (suspended).',
                    ], 403);
                }

                return redirect()->route('login')->with('error', 'Akun Anda sedang ditangguhkan (suspended). Hubungi administrator.');
            }
        }

        return $next($request);
    }
}
