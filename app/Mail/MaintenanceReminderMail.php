<?php

namespace App\Mail;

use App\Models\Maintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class MaintenanceReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $maintenance;
    public $daysUntil;

    /**
     * Create a new message instance.
     */
    public function __construct(Maintenance $maintenance, int $daysUntil = 0)
    {
        $this->maintenance = $maintenance->load(['equipment']);
        $this->daysUntil = $daysUntil;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $urgency = $this->daysUntil <= 1 ? '🔴' : ($this->daysUntil <= 3 ? '🟠' : '🔵');
        
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "{$urgency} Rappel Maintenance - {$this->maintenance->title}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.maintenance-reminder',
            with: [
                'maintenance' => $this->maintenance,
                'daysUntil' => $this->daysUntil,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
