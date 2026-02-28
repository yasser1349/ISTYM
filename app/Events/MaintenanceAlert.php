<?php

namespace App\Events;

use App\Models\Maintenance;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $maintenance;
    public $alertType;
    public $message;

    public function __construct(Maintenance $maintenance, string $alertType = 'reminder')
    {
        $this->maintenance = [
            'id' => $maintenance->id,
            'reference' => $maintenance->reference,
            'title' => $maintenance->title,
            'client_name' => $maintenance->client?->name ?? 'Client',
            'type' => $maintenance->type,
            'priority' => $maintenance->priority,
            'scheduled_date' => $maintenance->scheduled_date?->format('d/m/Y'),
            'status' => $maintenance->status,
            'equipment' => $maintenance->equipment,
        ];
        $this->alertType = $alertType;
        $this->message = $this->generateMessage($maintenance, $alertType);
    }

    private function generateMessage(Maintenance $maintenance, string $alertType): string
    {
        $title = $maintenance->title ?? $maintenance->equipment ?? 'Équipement';
        
        return match($alertType) {
            'due_today' => "🔧 Maintenance aujourd'hui: {$title}",
            'overdue' => "⚠️ Maintenance en retard: {$title}",
            'reminder' => "📅 Rappel maintenance: {$title} - {$maintenance->scheduled_date?->format('d/m/Y')}",
            'completed' => "✅ Maintenance terminée: {$title}",
            default => "Maintenance: {$title}"
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
        return 'maintenance.alert';
    }
}
