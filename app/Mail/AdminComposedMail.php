<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminComposedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyText,
    ) {}

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->view('emails.admin-composed')
            ->with(['bodyText' => $this->bodyText]);
    }
}
