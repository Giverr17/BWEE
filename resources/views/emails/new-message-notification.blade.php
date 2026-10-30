<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1C1B1F;">
    <h2 style="font-size: 18px;">{{ __('mail.contact_notification.heading', [], 'en') }}</h2>
    <p><strong>{{ __('mail.contact_notification.from', [], 'en') }}</strong> {{ $contactMessage->name }} ({{ $contactMessage->email }})</p>
    <p><strong>{{ __('mail.contact_notification.visitor_language', ['language' => $visitorLanguage], 'en') }}</strong></p>
    @if ($contactMessage->subject)
        <p><strong>{{ __('mail.contact_notification.subject_label', [], 'en') }}</strong> {{ $contactMessage->subject }}</p>
    @endif
    <p style="white-space: pre-wrap; line-height: 1.6; background: #FBF7F1; padding: 14px; border-radius: 8px; margin-top: 16px;">{{ $contactMessage->body }}</p>
    <p style="margin-top: 20px; font-size: 13px; color: #6B6878;">
        {{ __('mail.contact_notification.reply_hint', ['name' => $contactMessage->name], 'en') }}
    </p>
    <hr style="margin-top: 30px; border: none; border-top: 1px solid #E6E0D4;">
    <p style="font-size: 12px; color: #6B6878;">
        {{ __('mail.contact_notification.footer', [], 'en') }}
    </p>
</body>
</html>
