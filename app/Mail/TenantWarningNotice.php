<?php

namespace App\Mail;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantWarningNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Lease  $lease,
        public readonly string $reason,
        public readonly string $comments,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Official Warning Notice Regarding Your Lease Agreement',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.tenant-warning-notice',
        );
    }
}
