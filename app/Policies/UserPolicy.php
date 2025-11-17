<?php
// filepath: app/Policies/UserPolicy.php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if user can create users
     */
    public function create(User $user): bool
    {
        return $user->canCreateUsers();
    }

    /**
     * Determine if user can view any users
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers() || $user->isManager();
    }

    /**
     * Determine if user can view specific user
     */
    public function view(User $user, User $model): bool
    {
        // Super admin can view anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can view self
        if ($user->id === $model->id) {
            return true;
        }

        // Managers can view users in their warehouse
        if ($user->isManager()) {
            return $user->warehouse_id === $model->warehouse_id;
        }

        return false;
    }

    /**
     * Determine if user can update specific user
     */
    public function update(User $user, User $model): bool
    {
        // Super admin can update anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Can update self (basic info only)
        if ($user->id === $model->id) {
            return true;
        }

        // Managers can update staff in their warehouse
        if ($user->isManager()) {
            return $user->warehouse_id === $model->warehouse_id 
                && !$model->isManager(); // Cannot update other managers
        }

        return false;
    }

    /**
     * Determine if user can delete specific user
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete self
        if ($user->id === $model->id) {
            return false;
        }

        // Super admin can delete anyone (except other super admins)
        if ($user->isSuperAdmin()) {
            return !$model->isSuperAdmin();
        }

        // Managers can delete staff in their warehouse
        if ($user->isManager()) {
            return $user->warehouse_id === $model->warehouse_id 
                && !$model->isManager(); // Cannot delete managers
        }

        return false;
    }

    /**
     * Determine if user can reset password
     */
    public function resetPassword(User $user, User $model): bool
    {
        // Cannot reset own password (use change password instead)
        if ($user->id === $model->id) {
            return false;
        }

        return $this->update($user, $model);
    }

    /**
     * Determine if user can change status
     */
    public function changeStatus(User $user, User $model): bool
    {
        // Cannot change own status
        if ($user->id === $model->id) {
            return false;
        }

        return $this->update($user, $model);
    }
}   