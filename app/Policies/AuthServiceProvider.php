<?php
// filepath: app/Providers/AuthServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Policies\UserPolicy;
use App\Policies\BranchPolicy;
use App\Policies\WarehousePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Branch::class => BranchPolicy::class,
        Warehouse::class => WarehousePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Optional: Define additional gates
        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });

        Gate::define('access-central-warehouse', function (User $user) {
            return $user->isCentralLevel();
        });

        Gate::define('access-branch-warehouse', function (User $user) {
            return $user->isBranchLevel() || $user->isSuperAdmin() || $user->isCentralManager();
        });

        Gate::define('access-outlet-warehouse', function (User $user) {
            return $user->isOutletLevel() || $user->isSuperAdmin() || $user->isCentralManager() || $user->isBranchManager();
        });
    }
}