{{ __('mail.contact_notification.heading', [], 'en') }}

{{ __('mail.contact_notification.from', [], 'en') }} {{ $contactMessage->name }} ({{ $contactMessage->email }})
{{ __('mail.contact_notification.visitor_language', ['language' => $visitorLanguage], 'en') }}
@if($contactMessage->subject)
{{ __('mail.contact_notification.subject_label', [], 'en') }} {{ $contactMessage->subject }}
@endif

{{ $contactMessage->body }}

---
{{ __('mail.contact_notification.reply_hint', ['name' => $contactMessage->name], 'en') }}
