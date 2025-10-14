<?php
// filepath: app/Http/Middleware/CheckAuth.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                           ->with('error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}