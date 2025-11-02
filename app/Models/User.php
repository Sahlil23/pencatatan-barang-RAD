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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'password',
        'full_name',
        'role',
        'branch_id',
        'warehouse_access',
        'email',
        'phone',
        'status',
        'last_login_at',
        'last_branch_context'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'warehouse_access' => 'array'
        ];
    }

    protected $dates = [
        'last_login_at',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // ========================================
    // ROLE CONSTANTS - MULTI-BRANCH SYSTEM
    // ========================================

    /**
     * Super Admin - Full system access
     */
    const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * Central Roles - Work at central office/warehouse
     */
    const ROLE_CENTRAL_ADMIN = 'central_admin';
    const ROLE_CENTRAL_MANAGER = 'central_manager';
    const ROLE_CENTRAL_STAFF = 'central_staff';
    const ROLE_WAREHOUSE_MANAGER = 'warehouse_manager';
    const ROLE_WAREHOUSE_STAFF = 'warehouse_staff';

    /**
     * Branch Roles - Work at specific branch
     */
    const ROLE_BRANCH_MANAGER = 'branch_manager';
    const ROLE_BRANCH_ADMIN = 'branch_admin';
    const ROLE_BRANCH_STAFF = 'branch_staff';
    const ROLE_KITCHEN_MANAGER = 'kitchen_manager';
    const ROLE_KITCHEN_STAFF = 'kitchen_staff';

    /**
     * Available statuses
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * User belongs to branch (nullable untuk central users)
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * User dapat akses multiple warehouses
     */
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'user_warehouse_access')
                    ->withTimestamps();
    }

    /**
     * Stock transactions created by this user
     */
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    /**
     * Central stock transactions
     */
    public function centralStockTransactions()
    {
        return $this->hasMany(CentralStockTransaction::class);
    }

    /**
     * Branch stock transactions
     */
    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class);
    }

    /**
     * Kitchen stock transactions
     */
    public function kitchenStockTransactions()
    {
        return $this->hasMany(KitchenStockTransaction::class);
    }

    /**
     * Distribution orders requested by this user
     */
    public function requestedDistributionOrders()
    {
        return $this->hasMany(DistributionOrder::class, 'requested_by');
    }

    /**
     * Distribution orders approved by this user
     */
    public function approvedDistributionOrders()
    {
        return $this->hasMany(DistributionOrder::class, 'approved_by');
    }

    /**
     * Distribution orders prepared by this user
     */
    public function preparedDistributionOrders()
    {
        return $this->hasMany(DistributionOrder::class, 'prepared_by');
    }

    /**
     * Stock periods closed by this user
     */
    public function closedStockPeriods()
    {
        return $this->hasMany(StockPeriod::class, 'closed_by');
    }

    // ========================================
    // ROLE METHODS
    // ========================================

    /**
     * Get available roles
     */
    public static function getRoles()
    {
        return [
            // Super Admin
            self::ROLE_SUPER_ADMIN => 'Super Administrator',
            
            // Central Roles
            self::ROLE_CENTRAL_ADMIN => 'Central Administrator',
            self::ROLE_CENTRAL_MANAGER => 'Central Manager',
            self::ROLE_CENTRAL_STAFF => 'Central Staff',
            self::ROLE_WAREHOUSE_MANAGER => 'Warehouse Manager',
            self::ROLE_WAREHOUSE_STAFF => 'Warehouse Staff',
            
            // Branch Roles
            self::ROLE_BRANCH_MANAGER => 'Branch Manager',
            self::ROLE_BRANCH_ADMIN => 'Branch Administrator',
            self::ROLE_BRANCH_STAFF => 'Branch Staff',
            self::ROLE_KITCHEN_MANAGER => 'Kitchen Manager',
            self::ROLE_KITCHEN_STAFF => 'Kitchen Staff',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    /**
     * Get central roles
     */
    public static function getCentralRoles()
    {
        return [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CENTRAL_ADMIN,
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_CENTRAL_STAFF,
            self::ROLE_WAREHOUSE_MANAGER,
            self::ROLE_WAREHOUSE_STAFF
        ];
    }

    /**
     * Get branch roles
     */
    public static function getBranchRoles()
    {
        return [
            self::ROLE_BRANCH_MANAGER,
            self::ROLE_BRANCH_ADMIN,
            self::ROLE_BRANCH_STAFF,
            self::ROLE_KITCHEN_MANAGER,
            self::ROLE_KITCHEN_STAFF
        ];
    }

    // ========================================
    // PERMISSION METHODS
    // ========================================

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is central user (works at central office/warehouse)
     */
    public function isCentralUser()
    {
        return in_array($this->role, self::getCentralRoles());
    }

    /**
     * Check if user is branch user (works at specific branch)
     */
    public function isBranchUser()
    {
        return in_array($this->role, self::getBranchRoles()) && $this->branch_id !== null;
    }

    /**
     * Check if user has admin privileges
     */
    public function isAdmin()
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CENTRAL_ADMIN,
            self::ROLE_BRANCH_ADMIN
        ]);
    }

    /**
     * Check if user has manager privileges
     */
    public function isManager()
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_WAREHOUSE_MANAGER,
            self::ROLE_BRANCH_MANAGER,
            self::ROLE_KITCHEN_MANAGER
        ]);
    }

    /**
     * Check if user is staff level
     */
    public function isStaff()
    {
        return in_array($this->role, [
            self::ROLE_CENTRAL_STAFF,
            self::ROLE_WAREHOUSE_STAFF,
            self::ROLE_BRANCH_STAFF,
            self::ROLE_KITCHEN_STAFF
        ]);
    }

    /**
     * Check if user can manage warehouses
     */
    public function canManageWarehouses()
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CENTRAL_ADMIN,
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_WAREHOUSE_MANAGER
        ]);
    }

    /**
     * Check if user can manage kitchen
     */
    public function canManageKitchen()
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_BRANCH_MANAGER,
            self::ROLE_BRANCH_ADMIN,
            self::ROLE_KITCHEN_MANAGER
        ]);
    }

    /**
     * Check if user can approve distribution orders
     */
    public function canApproveDistribution()
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CENTRAL_ADMIN,
            self::ROLE_CENTRAL_MANAGER,
            self::ROLE_WAREHOUSE_MANAGER
        ]);
    }

    /**
     * Check if user can close stock periods
     */
    public function canCloseStockPeriods()
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_CENTRAL_ADMIN,
            self::ROLE_BRANCH_ADMIN,
            self::ROLE_BRANCH_MANAGER
        ]);
    }

    // ========================================
    // ACCESS CONTROL METHODS
    // ========================================

    /**
     * Check if user can access specific branch
     */
    public function canAccessBranch($branchId)
    {
        // Super admin dapat akses semua branch
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Central users dapat akses semua branch
        if ($this->isCentralUser()) {
            return true;
        }

        // Branch users hanya dapat akses branch mereka sendiri
        if ($this->isBranchUser()) {
            return $this->branch_id == $branchId;
        }

        return false;
    }

    /**
     * Check if user can access specific warehouse
     */
    public function canAccessWarehouse($warehouseId)
    {
        // Super admin dapat akses semua warehouse
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check warehouse access array
        $warehouseAccess = $this->warehouse_access ?? [];
        
        if (in_array($warehouseId, $warehouseAccess)) {
            return true;
        }

        // Check melalui relationship
        return $this->warehouses()->where('warehouses.id', $warehouseId)->exists();
    }

    /**
     * Get accessible warehouses untuk user ini
     */
    public function getAccessibleWarehouses()
    {
        if ($this->isSuperAdmin()) {
            return Warehouse::all();
        }

        if ($this->isCentralUser()) {
            return Warehouse::central()->get();
        }

        if ($this->isBranchUser() && $this->branch_id) {
            return Warehouse::where('branch_id', $this->branch_id)->get();
        }

        return collect();
    }

    /**
     * Get accessible branches untuk user ini
     */
    public function getAccessibleBranches()
    {
        if ($this->isSuperAdmin() || $this->isCentralUser()) {
            return Branch::all();
        }

        if ($this->isBranchUser() && $this->branch_id) {
            return Branch::where('id', $this->branch_id)->get();
        }

        return collect();
    }

    /**
     * Add warehouse access
     */
    public function addWarehouseAccess($warehouseId)
    {
        $warehouseAccess = $this->warehouse_access ?? [];
        
        if (!in_array($warehouseId, $warehouseAccess)) {
            $warehouseAccess[] = $warehouseId;
            $this->warehouse_access = $warehouseAccess;
            $this->save();
        }

        return $this;
    }

    /**
     * Remove warehouse access
     */
    public function removeWarehouseAccess($warehouseId)
    {
        $warehouseAccess = $this->warehouse_access ?? [];
        
        if (($key = array_search($warehouseId, $warehouseAccess)) !== false) {
            unset($warehouseAccess[$key]);
            $this->warehouse_access = array_values($warehouseAccess);
            $this->save();
        }

        return $this;
    }

    // ========================================
    // CONTEXT METHODS
    // ========================================

    /**
     * Get current branch context
     */
    public function getCurrentBranchContext()
    {
        // Jika ada last_branch_context, gunakan itu
        if ($this->last_branch_context) {
            $branch = Branch::find($this->last_branch_context);
            if ($branch && $this->canAccessBranch($branch->id)) {
                return $branch;
            }
        }

        // Jika user adalah branch user, return branch mereka
        if ($this->isBranchUser() && $this->branch_id) {
            return $this->branch;
        }

        // Untuk central user, return branch pertama yang bisa diakses
        if ($this->isCentralUser()) {
            return Branch::first();
        }

        return null;
    }

    /**
     * Set current branch context
     */
    public function setCurrentBranchContext($branchId)
    {
        if ($this->canAccessBranch($branchId)) {
            $this->last_branch_context = $branchId;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Get current warehouse context (main warehouse dari current branch)
     */
    public function getCurrentWarehouseContext()
    {
        $currentBranch = $this->getCurrentBranchContext();
        
        if ($currentBranch) {
            return $currentBranch->mainWarehouse();
        }

        // Untuk central users, return central warehouse
        if ($this->isCentralUser()) {
            return Warehouse::central()->first();
        }

        return null;
    }

    // ========================================
    // STATUS METHODS
    // ========================================

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user is suspended
     */
    public function isSuspended()
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Activate user
     */
    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
        return $this;
    }

    /**
     * Deactivate user
     */
    public function deactivate()
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
        return $this;
    }

    /**
     * Suspend user
     */
    public function suspend()
    {
        $this->status = self::STATUS_SUSPENDED;
        $this->save();
        return $this;
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope for active users only
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('role', self::ROLE_SUPER_ADMIN);
    }

    /**
     * Scope for central users
     */
    public function scopeCentralUsers($query)
    {
        return $query->whereIn('role', self::getCentralRoles());
    }

    /**
     * Scope for branch users
     */
    public function scopeBranchUsers($query)
    {
        return $query->whereIn('role', self::getBranchRoles());
    }

    /**
     * Scope for specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope for users with warehouse access
     */
    public function scopeWithWarehouseAccess($query, $warehouseId)
    {
        return $query->where(function($q) use ($warehouseId) {
            $q->whereJsonContains('warehouse_access', $warehouseId)
              ->orWhereHas('warehouses', function($warehouseQuery) use ($warehouseId) {
                  $warehouseQuery->where('warehouses.id', $warehouseId);
              });
        });
    }

    /**
     * Scope for search users
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('username', 'like', "%{$search}%")
              ->orWhere('full_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get role name
     */
    public function getRoleName()
    {
        return self::getRoles()[$this->role] ?? 'Unknown';
    }

    /**
     * Get status name
     */
    public function getStatusName()
    {
        return self::getStatuses()[$this->status] ?? 'Unknown';
    }

    /**
     * Get role badge untuk UI
     */
    public function getRoleBadgeAttribute()
    {
        $roleColors = [
            self::ROLE_SUPER_ADMIN => 'danger',
            self::ROLE_CENTRAL_ADMIN => 'primary',
            self::ROLE_CENTRAL_MANAGER => 'info',
            self::ROLE_WAREHOUSE_MANAGER => 'info',
            self::ROLE_BRANCH_MANAGER => 'success',
            self::ROLE_KITCHEN_MANAGER => 'warning',
            'default' => 'secondary',
        ];

        $color = $roleColors[$this->role] ?? $roleColors['default'];
        $roleName = $this->getRoleName();

        return "<span class=\"badge bg-{$color}\">{$roleName}</span>";
    }

    /**
     * Get status badge untuk UI
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_ACTIVE => '<span class="badge bg-success">Aktif</span>',
            self::STATUS_INACTIVE => '<span class="badge bg-warning">Tidak Aktif</span>',
            self::STATUS_SUSPENDED => '<span class="badge bg-danger">Suspended</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get user context display
     */
    public function getContextDisplayAttribute()
    {
        if ($this->isSuperAdmin()) {
            return 'System Wide';
        }

        if ($this->isCentralUser()) {
            return 'Central Office';
        }

        if ($this->isBranchUser() && $this->branch) {
            return $this->branch->branch_name;
        }

        return 'No Context';
    }

    /**
     * Get warehouse access display
     */
    public function getWarehouseAccessDisplayAttribute()
    {
        if ($this->isSuperAdmin()) {
            return 'All Warehouses';
        }

        $warehouses = $this->getAccessibleWarehouses();
        return $warehouses->pluck('warehouse_name')->implode(', ') ?: 'No Access';
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules($id = null)
    {
        return [
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'password' => $id ? 'nullable|string|min:8' : 'required|string|min:8',
            'full_name' => 'required|string|max:100',
            'role' => 'required|in:' . implode(',', array_keys(self::getRoles())),
            'branch_id' => 'nullable|exists:branches,id',
            'email' => 'nullable|email|max:100|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:' . implode(',', array_keys(self::getStatuses())),
            'warehouse_access' => 'nullable|array',
            'warehouse_access.*' => 'exists:warehouses,id'
        ];
    }

    public static function validationMessages()
    {
        return [
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'full_name.required' => 'Nama lengkap wajib diisi',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role tidak valid',
            'branch_id.exists' => 'Branch tidak valid',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'status.required' => 'Status wajib dipilih'
        ];
    }
}
