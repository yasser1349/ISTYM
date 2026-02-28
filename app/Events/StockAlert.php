<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $product;
    public $alertType;
    public $message;

    public function __construct(Product $product, string $alertType = 'low_stock')
    {
        $this->product = [
            'id' => $product->id,
            'name' => $product->name,
            'reference' => $product->reference,
            'quantity' => $product->quantity_in_stock,
            'min_quantity' => $product->minimum_stock,
            'category' => $product->category?->name,
        ];
        $this->alertType = $alertType;
        $this->message = $this->generateMessage($product, $alertType);
    }

    private function generateMessage(Product $product, string $alertType): string
    {
        return match($alertType) {
            'critical' => "⚠️ CRITIQUE: {$product->name} - Stock: {$product->quantity_in_stock} (Min: {$product->minimum_stock})",
            'low_stock' => "📦 Stock bas: {$product->name} - {$product->quantity_in_stock} restants",
            'out_of_stock' => "🚨 Rupture: {$product->name} - Stock épuisé!",
            default => "Stock alert: {$product->name}"
        };
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('dashboard'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.alert';
    }
}
