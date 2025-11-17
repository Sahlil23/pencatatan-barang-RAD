<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // ========================================
    // ROLE CONSTANTS
    // ========================================
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_CENTRAL_MANAGER = 'central_manager';
    const ROLE_CENTRAL_STAFF = 'central_staff';
    const ROLE_BRANCH_MANAGER = 'branch_manager';
    const ROLE_BRANCH_STAFF = 'branch_staff';
    const ROLE_OUTLET_MANAGER = 'outlet_manager';
    const ROLE_OUTLET_STAFF = 'outlet_staff';

    // ========================================
    // STATUS CONSTANTS
    // ========================================
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'role',
        'branch_id',
        'warehouse_id',
        'warehouse_access',
        'can_manage_users',
        'email',
        'phone',
        'status',
        'last_login_at',
        'last_branch_context',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'can_manage_users' => 'boolean',
            'warehouse_access' => 'array',
        ];
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the branch that owns the user.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the warehouse that owns the user.
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class);
    }

    // ========================================
    // ROLE CHECK METHODS
    // ========================================

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is admin (super admin or has manage users permission)
     * Used in blade: @if(Auth::user()->isAdmin())
     */
    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->can_manage_users;
    }

    /**
     * Check if user is any type of manager
     */
    public function isManager(): bool
    {
        return in_array($this->role, [
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_BRANCH_MANAGER,
            self::ROLE_OUTLET_MANAGER,
        ]);
    }

    /**
     * Check if user is central manager
     */
    public function isCentralManager(): bool
    {
        return $this->role === self::ROLE_CENTRAL_MANAGER;
    }

    /**
     * Check if user is branch manager
     */
    public function isBranchManager(): bool
    {
        return $this->role === self::ROLE_BRANCH_MANAGER;
    }

    /**
     * Check if user is outlet manager
     */
    public function isOutletManager(): bool
    {
        return $this->role === self::ROLE_OUTLET_MANAGER;
    }

    /**
     * Check if user is staff (any level)
     */
    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_CENTRAL_STAFF,
            self::ROLE_BRANCH_STAFF,
            self::ROLE_OUTLET_STAFF,
        ]);
    }

    /**
     * Check if user is central staff
     */
    public function isCentralStaff(): bool
    {
        return $this->role === self::ROLE_CENTRAL_STAFF;
    }

    /**
     * Check if user is branch staff
     */
    public function isBranchStaff(): bool
    {
        return $this->role === self::ROLE_BRANCH_STAFF;
    }

    /**
     * Check if user is outlet staff
     */
    public function isOutletStaff(): bool
    {
        return $this->role === self::ROLE_OUTLET_STAFF;
    }

    /**
     * Check if user works at central warehouse
     */
    public function isCentralLevel(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_CENTRAL_STAFF,
        ]);
    }

    /**
     * Check if user works at branch warehouse
     */
    public function isBranchLevel(): bool
    {
        return in_array($this->role, [
            self::ROLE_BRANCH_MANAGER,
            self::ROLE_BRANCH_STAFF,
        ]);
    }

    /**
     * Check if user works at outlet warehouse
     */
    public function isOutletLevel(): bool
    {
        return in_array($this->role, [
            self::ROLE_OUTLET_MANAGER,
            self::ROLE_OUTLET_STAFF,
        ]);
    }

    // ========================================
    // STATUS CHECK METHODS
    // ========================================

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    // ========================================
    // PERMISSION METHODS
    // ========================================

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(): bool
    {
        return $this->can_manage_users || $this->isSuperAdmin() || $this->isManager();
    }

    /**
     * Generic permission check method
     * Used by policies: $user->canView($model)
     */
    public function canView($model): bool
    {
        // Super admin can view everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check based on model type
        if ($model instanceof \App\Models\Warehouse) {
            return $this->canViewWarehouse($model->id);
        }

        if ($model instanceof \App\Models\Branch) {
            return $this->canAccessBranch($model->id);
        }

        if ($model instanceof \App\Models\User) {
            return $this->canViewUser($model);
        }

        // Default: allow if user is active
        return $this->isActive();
    }

    /**
     * Generic write permission check method
     * Used by policies: $user->canWrite($model)
     * 
     */
    public function canWrite($model): bool
    {
        // Super admin can write everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_int($model)){
            return $this->canWriteToWarehouse($model);
        }

        // Check based on model type
        if ($model instanceof \App\Models\Warehouse) {
            return $this->canManageWarehouse($model->id);
        }

        if ($model instanceof \App\Models\Branch) {
            return $this->canCreateInBranch($model->id);
        }

        if ($model instanceof \App\Models\User) {
            return $this->canEditUser($model);
        }

        // Default: managers can write
        return $this->isManager();
    }


    private function canWriteToWarehouse(int $warehouseId): bool
    {
        // Must have access to warehouse first
        if (!$this->hasWarehouseAccess($warehouseId)) {
            return false;
        }
        
        // ✅ FIX: Branch Manager CAN write to their own branch warehouse
        if ($this->isBranchManager()) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            // Can only write to branch warehouse in their branch
            return $warehouse 
                && $warehouse->warehouse_type === 'branch' 
                && $warehouse->branch_id === $this->branch_id;
        }
        
        // Staff and managers can write to their assigned warehouse
        $writeRoles = [
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_CENTRAL_STAFF,
            self::ROLE_BRANCH_MANAGER,
            self::ROLE_BRANCH_STAFF,
            self::ROLE_OUTLET_MANAGER,
            self::ROLE_OUTLET_STAFF,
        ];
        
        if (!in_array($this->role, $writeRoles)) {
            return false;
        }
        
        // Check if it's user's own warehouse
        if ($this->warehouse_id === $warehouseId) {
            return true;
        }
        
        // Central manager: can write to all central warehouses
        if ($this->isCentralManager()) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            return $warehouse && $warehouse->warehouse_type === 'central';
        }
        
        return false;
    }
    /**
     * Generic create permission check method
     * Used by policies: $user->canCreate($modelClass)
     * 
     */
    public function canCreate(string $modelClass): bool
    {
        // Super admin can create everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check based on model class
        if ($modelClass === \App\Models\Warehouse::class) {
            return $this->isManager(); // Managers can create warehouses
        }

        if ($modelClass === \App\Models\User::class) {
            return $this->canManageUsers();
        }

        if ($modelClass === \App\Models\Item::class) {
            return $this->isManager() || $this->isStaff();
        }

        // Default: only managers can create
        return $this->isManager();
    }

    /**
     * Generic delete permission check method
     * Used by policies: $user->canDelete($model)
     * 
     */
    public function canDelete($model): bool
    {
        // Super admin can delete everything
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check based on model type
        if ($model instanceof \App\Models\Warehouse) {
            return $this->canManageWarehouse($model->id);
        }

        if ($model instanceof \App\Models\User) {
            return $this->canDeleteUser($model);
        }

        // Default: only managers can delete
        return $this->isManager();
    }

    /**
     * Check if user can access specific branch
     * Alias untuk hasBranchAccess()
     * 
     */
    public function canAccessBranch(?int $branchId): bool
    {
        return $this->hasBranchAccess($branchId);
    }

    /**
     * Check if user can view another user
     * 
     */
    public function canViewUser(User $user): bool
    {
        // Super admin can view all users
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Can view self
        if ($this->id === $user->id) {
            return true;
        }

        // Managers can view users in their warehouse/branch
        if ($this->isManager()) {
            // Same warehouse
            if ($this->warehouse_id === $user->warehouse_id) {
                return true;
            }

            // Same branch
            if ($this->branch_id && $this->branch_id === $user->branch_id) {
                return true;
            }

            // Central manager can view all
            if ($this->isCentralManager()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can edit another user
     * 
     */
    public function canEditUser(User $user): bool
    {
        // Super admin can edit all users
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Cannot edit self through user management
        if ($this->id === $user->id) {
            return false;
        }

        // Must have user management permission
        if (!$this->canManageUsers()) {
            return false;
        }

        // Managers can edit users in their warehouse/branch
        if ($this->isManager()) {
            // Same warehouse
            if ($this->warehouse_id === $user->warehouse_id) {
                return true;
            }

            // Same branch
            if ($this->branch_id && $this->branch_id === $user->branch_id) {
                return true;
            }

            // Central manager can edit branch/outlet users
            if ($this->isCentralManager() && !$user->isCentralLevel()) {
                return true;
            }

            // Branch manager can edit outlet users in same branch
            if ($this->isBranchManager() && $user->isOutletLevel() && $this->branch_id === $user->branch_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user can delete another user
     * 
     */
    public function canDeleteUser(User $user): bool
    {
        // Cannot delete self
        if ($this->id === $user->id) {
            return false;
        }

        // Only super admin or managers with user management permission can delete
        if (!$this->isSuperAdmin() && !$this->canManageUsers()) {
            return false;
        }

        // Super admin can delete anyone except self
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Managers can delete users they can edit
        return $this->canEditUser($user);
    }

    /**
     * Check if user has access to specific warehouse
     */
    public function hasWarehouseAccess(int $warehouseId): bool
    {
        // Super admin has access to all
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if it's user's own warehouse
        if ($this->warehouse_id === $warehouseId) {
            return true;
        }

        // Check warehouse_access array (if exists)
        if (is_array($this->warehouse_access) && in_array($warehouseId, $this->warehouse_access)) {
            return true;
        }

        // Central manager can access all warehouses
        if ($this->isCentralManager()) {
            return true;
        }

        // Branch manager can access warehouses in their branch
        if ($this->isBranchManager() && $this->branch_id) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            if ($warehouse && $warehouse->branch_id === $this->branch_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has access to specific branch
     */
    public function hasBranchAccess(?int $branchId): bool
    {
        // Super admin has access to all
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Null branch (central warehouse) - only central users
        if ($branchId === null) {
            return $this->isCentralLevel();
        }

        // Check if it's user's own branch
        if ($this->branch_id === $branchId) {
            return true;
        }

        // Central manager can view all branches
        if ($this->isCentralManager()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can create in specific branch
     * 
     */
    public function canCreateInBranch(?int $branchId): bool
    {
        // Super admin can create anywhere
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Central manager can create in any branch
        if ($this->isCentralManager()) {
            return true;
        }

        // ✅ FIX: Branch Manager CAN create in their own branch
        if ($this->isBranchManager() && $this->branch_id === $branchId) {
            return true;
        }

        // Branch/outlet users can only create in their own branch
        if ($this->branch_id === $branchId) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can create in specific warehouse
     * 
     */
    public function canCreateInWarehouse(int $warehouseId): bool
    {
        // Super admin can create anywhere
        if ($this->isSuperAdmin()) {
            return true;
        }

        // ✅ FIX: Branch Manager CAN create in their branch warehouse
        if ($this->isBranchManager()) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            return $warehouse 
                && $warehouse->warehouse_type === 'branch' 
                && $warehouse->branch_id === $this->branch_id;
        }

        // Only managers can create
        if (!$this->isManager()) {
            return false;
        }

        // If central manager: only allow create in central warehouses
        if ($this->isCentralManager()) {
            $wh = \App\Models\Warehouse::find($warehouseId);
            return $wh && $wh->warehouse_type === 'central';
        }

        // Check if it's user's own warehouse
        if ($this->warehouse_id === $warehouseId) {
            return true;
        }

        return false;
    }

    /**
     * Get accessible warehouse IDs (array of IDs only)
     */
    public function getAccessibleWarehouseIds(): array
    {
        // Super admin gets all
        if ($this->isSuperAdmin()) {
            return \App\Models\Warehouse::pluck('id')->toArray();
        }

        $warehouseIds = [];

        // Add own warehouse
        if ($this->warehouse_id) {
            $warehouseIds[] = $this->warehouse_id;
        }

        // Add from warehouse_access
        if (is_array($this->warehouse_access)) {
            $warehouseIds = array_merge($warehouseIds, $this->warehouse_access);
        }

        // Central manager gets all warehouses
        if ($this->isCentralManager()) {
            $allWarehouses = \App\Models\Warehouse::pluck('id')->toArray();
            $warehouseIds = array_merge($warehouseIds, $allWarehouses);
        }

        // Branch manager gets all warehouses in their branch
        if ($this->isBranchManager() && $this->warehouse_id) {
            $childWarehouses = \App\Models\Warehouse::where('id', $this->warehouse_id)
                ->pluck('id')
                ->toArray();
            $warehouseIds = array_merge($warehouseIds, $childWarehouses);
        }

        return array_unique($warehouseIds);
    }

    /**
     * Get accessible warehouses (Collection of Warehouse models)
     */
    public function getAccessibleWarehouses()
    {
        $warehouseIds = $this->getAccessibleWarehouseIds();
        
        return \App\Models\Warehouse::whereIn('id', $warehouseIds)
            ->orderBy('warehouse_name')
            ->get();
    }

    /**
     * Get accessible warehouses by type
     */
    public function getAccessibleWarehousesByType(string $type)
    {
        $warehouseIds = $this->getAccessibleWarehouseIds();
        
        return \App\Models\Warehouse::whereIn('id', $warehouseIds)
            ->where('warehouse_type', $type)
            ->where('status', 'ACTIVE')
            ->orderBy('warehouse_name')
            ->get();
    }

    /**
     * Get accessible central warehouses
     */
    public function getAccessibleCentralWarehouses()
    {
        return $this->getAccessibleWarehousesByType('central');
    }

    /**
     * Get accessible branch warehouses
     */
    public function getAccessibleBranchWarehouses()
    {
        return $this->getAccessibleWarehousesByType('branch');
    }

    /**
     * Get accessible outlet warehouses
     */
    public function getAccessibleOutletWarehouses()
    {
        return $this->getAccessibleWarehousesByType('outlet');
    }

    /**
     * Get accessible branch IDs (array of IDs only)
     */
    public function getAccessibleBranchIds(): array
    {
        // Super admin gets all
        if ($this->isSuperAdmin()) {
            return \App\Models\Branch::pluck('id')->toArray();
        }

        // Central manager gets all
        if ($this->isCentralManager()) {
            return \App\Models\Branch::pluck('id')->toArray();
        }

        // Others only their branch
        if ($this->branch_id) {
            return [$this->branch_id];
        }

        return [];
    }

    /**
     * Get accessible branches (Collection of Branch models)
     */
    public function getAccessibleBranches()
    {
        $branchIds = $this->getAccessibleBranchIds();
        
        return \App\Models\Branch::whereIn('id', $branchIds)
            ->where('status', 'active')
            ->orderBy('branch_name')
            ->get();
    }

    /**
     * Check if user can access specific warehouse type
     */
    public function canAccessWarehouseType(string $type): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return match($type) {
            'central' => $this->isCentralLevel(),
            'branch' => $this->isCentralLevel() || $this->isBranchLevel(),
            'outlet' => true, // All authenticated users can access outlet
            default => false
        };
    }

    /**
     * Get user's primary warehouse
     */
    public function getPrimaryWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * Get user's primary branch
     */
    public function getPrimaryBranch()
    {
        return $this->branch;
    }

    /**
     * Check if user has multiple warehouse access
     */
    public function hasMultipleWarehouseAccess(): bool
    {
        return count($this->getAccessibleWarehouseIds()) > 1;
    }

    /**
     * Check if user can manage specific warehouse
     */
    public function canManageWarehouse(int $warehouseId): bool
    {
        // Super admin can manage all
        if ($this->isSuperAdmin()) {
            return true;
        }

        // ✅ FIX: Branch Manager CAN manage their branch warehouse ONLY
        if ($this->isBranchManager()) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            return $warehouse 
                && $warehouse->warehouse_type === 'branch' 
                && $warehouse->branch_id === $this->branch_id;
        }

        // Central manager: only manage central warehouses
        if ($this->isCentralManager()) {
            $wh = \App\Models\Warehouse::find($warehouseId);
            return $wh && $wh->warehouse_type === 'central';
        }

        // User can manage their own warehouse if they are manager
        return $this->warehouse_id === $warehouseId && $this->isManager();
    }

    /**
     * Check if user can view specific warehouse (read-only)
     */
    public function canViewWarehouse(int $warehouseId): bool
    {
        return $this->hasWarehouseAccess($warehouseId);
    }

    /**
     * Get warehouse access level for specific warehouse
     * Returns: 'full', 'read-only', or 'none'
     */
    public function getWarehouseAccessLevel(int $warehouseId): string
    {
        if (!$this->hasWarehouseAccess($warehouseId)) {
            return 'none';
        }

        // ✅ FIX: Branch Manager gets full access to branch warehouse, read-only for outlets
        if ($this->isBranchManager()) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            if ($warehouse) {
                // Full access to branch warehouse in their branch
                if ($warehouse->warehouse_type === 'branch' && $warehouse->branch_id === $this->branch_id) {
                    return 'full';
                }
                // Read-only for outlet warehouses in their branch
                if ($warehouse->warehouse_type === 'outlet' && $warehouse->branch_id === $this->branch_id) {
                    return 'read-only';
                }
            }
            return 'read-only';
        }

        if ($this->canManageWarehouse($warehouseId)) {
            return 'full';
        }

        return 'read-only';
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get user's role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_CENTRAL_MANAGER => 'Central Manager',
            self::ROLE_CENTRAL_STAFF => 'Central Staff',
            self::ROLE_BRANCH_MANAGER => 'Branch Manager',
            self::ROLE_BRANCH_STAFF => 'Branch Staff',
            self::ROLE_OUTLET_MANAGER => 'Outlet Manager',
            self::ROLE_OUTLET_STAFF => 'Outlet Staff',
            default => ucfirst(str_replace('_', ' ', $this->role))
        };
    }

    /**
     * Get user's status display name
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get user's status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-success',
            self::STATUS_INACTIVE => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope to filter active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter inactive users
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to filter by role
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to filter managers
     */
    public function scopeManagers($query)
    {
        return $query->whereIn('role', [
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_BRANCH_MANAGER,
            self::ROLE_OUTLET_MANAGER,
        ]);
    }

    /**
     * Scope to filter staff
     */
    public function scopeStaff($query)
    {
        return $query->whereIn('role', [
            self::ROLE_CENTRAL_STAFF,
            self::ROLE_BRANCH_STAFF,
            self::ROLE_OUTLET_STAFF,
        ]);
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope to filter by branch
     */
    public function scopeBranch($query, ?int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Check if given warehouse is lower level than user's warehouse
     * Central > Branch > Outlet
     * 
     */
    public function isLowerLevelWarehouse(int $warehouseId): bool
    {
        // Super admin has no lower level concept
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Get user's warehouse
        $userWarehouse = $this->warehouse;
        if (!$userWarehouse) {
            return false;
        }

        // Get target warehouse
        $targetWarehouse = \App\Models\Warehouse::find($warehouseId);
        if (!$targetWarehouse) {
            return false;
        }

        // Define hierarchy levels
        $hierarchyLevels = [
            'central' => 3,
            'branch' => 2,
            'outlet' => 1,
        ];

        $userLevel = $hierarchyLevels[$userWarehouse->warehouse_type] ?? 0;
        $targetLevel = $hierarchyLevels[$targetWarehouse->warehouse_type] ?? 0;

        // Target is lower if its level number is less than user's level
        return $targetLevel < $userLevel;
    }

    /**
     * Check if given warehouse is higher level than user's warehouse
     * 
     */
    public function isHigherLevelWarehouse(int $warehouseId): bool
    {
        // Super admin has no higher level concept
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Get user's warehouse
        $userWarehouse = $this->warehouse;
        if (!$userWarehouse) {
            return false;
        }

        // Get target warehouse
        $targetWarehouse = \App\Models\Warehouse::find($warehouseId);
        if (!$targetWarehouse) {
            return false;
        }

        // Define hierarchy levels
        $hierarchyLevels = [
            'central' => 3,
            'branch' => 2,
            'outlet' => 1,
        ];

        $userLevel = $hierarchyLevels[$userWarehouse->warehouse_type] ?? 0;
        $targetLevel = $hierarchyLevels[$targetWarehouse->warehouse_type] ?? 0;

        // Target is higher if its level number is greater than user's level
        return $targetLevel > $userLevel;
    }

    /**
     * Check if given warehouse is same level as user's warehouse
     * 
     */
    public function isSameLevelWarehouse(int $warehouseId): bool
    {
        // Super admin has no level concept
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Get user's warehouse
        $userWarehouse = $this->warehouse;
        if (!$userWarehouse) {
            return false;
        }

        // Get target warehouse
        $targetWarehouse = \App\Models\Warehouse::find($warehouseId);
        if (!$targetWarehouse) {
            return false;
        }

        return $userWarehouse->warehouse_type === $targetWarehouse->warehouse_type;
    }

    /**
     * Get warehouse hierarchy level number
     * Central = 3, Branch = 2, Outlet = 1
     * 
     */
    public function getWarehouseLevel(): int
    {
        if (!$this->warehouse) {
            return 0;
        }

        $hierarchyLevels = [
            'central' => 3,
            'branch' => 2,
            'outlet' => 1,
        ];

        return $hierarchyLevels[$this->warehouse->warehouse_type] ?? 0;
    }

    /**
     * Check if user can distribute to given warehouse
     * Can only distribute to lower level warehouses
     * 
     */
    public function canDistributeTo(int $warehouseId): bool
    {
        // Super admin can distribute anywhere
        if ($this->isSuperAdmin()) {
            return true;
        }

        // ✅ FIX: Branch Manager CAN distribute to outlet warehouses in their branch
        if ($this->isBranchManager()) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            // Can only distribute to outlet warehouses in same branch
            return $warehouse 
                && $warehouse->warehouse_type === 'outlet' 
                && $warehouse->branch_id === $this->branch_id;
        }

        // Must be manager to distribute
        if (!$this->isManager()) {
            return false;
        }

        // Can only distribute to lower level warehouses
        return $this->isLowerLevelWarehouse($warehouseId);
    }

    /**
     * Check if user can receive from given warehouse
     * Can only receive from higher level warehouses
     * 
     */
    public function canReceiveFrom(int $warehouseId): bool
    {
        // Super admin can receive from anywhere
        if ($this->isSuperAdmin()) {
            return true;
        }

        // ✅ FIX: Branch Manager CAN receive from central warehouse
        if ($this->isBranchManager()) {
            $warehouse = \App\Models\Warehouse::find($warehouseId);
            // Can only receive from central warehouses
            return $warehouse && $warehouse->warehouse_type === 'central';
        }

        // Must be manager to receive
        if (!$this->isManager()) {
            return false;
        }

        // Can only receive from higher level warehouses
        return $this->isHigherLevelWarehouse($warehouseId);
    }

    /**
     * Get warehouses that user can distribute to
     * 
     */
    public function getDistributableWarehouses()
    {
        // ✅ FIX: Branch Manager can distribute to outlet warehouses in their branch
        if ($this->isBranchManager()) {
            return \App\Models\Warehouse::where('warehouse_type', 'outlet')
                ->where('branch_id', $this->branch_id)
                ->where('status', 'ACTIVE')
                ->orderBy('warehouse_name')
                ->get();
        }

        // Super admin can distribute to all
        if ($this->isSuperAdmin()) {
            return \App\Models\Warehouse::where('status', 'ACTIVE')->get();
        }

        // Get all accessible warehouses
        $accessibleIds = $this->getAccessibleWarehouseIds();
        
        // Filter only lower level warehouses
        return \App\Models\Warehouse::whereIn('id', $accessibleIds)
            ->where('status', 'ACTIVE')
            ->get()
            ->filter(function ($warehouse) {
                return $this->isLowerLevelWarehouse($warehouse->id);
            });
    }

    /**
     * Get warehouses that user can receive from
     * 
     */
    public function getReceivableWarehouses()
    {
        // ✅ FIX: Branch Manager can receive from central warehouses
        if ($this->isBranchManager()) {
            return \App\Models\Warehouse::where('warehouse_type', 'central')
                ->where('status', 'ACTIVE')
                ->orderBy('warehouse_name')
                ->get();
        }

        // Super admin can receive from all
        if ($this->isSuperAdmin()) {
            return \App\Models\Warehouse::where('status', 'ACTIVE')->get();
        }

        // Get all accessible warehouses
        $accessibleIds = $this->getAccessibleWarehouseIds();
        
        // Filter only higher level warehouses
        return \App\Models\Warehouse::whereIn('id', $accessibleIds)
            ->where('status', 'ACTIVE')
            ->get()
            ->filter(function ($warehouse) {
                return $this->isHigherLevelWarehouse($warehouse->id);
            });
    }

    /**
     * Check if user can create users
     */
    public function canCreateUsers(): bool
    {
        return $this->isManager() || $this->isSuperAdmin();
    }

    /**
     * Get roles that current user can assign to new users
     */
    public function getAssignableRoles(): array
    {
        if ($this->isSuperAdmin()) {
            return [
                self::ROLE_SUPER_ADMIN => 'Super Admin',
                self::ROLE_CENTRAL_MANAGER => 'Central Manager',
                self::ROLE_CENTRAL_STAFF => 'Central Staff',
                self::ROLE_BRANCH_MANAGER => 'Branch Manager',
                self::ROLE_BRANCH_STAFF => 'Branch Staff',
                self::ROLE_OUTLET_MANAGER => 'Outlet Manager',
                self::ROLE_OUTLET_STAFF => 'Outlet Staff',
            ];
        }

        if ($this->isCentralManager()) {
            return [
                self::ROLE_CENTRAL_STAFF => 'Central Staff',
            ];
        }

        if ($this->isBranchManager()) {
            return [
                self::ROLE_BRANCH_STAFF => 'Branch Staff',
            ];
        }

        if ($this->isOutletManager()) {
            return [
                self::ROLE_OUTLET_STAFF => 'Outlet Staff',
            ];
        }

        return [];
    }

    /**
     * Get allowed warehouse types for user creation
     */
    public function getAllowedWarehouseTypesForUserCreation(): array
    {
        if ($this->isSuperAdmin()) {
            return ['central', 'branch', 'outlet'];
        }

        if ($this->isCentralManager()) {
            return ['central'];
        }

        if ($this->isBranchManager()) {
            return ['branch'];
        }

        if ($this->isOutletManager()) {
            return ['outlet'];
        }

        return [];
    }
}