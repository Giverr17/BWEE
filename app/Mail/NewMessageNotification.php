<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessageNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $contactMessage)
    {
        $this->locale = 'en';
    }

    public function build(): static
    {
        $from = config('mail.from.address');
        $fromName = config('mail.from.name');

        $visitorLanguage = match ($this->contactMessage->locale) {
            'fr' => 'French (Français)',
            default => 'English',
        };

        return $this
            ->from($from, $fromName)
            ->subject('New message on BWEE: '.($this->contactMessage->subject ?: 'No subject'))
            ->replyTo($this->contactMessage->email, $this->contactMessage->name)
            ->view('emails.new-message-notification')
            ->text('emails.new-message-notification-text')
            ->with([
                'visitorLanguage' => $visitorLanguage,
            ]);
    }
}
