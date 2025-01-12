<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    protected $url;

    /**
     * Create a new message instance.
     */
    public function __construct($url)
    {
        $path = explode("/", trim(parse_url($url, PHP_URL_PATH), "/"));
        $this->url =
            config("app.frontend_url") .
            "/verify-email" .
            "?" .
            "user_id=" .
            $path[1] .
            "&hash=" .
            $path[2] .
            "&" .
            parse_url($url, PHP_URL_QUERY);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(subject: "Verify Email Address");
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: "maizzle.emails.mail-address-registration",
            with: [
                "verificationUrl" => $this->url,
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
