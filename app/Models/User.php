<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // User types
    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_SUPPLIER = 'supplier';
    public const TYPE_DISTRIBUTOR = 'distributor';
    public const TYPE_ADMIN = 'admin';

    // Scopes
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CUSTOMER);
    }

    public function scopeSuppliers(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SUPPLIER);
    }

    public function scopeDistributors(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_DISTRIBUTOR);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('type', self::TYPE_ADMIN);
    }

    /**
     * Scope a query to only include non-admin users.
     */
    public function scopeNonAdmins($query)
    {
        return $query->where('type', '!=', self::TYPE_ADMIN);
    }

    // Accessors
    public function getIsCustomerAttribute(): bool
    {
        return $this->type === self::TYPE_CUSTOMER;
    }

    public function getIsSupplierAttribute(): bool
    {
        return $this->type === self::TYPE_SUPPLIER;
    }

    public function getIsDistributorAttribute(): bool
    {
        return $this->type === self::TYPE_DISTRIBUTOR;
    }

    /**
     * Check if user is an admin.
     */
    public function getIsAdminAttribute(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    // Relationships
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
