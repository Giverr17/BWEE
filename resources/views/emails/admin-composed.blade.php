<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1C1B1F;">

    <p style="white-space: pre-wrap; line-height: 1.6;">{{ $bodyText }}</p>

    <hr style="margin-top: 30px; border: none; border-top: 1px solid #E6E0D4;">

    <p style="font-size: 12px; color: #6B6878; line-height: 1.5;">
        {{ __('mail.newsletter.footer_title', [], $locale) }}<br>
        {{ __('mail.newsletter.footer_affiliation', [], $locale) }}<br>
        <a href="https://{{ __('mail.newsletter.footer_website', [], $locale) }}" style="color: #6B6878;">{{ __('mail.newsletter.footer_website', [], $locale) }}</a>
    </p>

    @if($unsubscribeUrl)
        <p style="font-size: 11px; color: #9B9AA7; margin-top: 12px;">
            <a href="{{ $unsubscribeUrl }}" style="color: #9B9AA7;">{{ __('mail.newsletter.unsubscribe', [], $locale) }}</a>
        </p>
    @endif

</body>
</html>
