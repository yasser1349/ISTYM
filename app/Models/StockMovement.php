<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'notes',
    ];

    // Relations
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scopes
    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }

    // Enregistrer un mouvement de stock
    public static function recordMovement(
        int $productId,
        int $userId,
        string $type,
        int $quantity,
        ?int $orderId = null,
        ?string $reason = null,
        ?string $notes = null
    ): self {
        $product = Product::findOrFail($productId);
        $quantityBefore = $product->quantity_in_stock;

        // Mettre à jour le stock du produit
        if ($type === 'in' || $type === 'return') {
            $product->quantity_in_stock += $quantity;
        } else {
            $product->quantity_in_stock -= $quantity;
        }
        $product->save();

        // Créer le mouvement
        return self::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'order_id' => $orderId,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $product->quantity_in_stock,
            'reason' => $reason,
            'notes' => $notes,
        ]);
    }
}
