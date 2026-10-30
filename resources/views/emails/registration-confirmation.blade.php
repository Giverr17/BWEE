<!DOCTYPE html>
<html lang="{{ $registration->locale ?? 'en' }}">
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 24px; color: #1C1B1F; background: #ffffff;">

    @if(($registration->locale ?? 'en') === 'fr')
        <h2 style="font-size: 20px; color: #1C1B1F; margin-bottom: 8px;">Confirmation d'inscription</h2>
        <p style="line-height: 1.6;">Bonjour {{ $registration->full_name }},</p>
        <p style="line-height: 1.6;">Votre inscription a bien ete enregistree pour :</p>
    @else
        <h2 style="font-size: 20px; color: #1C1B1F; margin-bottom: 8px;">Registration Confirmed</h2>
        <p style="line-height: 1.6;">Hello {{ $registration->full_name }},</p>
        <p style="line-height: 1.6;">Your registration has been confirmed for:</p>
    @endif

    <div style="background: #FBF7F1; border: 1px solid #E6E0D4; border-radius: 10px; padding: 20px; margin: 20px 0;">
        <p style="margin: 0 0 8px; font-size: 18px; font-weight: bold;">{{ $registration->event->title ?? '' }}</p>
        @if($registration->event->date_label)
            <p style="margin: 4px 0; color: #6B6878;">
                @if(($registration->locale ?? 'en') === 'fr')
                    Date :
                @else
                    Date:
                @endif
                {{ $registration->event->date_label }}
            </p>
        @endif
        @if($registration->event->location)
            <p style="margin: 4px 0; color: #6B6878;">
                @if(($registration->locale ?? 'en') === 'fr')
                    Lieu :
                @else
                    Location:
                @endif
                {{ $registration->event->location }}
            </p>
        @endif
    </div>

    @if(($registration->locale ?? 'en') === 'fr')
        <p style="line-height: 1.6;">Nous avons hate de vous accueillir.</p>
        <p style="font-size: 0.85rem; color: #6B6878; line-height: 1.5; margin-top: 8px;">
            Vous recevez cet email parce que vous vous etes inscrit(e) a cet evenement BWEE.
        </p>
    @else
        <p style="line-height: 1.6;">We look forward to having you with us.</p>
        <p style="font-size: 0.85rem; color: #6B6878; line-height: 1.5; margin-top: 8px;">
            You are receiving this email because you registered for this BWEE event.
        </p>
    @endif

    <hr style="margin-top: 30px; border: none; border-top: 1px solid #E6E0D4;">
    <p style="font-size: 12px; color: #6B6878; line-height: 1.5;">
        Black Women's Enterprise Exchange (BWEE)<br>
        Harriet Tubman Institute, York University<br>
        <a href="https://bweeofficial.com" style="color: #6B6878;">bweeofficial.com</a>
    </p>

</body>
</html>
