<?php
// filepath: app/Http/Kernel.php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        // ========================================
        // CUSTOM MIDDLEWARE - ACCESS CONTROL
        // ========================================
        
        /**
         * Check warehouse access (view or write permission)
         * Usage: ->middleware('check.warehouse.access:view')
         *        ->middleware('check.warehouse.access:write')
         */
        'check.warehouse.access' => \App\Http\Middleware\CheckWarehouseAccess::class,
        
        /**
         * Set branch context for super admin & central users
         * Usage: ->middleware('set.branch.context')
         */
        'set.branch.context' => \App\Http\Middleware\SetBranchContext::class,
        
        /**
         * Check user management permission
         * Usage: ->middleware('check.user.management:create')
         *        ->middleware('check.user.management:update')
         */
        'check.user.management' => \App\Http\Middleware\CheckUserManagement::class,
        
        /**
         * Check if user has specific role(s)
         * Usage: ->middleware('role:super_admin,central_manager')
         */
        'role' => \App\Http\Middleware\CheckRole::class,
    ];

    /**
     * The application's middleware aliases.
     * (Laravel 11+)
     */
    protected $middlewareAliases = [
        // ...same as $routeMiddleware above for Laravel 11+
    ];
}