<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'link',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    // Marquer comme lu
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    // Créer une notification
    public static function notify(
        int $userId,
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
        ]);
    }

    // Notifier pour stock critique
    public static function notifyLowStock(Product $product): void
    {
        $admins = User::where('role', 'admin')->get();
        
        foreach ($admins as $admin) {
            self::notify(
                $admin->id,
                'Stock Critique',
                "Le produit {$product->name} (Réf: {$product->reference}) est en stock critique: {$product->quantity_in_stock} unités.",
                'warning',
                "/inventory/{$product->id}"
            );
        }
    }
}
