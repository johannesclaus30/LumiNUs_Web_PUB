<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlumniWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alumni;

    public function __construct($alumni)
    {
        $this->alumni = $alumni;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to LumiNUs - Your Account Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-alumni',
        );
    }
}