<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class AdminComposedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyText,
        public string $unsubscribeUrl = '',
        ?string $locale = null,
    ) {
        if ($locale) {
            $this->locale = $locale;
        }
    }

    public function headers(): Headers
    {
        $headers = [];

        if ($this->unsubscribeUrl) {
            $headers['List-Unsubscribe'] = '<'.$this->unsubscribeUrl.'>';
            $headers['List-Unsubscribe-Post'] = 'List-Unsubscribe=One-Click';
        }

        return new Headers(text: $headers);
    }

    public function build(): static
    {
        $from = config('mail.from.address');
        $fromName = config('mail.from.name');
        $replyTo = config('mail.reply_to.address', config('mail.from.address'));
        $replyToName = config('mail.reply_to.name', config('mail.from.name'));

        $locale = $this->locale ?: app()->getLocale();

        return $this
            ->from($from, $fromName)
            ->subject($this->subjectLine)
            ->replyTo($replyTo, $replyToName)
            ->view('emails.admin-composed')
            ->text('emails.admin-composed-text')
            ->with([
                'bodyText' => $this->bodyText,
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'locale' => $locale,
            ]);
    }
}
