<?php
// filepath: app/Http/Middleware/SetBranchContext.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetBranchContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Log untuk debugging
        Log::info('SetBranchContext middleware', [
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'warehouse_id' => $user->warehouse_id,
            'branch_id' => $user->branch_id,
        ]);

        // 1. Set branch_id to request (untuk filter otomatis)
        if ($user->branch_id && !$request->filled('branch_id')) {
            $request->merge(['branch_id' => $user->branch_id]);
        }

        // 2. Set warehouse_id to request (untuk filter otomatis)
        if ($user->warehouse_id && !$request->filled('warehouse_id')) {
            $request->merge(['warehouse_id' => $user->warehouse_id]);
        }

        // 3. Share data ke semua views
        view()->share([
            'currentUser' => $user,
            'currentBranchId' => $user->branch_id,
            'currentWarehouseId' => $user->warehouse_id,
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isManager' => $user->isManager(),
            'canManageUsers' => $user->canManageUsers(),
        ]);

        return $next($request);
    }
}