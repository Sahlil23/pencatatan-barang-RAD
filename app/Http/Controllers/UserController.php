<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warehouse;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Constructor - Apply middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('set.branch.context');
    }

    // ========================================
    // 📋 LIST USERS
    // ========================================

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            // Check policy
            $this->authorize('viewAny', User::class);

            // Build query
            $query = User::with(['branch', 'warehouse']);

            // Apply warehouse filter (managers only see their warehouse users)
            if (!$this->isSuperAdmin()) {
                $query = $this->applyWarehouseFilter($query, 'warehouse_id');
            }

            // Apply branch filter (jika branch context aktif)
            if ($request->filled('branch_id')) {
                $branchId = $request->input('branch_id');
                $this->validateBranchAccess($branchId);
                $query->where('branch_id', $branchId);
            } elseif ($this->getBranchId($request)) {
                $query->where('branch_id', $this->getBranchId($request));
            }

            // Apply search filter
            $query = $this->applySearchFilter($query, $request, [
                'username',
                'full_name',
                'email',
                'phone'
            ]);

            // Filter by role
            if ($request->filled('role')) {
                $query->where('role', $request->input('role'));
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            // Sort
            $query->orderBy('created_at', 'desc');

            // Get pagination
            $perPage = $this->getPerPage($request, 20);
            $users = $query->paginate($perPage)->appends($request->query());

            // Get common data
            $commonData = $this->getCommonViewData($request);

            return view('users.index', array_merge($commonData, [
                'users' => $users,
                'roles' => $this->getAvailableRoles(),
                'statuses' => [
                    'ACTIVE' => 'Active',
                    'INACTIVE' => 'Inactive',
                    'SUSPENDED' => 'Suspended'
                ]
            ]));

        } catch (\Exception $e) {
            Log::error('User index error: ' . $e->getMessage());
            return $this->errorResponse('Error loading users: ' . $e->getMessage());
        }
    }

    // ========================================
    // ➕ CREATE USER
    // ========================================

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        try {
            // Check policy
            $this->authorize('create', User::class);

            $user = $this->currentUser();

            // Get available roles based on current user
            $availableRoles = $this->getAvailableRoles();

            // Get accessible warehouses & branches
            $warehouses = $this->getAccessibleWarehouses();
            $branches = $this->getAccessibleBranches();

            $commonData = $this->getCommonViewData(request());

            return view('users.create', array_merge($commonData, [
                'availableRoles' => $availableRoles,
                'warehouses' => $warehouses,
                'branches' => $branches,
                'statuses' => [
                    'ACTIVE' => 'Active',
                    'INACTIVE' => 'Inactive'
                ]
            ]));

        } catch (\Exception $e) {
            Log::error('User create form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        try {
            // Check policy
            $this->authorize('create', User::class);

            // Validation rules
            $rules = [
                'username' => 'required|string|max:50|unique:users,username',
                'email' => 'nullable|email|max:100|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'full_name' => 'required|string|max:100',
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:' . implode(',', array_keys($this->getAvailableRoles())),
                'status' => 'required|in:ACTIVE,INACTIVE',
                'branch_id' => 'nullable|exists:branches,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
            ];

            // Additional rules for super admin
            if ($this->isSuperAdmin()) {
                $rules['can_manage_users'] = 'nullable|boolean';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $validator->validated();

            // Override for managers (tidak bisa set sembarangan warehouse/branch)
            if (!$this->isSuperAdmin()) {
                // Manager hanya bisa create staff di warehouse/branch mereka
                $validated['warehouse_id'] = $this->currentUser()->warehouse_id;
                $validated['branch_id'] = $this->currentUser()->branch_id;
                $validated['can_manage_users'] = false;
                
                // Validate role (hanya bisa create staff)
                $allowedRole = $this->getStaffRoleForManager($this->currentUser()->role);
                if ($validated['role'] !== $allowedRole) {
                    return $this->errorResponse('You can only create staff at your level.');
                }
            } else {
                // Super admin: validate warehouse/branch sesuai role
                $this->validateRoleRequirements($validated);
            }

            return $this->executeTransaction(
                function () use ($validated) {
                    // Hash password
                    $validated['password'] = Hash::make($validated['password']);

                    $user = User::create($validated);

                    // Log activity
                    // $this->logActivity('create_user', 'User', $user->id, [
                    //     'username' => $user->username,
                    //     'role' => $user->role,
                    //     'warehouse_id' => $user->warehouse_id,
                    //     'branch_id' => $user->branch_id
                    // ]);

                    return $user;
                },
                'User created successfully',
                'Failed to create user'
            );

        } catch (\Exception $e) {
            Log::error('User store error: ' . $e->getMessage());
            return $this->errorResponse('Error creating user: ' . $e->getMessage());
        }
    }

    // ========================================
    // 👁️ VIEW USER
    // ========================================

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        try {
            // Check policy
            $this->authorize('view', $user);

            // Load relationships
            $user->load(['branch', 'warehouse']);

            // Get user statistics
            $stats = [
                'total_logins' => 0, // Implement jika ada login tracking
                'last_login' => $user->last_login_at,
                'created_at' => $user->created_at,
                'days_active' => $user->created_at ? $user->created_at->diffInDays(now()) : 0,
            ];

            // Get recent activities (jika ada activity log)
            $recentActivities = collect(); // TODO: Implement activity log

            $commonData = $this->getCommonViewData(request());

            return view('users.show', array_merge($commonData, [
                'user' => $user,
                'canEdit' => $this->currentUser()->can('update', $user),
                'canDelete' => $this->currentUser()->can('delete', $user),
                'canResetPassword' => $this->currentUser()->can('resetPassword', $user),
                'canChangeStatus' => $this->currentUser()->can('changeStatus', $user),
                'stats' => $stats,
                'recentActivities' => $recentActivities
            ]));

        } catch (\Exception $e) {
            Log::error('User show error: ' . $e->getMessage());
            return $this->errorResponse('Error loading user: ' . $e->getMessage());
        }
    }

    // ========================================
    // ✏️ EDIT USER
    // ========================================

    /**
     * Show the form for editing the user.
     */
    public function edit(User $user)
    {
        try {
            // Check policy
            $this->authorize('update', $user);

            // Validate user management
            if (!$this->validateUserManagement($user)) {
                abort(403, 'You can only manage users in your warehouse.');
            }

            // Get available roles
            $availableRoles = $this->getAvailableRolesForEdit($user);

            // Get accessible warehouses & branches
            $warehouses = $this->getAccessibleWarehouses();
            $branches = $this->getAccessibleBranches();

            $commonData = $this->getCommonViewData(request());

            return view('users.edit', array_merge($commonData, [
                'user' => $user,
                'availableRoles' => $availableRoles,
                'warehouses' => $warehouses,
                'branches' => $branches,
                'statuses' => [
                    'ACTIVE' => 'Active',
                    'INACTIVE' => 'Inactive',
                    'SUSPENDED' => 'Suspended'
                ]
            ]));

        } catch (\Exception $e) {
            Log::error('User edit form error: ' . $e->getMessage());
            return $this->errorResponse('Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        try {
            // Check policy
            $this->authorize('update', $user);

            // Validation rules
            $rules = [
                'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
                'email' => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
                'full_name' => 'required|string|max:100',
                'phone' => 'nullable|string|max:20',
                'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED',
            ];

            // Only super admin can change these
            if ($this->isSuperAdmin()) {
                $rules['role'] = 'required|in:' . implode(',', array_keys($this->getAvailableRoles()));
                $rules['branch_id'] = 'nullable|exists:branches,id';
                $rules['warehouse_id'] = 'nullable|exists:warehouses,id';
                $rules['can_manage_users'] = 'nullable|boolean';
            }

            // Password update (optional)
            if ($request->filled('password')) {
                $rules['password'] = 'string|min:6|confirmed';
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $validator->validated();

            // Remove sensitive fields for non-super-admin
            if (!$this->isSuperAdmin()) {
                unset($validated['role']);
                unset($validated['warehouse_id']);
                unset($validated['branch_id']);
                unset($validated['can_manage_users']);
            } else {
                // Validate role requirements (warehouse/branch must match role)
                $this->validateRoleRequirements($validated);
            }

            return $this->executeTransaction(
                function () use ($user, $validated) {
                    // Hash password if provided
                    if (isset($validated['password'])) {
                        $validated['password'] = Hash::make($validated['password']);
                    } else {
                        unset($validated['password']);
                    }

                    $oldData = $user->only(['username', 'role', 'warehouse_id', 'branch_id', 'status']);
                    $user->update($validated);

                    // Log activity
                    // $this->logActivity('update_user', 'User', $user->id, [
                    //     'old' => $oldData,
                    //     'new' => $validated
                    // ]);

                    return $user;
                },
                'User updated successfully',
                'Failed to update user'
            );

        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage());
            return $this->errorResponse('Error updating user: ' . $e->getMessage());
        }
    }

    // ========================================
    // 🗑️ DELETE USER
    // ========================================

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        try {
            // Check policy
            $this->authorize('delete', $user);

            // Cannot delete self
            if ($user->id === $this->currentUser()->id) {
                return $this->errorResponse('You cannot delete yourself.');
            }

            // Cannot delete super admin (optional protection)
            if ($user->isSuperAdmin() && !$this->isSuperAdmin()) {
                return $this->errorResponse('You cannot delete a super admin.');
            }

            return $this->executeTransaction(
                function () use ($user) {
                    $userId = $user->id;
                    $userData = [
                        'username' => $user->username,
                        'role' => $user->role
                    ];

                    $user->delete();

                    // Log activity
                    // $this->logActivity('delete_user', 'User', $userId, $userData);

                    return true;
                },
                'User deleted successfully',
                'Failed to delete user'
            );

        } catch (\Exception $e) {
            Log::error('User delete error: ' . $e->getMessage());
            return $this->errorResponse('Error deleting user: ' . $e->getMessage());
        }
    }

    // ========================================
    // 🔐 CHANGE PASSWORD (by user themselves)
    // ========================================

    /**
     * Show change password form
     */
    public function changePasswordForm()
    {
        $commonData = $this->getCommonViewData(request());
        
        return view('users.change-password', $commonData);
    }

    /**
     * Update user's own password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = $this->currentUser();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect.');
        }

        return $this->executeTransaction(
            function () use ($user, $request) {
                $user->password = Hash::make($request->password);
                $user->save();

                // Log activity
                // $this->logActivity('change_password', 'User', $user->id);

                return true;
            },
            'Password changed successfully',
            'Failed to change password'
        );
    }

    // ========================================
    // 🔄 RESET PASSWORD (by admin/manager)
    // ========================================

    /**
     * Show reset password form
     */
    public function resetPasswordForm(User $user)
    {
        // Check policy
        $this->authorize('resetPassword', $user);

        $commonData = $this->getCommonViewData(request());

        return view('users.reset-password', array_merge($commonData, [
            'user' => $user
        ]));
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        // Check policy
        $this->authorize('resetPassword', $user);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        return $this->executeTransaction(
            function () use ($user, $request) {
                $user->password = Hash::make($request->password);
                $user->save();

                // Log activity
                // $this->logActivity('reset_password', 'User', $user->id, [
                //     'reset_by' => $this->currentUser()->username
                // ]);

                return true;
            },
            'Password reset successfully for ' . $user->username,
            'Failed to reset password'
        );
    }

    // ========================================
    // 🔄 CHANGE STATUS
    // ========================================

    /**
     * Activate/deactivate user
     */
    public function changeStatus(Request $request, User $user)
    {
        // Check policy
        $this->authorize('changeStatus', $user);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Cannot change own status
        if ($user->id === $this->currentUser()->id) {
            return $this->errorResponse('You cannot change your own status.');
        }

        return $this->executeTransaction(
            function () use ($user, $request) {
                $oldStatus = $user->status;
                $user->status = $request->status;
                $user->save();

                // Log activity
                // $this->logActivity('change_status', 'User', $user->id, [
                //     'old_status' => $oldStatus,
                //     'new_status' => $request->status,
                //     'changed_by' => $this->currentUser()->username
                // ]);

                return true;
            },
            'User status changed to ' . $request->status,
            'Failed to change status'
        );
    }

    // ========================================
    // 📊 USER STATISTICS
    // ========================================

    /**
     * Display user statistics (for managers and super admin)
     */
    public function statistics(Request $request)
    {
        try {
            // Check permission
            if (!$this->isManager() && !$this->isSuperAdmin()) {
                abort(403, 'You do not have permission to view statistics.');
            }

            $query = User::query();

            // Filter by accessible warehouses
            if (!$this->isSuperAdmin()) {
                $query = $this->applyWarehouseFilter($query, 'warehouse_id');
            }

            // Apply branch filter
            if ($branchId = $this->getBranchId($request)) {
                $query->where('branch_id', $branchId);
            }

            $stats = [
                'total_users' => (clone $query)->count(),
                'active_users' => (clone $query)->where('status', 'ACTIVE')->count(),
                'inactive_users' => (clone $query)->where('status', 'INACTIVE')->count(),
                'suspended_users' => (clone $query)->where('status', 'SUSPENDED')->count(),
                'by_role' => (clone $query)->select('role', DB::raw('count(*) as total'))
                    ->groupBy('role')
                    ->pluck('total', 'role'),
                'recent_registrations' => (clone $query)->where('created_at', '>=', now()->subDays(30))->count(),
            ];

            $recentUsers = $query->latest()->limit(10)->get();

            $commonData = $this->getCommonViewData($request);

            return view('users.statistics', array_merge($commonData, [
                'stats' => $stats,
                'recentUsers' => $recentUsers
            ]));

        } catch (\Exception $e) {
            Log::error('User statistics error: ' . $e->getMessage());
            return $this->errorResponse('Error loading statistics: ' . $e->getMessage());
        }
    }

    // ========================================
    // 🔧 HELPER METHODS
    // ========================================

    /**
     * Get available roles for current user
     */
    private function getAvailableRoles(): array
    {
        $user = $this->currentUser();

        if ($user->isSuperAdmin()) {
            return [
                User::ROLE_SUPER_ADMIN => 'Super Admin',
                User::ROLE_CENTRAL_MANAGER => 'Central Manager',
                User::ROLE_CENTRAL_STAFF => 'Central Staff',
                User::ROLE_BRANCH_MANAGER => 'Branch Manager',
                User::ROLE_BRANCH_STAFF => 'Branch Staff',
                User::ROLE_OUTLET_MANAGER => 'Outlet Manager',
                User::ROLE_OUTLET_STAFF => 'Outlet Staff',
            ];
        }

        if ($user->isManager()) {
            // Manager hanya bisa create staff sesuai level mereka
            $staffRole = $this->getStaffRoleForManager($user->role);
            return [
                $staffRole => ucfirst(str_replace('_', ' ', $staffRole))
            ];
        }

        return [];
    }

    /**
     * Get available roles for editing (tidak boleh upgrade role)
     */
    private function getAvailableRolesForEdit(User $targetUser): array
    {
        if ($this->isSuperAdmin()) {
            return $this->getAvailableRoles();
        }

        // Manager hanya bisa edit staff mereka, tidak bisa ubah role
        return [
            $targetUser->role => ucfirst(str_replace('_', ' ', $targetUser->role))
        ];
    }

    /**
     * Get staff role for manager level
     */
    private function getStaffRoleForManager(string $managerRole): string
    {
        return match($managerRole) {
            User::ROLE_CENTRAL_MANAGER => User::ROLE_CENTRAL_STAFF,
            User::ROLE_BRANCH_MANAGER => User::ROLE_BRANCH_STAFF,
            User::ROLE_OUTLET_MANAGER => User::ROLE_OUTLET_STAFF,
            default => ''
        };
    }

    /**
     * Validate role requirements (warehouse/branch sesuai role)
     */
    private function validateRoleRequirements(array &$data): void
    {
        $role = $data['role'] ?? null;

        if (!$role) {
            return;
        }

        // Super admin tidak perlu warehouse/branch
        if ($role === User::ROLE_SUPER_ADMIN) {
            $data['warehouse_id'] = null;
            $data['branch_id'] = null;
            return;
        }

        // Central roles
        if (in_array($role, [User::ROLE_CENTRAL_MANAGER, User::ROLE_CENTRAL_STAFF])) {
            if (empty($data['warehouse_id'])) {
                throw new \Exception('Central roles must have a warehouse assigned.');
            }
            
            $warehouse = Warehouse::find($data['warehouse_id']);
            if ($warehouse->warehouse_type !== 'central') {
                throw new \Exception('Central roles must be assigned to a central warehouse.');
            }

            $data['branch_id'] = null; // Central tidak ada branch
            return;
        }

        // Branch & outlet roles
        if (in_array($role, [
            User::ROLE_BRANCH_MANAGER, User::ROLE_BRANCH_STAFF,
            User::ROLE_OUTLET_MANAGER, User::ROLE_OUTLET_STAFF
        ])) {
            if (empty($data['branch_id'])) {
                throw new \Exception('Branch/Outlet roles must have a branch assigned.');
            }

            if (empty($data['warehouse_id'])) {
                throw new \Exception('Branch/Outlet roles must have a warehouse assigned.');
            }

            $warehouse = Warehouse::find($data['warehouse_id']);
            
            // Validate warehouse type sesuai role
            if (in_array($role, [User::ROLE_BRANCH_MANAGER, User::ROLE_BRANCH_STAFF])) {
                if ($warehouse->warehouse_type !== 'branch') {
                    throw new \Exception('Branch roles must be assigned to a branch warehouse.');
                }
            }

            if (in_array($role, [User::ROLE_OUTLET_MANAGER, User::ROLE_OUTLET_STAFF])) {
                if ($warehouse->warehouse_type !== 'outlet') {
                    throw new \Exception('Outlet roles must be assigned to an outlet warehouse.');
                }
            }

            // Validate warehouse & branch match
            if ($warehouse->branch_id != $data['branch_id']) {
                throw new \Exception('Warehouse and branch must match.');
            }
        }
    }
}