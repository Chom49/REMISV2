<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FoInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $fo,
        public string $plainPassword,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from:    new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'You have been appointed as Financial Officer on REMIS',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.fo-invitation');
    }
}
