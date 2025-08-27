<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_number',
        'weight_ton',
        'rate_11_8_kg',
        'total_kg',
        'total_cylinders',
        'total_amount',
        'reference_no',
        'status',
        'notes',
    ];

    protected $casts = [
        'weight_ton' => 'decimal:2',
        'rate_11_8_kg' => 'decimal:2',
        'total_kg' => 'decimal:2',
        'total_cylinders' => 'integer',
        'total_amount' => 'decimal:2',
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
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the transactions for the purchase.
     */
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    // Scopes
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeBySupplier(Builder $query, int $supplierId): Builder
    {
        return $query->where('user_id', $supplierId);
    }

    public function scopeByVehicle(Builder $query, string $vehicleNumber): Builder
    {
        return $query->where('vehicle_number', $vehicleNumber);
    }

    public function scopeByReference(Builder $query, string $referenceNo): Builder
    {
        return $query->where('reference_no', $referenceNo);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PARTIALLY_PAID]);
    }

    // Accessors
    public function getBalanceAttribute(): float
    {
        $paidAmount = $this->transactions()
            ->where('transaction_type', Transaction::TYPE_PAYMENT_OUT)
            ->sum('amount');
        
        return $this->total_amount - abs($paidAmount);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->balance <= 0;
    }

    public function getIsPartiallyPaidAttribute(): bool
    {
        return $this->balance > 0 && $this->balance < $this->total_amount;
    }

    public function getIsOverpaidAttribute(): bool
    {
        return $this->balance < 0;
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return 'PKR' . number_format($this->total_amount, 2);
    }

    public function getFormattedBalanceAttribute(): string
    {
        return 'PKR' . number_format($this->balance, 2);
    }

    public function getFormattedWeightAttribute(): string
    {
        return $this->weight_ton . ' ton';
    }

    public function getFormattedTotalKgAttribute(): string
    {
        return number_format($this->total_kg, 0) . ' kg';
    }

    public function getFormattedTotalCylindersAttribute(): string
    {
        return number_format($this->total_cylinders, 0) . ' cylinders';
    }

    public function getFormattedRateAttribute(): string
    {
        return 'PKR' . number_format($this->rate_11_8_kg, 2) . ' per 11.8kg';
    }

    // Business logic methods
    public function calculateTotals(): void
    {
        $this->total_kg = $this->weight_ton * 1000;
        $this->total_cylinders = (int) round($this->total_kg / 11.8);
        $this->total_amount = $this->total_cylinders * $this->rate_11_8_kg;
    }

    public function updateStatus(): void
    {
        if ($this->balance <= 0) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->balance < $this->total_amount) {
            $this->status = self::STATUS_PARTIALLY_PAID;
        } elseif ($this->status === self::STATUS_DRAFT) {
            $this->status = self::STATUS_CONFIRMED;
        }
    }
}
