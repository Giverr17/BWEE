New message from the BWEE website

From: {{ $contactMessage->name }} ({{ $contactMessage->email }})
@if($contactMessage->subject)
Subject: {{ $contactMessage->subject }}
@endif

{{ $contactMessage->body }}

---
Reply directly to this email to respond to {{ $contactMessage->name }}.
