<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class CheckBactiveAccess
{
    public function handle(Request $request, Closure $next, $accessType)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // Check if the user has the required access in bactive
        if (!$user->hasAccess($accessType)) {
            return response()->view('no-access', ['previousUrl' => URL::previous()], 403);
        }

        return $next($request);
    }
}
