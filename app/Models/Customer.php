<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'customer_type',
        'mobile',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'gstin',
        'credit_limit',
        'opening_balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class)->orderBy('transaction_date', 'asc')->orderBy('id', 'asc');
    }

    public function latestLedgerEntry()
    {
        return $this->hasOne(LedgerEntry::class)->ofMany([
            'transaction_date' => 'max',
            'id' => 'max',
        ]);
    }

    public function getTotalSalesAttribute(): float
    {
        return app(\App\Services\CustomerFinancialService::class)->getTotalSales($this);
    }

    public function getTotalPaidAttribute(): float
    {
        return app(\App\Services\CustomerFinancialService::class)->getTotalPaid($this);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return app(\App\Services\CustomerFinancialService::class)->getOutstanding($this);
    }

    public function getCreditBalanceAttribute(): float
    {
        return app(\App\Services\CustomerFinancialService::class)->getCreditBalance($this);
    }

    public function getNetBalanceAttribute(): float
    {
        return app(\App\Services\CustomerFinancialService::class)->getNetBalance($this);
    }

    public function getAvailableCreditAttribute(): float
    {
        return app(\App\Services\CustomerFinancialService::class)->getAvailableCredit($this);
    }

    public function financialSummary(): array
    {
        return app(\App\Services\CustomerFinancialService::class)->getSummary($this);
    }
}
