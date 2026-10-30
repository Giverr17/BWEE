<?php

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Component;

new class extends Component
{
    public string $email = '';
    public string $hp_website = '';
    public string $status = '';
    public string $message = '';

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
        ];
    }

    public function subscribe(): void
    {
        if (! empty($this->hp_website)) {
            $this->status = 'success';
            $this->message = __('home.newsletter.success');
            $this->email = '';
            return;
        }

        $key = 'newsletter-subscribe:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->status = 'error';
            $this->message = __('home.newsletter.rate_limited');
            return;
        }
        RateLimiter::hit($key, 60);

        $this->validate();

        $normalizedEmail = strtolower(trim($this->email));
        $locale = in_array(app()->getLocale(), ['en', 'fr'], true) ? app()->getLocale() : 'en';

        $subscriber = NewsletterSubscriber::where('email', $normalizedEmail)->first();

        if ($subscriber) {
            if ($subscriber->unsubscribed_at) {
                $subscriber->update([
                    'locale' => $locale,
                    'unsubscribed_at' => null,
                    'subscribed_at' => now(),
                    'consented_at' => now(),
                    'source' => 'web',
                ]);
                $this->status = 'success';
                $this->message = __('home.newsletter.success');
                $this->email = '';
            } else {
                $this->status = 'already';
                $this->message = __('home.newsletter.already');
                $this->email = '';
            }
            return;
        }

        NewsletterSubscriber::create([
            'email' => $normalizedEmail,
            'locale' => $locale,
            'source' => 'web',
            'subscribed_at' => now(),
            'consented_at' => now(),
        ]);

        $this->status = 'success';
        $this->message = __('home.newsletter.success');
        $this->email = '';
    }
};
?>

<section class="newsletter-section" id="newsletter">
  <div class="wrap newsletter-wrap">
    <div class="newsletter-content">
      <div class="newsletter-eyebrow">{{ __('home.newsletter.eyebrow') }}</div>
      <h3>{{ __('home.newsletter.title') }}</h3>
      <p>{{ __('home.newsletter.text') }}</p>
    </div>

    <div class="newsletter-form-container">
      @if($status === 'success')
        <div class="newsletter-alert newsletter-alert-success" role="alert" style="margin-bottom: 12px;">
          {{ $message ?: __('home.newsletter.success') }}
        </div>
      @elseif($status === 'already')
        <div class="newsletter-alert" role="alert" style="margin-bottom: 12px; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 10px 14px; border-radius: 8px; font-size: 0.9rem;">
          {{ $message ?: __('home.newsletter.already') }}
        </div>
      @elseif($status === 'error')
        <div class="newsletter-alert" role="alert" style="margin-bottom: 12px; background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; padding: 10px 14px; border-radius: 8px; font-size: 0.9rem;">
          {{ $message }}
        </div>
      @endif

      <form wire:submit="subscribe" class="newsletter-form" id="newsletterForm">
        <div style="display:none" aria-hidden="true">
          <input type="text" wire:model="hp_website" tabindex="-1" autocomplete="off">
        </div>

        <div class="newsletter-input-group">
          <input
            type="email"
            wire:model="email"
            required
            placeholder="{{ __('home.newsletter.placeholder') }}"
            aria-label="{{ __('home.newsletter.aria') }}"
            class="newsletter-input"
          >
          <button type="submit" class="newsletter-btn" id="newsletterSubmit" wire:loading.attr="disabled">
            <span wire:loading.remove>{{ __('home.newsletter.button') }}</span>
            <span wire:loading>...</span>
          </button>
        </div>
        @error('email')
          <span class="field-error" style="display: block; margin-top: 6px; color: #b91c1c; font-size: 0.85rem;">{{ $message }}</span>
        @enderror
      </form>
    </div>
  </div>
</section>

