<?php

namespace App\Mail;

use App\Models\OnlineOrder;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PaymentInstructionsMail extends Mailable
{
    public function __construct(public readonly OnlineOrder $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vapour Garage] Payment Instructions — Order ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.payment-instructions');
    }
}
