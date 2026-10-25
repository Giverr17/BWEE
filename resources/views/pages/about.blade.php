@extends('layouts.app')

@section('title', __('about.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('about.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('about.hero.eyebrow') }}</div>
    <h1>{{ __('about.hero.heading') }}</h1>
    <p>
      {{ __('about.hero.text') }}
    </p>
  </div>
</section>

{{-- Mission & Vision --}}
<section style="padding: 70px 0;">
  <div class="wrap about-mission-grid">
    <div>
      <div style="font-size: 0.82rem; font-weight: 700; color: var(--clay); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px;">{{ __('about.mission.label') }}</div>
      <h2 style="font-size: clamp(1.8rem, 3vw, 2.4rem); line-height: 1.15; margin-bottom: 20px;">
        {{ $settings['mission_title'] ?? __('about.mission.fallback_title') }}
      </h2>
      <div class="affil" style="margin-top: 0; padding-top: 0; border-top: none; color: var(--indigo); font-weight: 600;">
        📍 {{ $settings['mission_affiliation'] ?? __('about.mission.fallback_affil') }}
      </div>
    </div>
    <div style="font-size: 1.02rem; line-height: 1.7; color: var(--ink-soft);">
      <p style="margin-bottom: 20px;">
        {{ $settings['mission_paragraph_1'] ?? '' }}
      </p>
      <p style="margin-bottom: 20px;">
        {{ $settings['mission_paragraph_2'] ?? '' }}
      </p>
      <p>
        {{ __('about.mission.paragraph_3') }}
      </p>
    </div>
  </div>
</section>

{{-- Numbers / Launch Stats --}}
<section style="padding: 0 0 70px 0;">
  <div class="wrap">
    <div class="numbers-strip">
      <div class="number-cell">
        <div class="n">{{ $settings['stat_1_number'] ?? '172' }}</div>
        <div class="l">{{ $settings['stat_1_label'] ?? __('site.stats.participants') }}</div>
      </div>
      <div class="number-cell">
        <div class="n">{{ $settings['stat_2_number'] ?? '62' }}</div>
        <div class="l">{{ $settings['stat_2_label'] ?? __('site.stats.provinces') }}</div>
      </div>
      <div class="number-cell">
        <div class="n">{{ $settings['stat_3_number'] ?? '13' }}</div>
        <div class="l">{{ $settings['stat_3_label'] ?? __('site.stats.countries') }}</div>
      </div>
    </div>
  </div>
</section>

{{-- EDI, Anti-Racism & Intersectional Equity --}}
<section style="background: var(--paper-dim); padding: 70px 0;">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div style="font-size: 0.82rem; font-weight: 700; color: var(--clay); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">{{ __('about.edi.label') }}</div>
        <h2>{{ __('about.edi.heading') }}</h2>
      </div>
    </div>

    <div class="about-edi-grid">
      <div style="background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius); padding: 32px;">
        <div style="font-size: 1.8rem; margin-bottom: 12px;">✊</div>
        <h3 style="font-size: 1.25rem; margin-bottom: 10px;">{{ __('about.edi.card1_title') }}</h3>
        <p style="color: var(--ink-soft); line-height: 1.6; font-size: 0.95rem;">
          {{ __('about.edi.card1_text') }}
        </p>
      </div>

      <div style="background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius); padding: 32px;">
        <div style="font-size: 1.8rem; margin-bottom: 12px;">♿</div>
        <h3 style="font-size: 1.25rem; margin-bottom: 10px;">{{ __('about.edi.card2_title') }}</h3>
        <p style="color: var(--ink-soft); line-height: 1.6; font-size: 0.95rem;">
          {{ __('about.edi.card2_text') }}
        </p>
      </div>

      <div style="background: var(--paper); border: 1px solid var(--line); border-radius: var(--radius); padding: 32px;">
        <div style="font-size: 1.8rem; margin-bottom: 12px;">🇫🇷</div>
        <h3 style="font-size: 1.25rem; margin-bottom: 10px;">{{ __('about.edi.card3_title') }}</h3>
        <p style="color: var(--ink-soft); line-height: 1.6; font-size: 0.95rem;">
          {{ __('about.edi.card3_text') }}
        </p>
      </div>
    </div>
  </div>
</section>

{{-- FAQ Section --}}
@if(count($faqs) > 0)
<section style="padding: 70px 0;">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div style="font-size: 0.82rem; font-weight: 700; color: var(--clay); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">{{ __('about.faq.label') }}</div>
        <h2>{{ __('about.faq.heading') }}</h2>
      </div>
      <a href="mailto:{{ $settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com' }}" class="see-all">{{ __('about.faq.ask') }} &#8594;</a>
    </div>
    <div class="faq-list">
      @foreach($faqs as $faq)
        @include('partials.faq-item', ['faq' => $faq, 'open' => $loop->first])
      @endforeach
    </div>
  </div>
</section>
@endif

@endsection