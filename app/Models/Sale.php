<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'base_price_11_8',
        'invoice_no',
        'status',
        'sub_total',
        'discount_amount',
        'grand_total',
    ];

    protected $casts = [
        'base_price_11_8' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_CONFIRMED,
            self::STATUS_PARTIALLY_PAID,
            self::STATUS_PAID,
            self::STATUS_CANCELLED,
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Get the transactions for the sale.
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors & Mutators
    public function getBalanceAttribute(): float
    {
        $totalPaid = $this->transactions()
            // ->whereIn('type', ['payment', 'refund', 'adjustment'])
            ->sum('amount');
        
        return $this->grand_total + $totalPaid; // +ve = owes, -ve = overpaid
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getIsPartiallyPaidAttribute(): bool
    {
        return $this->balance > 0 && $this->balance < $this->grand_total;
    }

    public function getIsOverpaidAttribute(): bool
    {
        return $this->balance < 0;
    }

    public function getTotalBalanceAttribute()
    {
        return $this->transactions()->sum('amount');
    }
}
