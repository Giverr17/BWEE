<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; color: #1C1B1F; background: #ffffff;">

    <h2 style="font-size: 20px; color: #1C1B1F; margin-bottom: 8px;">
        {{ __('mail.registration_confirmation.heading', [], $locale) }}
    </h2>
    <p style="line-height: 1.6;">
        {{ __('mail.registration_confirmation.greeting', ['name' => $registration->full_name], $locale) }}
    </p>
    <p style="line-height: 1.6;">
        {{ __('mail.registration_confirmation.intro', [], $locale) }}
    </p>

    <div style="background: #FBF7F1; border: 1px solid #E6E0D4; border-radius: 10px; padding: 20px; margin: 20px 0;">
        <p style="margin: 0 0 8px; font-size: 18px; font-weight: bold;">{{ $eventTitle }}</p>
        @if(! empty($dateLabel))
            <p style="margin: 4px 0; color: #6B6878;">
                {{ __('mail.registration_confirmation.date', [], $locale) }} {{ $dateLabel }}
            </p>
        @endif
        @if(! empty($location))
            <p style="margin: 4px 0; color: #6B6878;">
                {{ __('mail.registration_confirmation.location', [], $locale) }} {{ $location }}
            </p>
        @endif
    </div>

    <p style="line-height: 1.6;">
        {{ __('mail.registration_confirmation.closing', [], $locale) }}
    </p>
    <p style="font-size: 0.85rem; color: #6B6878; line-height: 1.5; margin-top: 8px;">
        {{ __('mail.registration_confirmation.reason', [], $locale) }}
    </p>

    <hr style="margin-top: 30px; border: none; border-top: 1px solid #E6E0D4;">
    <p style="font-size: 12px; color: #6B6878; line-height: 1.5;">
        {{ __('mail.registration_confirmation.footer_title', [], $locale) }}<br>
        {{ __('mail.registration_confirmation.footer_affiliation', [], $locale) }}<br>
        <a href="https://{{ __('mail.registration_confirmation.footer_website', [], $locale) }}" style="color: #6B6878;">{{ __('mail.registration_confirmation.footer_website', [], $locale) }}</a>
    </p>

</body>
</html>
