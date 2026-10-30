@if(($registration->locale ?? 'en') === 'fr')
Confirmation d'inscription - {{ $registration->event->title ?? '' }}

Bonjour {{ $registration->full_name }},

Votre inscription a bien ete enregistree pour :

{{ $registration->event->title ?? '' }}
@if($registration->event->date_label)
Date : {{ $registration->event->date_label }}
@endif
@if($registration->event->location)
Lieu : {{ $registration->event->location }}
@endif

Nous avons hate de vous accueillir.

Vous recevez cet email parce que vous vous etes inscrit(e) a cet evenement BWEE.

Black Women's Enterprise Exchange (BWEE)
Harriet Tubman Institute, York University
https://bweeofficial.com
@else
Registration Confirmed - {{ $registration->event->title ?? '' }}

Hello {{ $registration->full_name }},

Your registration has been confirmed for:

{{ $registration->event->title ?? '' }}
@if($registration->event->date_label)
Date: {{ $registration->event->date_label }}
@endif
@if($registration->event->location)
Location: {{ $registration->event->location }}
@endif

We look forward to having you with us.

You are receiving this email because you registered for this BWEE event.

Black Women's Enterprise Exchange (BWEE)
Harriet Tubman Institute, York University
https://bweeofficial.com
@endif
