<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'client_id',
        'user_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'equipment',
        'location',
        'scheduled_date',
        'scheduled_time',
        'duration_hours',
        'completed_date',
        'work_done',
        'parts_used',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'notes',
        'next_maintenance',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'scheduled_time' => 'datetime:H:i',
            'completed_date' => 'date',
            'next_maintenance' => 'date',
            'parts_used' => 'array',
            'labor_cost' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    // Relations
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePreventive($query)
    {
        return $query->where('type', 'preventive');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('scheduled_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Générer référence
    public static function generateReference(): string
    {
        $year = date('Y');
        $lastMaintenance = self::where('reference', 'like', "MNT-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastMaintenance) {
            $lastNumber = (int) substr($lastMaintenance->reference, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "MNT-{$year}-{$newNumber}";
    }

    // Calculer le coût total
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->labor_cost + $this->parts_cost;
        $this->save();
    }

    // Couleur de priorité
    public function priorityColor(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }

    // Couleur de statut
    public function statusColor(): string
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
