<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $admin;
    public $temporaryPassword;

    public function __construct($admin, $temporaryPassword)
    {
        $this->admin = $admin;
        $this->temporaryPassword = $temporaryPassword;
    }

    public function envelope(): Envelope
    {
        $name = trim(($this->admin->admin_first_name ?? '') . ' ' . ($this->admin->admin_last_name ?? ''));
        
        return new Envelope(
            subject: '🎉 ' . ($name ?: 'You') . ', You\'ve Been Invited as a LumiNUs Admin!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_invitation',
        );
    }
}