<?php
// filepath: app/Policies/UserPolicy.php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     * (List/Index page)
     */
    public function viewAny(User $user): bool
    {
        // Super admin: can view all users
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Managers: can view users in their warehouse
        if ($user->canManageUsers()) {
            return true;
        }

        // Staff: cannot view user list
        return false;
    }

    /**
     * Determine whether the user can view the target user.
     */
    public function view(User $user, User $targetUser): bool
    {
        // Can view own profile
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Super admin: can view all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: can view users in their warehouse
        if ($user->canManageUsers()) {
            // Same warehouse
            if ($targetUser->warehouse_id === $user->warehouse_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Managers: can create staff in their warehouse
        if ($user->canManageUsers()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the target user.
     */
    public function update(User $user, User $targetUser): bool
    {
        // Can edit own profile (limited fields)
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Super admin: can edit all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Use User model's canManageUser method
        return $user->canManageUser($targetUser);
    }

    /**
     * Determine whether the user can delete the target user.
     */
    public function delete(User $user, User $targetUser): bool
    {
        // Cannot delete self
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Super admin: can delete (except other super admins - optional)
        if ($user->isSuperAdmin()) {
            // Optional: prevent deleting other super admins
            // return !$targetUser->isSuperAdmin();
            return true;
        }

        // Use User model's canManageUser method
        return $user->canManageUser($targetUser);
    }

    /**
     * Determine whether the user can restore the target user.
     */
    public function restore(User $user, User $targetUser): bool
    {
        // Super admin: can restore
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: can restore staff they could manage
        return $user->canManageUser($targetUser);
    }

    /**
     * Determine whether the user can permanently delete the target user.
     */
    public function forceDelete(User $user, User $targetUser): bool
    {
        // Only super admin can force delete
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can change the role of target user.
     */
    public function changeRole(User $user, User $targetUser): bool
    {
        // Super admin: can change any role
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Managers: cannot change roles (fixed as staff)
        return false;
    }

    /**
     * Determine whether the user can change warehouse assignment.
     */
    public function changeWarehouse(User $user, User $targetUser): bool
    {
        // Only super admin can reassign warehouses
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can change branch assignment.
     */
    public function changeBranch(User $user, User $targetUser): bool
    {
        // Only super admin can reassign branches
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can activate/deactivate users.
     */
    public function changeStatus(User $user, User $targetUser): bool
    {
        // Cannot change own status
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Super admin: yes
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: can change status of their staff
        return $user->canManageUser($targetUser);
    }

    /**
     * Determine whether the user can reset password for target user.
     */
    public function resetPassword(User $user, User $targetUser): bool
    {
        // Can reset own password
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Super admin: can reset any password
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: can reset password for their staff
        return $user->canManageUser($targetUser);
    }

    /**
     * Determine whether the user can view activity logs of target user.
     */
    public function viewActivityLogs(User $user, User $targetUser): bool
    {
        // Can view own logs
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Super admin: can view all logs
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: can view logs of their staff
        if ($user->isManager() && $targetUser->warehouse_id === $user->warehouse_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can impersonate (login as) target user.
     */
    public function impersonate(User $user, User $targetUser): bool
    {
        // Only super admin can impersonate
        if (!$user->isSuperAdmin()) {
            return false;
        }

        // Cannot impersonate self
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Cannot impersonate other super admins (optional)
        if ($targetUser->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can manage permissions of target user.
     */
    public function managePermissions(User $user, User $targetUser): bool
    {
        // Only super admin can manage permissions
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view user statistics/reports.
     */
    public function viewStatistics(User $user): bool
    {
        // Super admin: all statistics
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Manager: statistics for their warehouse
        if ($user->isManager()) {
            return true;
        }

        return false;
    }
}