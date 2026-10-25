<section class="newsletter-section" id="newsletter">
  <div class="wrap newsletter-wrap">
    <div class="newsletter-content">
      <div class="newsletter-eyebrow">{{ __('home.newsletter.eyebrow') }}</div>
      <h3>{{ __('home.newsletter.title') }}</h3>
      <p>{{ __('home.newsletter.text') }}</p>
    </div>

    <div class="newsletter-form-container">
      @if(session('newsletter_success'))
        <div class="newsletter-alert newsletter-alert-success" role="alert">
          ✅ {{ session('newsletter_success') }}
        </div>
      @endif

      <form action="{{ route('newsletter.store') }}" method="POST" class="newsletter-form" id="newsletterForm">
        @csrf
        <div class="newsletter-input-group">
          <input
            type="email"
            name="email"
            value="{{ old('email') }}"
            required
            placeholder="{{ __('home.newsletter.placeholder') }}"
            aria-label="{{ __('home.newsletter.aria') }}"
            class="newsletter-input"
          >
          <button type="submit" class="newsletter-btn" id="newsletterSubmit">
            {{ __('home.newsletter.button') }}
          </button>
        </div>
        @error('email')
          <span class="field-error" style="display: block; margin-top: 6px;">{{ $message }}</span>
        @enderror
      </form>
    </div>
  </div>
</section>