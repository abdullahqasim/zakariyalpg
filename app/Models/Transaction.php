<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transactionable_type',
        'transactionable_id',
        'transaction_type',
        'amount',
        'balance',
        'details',
        'payment_method_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'details' => 'array',
    ];

    // Transaction types
    const TYPE_SALE = 'sale';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_PAYMENT_IN = 'payment_in';
    const TYPE_REFUND = 'payment_refund';
    const TYPE_PAYMENT_OUT = 'payment_out';
    

    /**
     * Get the parent transactionable model (sale or purchase).
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    /**
     * Scope a query to only include transactions by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope a query to only include transactions by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include sales transactions.
     */
    public function scopeSales($query)
    {
        return $query->where('transaction_type', self::TYPE_SALE);
    }

    /**
     * Scope a query to only include purchase transactions.
     */
    public function scopePurchases($query)
    {
        return $query->where('transaction_type', self::TYPE_PURCHASE);
    }

    /**
     * Scope a query to only include payment in transactions.
     */
    public function scopePaymentsIn($query)
    {
        return $query->where('transaction_type', self::TYPE_PAYMENT_IN);
    }

    /**
     * Scope a query to only include payment out transactions.
     */
    public function scopePaymentsOut($query)
    {
        return $query->where('transaction_type', self::TYPE_PAYMENT_OUT);
    }

    /**
     * Scope a query to only include payment transactions.
     */
    public function scopePayments($query)
    {
        return $query->whereIn('transaction_type', [self::TYPE_PAYMENT_IN, self::TYPE_PAYMENT_OUT]);
    }

    /**
     * Get formatted amount attribute.
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get formatted balance attribute.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return number_format($this->balance, 2);
    }

    /**
     * Check if transaction is a debit (increases balance).
     */
    public function getIsDebitAttribute(): bool
    {
        $user = $this->user;
        
        if (!$user) {
            return false;
        }

        // For customers: sales increase balance (debit), payments decrease balance (credit)
        if ($user->is_customer) {
            return in_array($this->transaction_type, [self::TYPE_SALE]);
        }
        
        // For suppliers: purchases increase balance (credit), payments decrease balance (debit)
        if ($user->is_supplier) {
            return in_array($this->transaction_type, [self::TYPE_PAYMENT_OUT]);
        }

        return false;
    }

    /**
     * Check if transaction is a credit (decreases balance).
     */
    public function getIsCreditAttribute(): bool
    {
        return !$this->is_debit;
    }

    /**
     * Check if transaction is a sale.
     */
    public function getIsSaleAttribute(): bool
    {
        return $this->transaction_type === self::TYPE_SALE;
    }

    /**
     * Check if transaction is a purchase.
     */
    public function getIsPurchaseAttribute(): bool
    {
        return $this->transaction_type === self::TYPE_PURCHASE;
    }

    /**
     * Check if transaction is a payment.
     */
    public function getIsPaymentAttribute(): bool
    {
        return in_array($this->transaction_type, [self::TYPE_PAYMENT_IN, self::TYPE_PAYMENT_OUT]);
    }

    /**
     * Check if transaction is a payment in.
     */
    public function getIsPaymentInAttribute(): bool
    {
        return $this->transaction_type === self::TYPE_PAYMENT_IN;
    }

    /**
     * Check if transaction is a payment out.
     */
    public function getIsPaymentOutAttribute(): bool
    {
        return $this->transaction_type === self::TYPE_PAYMENT_OUT;
    }

    /**
     * Get debit amount (positive for debits, 0 for credits).
     */
    public function getDebitAmountAttribute(): float
    {
        return $this->is_debit ? abs($this->amount) : 0;
    }

    /**
     * Get credit amount (positive for credits, 0 for debits).
     */
    public function getCreditAmountAttribute(): float
    {
        return $this->is_credit ? abs($this->amount) : 0;
    }

    /**
     * Get the reference number from the transactionable model.
     */
    public function getReferenceNumberAttribute(): ?string
    {
        if ($this->transactionable) {
            return $this->transactionable->reference_no ?? $this->transactionable->invoice_no ?? null;
        }
        
        return null;
    }

    /**
     * Get the description for the transaction.
     */
    public function getDescriptionAttribute(): string
    {
        switch ($this->transaction_type) {
            case self::TYPE_SALE:
                return "Sale #{$this->reference_number}";
            case self::TYPE_PURCHASE:
                return "Purchase #{$this->reference_number}";
            case self::TYPE_PAYMENT_IN:
                return "Payment Received";
            case self::TYPE_PAYMENT_OUT:
                return "Payment Made";
            default:
                return "Transaction";
        }
    }
}
