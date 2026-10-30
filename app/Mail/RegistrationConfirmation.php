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

    public function __construct(public EventRegistration $registration)
    {
        $this->locale = in_array($registration->locale, ['en', 'fr'], true) ? $registration->locale : 'en';
    }

    public function build(): static
    {
        $locale = $this->locale ?: ($this->registration->locale ?? 'en');
        $event = $this->registration->event;

        $eventTitle = $event?->getTranslation('title', $locale, false);
        if (empty($eventTitle)) {
            $eventTitle = $event?->getTranslation('title', 'en', false) ?: ($event?->title ?? '');
        }

        $dateLabel = $event?->getTranslation('date_label', $locale, false);
        if (empty($dateLabel)) {
            $dateLabel = $event?->getTranslation('date_label', 'en', false) ?: ($event?->date_label ?? '');
        }

        $location = $event?->getTranslation('location', $locale, false);
        if (empty($location)) {
            $location = $event?->getTranslation('location', 'en', false) ?: ($event?->location ?? '');
        }

        $from = config('mail.from.address');
        $fromName = config('mail.from.name');
        $replyTo = config('mail.reply_to.address', config('mail.from.address'));
        $replyToName = config('mail.reply_to.name', config('mail.from.name'));

        $subject = __('mail.registration_confirmation.subject', ['event' => $eventTitle], $locale);

        $this
            ->from($from, $fromName)
            ->replyTo($replyTo, $replyToName)
            ->subject($subject)
            ->view('emails.registration-confirmation')
            ->text('emails.registration-confirmation-text')
            ->with([
                'locale' => $locale,
                'eventTitle' => $eventTitle,
                'dateLabel' => $dateLabel,
                'location' => $location,
            ]);

        try {
            if ($event) {
                $qrSvg = app(QrCodeService::class)->generateSvg(route('events.register', $event));
                $this->attachData($qrSvg, 'event-qr.svg', ['mime' => 'image/svg+xml']);
            }
        } catch (\Throwable) {
            // Keep email functional even if QR code generation fails
        }

        return $this;
    }
}
