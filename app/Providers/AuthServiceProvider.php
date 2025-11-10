<?php
// filepath: app/Providers/AuthServiceProvider.php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Models
use App\Models\User;
use App\Models\Warehouse;
use App\Models\CentralStockTransaction;
use App\Models\BranchStockTransaction;
use App\Models\KitchenStockTransaction;
use App\Models\DistributionOrder;

// Policies
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use App\Policies\StockTransactionPolicy;
use App\Policies\DistributionOrderPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // User Management
        User::class => UserPolicy::class,
        
        // Warehouse Management
        Warehouse::class => WarehousePolicy::class,
        
        // Stock Transactions
        CentralStockTransaction::class => StockTransactionPolicy::class,
        BranchStockTransaction::class => StockTransactionPolicy::class,
        KitchenStockTransaction::class => StockTransactionPolicy::class,
        
        // Distribution Orders
        DistributionOrder::class => DistributionOrderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ========================================
        // ADDITIONAL GATES (Optional)
        // ========================================

        // Super Admin Gate - bypass all checks
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                // Super admin can do everything
                // return true; // Uncomment to bypass all policy checks
            }
        });

        // Custom Gates untuk specific permissions
        
        // Gate: Can manage system settings
        Gate::define('manage-system-settings', function (User $user) {
            return $user->isSuperAdmin();
        });

        // Gate: Can view all branches
        Gate::define('view-all-branches', function (User $user) {
            return $user->isSuperAdmin() || $user->isCentralUser();
        });

        // Gate: Can approve distributions
        Gate::define('approve-distributions', function (User $user) {
            return $user->isSuperAdmin() || $user->role === User::ROLE_CENTRAL_MANAGER;
        });

        // Gate: Can close stock periods
        Gate::define('close-stock-periods', function (User $user) {
            return $user->isSuperAdmin() || $user->isManager();
        });

        // Gate: Can access reports
        Gate::define('access-reports', function (User $user) {
            return $user->isSuperAdmin() || $user->isManager();
        });

        // Gate: Can manage master data (items, categories, etc)
        Gate::define('manage-master-data', function (User $user) {
            return $user->isSuperAdmin();
        });

        // Gate: Can export data
        Gate::define('export-data', function (User $user) {
            // All authenticated users can export (with their access level)
            return true;
        });

        // Gate: Can import data
        Gate::define('import-data', function (User $user) {
            return $user->isSuperAdmin() || $user->isManager();
        });

        // Gate: Can view audit logs
        Gate::define('view-audit-logs', function (User $user) {
            return $user->isSuperAdmin() || $user->isManager();
        });

        // Gate: Can impersonate users
        Gate::define('impersonate-users', function (User $user) {
            return $user->isSuperAdmin();
        });
    }
}