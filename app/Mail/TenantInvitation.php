<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $tenant,
        public string $plainPassword,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from:    new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Your REMIS Account is Ready – Login Credentials Inside',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.tenant-invitation');
    }
}
