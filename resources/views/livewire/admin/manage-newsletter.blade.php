<?php

use App\Mail\AdminComposedMail;
use App\Models\NewsletterSubscriber;
use App\Services\MailLogService;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Component;

new class extends Component
{
    public string $subject = '';
    public string $body = '';
    public int $subscriberCount = 0;
    public int $sentToday = 0;
    public int $dailyLimit = 400;
    public int $remainingToday = 400;

    public function mount(MailLogService $mailLog): void
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
        ];
    }

    public function send(MailLogService $mailLog): void
    {
        $this->validate();

        $subscribers = NewsletterSubscriber::active()->get();
        $remaining = $mailLog->remainingToday();

        if ($remaining <= 0) {
            $this->dispatch('notify', 'Daily send limit reached. No emails were sent today. Try again tomorrow.');
            return;
        }

        $toSend = $subscribers->take($remaining);
        $deferred = $subscribers->count() - $toSend->count();

        $delay = 0;

        foreach ($toSend as $subscriber) {
            $unsubscribeUrl = URL::signedRoute('unsubscribe', [
                'email' => $subscriber->email,
                'locale' => 'en',
            ]);

            Mail::to($subscriber->email)
                ->later(
                    now()->addSeconds($delay),
                    new AdminComposedMail($this->subject, $this->body, $unsubscribeUrl)
                );

            $mailLog->record($subscriber->email, 'bulk', AdminComposedMail::class);

            $delay += 3;
        }

        $sentCount = $toSend->count();
        $this->reset(['subject', 'body']);

        $this->sentToday = $mailLog->countToday();
        $this->remainingToday = $mailLog->remainingToday();
        $this->subscriberCount = NewsletterSubscriber::active()->count();

        $msg = "Newsletter queued for {$sentCount} subscriber" . ($sentCount === 1 ? '' : 's') . '.';
        if ($deferred > 0) {
            $msg .= " {$deferred} subscriber" . ($deferred === 1 ? '' : 's') . " will receive the message tomorrow when the daily limit resets.";
        }

        $this->dispatch('notify', $msg);
    }
};
?>

<div>
    <div class="card" style="max-width: 640px">
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
        @elseif($subscriberCount > $remainingToday)
            <div style="background: #FEF3CD; border: 1px solid #F9D77E; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; color: #8A6D3B; font-size: 0.9rem;">
                You have {{ $subscriberCount }} active subscribers but only {{ $remainingToday }} sends remaining today.
                The first {{ $remainingToday }} will be sent now; the rest will go out tomorrow.
            </div>
        @else
            <p style="color:var(--muted);font-size:.9rem;margin-bottom:20px">
                This will be queued for all <strong>{{ $subscriberCount }}</strong> active subscriber{{ $subscriberCount === 1 ? '' : 's' }}, sent individually. Each email includes an unsubscribe link.
            </p>
        @endif

        <form wire:submit="send">
            <div class="field" style="margin-top:0">
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
                    @if($remainingToday <= 0) disabled @endif
                    wire:confirm="Send this to {{ min($subscriberCount, $remainingToday) }} subscriber(s)? This cannot be undone.">
                Send to {{ min($subscriberCount, $remainingToday) }} Subscriber{{ min($subscriberCount, $remainingToday) === 1 ? '' : 's' }}
            </button>
        </form>
    </div>
</div>
