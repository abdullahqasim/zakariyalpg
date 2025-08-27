<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'size_kg',
        'description',
        'is_active',
    ];

    protected $casts = [
        'size_kg' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBySize(Builder $query, float $size): Builder
    {
        return $query->where('size_kg', $size);
    }

    // Accessors
    public function getFormattedSizeAttribute(): string
    {
        return $this->size_kg . 'kg';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . $this->formatted_size . ')';
    }

    // Static methods for common sizes
    public static function getBaseSize(): float
    {
        return 11.8;
    }

    public static function getAvailableSizes(): array
    {
        return [6.0, 11.8, 15.0, 45.6];
    }

    public static function isValidSize(float $size): bool
    {
        return in_array($size, self::getAvailableSizes());
    }
}
