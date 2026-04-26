<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $tenant, public string $setupUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You\'re invited to REMIS – Create your password');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.tenant-invitation');
    }
}
