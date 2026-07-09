<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FoPaymentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public User    $landlord,
        public string  $foName,
    ) {}

    public function envelope(): Envelope
    {
        $property = optional(optional($this->payment->lease)->property)->name ?? 'a property';
        return new Envelope(
            from:    new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Payment Received – {$property}",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.fo-payment-notification');
    }
}
