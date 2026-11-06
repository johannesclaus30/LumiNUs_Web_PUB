<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TracerReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alumni;
    public $form;

    public function __construct($alumni, $form)
    {
        $this->alumni = $alumni;
        $this->form = $form;
    }

    public function build()
    {
        return $this->subject('Reminder: Complete Your Alumni Tracer Survey')
                    ->view('emails.tracer_reminder')
                    ->with([
                        'alumniName' => $this->alumni->first_name,
                        'formTitle' => $this->form->form_title,
                        'tracerUrl' => url('/alumni/tracer/' . $this->form->id),
                    ]);
    }
}