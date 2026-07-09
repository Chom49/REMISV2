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

class PaymentControlNumber extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Payment $payment,
        public User    $tenant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from:    new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Your Rent Payment Control Number – ' . optional(optional($this->payment->lease)->property)->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.payment-control-number');
    }
}
