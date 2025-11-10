<?php
// filepath: app/Policies/DistributionOrderPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\DistributionOrder;
use Illuminate\Auth\Access\HandlesAuthorization;

class DistributionOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any distribution orders.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the distribution order.
     */
    public function view(User $user, DistributionOrder $order): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can view if involved in the distribution
        // (from_warehouse or to_warehouse)
        if ($user->canView($order->from_warehouse_id) || 
            $user->canView($order->to_warehouse_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create distribution orders.
     */
    public function create(User $user): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Branch/outlet users can request from central/branch
        if ($user->isBranchUser() || $user->isOutletUser()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the distribution order.
     */
    public function update(User $user, DistributionOrder $order): bool
    {
        // Can only update draft/pending orders
        if (!in_array($order->status, ['draft', 'pending'])) {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Requester can update draft
        if ($order->status === 'draft' && $order->requested_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the distribution order.
     */
    public function delete(User $user, DistributionOrder $order): bool
    {
        // Can only delete draft orders
        if ($order->status !== 'draft') {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Requester can delete draft
        if ($order->requested_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can approve the distribution order.
     */
    public function approve(User $user, DistributionOrder $order): bool
    {
        // Must be pending
        if ($order->status !== 'pending') {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Central manager can approve (distribusi dari central)
        if ($user->role === User::ROLE_CENTRAL_MANAGER) {
            // Must be from central warehouse
            if ($user->ownsWarehouse($order->from_warehouse_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can reject the distribution order.
     */
    public function reject(User $user, DistributionOrder $order): bool
    {
        return $this->approve($user, $order);
    }

    /**
     * Determine whether the user can prepare the distribution order.
     */
    public function prepare(User $user, DistributionOrder $order): bool
    {
        // Must be approved
        if ($order->status !== 'approved') {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Staff at from_warehouse can prepare
        if ($user->warehouse_id === $order->from_warehouse_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can mark as sent.
     */
    public function send(User $user, DistributionOrder $order): bool
    {
        // Must be prepared
        if ($order->status !== 'prepared') {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager at from_warehouse can send
        if ($user->isManager() && $user->ownsWarehouse($order->from_warehouse_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can receive the distribution order.
     */
    public function receive(User $user, DistributionOrder $order): bool
    {
        // Must be sent
        if ($order->status !== 'sent') {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Staff at to_warehouse can receive
        if ($user->warehouse_id === $order->to_warehouse_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the distribution order.
     */
    public function cancel(User $user, DistributionOrder $order): bool
    {
        // Cannot cancel completed orders
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager who approved can cancel
        if ($user->isManager() && $order->approved_by === $user->id) {
            return true;
        }

        return false;
    }
}