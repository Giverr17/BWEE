{{ __('mail.registration_confirmation.heading', [], $locale) }} - {{ $eventTitle }}

{{ __('mail.registration_confirmation.greeting', ['name' => $registration->full_name], $locale) }}

{{ __('mail.registration_confirmation.intro', [], $locale) }}

{{ $eventTitle }}
@if(! empty($dateLabel))
{{ __('mail.registration_confirmation.date', [], $locale) }} {{ $dateLabel }}
@endif
@if(! empty($location))
{{ __('mail.registration_confirmation.location', [], $locale) }} {{ $location }}
@endif

{{ __('mail.registration_confirmation.closing', [], $locale) }}

{{ __('mail.registration_confirmation.reason', [], $locale) }}

{{ __('mail.registration_confirmation.footer_title', [], $locale) }}
{{ __('mail.registration_confirmation.footer_affiliation', [], $locale) }}
https://{{ __('mail.registration_confirmation.footer_website', [], $locale) }}
