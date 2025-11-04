<?php
// filepath: app/Models/DistributionOrder.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class DistributionOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'do_number',
        'from_warehouse_id',
        'to_branch_id',
        'to_warehouse_id',
        'requested_by',
        'approved_by',
        'prepared_by',
        'status',
        'request_date',
        'approved_date',
        'shipped_date',
        'delivered_date',
        'total_items',
        'total_quantity',
        'notes',
        'rejection_reason'
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approved_date' => 'datetime',
        'shipped_date' => 'datetime',
        'delivered_date' => 'datetime',
        'total_quantity' => 'decimal:3'
    ];

    protected $dates = [
        'request_date',
        'approved_date',
        'shipped_date',
        'delivered_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_REQUESTED = 'requested';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PREPARED = 'prepared';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Distribution order belongs to from warehouse (central)
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Distribution order belongs to target branch
     */
    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Distribution order belongs to destination warehouse (branch)
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * User who requested the order
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * User who approved the order
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * User who prepared the order
     */
    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Distribution order has many details
     */
    public function details()
    {
        return $this->hasMany(DistributionOrderDetail::class);
    }

    /**
     * Related central stock transactions (when shipped)
     */
    public function centralStockTransactions()
    {
        return $this->hasMany(CentralStockTransaction::class, 'reference_no', 'do_number');
    }

    /**
     * Related branch stock transactions (when received)
     */
    public function branchStockTransactions()
    {
        return $this->hasMany(BranchStockTransaction::class, 'reference_no', 'do_number');
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope untuk specific status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_REQUESTED);
    }

    /**
     * Scope untuk approved orders
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope untuk in-progress orders
     */
    public function scopeInProgress($query)
    {
        return $query->whereIn('status', [
            self::STATUS_APPROVED,
            self::STATUS_PREPARED,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED
        ]);
    }

    /**
     * Scope untuk completed orders
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope untuk specific branch
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('to_branch_id', $branchId);
    }

    /**
     * Scope untuk date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('request_date', [$startDate, $endDate]);
    }

    /**
     * Scope untuk search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('do_number', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%")
              ->orWhereHas('toBranch', function($branchQuery) use ($search) {
                  $branchQuery->where('branch_name', 'like', "%{$search}%");
              });
        });
    }

    // ========================================
    // BUSINESS METHODS
    // ========================================

    /**
     * Generate DO number otomatis
     */
    public static function generateDoNumber()
    {
        $date = now()->format('ymd');
        $lastDO = self::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastDO ? 
            (intval(substr($lastDO->do_number, -4)) + 1) : 1;

        return "DO-{$date}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create new distribution order
     */
    public static function createOrder($data)
    {
        $do = self::create([
            'do_number' => self::generateDoNumber(),
            'from_warehouse_id' => $data['from_warehouse_id'],
            'to_branch_id' => $data['to_branch_id'],
            'to_warehouse_id' => $data['to_warehouse_id'],
            'requested_by' => $data['requested_by'],
            'status' => self::STATUS_DRAFT,
            'request_date' => $data['request_date'] ?? now(),
            'notes' => $data['notes'] ?? null
        ]);

        // Add items to distribution order
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $do->details()->create([
                    'item_id' => $item['item_id'],
                    'requested_quantity' => $item['requested_quantity'],
                    'notes' => $item['notes'] ?? null
                ]);
            }
        }

        $do->calculateTotals();
        return $do;
    }

    /**
     * Calculate total items dan quantity
     */
    public function calculateTotals()
    {
        $this->total_items = $this->details()->count();
        $this->total_quantity = $this->details()->sum('requested_quantity');
        $this->save();

        return $this;
    }

    /**
     * Submit for approval
     */
    public function submitForApproval()
    {
        if ($this->status !== self::STATUS_DRAFT) {
            throw new \Exception('Only draft orders can be submitted for approval');
        }

        if ($this->details()->count() === 0) {
            throw new \Exception('Distribution order must have at least one item');
        }

        $this->update([
            'status' => self::STATUS_REQUESTED,
            'request_date' => now()
        ]);

        return $this;
    }

    /**
     * Approve distribution order
     */
    public function approve($userId, $approvedQuantities = [])
    {
        if ($this->status !== self::STATUS_REQUESTED) {
            throw new \Exception('Only requested orders can be approved');
        }

        // Validate stock availability
        $this->validateStockAvailability($approvedQuantities);

        // Update approved quantities
        foreach ($approvedQuantities as $detailId => $quantity) {
            $detail = $this->details()->find($detailId);
            if ($detail) {
                $detail->update(['approved_quantity' => $quantity]);
            }
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_date' => now()
        ]);

        return $this;
    }

    /**
     * Reject distribution order
     */
    public function reject($userId, $reason)
    {
        if ($this->status !== self::STATUS_REQUESTED) {
            throw new \Exception('Only requested orders can be rejected');
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'approved_date' => now(),
            'rejection_reason' => $reason
        ]);

        return $this;
    }

    /**
     * Mark as prepared
     */
    public function markAsPrepared($userId)
    {
        if ($this->status !== self::STATUS_APPROVED) {
            throw new \Exception('Only approved orders can be prepared');
        }

        $this->update([
            'status' => self::STATUS_PREPARED,
            'prepared_by' => $userId
        ]);

        return $this;
    }

    /**
     * Ship distribution order
     */
    public function ship($userId, $shippedQuantities = [])
    {
        if ($this->status !== self::STATUS_PREPARED) {
            throw new \Exception('Only prepared orders can be shipped');
        }

        // Create central stock transactions
        foreach ($this->details as $detail) {
            $shippedQty = $shippedQuantities[$detail->id] ?? $detail->approved_quantity;
            
            if ($shippedQty > 0) {
                // Update shipped quantity
                $detail->update(['shipped_quantity' => $shippedQty]);

                // Create central stock transaction
                CentralStockTransaction::createDistribution([
                    'item_id' => $detail->item_id,
                    'warehouse_id' => $this->from_warehouse_id,
                    'user_id' => $userId,
                    'quantity' => $shippedQty,
                    'reference_no' => $this->do_number,
                    'target_branch_id' => $this->to_branch_id,
                    'notes' => "Distribution to {$this->toBranch->branch_name}",
                    'transaction_date' => now()
                ]);
            }
        }

        $this->update([
            'status' => self::STATUS_SHIPPED,
            'shipped_date' => now()
        ]);

        return $this;
    }

    /**
     * Mark as delivered (receive at branch)
     */
    public function markAsDelivered($userId, $receivedQuantities = [])
    {
        if ($this->status !== self::STATUS_SHIPPED) {
            throw new \Exception('Only shipped orders can be marked as delivered');
        }

        // Create branch stock transactions
        foreach ($this->details as $detail) {
            $receivedQty = $receivedQuantities[$detail->id] ?? $detail->shipped_quantity;
            
            if ($receivedQty > 0) {
                // Update received quantity
                $detail->update(['received_quantity' => $receivedQty]);

                // Create branch stock transaction
                BranchStockTransaction::create([
                    'item_id' => $detail->item_id,
                    'branch_id' => $this->to_branch_id,
                    'warehouse_id' => $this->to_warehouse_id,
                    'user_id' => $userId,
                    'transaction_type' => 'RECEIVE_FROM_CENTRAL',
                    'quantity' => $receivedQty,
                    'reference_no' => $this->do_number,
                    'notes' => "Barang dari Central Warehouse: " . $detail->item->item_name,
                    'transaction_date' => now()
                ]);
            }
        }

        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_date' => now()
        ]);

        return $this;
    }

    /**
     * Complete distribution order
     */
    public function complete()
    {
        if ($this->status !== self::STATUS_DELIVERED) {
            throw new \Exception('Only delivered orders can be completed');
        }

        $this->update(['status' => self::STATUS_COMPLETED]);
        return $this;
    }

    /**
     * Cancel distribution order
     */
    public function cancel($reason = null)
    {
        if (in_array($this->status, [self::STATUS_SHIPPED, self::STATUS_DELIVERED, self::STATUS_COMPLETED])) {
            throw new \Exception('Cannot cancel orders that have been shipped or delivered');
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'rejection_reason' => $reason
        ]);

        return $this;
    }

    /**
     * Validate stock availability
     */
    protected function validateStockAvailability($approvedQuantities = [])
    {
        foreach ($this->details as $detail) {
            $requestedQty = $approvedQuantities[$detail->id] ?? $detail->requested_quantity;
            $currentStock = CentralStockTransaction::getCurrentStock(
                $detail->item_id, 
                $this->from_warehouse_id
            );

            if ($currentStock < $requestedQty) {
                throw new \Exception(
                    "Insufficient stock for {$detail->item->item_name}. " .
                    "Available: {$currentStock}, Requested: {$requestedQty}"
                );
            }
        }
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage()
    {
        $statusFlow = [
            self::STATUS_DRAFT => 0,
            self::STATUS_REQUESTED => 20,
            self::STATUS_APPROVED => 40,
            self::STATUS_PREPARED => 60,
            self::STATUS_SHIPPED => 80,
            self::STATUS_DELIVERED => 90,
            self::STATUS_COMPLETED => 100,
            self::STATUS_REJECTED => 0,
            self::STATUS_CANCELLED => 0
        ];

        return $statusFlow[$this->status] ?? 0;
    }

    /**
     * Get order performance metrics
     */
    public function getPerformanceMetrics()
    {
        $requestToApproval = $this->approved_date ? 
            $this->request_date->diffInHours($this->approved_date) : null;
            
        $approvalToShipment = ($this->approved_date && $this->shipped_date) ? 
            $this->approved_date->diffInHours($this->shipped_date) : null;
            
        $shipmentToDelivery = ($this->shipped_date && $this->delivered_date) ? 
            $this->shipped_date->diffInHours($this->delivered_date) : null;
            
        $totalCycleTime = ($this->request_date && $this->delivered_date) ? 
            $this->request_date->diffInHours($this->delivered_date) : null;

        return [
            'request_to_approval_hours' => $requestToApproval,
            'approval_to_shipment_hours' => $approvalToShipment,
            'shipment_to_delivery_hours' => $shipmentToDelivery,
            'total_cycle_time_hours' => $totalCycleTime,
            'fill_rate' => $this->getFillRate(),
            'accuracy_rate' => $this->getAccuracyRate()
        ];
    }

    /**
     * Get fill rate (berapa % yang berhasil di-ship vs requested)
     */
    public function getFillRate()
    {
        $totalRequested = $this->details()->sum('requested_quantity');
        $totalShipped = $this->details()->sum('shipped_quantity');

        return $totalRequested > 0 ? ($totalShipped / $totalRequested) * 100 : 0;
    }

    /**
     * Get accuracy rate (berapa % yang diterima sesuai shipped)
     */
    public function getAccuracyRate()
    {
        $totalShipped = $this->details()->sum('shipped_quantity');
        $totalReceived = $this->details()->sum('received_quantity');

        return $totalShipped > 0 ? ($totalReceived / $totalShipped) * 100 : 0;
    }

    // ========================================
    // STATIC ANALYTICS METHODS
    // ========================================

    /**
     * Get distribution summary untuk period
     */
    public static function getDistributionSummary($startDate = null, $endDate = null, $branchId = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        } else {
            $query->whereMonth('request_date', now()->month)
                  ->whereYear('request_date', now()->year);
        }

        if ($branchId) {
            $query->forBranch($branchId);
        }

        $orders = $query->with('details')->get();

        return [
            'total_orders' => $orders->count(),
            'pending_approval' => $orders->where('status', self::STATUS_REQUESTED)->count(),
            'in_progress' => $orders->whereIn('status', [
                self::STATUS_APPROVED, self::STATUS_PREPARED, self::STATUS_SHIPPED
            ])->count(),
            'completed' => $orders->where('status', self::STATUS_COMPLETED)->count(),
            'cancelled_rejected' => $orders->whereIn('status', [
                self::STATUS_CANCELLED, self::STATUS_REJECTED
            ])->count(),
            'total_items_requested' => $orders->sum('total_items'),
            'total_quantity_requested' => $orders->sum('total_quantity'),
            'avg_cycle_time' => $orders->avg(function($order) {
                return $order->getPerformanceMetrics()['total_cycle_time_hours'];
            }),
            'avg_fill_rate' => $orders->avg->getFillRate()
        ];
    }

    /**
     * Get top requested items
     */
    public static function getTopRequestedItems($limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->join('distribution_order_details', 'distribution_orders.id', '=', 'distribution_order_details.distribution_order_id')
            ->join('items', 'distribution_order_details.item_id', '=', 'items.id')
            ->selectRaw('items.id, items.item_name, items.sku, SUM(distribution_order_details.requested_quantity) as total_requested')
            ->groupBy('items.id', 'items.item_name', 'items.sku')
            ->orderBy('total_requested', 'desc')
            ->limit($limit)
            ->get();
    }

    // ========================================
    // ATTRIBUTES
    // ========================================

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_REQUESTED => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_PREPARED => 'Disiapkan',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_DELIVERED => 'Diterima',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge untuk UI
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => '<span class="badge bg-secondary">Draft</span>',
            self::STATUS_REQUESTED => '<span class="badge bg-warning">Menunggu Persetujuan</span>',
            self::STATUS_APPROVED => '<span class="badge bg-info">Disetujui</span>',
            self::STATUS_REJECTED => '<span class="badge bg-danger">Ditolak</span>',
            self::STATUS_PREPARED => '<span class="badge bg-primary">Disiapkan</span>',
            self::STATUS_SHIPPED => '<span class="badge bg-dark">Dikirim</span>',
            self::STATUS_DELIVERED => '<span class="badge bg-success">Diterima</span>',
            self::STATUS_COMPLETED => '<span class="badge bg-success">Selesai</span>',
            self::STATUS_CANCELLED => '<span class="badge bg-danger">Dibatalkan</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    /**
     * Get next possible actions
     */
    public function getNextActionsAttribute()
    {
        return match($this->status) {
            self::STATUS_DRAFT => ['submit', 'edit', 'delete'],
            self::STATUS_REQUESTED => ['approve', 'reject'],
            self::STATUS_APPROVED => ['prepare', 'cancel'],
            self::STATUS_PREPARED => ['ship', 'cancel'],
            self::STATUS_SHIPPED => ['deliver'],
            self::STATUS_DELIVERED => ['complete'],
            default => []
        };
    }

    /**
     * Check if order can be edited
     */
    public function getCanEditAttribute()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if order can be cancelled
     */
    public function getCanCancelAttribute()
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REQUESTED,
            self::STATUS_APPROVED,
            self::STATUS_PREPARED
        ]);
    }

    // ========================================
    // VALIDATION
    // ========================================

    public static function validationRules()
    {
        return [
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_branch_id' => 'required|exists:branches,id',
            'to_warehouse_id' => 'required|exists:warehouses,id',
            'requested_by' => 'required|exists:users,id',
            'request_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ];
    }

    public static function validationMessages()
    {
        return [
            'from_warehouse_id.required' => 'Warehouse asal wajib dipilih',
            'to_branch_id.required' => 'Branch tujuan wajib dipilih',
            'to_warehouse_id.required' => 'Warehouse tujuan wajib dipilih',
            'requested_by.required' => 'User requester wajib dipilih',
            'request_date.required' => 'Tanggal request wajib diisi'
        ];
    }
}