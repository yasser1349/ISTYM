<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class StockAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $product;
    public $alertType;

    /**
     * Create a new message instance.
     */
    public function __construct(Product $product, string $alertType = 'low')
    {
        $this->product = $product;
        $this->alertType = $alertType; // 'low', 'critical', 'out_of_stock'
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'low' => "⚠️ Stock Bas - {$this->product->name}",
            'critical' => "🔴 Stock Critique - {$this->product->name}",
            'out_of_stock' => "❌ Rupture de Stock - {$this->product->name}",
        ];

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $subjects[$this->alertType] ?? "Alerte Stock - {$this->product->name}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.stock-alert',
            with: [
                'product' => $this->product,
                'alertType' => $this->alertType,
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
