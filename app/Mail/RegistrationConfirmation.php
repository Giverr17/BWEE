<?php

namespace App\Mail;

use App\Models\EventRegistration;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EventRegistration $registration) {}

    public function build(): static
    {
        $from = config('mail.from.address');
        $fromName = config('mail.from.name');
        $replyTo = config('mail.reply_to.address', config('mail.from.address'));
        $replyToName = config('mail.reply_to.name', config('mail.from.name'));

        $this
            ->from($from, $fromName)
            ->replyTo($replyTo, $replyToName)
            ->subject($this->resolveSubject())
            ->view('emails.registration-confirmation')
            ->text('emails.registration-confirmation-text');

        try {
            if ($this->registration->event) {
                $qrSvg = app(QrCodeService::class)->generateSvg(route('events.register', $this->registration->event));
                $this->attachData($qrSvg, 'event-qr.svg', ['mime' => 'image/svg+xml']);
            }
        } catch (\Throwable) {
            // Keep email functional even if QR code generation fails
        }

        return $this;
    }

    private function resolveSubject(): string
    {
        $locale = $this->registration->locale ?? 'en';
        $eventTitle = $this->registration->event->title ?? '';

        if ($locale === 'fr') {
            return 'Confirmation d\'inscription : '.$eventTitle;
        }

        return 'Registration confirmed: '.$eventTitle;
    }
}
