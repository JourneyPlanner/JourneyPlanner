<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailUpdateConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Model $pendingUserEmail)
    {
        $backendUrl = $pendingUserEmail->verificationUrl();
        $path = explode('/', trim(parse_url($backendUrl, PHP_URL_PATH), '/'));
        $this->url =
            config('app.frontend_url').
            '/verify-email?token='.
            $path[3].
            '&'.
            parse_url($backendUrl, PHP_URL_QUERY).
            '&user_id='.
            $pendingUserEmail->user_id;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify Email Address');
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'maizzle.emails.mail-address-update',
            with: [
                'verificationUrl' => $this->url,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
