<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $contactMessage) {}

    public function build()
    {
        return $this->subject('New message on BWEE: '.($this->contactMessage->subject ?: 'No subject'))
            ->replyTo($this->contactMessage->email, $this->contactMessage->name)
            ->view('emails.new-message-notification');
    }
}
