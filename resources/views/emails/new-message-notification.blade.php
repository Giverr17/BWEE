<!DOCTYPE html>
<html>
<body style="font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #1C1B1F;">
    <h2 style="font-size: 18px;">New message from the BWEE website</h2>
    <p><strong>From:</strong> {{ $contactMessage->name }} ({{ $contactMessage->email }})</p>
    @if ($contactMessage->subject)
        <p><strong>Subject:</strong> {{ $contactMessage->subject }}</p>
    @endif
    <p style="white-space: pre-wrap; line-height: 1.6; background: #FBF7F1; padding: 14px; border-radius: 8px; margin-top: 16px;">{{ $contactMessage->body }}</p>
    <p style="margin-top: 20px; font-size: 13px; color: #6B6878;">
        Just hit reply on this email to respond directly to {{ $contactMessage->name }}.
    </p>
</body>
</html>