<?php

declare(strict_types=1);

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class UserInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly string $rawToken,
        private readonly CarbonInterface $expiresAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Fantasy Draft Lottery invitation');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.user-invitation',
            with: [
                'acceptUrl' => route('invitations.accept', ['token' => $this->rawToken]),
                'expiresAt' => $this->expiresAt,
            ],
        );
    }
}
