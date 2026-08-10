<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $recipientName;

    public function __construct(string $code, string $recipientName)
    {
        $this->code = $code;
        $this->recipientName = $recipientName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hackathon OTP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}
