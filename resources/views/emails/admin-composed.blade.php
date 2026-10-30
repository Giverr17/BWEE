<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1C1B1F;">

    <p style="white-space: pre-wrap; line-height: 1.6;">{{ $bodyText }}</p>

    <hr style="margin-top: 30px; border: none; border-top: 1px solid #E6E0D4;">

    <p style="font-size: 12px; color: #6B6878; line-height: 1.5;">
        Black Women's Enterprise Exchange (BWEE)<br>
        Harriet Tubman Institute, York University<br>
        <a href="https://bweeofficial.com" style="color: #6B6878;">bweeofficial.com</a>
    </p>

    @if($unsubscribeUrl)
        <p style="font-size: 11px; color: #9B9AA7; margin-top: 12px;">
            <a href="{{ $unsubscribeUrl }}" style="color: #9B9AA7;">Unsubscribe</a> from this mailing list /
            <a href="{{ $unsubscribeUrl }}" style="color: #9B9AA7;">Se desabonner</a> de cette liste
        </p>
    @endif

</body>
</html>
