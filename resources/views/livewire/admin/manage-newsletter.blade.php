<?php

use App\Mail\AdminComposedMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Component;

new class extends Component
{
    public $subject = '';
    public $body = '';
    public $subscriberCount = 0;

    public function mount()
    {
        $this->subscriberCount = NewsletterSubscriber::count();
    }

    public function rules()
    {
        return [
            'subject' => ['required', 'min:3'],
            'body' => ['required', 'min:10'],
        ];
    }

    public function send()
    {
        $this->validate();

        $emails = NewsletterSubscriber::pluck('email');

        foreach ($emails as $email) {
            // .queue() instead of .send() — this drops it in the jobs table
            // instead of sending right now, so this loop finishes instantly
            // regardless of list size.
            Mail::to($email)->queue(new AdminComposedMail($this->subject, $this->body));
        }

        $sentCount = $emails->count();
        $this->reset(['subject', 'body']);
        $this->dispatch('notify', "Newsletter queued for {$sentCount} subscriber".($sentCount === 1 ? '' : 's').'.');
    }
};
?>

<div>
    <div class="card" style="max-width: 640px">
        <p style="color:var(--muted);font-size:.9rem;margin-bottom:20px">
            This will be queued for all <strong>{{ $subscriberCount }}</strong> current subscriber{{ $subscriberCount === 1 ? '' : 's' }}, sent individually, not as one shared email.
        </p>

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
                    wire:confirm="Send this to all {{ $subscriberCount }} subscribers? This cannot be undone.">
                Send to {{ $subscriberCount }} Subscriber{{ $subscriberCount === 1 ? '' : 's' }}
            </button>
        </form>
    </div>
</div>