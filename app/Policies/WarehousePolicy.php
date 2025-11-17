<?php
// filepath: app/Policies/WarehousePolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any warehouses.
     * (List/Index page)
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view list
        // (filtering akan dilakukan di controller)
        return true;
    }

    /**
     * Determine whether the user can view the warehouse.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        // Super admin: can view all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Use User model's canView method
        return $user->canView($warehouse->id);
    }

    /**
     * Determine whether the user can create warehouses.
     */
    public function create(User $user): bool
    {

        if ($user->isSuperAdmin() || $user->isCentralManager() || $user->isBranchManager()) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can update the warehouse.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        // Super admin: can update all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: only their own warehouse
        if ($user->isManager() && $user->ownsWarehouse($warehouse->id)) {
            return true;
        }

        // Staff: cannot update
        return false;
    }

    /**
     * Determine whether the user can delete the warehouse.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        // Only super admin can delete warehouses
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the warehouse.
     */
    public function restore(User $user, Warehouse $warehouse): bool
    {
        // Only super admin can restore
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the warehouse.
     */
    public function forceDelete(User $user, Warehouse $warehouse): bool
    {
        // Only super admin can force delete
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can manage stock in the warehouse.
     * (Create/edit stock transactions)
     */
    public function manageStock(User $user, Warehouse $warehouse): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Check write permission (own warehouse)
        return $user->canWrite($warehouse->id);
    }

    /**
     * Determine whether the user can view stock reports.
     */
    public function viewStockReports(User $user, Warehouse $warehouse): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can view reports if can view warehouse
        return $user->canView($warehouse->id);
    }

    /**
     * Determine whether the user can export stock data.
     */
    public function exportStock(User $user, Warehouse $warehouse): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager & staff: can export their own warehouse
        // Manager: can also export lower level warehouses (read-only access)
        return $user->canView($warehouse->id);
    }

    /**
     * Determine whether the user can approve stock adjustments.
     */
    public function approveAdjustments(User $user, Warehouse $warehouse): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Only manager of the warehouse can approve
        if ($user->isManager() && $user->ownsWarehouse($warehouse->id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can close stock periods.
     */
    public function closeStockPeriod(User $user, Warehouse $warehouse): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Only manager of the warehouse can close periods
        if ($user->isManager() && $user->ownsWarehouse($warehouse->id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can reopen stock periods.
     */
    public function reopenStockPeriod(User $user, Warehouse $warehouse): bool
    {
        // Only super admin can reopen closed periods
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view audit logs.
     */
    public function viewAuditLogs(User $user, Warehouse $warehouse): bool
    {
        // Super admin: all logs
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: logs for their warehouse and lower levels
        if ($user->isManager() && $user->canView($warehouse->id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can configure warehouse settings.
     */
    public function configure(User $user, Warehouse $warehouse): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: only their own warehouse
        if ($user->isManager() && $user->ownsWarehouse($warehouse->id)) {
            return true;
        }

        return false;
    }
}