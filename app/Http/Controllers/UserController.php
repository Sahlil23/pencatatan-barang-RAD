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
use Illuminate\Validation\ValidationException;

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
            $query = User::with(['warehouse', 'branch']);

            $query->where('id', '!=', auth()->id()); 

            // Apply warehouse filter (managers only see their warehouse users)
            if (!$this->isSuperAdmin()) {

                $curentUser = auth()->user();

                if($curentUser->warehouse_id){
                    $query->where('warehouse_id', $curentUser->warehouse_id);
                }
                // $query = $this->applyWarehouseFilter($query, 'warehouse_id');
            }

            // Apply branch filter (jika branch context aktif)
            if ($request->filled('branch_id')) {
                $branchId = $request->input('branch_id');
                $this->validateBranchAccess($branchId);
                $query->where('branch_id', $branchId);
            } elseif (!$this->isSuperAdmin() && auth()->user()->branch_id) {
                $query->where('branch_id', auth()->user()->branch_id);
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

            $currentUser = $this->currentUser();

            // Check if user can create users
            if (!$currentUser->canCreateUsers()) {
                abort(403, 'You do not have permission to create users.');
            }

            // Get roles that current user can assign
            $availableRoles = $currentUser->getAssignableRoles();

            if (empty($availableRoles)) {
                abort(403, 'You cannot assign any roles.');
            }

            // Get warehouses filtered by allowed types
            $allowedWarehouseTypes = $currentUser->getAllowedWarehouseTypesForUserCreation();
            
            $warehouses = Warehouse::where('status', 'ACTIVE')
                ->whereIn('warehouse_type', $allowedWarehouseTypes);

            // Non-super admin: filter by accessible warehouses
            if (!$currentUser->isSuperAdmin()) {
                $accessibleWarehouseIds = $currentUser->getAccessibleWarehouseIds();
                $warehouses->whereIn('id', $accessibleWarehouseIds);
            }

            $warehouses = $warehouses->with('branch')->orderBy('warehouse_name')->get();

            // Get branches
            $branches = Branch::where('status', 'active');
            
            // Non-super admin: filter by accessible branches
            if (!$currentUser->isSuperAdmin()) {
                $accessibleBranchIds = $currentUser->getAccessibleBranchIds();
                $branches->whereIn('id', $accessibleBranchIds);
            }
            
            $branches = $branches->orderBy('branch_name')->get();

            // Auto-select warehouse & branch for managers (non-super-admin)
            $defaultWarehouseId = null;
            $defaultBranchId = null;

            if (!$currentUser->isSuperAdmin()) {
                $defaultWarehouseId = $currentUser->warehouse_id;
                $defaultBranchId = $currentUser->branch_id;
            }

            $commonData = $this->getCommonViewData(request());

            return view('users.create', array_merge($commonData, [
                'availableRoles' => $availableRoles,
                'warehouses' => $warehouses,
                'branches' => $branches,
                'defaultWarehouseId' => $defaultWarehouseId,
                'defaultBranchId' => $defaultBranchId,
                'isLimitedCreator' => !$currentUser->isSuperAdmin(), // Flag untuk disable input
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

            $currentUser = $this->currentUser();

            // Get assignable roles for validation
            $assignableRoles = array_keys($currentUser->getAssignableRoles());

            // ✅ Validation rules - Different for super admin vs manager
            if ($currentUser->isSuperAdmin()) {
                // Super admin: warehouse & branch are OPTIONAL
                $rules = [
                    'username' => 'required|string|max:50|unique:users,username',
                    'email' => 'nullable|email|max:100|unique:users,email',
                    'password' => 'required|string|min:6|confirmed',
                    'full_name' => 'required|string|max:100',
                    'phone' => 'nullable|string|max:20',
                    'role' => 'required|in:' . implode(',', $assignableRoles),
                    'status' => 'required|in:ACTIVE,INACTIVE',
                    'branch_id' => 'nullable|exists:branches,id',
                    'warehouse_id' => 'nullable|exists:warehouses,id',
                ];
            } else {
                // Manager: warehouse & branch are REQUIRED and auto-set
                $rules = [
                    'username' => 'required|string|max:50|unique:users,username',
                    'email' => 'nullable|email|max:100|unique:users,email',
                    'password' => 'required|string|min:6|confirmed',
                    'full_name' => 'required|string|max:100',
                    'phone' => 'nullable|string|max:20',
                    'role' => 'required|in:' . implode(',', $assignableRoles),
                    'status' => 'required|in:ACTIVE,INACTIVE',
                    'branch_id' => 'required|exists:branches,id',
                    'warehouse_id' => 'required|exists:warehouses,id',
                ];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator);
            }

            $validated = $validator->validated();

            // ✅ AUTHORIZATION CHECKS
            if ($currentUser->isSuperAdmin()) {
                // ✅ Super Admin: Only validate IF warehouse is selected
                // If warehouse is selected, auto-set branch from it
                if (!empty($validated['warehouse_id'])) {
                    $warehouse = Warehouse::find($validated['warehouse_id']);
                    if ($warehouse) {
                        // Auto-set branch from warehouse
                        $validated['branch_id'] = $warehouse->branch_id;
                    }
                }
                
                // ✅ For roles that REQUIRE warehouse, validate it
                $role = $validated['role'];
                
                // Central roles: If warehouse selected, must be central type
                if (in_array($role, [User::ROLE_CENTRAL_MANAGER, User::ROLE_CENTRAL_STAFF])) {
                    if (!empty($validated['warehouse_id'])) {
                        $warehouse = Warehouse::find($validated['warehouse_id']);
                        if ($warehouse && $warehouse->warehouse_type !== 'central') {
                            return back()->withErrors([
                                'warehouse_id' => 'Central roles must be assigned to a central warehouse.'
                            ])->withInput();
                        }
                    }
                }
                
                // Branch roles: MUST have branch warehouse
                if (in_array($role, [User::ROLE_BRANCH_MANAGER, User::ROLE_BRANCH_STAFF])) {
                    if (empty($validated['warehouse_id'])) {
                        return back()->withErrors([
                            'warehouse_id' => 'Branch roles must be assigned to a branch warehouse.'
                        ])->withInput();
                    }
                    
                    $warehouse = Warehouse::find($validated['warehouse_id']);
                    if (!$warehouse || $warehouse->warehouse_type !== 'branch') {
                        return back()->withErrors([
                            'warehouse_id' => 'Branch roles must be assigned to a branch warehouse.'
                        ])->withInput();
                    }
                    
                    // Auto-set branch
                    $validated['branch_id'] = $warehouse->branch_id;
                    
                    if (!$validated['branch_id']) {
                        return back()->withErrors([
                            'warehouse_id' => 'Selected warehouse does not have a branch assigned.'
                        ])->withInput();
                    }
                }
                
                // Outlet roles: MUST have outlet warehouse
                if (in_array($role, [User::ROLE_OUTLET_MANAGER, User::ROLE_OUTLET_STAFF])) {
                    if (empty($validated['warehouse_id'])) {
                        return back()->withErrors([
                            'warehouse_id' => 'Outlet roles must be assigned to an outlet warehouse.'
                        ])->withInput();
                    }
                    
                    $warehouse = Warehouse::find($validated['warehouse_id']);
                    if (!$warehouse || $warehouse->warehouse_type !== 'outlet') {
                        return back()->withErrors([
                            'warehouse_id' => 'Outlet roles must be assigned to an outlet warehouse.'
                        ])->withInput();
                    }
                    
                    // Auto-set branch
                    $validated['branch_id'] = $warehouse->branch_id;
                    
                    if (!$validated['branch_id']) {
                        return back()->withErrors([
                            'warehouse_id' => 'Selected warehouse does not have a branch assigned.'
                        ])->withInput();
                    }
                }
                
                // Super Admin role: no warehouse/branch needed
                if ($role === User::ROLE_SUPER_ADMIN) {
                    $validated['warehouse_id'] = null;
                    $validated['branch_id'] = null;
                }

            } else {
                // ✅ NON-SUPER-ADMIN (Manager): Strict validation
                
                // 1. Check role is allowed
                if (!in_array($validated['role'], $assignableRoles)) {
                    return back()->withErrors([
                        'role' => 'You are not authorized to create users with this role.'
                    ])->withInput();
                }

                // 2. Force warehouse & branch to current user's
                $validated['warehouse_id'] = $currentUser->warehouse_id;
                $validated['branch_id'] = $currentUser->branch_id;

                // 3. Validate warehouse & branch match
                if ($request->warehouse_id != $currentUser->warehouse_id || 
                    $request->branch_id != $currentUser->branch_id) {
                    return back()->withErrors([
                        'warehouse_id' => 'You can only create users in your assigned warehouse and branch.'
                    ])->withInput();
                }

                // 4. Validate warehouse type matches role
                $warehouse = Warehouse::find($validated['warehouse_id']);
                
                if ($currentUser->isCentralManager() && $warehouse->warehouse_type !== 'central') {
                    return back()->withErrors([
                        'warehouse_id' => 'Central Manager can only create staff in central warehouses.'
                    ])->withInput();
                }

                if ($currentUser->isBranchManager() && $warehouse->warehouse_type !== 'branch') {
                    return back()->withErrors([
                        'warehouse_id' => 'Branch Manager can only create staff in branch warehouses.'
                    ])->withInput();
                }

                if ($currentUser->isOutletManager() && $warehouse->warehouse_type !== 'outlet') {
                    return back()->withErrors([
                        'warehouse_id' => 'Outlet Manager can only create staff in outlet warehouses.'
                    ])->withInput();
                }

                // 5. Force can_manage_users = false for non-admin created users
                $validated['can_manage_users'] = false;
            }

            return $this->executeTransaction(
                function () use ($validated, $currentUser) {
                    // Hash password
                    $validated['password'] = Hash::make($validated['password']);

                    $user = User::create($validated);

                    Log::info('User created successfully', [
                        'created_user_id' => $user->id,
                        'created_user_username' => $user->username,
                        'created_user_role' => $user->role,
                        'created_by_user_id' => $currentUser->id,
                        'created_by_username' => $currentUser->username,
                        'created_by_role' => $currentUser->role,
                        'warehouse_id' => $user->warehouse_id,
                        'branch_id' => $user->branch_id
                    ]);

                    return $user;
                },
                'User created successfully',
                'Failed to create user'
            );

        } catch (\Exception $e) {
            Log::error('User store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
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
     * Validate role requirements (warehouse type, branch assignment, etc)
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateRoleRequirements(array &$validated): void
    {
        $role = $validated['role'];
        $warehouseId = $validated['warehouse_id'] ?? null;
        $branchId = $validated['branch_id'] ?? null;

        // ✅ Super Admin: No requirements
        if ($role === User::ROLE_SUPER_ADMIN) {
            // Super admin doesn't need warehouse or branch
            $validated['warehouse_id'] = null;
            $validated['branch_id'] = null;
            return;
        }

        // ✅ Central roles: Must have central warehouse
        if (in_array($role, [User::ROLE_CENTRAL_MANAGER, User::ROLE_CENTRAL_STAFF])) {
            if ($warehouseId) {
                $warehouse = Warehouse::find($warehouseId);
                if (!$warehouse || $warehouse->warehouse_type !== 'central') {
                    throw ValidationException::withMessages([
                        'warehouse_id' => 'Central roles must be assigned to a central warehouse.'
                    ]);
                }
                // ✅ Auto-set branch from warehouse (or null for central)
                $validated['branch_id'] = $warehouse->branch_id;
            }
            return;
        }

        // ✅ Branch roles: Must have branch warehouse
        if (in_array($role, [User::ROLE_BRANCH_MANAGER, User::ROLE_BRANCH_STAFF])) {
            if (!$warehouseId) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Branch roles must be assigned to a warehouse.'
                ]);
            }

            $warehouse = Warehouse::find($warehouseId);
            
            if (!$warehouse) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Selected warehouse not found.'
                ]);
            }

            if ($warehouse->warehouse_type !== 'branch') {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Branch roles must be assigned to a branch warehouse.'
                ]);
            }

            // ✅ ALWAYS auto-set branch from warehouse
            $validated['branch_id'] = $warehouse->branch_id;

            if (!$validated['branch_id']) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Selected warehouse does not have a branch assigned.'
                ]);
            }

            return;
        }

        // ✅ Outlet roles: Must have outlet warehouse
        if (in_array($role, [User::ROLE_OUTLET_MANAGER, User::ROLE_OUTLET_STAFF])) {
            if (!$warehouseId) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Outlet roles must be assigned to a warehouse.'
                ]);
            }

            $warehouse = Warehouse::find($warehouseId);
            
            if (!$warehouse) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Selected warehouse not found.'
                ]);
            }

            if ($warehouse->warehouse_type !== 'outlet') {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Outlet roles must be assigned to an outlet warehouse.'
                ]);
            }

            // ✅ ALWAYS auto-set branch from warehouse
            $validated['branch_id'] = $warehouse->branch_id;

            if (!$validated['branch_id']) {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Selected warehouse does not have a branch assigned.'
                ]);
            }

            return;
        }
    }
}