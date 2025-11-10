<?php
// filepath: app/Http/Middleware/CheckWarehouseAccess.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckWarehouseAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $warehouseId = $request->route('warehouse') 
            ?? $request->route('id') 
            ?? $request->input('warehouse_id');

        if (!$warehouseId) {
            return $next($request);
        }

        // Super admin has access to everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has access to this warehouse
        if (!$user->hasWarehouseAccess($warehouseId)) {
            Log::warning('Unauthorized warehouse access attempt', [
                'user_id' => $user->id,
                'warehouse_id' => $warehouseId,
                'permission' => $permission
            ]);

            abort(403, 'You do not have access to this warehouse.');
        }

        return $next($request);
    }
}