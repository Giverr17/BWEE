<?php

use App\Mail\AdminComposedMail;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\NewsletterSubscriber;
use App\Services\MailLogService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Component;

new class extends Component
{
    public string $subject = '';
    public string $body = '';
    public string $audience = 'subscribers';
    public ?int $selectedEventId = null;

    public int $subscriberCount = 0;
    public int $sentToday = 0;
    public int $dailyLimit = 400;
    public int $remainingToday = 400;

    public function mount(MailLogService $mailLog): void
    {
        if (request()->query('audience') === 'event') {
            $this->audience = 'event_registrants';
        }

        $events = Event::orderBy('event_date', 'desc')->get();
        if (request()->query('event_id')) {
            $this->selectedEventId = (int) request()->query('event_id');
        } elseif ($events->isNotEmpty()) {
            $this->selectedEventId = (int) $events->first()->id;
        }

        $this->refreshStats($mailLog);
    }

    public function refreshStats(MailLogService $mailLog): void
    {
        $this->subscriberCount = NewsletterSubscriber::active()->count();
        $this->sentToday = $mailLog->countToday();
        $this->dailyLimit = $mailLog->dailyLimit();
        $this->remainingToday = $mailLog->remainingToday();
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'min:3'],
            'body' => ['required', 'min:10'],
            'audience' => ['required', 'in:subscribers,event_registrants'],
            'selectedEventId' => ['required_if:audience,event_registrants', 'nullable', 'integer'],
        ];
    }

    public function getRecipientCountProperty(): int
    {
        if ($this->audience === 'event_registrants') {
            return $this->selectedEventId
                ? EventRegistration::where('event_id', $this->selectedEventId)->count()
                : 0;
        }

        return NewsletterSubscriber::active()->count();
    }

    public function send(MailLogService $mailLog): void
    {
        $this->validate();

        $remaining = $mailLog->remainingToday();

        if ($remaining <= 0) {
            $this->dispatch('notify', 'Daily send limit reached. No emails were sent today. Try again tomorrow.');
            return;
        }

        if ($this->audience === 'event_registrants') {
            $recipients = EventRegistration::where('event_id', $this->selectedEventId)->get();
            $audienceLabel = 'registrant';
        } else {
            $recipients = NewsletterSubscriber::active()->get();
            $audienceLabel = 'subscriber';
        }

        if ($recipients->isEmpty()) {
            $this->dispatch('notify', 'No recipients found for this audience.');
            return;
        }

        $toSend = $recipients->take($remaining);
        $deferred = $recipients->count() - $toSend->count();

        $delay = 0;

        foreach ($toSend as $recipient) {
            $recipientLocale = $recipient->locale ?? 'en';

            $unsubscribeUrl = URL::signedRoute('unsubscribe', [
                'email' => $recipient->email,
                'locale' => $recipientLocale,
            ]);

            Mail::to($recipient->email)
                ->locale($recipientLocale)
                ->later(
                    now()->addSeconds($delay),
                    new AdminComposedMail($this->subject, $this->body, $unsubscribeUrl, $recipientLocale)
                );

            $mailLog->record($recipient->email, 'bulk', AdminComposedMail::class);

            $delay += 3;
        }

        $sentCount = $toSend->count();
        $this->reset(['subject', 'body']);

        $this->refreshStats($mailLog);

        $msg = "Message queued for {$sentCount} {$audienceLabel}" . ($sentCount === 1 ? '' : 's') . '.';
        if ($deferred > 0) {
            $msg .= " {$deferred} {$audienceLabel}" . ($deferred === 1 ? '' : 's') . " will receive the message tomorrow when the daily limit resets.";
        }

        $this->dispatch('notify', $msg);
    }

    public function with(): array
    {
        return [
            'events' => Event::orderBy('event_date', 'desc')->get(),
        ];
    }
};
?>

<div>
    <div class="card" style="max-width: 680px">
        {{-- Stats bar --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 16px; padding: 12px 16px; background: var(--paper); border-radius: 8px; border: 1px solid var(--line);">
            <div>
                <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 2px;">Sent today</div>
                <div style="font-size: 1.2rem; font-weight: 600;">{{ $sentToday }} <span style="font-size: 0.85rem; color: var(--muted); font-weight: 400;">of {{ $dailyLimit }}</span></div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 2px;">Remaining today</div>
                <div style="font-size: 1.2rem; font-weight: 600; color: {{ $remainingToday < 50 ? 'var(--danger)' : 'inherit' }};">{{ $remainingToday }}</div>
            </div>
            <div>
                <div style="font-size: 0.8rem; color: var(--muted); margin-bottom: 2px;">Active subscribers</div>
                <div style="font-size: 1.2rem; font-weight: 600;">{{ $subscriberCount }}</div>
            </div>
        </div>

        @if($remainingToday <= 0)
            <div style="background: #FEF3CD; border: 1px solid #F9D77E; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; color: #8A6D3B; font-size: 0.9rem;">
                Daily send limit reached. Sending will resume tomorrow.
            </div>
        @elseif($this->recipientCount > $remainingToday)
            <div style="background: #FEF3CD; border: 1px solid #F9D77E; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; color: #8A6D3B; font-size: 0.9rem;">
                You have {{ $this->recipientCount }} recipients selected but only {{ $remainingToday }} sends remaining today.
                The first {{ $remainingToday }} will be sent now; the rest will go out tomorrow.
            </div>
        @endif

        <form wire:submit="send">
            {{-- Audience selection --}}
            <div class="field" style="margin-top:0">
                <label style="font-weight:600;margin-bottom:8px;display:block">Audience</label>
                <div style="display:flex;flex-direction:column;gap:10px;padding:12px;background:var(--paper);border:1px solid var(--line);border-radius:8px">
                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
                        <input type="radio" wire:model.live="audience" value="subscribers" style="margin-top:3px">
                        <div>
                            <div style="font-weight:600;font-size:.9rem">Newsletter subscribers only</div>
                            <div style="font-size:.8rem;color:var(--muted)">Sends to all active subscribers who opted in to marketing newsletters.</div>
                        </div>
                    </label>

                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
                        <input type="radio" wire:model.live="audience" value="event_registrants" style="margin-top:3px">
                        <div>
                            <div style="font-weight:600;font-size:.9rem">All registrants of this event (event information only)</div>
                            <div style="font-size:.8rem;color:var(--muted)">Sends event-specific updates to everyone registered for an event, regardless of general newsletter opt-in.</div>
                        </div>
                    </label>
                </div>
            </div>

            @if($audience === 'event_registrants')
                <div class="field">
                    <label>Select Event</label>
                    <select wire:model.live="selectedEventId" class="input @error('selectedEventId') is-invalid @enderror">
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}">
                                {{ $ev->getTranslation('title', 'en', false) }} ({{ $ev->registrations()->count() }} registrants)
                            </option>
                        @endforeach
                    </select>
                    @error('selectedEventId') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            @endif

            <p style="color:var(--muted);font-size:.85rem;margin:16px 0">
                Targeting: <strong>{{ $this->recipientCount }}</strong> {{ $audience === 'event_registrants' ? 'event registrant' : 'active subscriber' }}{{ $this->recipientCount === 1 ? '' : 's' }}.
                Messages are queued with rate spacing and sent individually in each recipient's chosen language with an unsubscribe link.
            </p>

            <div class="field">
                <label>Subject</label>
                <input type="text" wire:model="subject" class="input @error('subject') is-invalid @enderror">
                @error('subject') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label>Message</label>
                <textarea wire:model="body" rows="10" class="input @error('body') is-invalid @enderror" style="resize:vertical"></textarea>
                @error('body') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary" style="width:auto;margin-top:22px"
                    @if($remainingToday <= 0 || $this->recipientCount === 0) disabled @endif
                    wire:confirm="Send this to {{ min($this->recipientCount, $remainingToday) }} recipient(s)? This cannot be undone.">
                Send to {{ min($this->recipientCount, $remainingToday) }} {{ $audience === 'event_registrants' ? 'Registrant' : 'Subscriber' }}{{ min($this->recipientCount, $remainingToday) === 1 ? '' : 's' }}
            </button>
        </form>
    </div>
</div>
