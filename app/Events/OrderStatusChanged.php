<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $oldStatus;
    public $newStatus;
    public $message;

    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = [
            'id' => $order->id,
            'reference' => $order->reference,
            'client_name' => $order->client?->company_name ?? 'Client',
            'total_amount' => $order->total_amount,
        ];
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->message = "📋 Commande #{$order->reference}: {$this->getStatusLabel($oldStatus)} → {$this->getStatusLabel($newStatus)}";
    }

    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En cours',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            default => $status
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
        return 'order.status';
    }
}
