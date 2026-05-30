<?php

namespace App\Mail;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaseTerminationNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Lease  $lease,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from:    new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Important: Your Lease Has Been Terminated – REMIS',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.lease-termination');
    }
}
