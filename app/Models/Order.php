<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PACKED = 'packed';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIALLY_PAID = 'partially_paid';
    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'status',
        'payment_status',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'paid_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', 'Order');
    }

    public function getOutstandingAmountAttribute(): float
    {
        return max(0.00, round((float) ($this->grand_total - $this->paid_amount), 2));
    }

    public function isValidSale(): bool
    {
        return in_array($this->status, \App\Services\CustomerFinancialService::VALID_SALES_STATUSES);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-secondary',
            'confirmed' => 'bg-info text-dark',
            'packed' => 'bg-primary',
            'dispatched' => 'bg-warning text-dark',
            'delivered' => 'bg-success',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getPaymentBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-success',
            'partially_paid' => 'bg-warning text-dark',
            'unpaid' => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
