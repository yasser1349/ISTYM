<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'reference',
        'description',
        'category_id',
        'supplier_id',
        'purchase_price',
        'selling_price',
        'quantity_in_stock',
        'minimum_stock',
        'maximum_stock',
        'unit',
        'location',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCriticalStock($query)
    {
        return $query->whereColumn('quantity_in_stock', '<=', 'minimum_stock');
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity_in_stock', '>', 0);
    }

    // Vérifier si stock critique
    public function isCriticalStock(): bool
    {
        return $this->quantity_in_stock <= $this->minimum_stock;
    }

    // Pourcentage de stock
    public function stockPercentage(): float
    {
        if ($this->maximum_stock == 0) return 0;
        return min(100, ($this->quantity_in_stock / $this->maximum_stock) * 100);
    }

    // Marge bénéficiaire
    public function profitMargin(): float
    {
        if ($this->purchase_price == 0) return 0;
        return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
    }
}
