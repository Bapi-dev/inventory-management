<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'date' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMovementTypeLabelAttribute(): string
    {
        return match ($this->movement_type) {
            'opening_stock' => 'Opening Stock',
            'stock_in' => 'Stock In',
            'sale' => 'Sale (Order)',
            'adjustment' => 'Adjustment',
            'return' => 'Return',
            default => ucfirst(str_replace('_', ' ', $this->movement_type)),
        };
    }

    public function getMovementBadgeClassAttribute(): string
    {
        return match ($this->movement_type) {
            'opening_stock' => 'bg-info text-dark',
            'stock_in' => 'bg-success',
            'sale' => 'bg-danger',
            'adjustment' => 'bg-secondary',
            'return' => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
