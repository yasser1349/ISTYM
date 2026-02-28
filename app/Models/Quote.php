<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'client_id',
        'user_id',
        'status',
        'notes',
        'items',
        'subtotal',
        'tax',
        'discount',
        'total',
        'valid_until',
        'converted_order_id',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public static function generateQuoteNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $count = self::whereYear('created_at', $year)
                     ->whereMonth('created_at', $month)
                     ->count() + 1;
        
        return "DEV-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    public function calculateTotals(): void
    {
        $this->subtotal = collect($this->items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });
        
        $this->tax = $this->subtotal * 0.20; // 20% TVA
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }
}
