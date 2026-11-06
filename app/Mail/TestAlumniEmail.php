<?php // 👈 MAKE SURE THIS IS HERE!

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestAlumniEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $alumnus;

    public function __construct($alumnus)
    {
        $this->alumnus = $alumnus;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email from LumiNUs Admin',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test_email',
        );
    }
}