<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'size_kg',
        'quantity',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'size_kg' => 'decimal:2',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Accessors & Mutators
    public function getFormattedSizeAttribute(): string
    {
        return number_format($this->size_kg, 1) . ' kg';
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return 'PKR' . number_format($this->unit_price, 2);
    }

    public function getFormattedLineTotalAttribute(): string
    {
        return 'PKR' . number_format($this->line_total, 2);
    }
}
