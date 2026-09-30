<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'description',
        'size',
        'color',
        'unit',
        'selling_price',
        'wholesale_price',
        'min_stock_level',
        'current_stock',
        'status',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'min_stock_level' => 'integer',
        'current_stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'Out of Stock';
        }
        if ($this->current_stock <= $this->min_stock_level) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    public function getStockBadgeClassAttribute(): string
    {
        if ($this->current_stock <= 0) {
            return 'bg-danger';
        }
        if ($this->current_stock <= $this->min_stock_level) {
            return 'bg-warning text-dark';
        }
        return 'bg-success';
    }
}
