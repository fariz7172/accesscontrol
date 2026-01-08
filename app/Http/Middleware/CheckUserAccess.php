<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Cek apakah user terautentikasi dan memiliki akses yang benar
        if ($user) {
        } else {
            // Jika user tidak terautentikasi
            return redirect()->route('login')->with('error', 'You need to login first.');
        }

        // Jika semua cek lolos, lanjutkan request
        return $next($request);
    }
}
