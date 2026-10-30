{{ $bodyText }}

---
{{ __('mail.newsletter.footer_title', [], $locale) }}
{{ __('mail.newsletter.footer_affiliation', [], $locale) }}
https://{{ __('mail.newsletter.footer_website', [], $locale) }}
@if($unsubscribeUrl)

{{ __('mail.newsletter.unsubscribe_notice', [], $locale) }} {{ $unsubscribeUrl }}
@endif
