<?php
// filepath: app/Policies/StockTransactionPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\StockTransaction;
use App\Models\CentralStockTransaction;
use App\Models\BranchStockTransaction;
use App\Models\KitchenStockTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class StockTransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any transactions.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view (filtering di controller)
        return true;
    }

    /**
     * Determine whether the user can view the transaction.
     */
    public function view(User $user, $transaction): bool
    {
        // Super admin: can view all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Check warehouse access
        $warehouseId = $transaction->warehouse_id ?? $transaction->from_warehouse_id;
        
        if (!$warehouseId) {
            return false;
        }

        return $user->canView($warehouseId);
    }

    /**
     * Determine whether the user can create transactions.
     */
    public function create(User $user, ?int $warehouseId = null): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // If warehouse specified, check write access
        if ($warehouseId) {
            return $user->canWrite($warehouseId);
        }

        // Otherwise, user must have at least one warehouse with write access
        return $user->getAccessibleWarehouses(true)->isNotEmpty();
    }

    /**
     * Determine whether the user can update the transaction.
     */
    public function update(User $user, $transaction): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can only update draft transactions
        if (isset($transaction->status) && $transaction->status !== 'draft') {
            return false;
        }

        // Check write access to warehouse
        $warehouseId = $transaction->warehouse_id ?? $transaction->from_warehouse_id;
        
        if (!$warehouseId) {
            return false;
        }

        return $user->canWrite($warehouseId);
    }

    /**
     * Determine whether the user can delete the transaction.
     */
    public function delete(User $user, $transaction): bool
    {
        // Super admin: yes (with restrictions)
        if ($user->isSuperAdmin()) {
            // Optional: prevent deleting approved transactions
            if (isset($transaction->status) && $transaction->status === 'approved') {
                return false;
            }
            return true;
        }

        // Can only delete draft transactions
        if (isset($transaction->status) && $transaction->status !== 'draft') {
            return false;
        }

        // Must be created by user or user has write access
        $warehouseId = $transaction->warehouse_id ?? $transaction->from_warehouse_id;
        
        if ($transaction->created_by === $user->id) {
            return true;
        }

        return $user->canWrite($warehouseId);
    }

    /**
     * Determine whether the user can approve the transaction.
     */
    public function approve(User $user, $transaction): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Must be pending status
        if (isset($transaction->status) && $transaction->status !== 'pending') {
            return false;
        }

        // Only manager can approve
        if (!$user->isManager()) {
            return false;
        }

        // Must own the warehouse
        $warehouseId = $transaction->warehouse_id ?? $transaction->from_warehouse_id;
        
        return $user->ownsWarehouse($warehouseId);
    }

    /**
     * Determine whether the user can reject the transaction.
     */
    public function reject(User $user, $transaction): bool
    {
        // Same as approve
        return $this->approve($user, $transaction);
    }

    /**
     * Determine whether the user can void/cancel the transaction.
     */
    public function void(User $user, $transaction): bool
    {
        // Only super admin or manager who approved can void
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Must be approved status
        if (isset($transaction->status) && $transaction->status !== 'approved') {
            return false;
        }

        // Must be manager who approved it
        if ($user->isManager() && isset($transaction->approved_by)) {
            return $transaction->approved_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can export transactions.
     */
    public function export(User $user, ?int $warehouseId = null): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager & staff: can export if they have view access
        if ($warehouseId) {
            return $user->canView($warehouseId);
        }

        return true;
    }

    /**
     * Determine whether the user can view transaction history.
     */
    public function viewHistory(User $user, $transaction): bool
    {
        // Same as view
        return $this->view($user, $transaction);
    }

    /**
     * Determine whether the user can print transaction.
     */
    public function print(User $user, $transaction): bool
    {
        // Same as view
        return $this->view($user, $transaction);
    }
}