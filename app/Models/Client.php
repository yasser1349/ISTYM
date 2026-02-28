<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'ice',
        'sector',
        'notes',
        'preferences',
        'credit_limit',
        'balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'preferences' => 'array',
            'credit_limit' => 'decimal:2',
            'balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Calculs
    public function totalPurchases(): float
    {
        return $this->orders()->where('status', 'delivered')->sum('total');
    }

    public function pendingOrders(): int
    {
        return $this->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count();
    }
}
