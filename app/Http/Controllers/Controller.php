<?php
// filepath: app/Http/Controllers/Controller.php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;
use Illuminate\Http\Request; 
use App\Models\User; 
use App\Models\Warehouse;
use App\Models\Branch;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // ========================================
    // USER & AUTHENTICATION HELPERS
    // ========================================

    /**
     * Get current authenticated user
     *
     * @return \App\Models\User
     */
    protected function currentUser(): User
    {
        return auth()->user();
    }

    /**
     * Check if current user is super admin
     *
     * @return bool
     */
    protected function isSuperAdmin(): bool
    {
        return $this->currentUser()->isSuperAdmin();
    }

    /**
     * Check if current user is central user
     *
     * @return bool
     */
    protected function isCentralLevel(): bool
    {
        return $this->currentUser()->isCentralLevel();
    }

    /**
     * Check if current user is branch user
     *
     * @return bool
     */
    protected function isBranchUser(): bool
    {
        return $this->currentUser()->isBranchUser();
    }

    /**
     * Check if current user is outlet user
     *
     * @return bool
     */
    protected function isOutletUser(): bool
    {
        return $this->currentUser()->isOutletUser();
    }

    /**
     * Check if current user is manager
     *
     * @return bool
     */
    protected function isManager(): bool
    {
        return $this->currentUser()->isManager();
    }

    /**
     * Check if current user is staff
     *
     * @return bool
     */
    protected function isStaff(): bool
    {
        return $this->currentUser()->isStaff();
    }

    // ========================================
    // BRANCH CONTEXT HELPERS
    // ========================================

    /**
     * Get current branch ID based on user context
     *
     * Priority:
     * 1. Request parameter (from branch selector)
     * 2. Session (from previous selection)
     * 3. User's branch_id (for branch/outlet users)
     * 4. First available branch (for super admin/central)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int|null
     */
    protected function getBranchId(Request $request): ?int
    {
        $user = $this->currentUser();

        // From request (branch selector form)
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
            
            // Validate access
            if ($user->canAccessBranch($branchId)) {
                // Store to session for persistence
                session(['current_branch_id' => $branchId]);
                return $branchId;
            }
        }

        // From session (persistent selection)
        if (session()->has('current_branch_id')) {
            $branchId = (int) session('current_branch_id');
            
            // Validate access
            if ($user->canAccessBranch($branchId)) {
                return $branchId;
            }
        }

        // From request merge (set by middleware)
        if ($request->has('current_branch_id')) {
            return (int) $request->input('current_branch_id');
        }

        // Super Admin/Central: from last context or first branch
        if ($user->isSuperAdmin() || $user->isCentralLevel()) {
            // From user's last context
            if ($user->last_branch_context) {
                $branchId = (int) $user->last_branch_context;
                if ($user->canAccessBranch($branchId)) {
                    session(['current_branch_id' => $branchId]);
                    return $branchId;
                }
            }
            
            // Default: first branch
            $firstBranch = Branch::first();
            if ($firstBranch) {
                session(['current_branch_id' => $firstBranch->id]);
                return $firstBranch->id;
            }
        }

        // Branch/Outlet users: their branch
        if ($user->branch_id) {
            session(['current_branch_id' => $user->branch_id]);
            return $user->branch_id;
        }

        return null;
    }

    /**
     * Get current branch object
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Branch|null
     */
    protected function getCurrentBranch(Request $request): ?Branch
    {
        $branchId = $this->getBranchId($request);
        return $branchId ? Branch::find($branchId) : null;
    }

    /**
     * Set branch context (update session & user's last context)
     *
     * @param  int  $branchId
     * @return bool
     */
    protected function setBranchContext(int $branchId): bool
    {
        $user = $this->currentUser();

        // Validate access
        if (!$user->canAccessBranch($branchId)) {
            return false;
        }

        // Update session
        session(['current_branch_id' => $branchId]);

        // Update user's last context (for next login)
        if ($user->isSuperAdmin() || $user->isCentralLevel()) {
            $user->last_branch_context = $branchId;
            $user->save();
        }

        return true;
    }

    /**
     * Get accessible branches for current user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAccessibleBranches()
    {
        return $this->currentUser()->getAccessibleBranches();
    }

    // ========================================
    // WAREHOUSE CONTEXT HELPERS
    // ========================================

    /**
     * Get current warehouse ID
     *
     * Priority:
     * 1. Request parameter
     * 2. User's assigned warehouse
     *
     * @param  \Illuminate\Http\Request  $request
     * @return int|null
     */
    protected function getWarehouseId(Request $request): ?int
    {
        // From request
        if ($request->filled('warehouse_id')) {
            return (int) $request->input('warehouse_id');
        }

        // From route parameter
        if ($request->route('warehouse_id')) {
            return (int) $request->route('warehouse_id');
        }

        // From user's assigned warehouse
        return $this->currentUser()->warehouse_id;
    }

    /**
     * Get current warehouse object
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Warehouse|null
     */
    protected function getCurrentWarehouse(Request $request): ?Warehouse
    {
        $warehouseId = $this->getWarehouseId($request);
        return $warehouseId ? Warehouse::find($warehouseId) : null;
    }

    /**
     * Get accessible warehouses for current user
     *
     * @param  bool  $writeAccess  Filter only warehouses with write access
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAccessibleWarehouses(bool $writeAccess = false)
    {
        return $this->currentUser()->getAccessibleWarehouses($writeAccess);
    }

    /**
     * Get warehouses by branch ID
     *
     * @param  int  $branchId
     * @param  bool  $writeAccess
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getWarehousesByBranch(int $branchId, bool $writeAccess = false)
    {
        $query = Warehouse::where('branch_id', $branchId);

        // Filter by accessible warehouses
        $accessibleIds = $this->getAccessibleWarehouses($writeAccess)->pluck('id');
        $query->whereIn('id', $accessibleIds);

        return $query->get();
    }

    // ========================================
    // PERMISSION CHECK HELPERS
    // ========================================

    /**
     * Check if user can write to specific warehouse
     *
     * @param  int  $warehouseId
     * @return bool
     */
    protected function canWrite(int $warehouseId): bool
    {
        return $this->currentUser()->canWrite($warehouseId);
    }

    /**
     * Check if user can view specific warehouse
     *
     * @param  int  $warehouseId
     * @return bool
     */
    protected function canView(int $warehouseId): bool
    {
        return $this->currentUser()->canView($warehouseId);
    }

    /**
     * Check if current access is read-only for warehouse
     *
     * @param  int  $warehouseId
     * @return bool
     */
    protected function isReadOnly(int $warehouseId): bool
    {
        return !$this->canWrite($warehouseId);
    }

    /**
     * Check if user is viewing lower level warehouse
     *
     * @param  int  $warehouseId
     * @return bool
     */
    protected function isViewingLowerLevel(int $warehouseId): bool
    {
        $user = $this->currentUser();

        // Super admin: not viewing lower level (can edit everything)
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Check if target warehouse is lower level
        return $user->isLowerLevelWarehouse($warehouseId);
    }

    /**
     * Validate warehouse access (throw exception if no access)
     *
     * @param  int  $warehouseId
     * @param  bool  $write  Require write access
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function validateWarehouseAccess(int $warehouseId, bool $write = false): void
    {
        $user = $this->currentUser();

        if ($write && !$user->canWrite($warehouseId)) {
            abort(403, 'You do not have write access to this warehouse. This is read-only access.');
        }

        if (!$user->canView($warehouseId)) {
            abort(403, 'You do not have access to this warehouse.');
        }
    }

    /**
     * Validate branch access (throw exception if no access)
     *
     * @param  int  $branchId
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function validateBranchAccess(int $branchId): void
    {
        if (!$this->currentUser()->canAccessBranch($branchId)) {
            abort(403, 'You do not have access to this branch.');
        }
    }

    // ========================================
    // QUERY BUILDER HELPERS
    // ========================================

    /**
     * Apply warehouse access filter to query
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $warehouseColumn  Column name for warehouse_id
     * @param  bool  $writeAccess  Filter only writable warehouses
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyWarehouseFilter($query, string $warehouseColumn = 'warehouse_id', bool $writeAccess = false)
    {
        $accessibleIds = $this->getAccessibleWarehouses($writeAccess)->pluck('id');

        return $query->whereIn($warehouseColumn, $accessibleIds);
    }

    /**
     * Apply branch access filter to query
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $branchColumn  Column name for branch_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyBranchFilter($query, string $branchColumn = 'branch_id')
    {
        $accessibleIds = $this->getAccessibleBranches()->pluck('id');

        return $query->whereIn($branchColumn, $accessibleIds);
    }

    /**
     * Apply current branch context filter to query
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $branchColumn  Column name for branch_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyCurrentBranchFilter($query, Request $request, string $branchColumn = 'branch_id')
    {
        $branchId = $this->getBranchId($request);

        if ($branchId) {
            $query->where($branchColumn, $branchId);
        }

        return $query;
    }

    // ========================================
    // RESPONSE HELPERS
    // ========================================

    /**
     * Return success response (JSON or redirect)
     *
     * @param  string  $message
     * @param  mixed  $data
     * @param  string|null  $redirectRoute
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function successResponse(string $message, $data = null, ?string $redirectRoute = null)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ]);
        }

        if ($redirectRoute) {
            return redirect()->route($redirectRoute)->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Return error response (JSON or redirect)
     *
     * @param  string  $messagef
     * @param  int  $statusCode
     * @param  array  $errors
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $errors = [])
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], $statusCode);
        }

        return back()
            ->withInput()
            ->with('error', $message)
            ->withErrors($errors);
    }

    /**
     * Return validation error response
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function validationErrorResponse($validator)
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        return back()
            ->withInput()
            ->withErrors($validator);
    }

    // ========================================
    // VIEW DATA HELPERS
    // ========================================

    /**
     * Get common view data (for sharing across views)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function getCommonViewData(Request $request): array
    {
        $user = $this->currentUser();
        $currentBranch = $this->getCurrentBranch($request);
        $currentWarehouse = $this->getCurrentWarehouse($request);

        return [
            'currentUser' => $user,
            'currentBranch' => $currentBranch,
            'currentBranchId' => $currentBranch?->id,
            'currentWarehouse' => $currentWarehouse,
            'currentWarehouseId' => $currentWarehouse?->id,
            'accessibleBranches' => $this->getAccessibleBranches(),
            'accessibleWarehouses' => $this->getAccessibleWarehouses(),
            'writableWarehouses' => $this->getAccessibleWarehouses(true),
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isManager' => $user->isManager(),
            'isStaff' => $user->isStaff(),
            'canManageUsers' => $user->canManageUsers(),
        ];
    }

    /**
     * Share data to all views
     *
     * @param  array  $data
     * @return void
     */
    protected function shareToViews(array $data): void
    {
        view()->share($data);
    }

    /**
     * Get warehouse display data for views
     *
     * @param  int  $warehouseId
     * @return array
     */
    protected function getWarehouseDisplayData(int $warehouseId): array
    {
        $user = $this->currentUser();
        $warehouse = Warehouse::find($warehouseId);

        return [
            'warehouse' => $warehouse,
            'canWrite' => $user->canWrite($warehouseId),
            'canView' => $user->canView($warehouseId),
            'isReadOnly' => !$user->canWrite($warehouseId),
            'isLowerLevel' => $user->isLowerLevelWarehouse($warehouseId),
            'warehouseLevel' => match($warehouse?->warehouse_type) {
                'central' => 1,
                'branch' => 2,
                'outlet' => 3,
                default => null
            }
        ];
    }

    // ========================================
    // TRANSACTION HELPERS
    // ========================================

    /**
     * Execute database transaction with error handling
     * 
     * @param callable $callback The transaction callback
     * @param string|callable $successMessage Success message (string or callback that receives $result)
     * @param string $errorMessage Error message
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function executeTransaction(
        callable $callback, 
        $successMessage = 'Operation completed successfully', 
        string $errorMessage = 'Operation failed'
    ) {
        try {
            DB::beginTransaction();
            
            // Execute callback and get result
            $result = $callback();
            
            DB::commit();
            
            // ✅ Support both string and callback for success message
            if (is_callable($successMessage)) {
                $successMessage = $successMessage($result);
            }
            
            return redirect()->back()->with('success', $successMessage);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error($errorMessage . ': ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMsg = $errorMessage . ': ' . $e->getMessage();
            
            // If in debug mode, show full error
            if (config('app.debug')) {
                $errorMsg .= " (Line {$e->getLine()} in {$e->getFile()})";
            }
            
            return redirect()->back()
                ->with('error', $errorMsg)
                ->withInput();
        }
    }

    /**
     * Execute database transaction and return JSON response
     * 
     * @param callable $callback The transaction callback
     * @param string|callable $successMessage Success message (string or callback that receives $result)
     * @param string $errorMessage Error message
     * @param int $successCode HTTP status code for success (default: 200)
     * @return \Illuminate\Http\JsonResponse
     */
    protected function executeTransactionJson(
        callable $callback,
        $successMessage = 'Operation completed successfully',
        string $errorMessage = 'Operation failed',
        int $successCode = 200
    ) {
        try {
            DB::beginTransaction();
            
            // Execute callback and get result
            $result = $callback();
            
            DB::commit();
            
            // ✅ Support both string and callback for success message
            if (is_callable($successMessage)) {
                $successMessage = $successMessage($result);
            }
            
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => $result
            ], $successCode);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error($errorMessage . ': ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMsg = $errorMessage . ': ' . $e->getMessage();
            
            // If in debug mode, show full error
            if (config('app.debug')) {
                $errorMsg .= " (Line {$e->getLine()} in {$e->getFile()})";
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMsg
            ], 500);
        }
    }

    // ========================================
    // PAGINATION HELPERS
    // ========================================

    /**
     * Get pagination per page from request or default
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $default
     * @return int
     */
    protected function getPerPage(Request $request, int $default = 20): int
    {
        $perPage = $request->input('per_page', $default);

        // Limit max per page
        return min($perPage, 100);
    }

    // ========================================
    // DATE RANGE HELPERS
    // ========================================

    /**
     * Get date range from request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $defaultDays  Default days back
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    protected function getDateRange(Request $request, int $defaultDays = 30): array
    {
        $startDate = $request->filled('start_date') 
            ? \Carbon\Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->subDays($defaultDays)->startOfDay();

        $endDate = $request->filled('end_date')
            ? \Carbon\Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfDay();

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }

    /**
     * Apply date range filter to query
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $column
     * @param  int  $defaultDays
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyDateRangeFilter($query, Request $request, string $column = 'created_at', int $defaultDays = 30)
    {
        $dateRange = $this->getDateRange($request, $defaultDays);

        return $query->whereBetween($column, [$dateRange['start'], $dateRange['end']]);
    }

    // ========================================
    // SEARCH HELPERS
    // ========================================

    /**
     * Apply search filter to query
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $searchableColumns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applySearchFilter($query, Request $request, array $searchableColumns)
    {
        if (!$request->filled('search')) {
            return $query;
        }

        $search = $request->input('search');

        return $query->where(function ($q) use ($search, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    // ========================================
    // EXPORT HELPERS
    // ========================================

    /**
     * Generate filename for export
     *
     * @param  string  $prefix
     * @param  string  $extension
     * @return string
     */
    protected function generateExportFilename(string $prefix, string $extension = 'xlsx'): string
    {
        $date = now()->format('Y-m-d_His');
        $branch = session('current_branch_id') ? Branch::find(session('current_branch_id'))?->branch_code : 'ALL';
        
        return "{$prefix}_{$branch}_{$date}.{$extension}";
    }

    /**
     * Check if user can export from warehouse
     *
     * @param  int  $warehouseId
     * @return bool
     */
    protected function canExport(int $warehouseId): bool
    {
        // Can export if can view
        return $this->canView($warehouseId);
    }

    // ========================================
    // VALIDATION HELPERS
    // ========================================

    /**
     * Validate warehouse ownership (for write operations)
     *
     * @param  int  $warehouseId
     * @return bool
     */
    protected function validateWarehouseOwnership(int $warehouseId): bool
    {
        $user = $this->currentUser();

        // Super admin: always true
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Must own the warehouse
        return $user->ownsWarehouse($warehouseId);
    }

    /**
     * Validate if user can manage target user
     *
     * @param  \App\Models\User  $targetUser
     * @return bool
     */
    protected function validateUserManagement(User $targetUser): bool
    {
        return $this->currentUser()->canManageUsers($targetUser);
    }
}