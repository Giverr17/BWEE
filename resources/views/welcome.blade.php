@extends('layouts.app')

@section('title', $settings['site_name'] ?? 'Black Women’s Enterprise Exchange (BWEE)')
@section('meta_description', $settings['mission_paragraph_1'] ?? '')

@section('content')
{{-- Split Hero: BWEE Brand Image on one side, Text on the other --}}
<section class="hero-split-stage" id="hero">
  <div class="wrap hero-split-stage-wrap">
    {{-- Left: Visual with BWEE Full Brand Logo --}}
    <div class="hero-split-stage-visual">
      <div class="hero-stage-badge">
        <img src="{{ asset('images/bwee-brand-logo.png') }}" alt="Black Women’s Enterprise Exchange (BWEE)" class="hero-stage-logo">
      </div>
    </div>

    {{-- Right: Content & CTAs --}}
    <div class="hero-split-stage-content">
      <div class="hero-eyebrow">{{ __('home.hero.eyebrow') }}</div>
      <h1 class="hero-split-title">{{ $settings['site_tagline'] ?? '' }}</h1>
      <div class="hero-split-pill">{{ __('home.hero.pill') }}</div>
      <p class="hero-split-desc">
        {{ $settings['mission_paragraph_1'] ?? '' }}
      </p>
      <div class="hero-split-actions">
        <a href="{{ route('involved') }}" class="btn btn-primary">{{ __('home.hero.cta_primary') }}</a>
        <a href="{{ route('contact') }}" class="btn btn-ghost">{{ __('home.hero.cta_secondary') }}</a>
      </div>
    </div>
  </div>

  <div class="hero-scroll-cue">
    <span>{{ __('home.hero.scroll') }}</span>
    <span>&#8595;</span>
  </div>
</section>

{{-- Mission Preview Section --}}
<section class="mission" id="mission">
  <div class="wrap">
    <div>
      <h2>
        {{ $settings['mission_title'] ?? __('site.headings.mission') }}<br>
        <span id="typewriterTarget" data-text="{{ __('home.mission.typewriter') }}"></span><span class="type-cursor" id="typewriterCursor"></span>
      </h2>
      <div style="margin-top: 24px;">
        <a href="{{ route('about') }}" style="color: var(--marigold-soft); font-weight: 600; font-size: 0.95rem; text-decoration: none; border-bottom: 1.5px solid var(--marigold); padding-bottom: 2px;">
          {{ __('home.mission.learn_more') }} &rarr;
        </a>
      </div>
    </div>
    <div>
      <p>
        {{ $settings['mission_paragraph_1'] ?? '' }}
      </p>
      <p style="margin-top: 16px;">
        {{ $settings['mission_paragraph_2'] ?? '' }}
      </p>
      <div class="affil">
        {{ $settings['mission_affiliation'] ?? '' }}
      </div>
    </div>
  </div>
</section>

{{-- Impact / Numbers Strip --}}
<section style="padding: 0 0 40px 0;">
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

{{-- Founders Preview Section --}}
<section id="founders">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>{{ __('home.founders.title') }}</h2>
        <p style="color: var(--ink-soft); font-size: 0.95rem; margin-top: 4px;">{{ __('home.founders.subtitle') }}</p>
      </div>
      <a href="{{ route('founders') }}" class="see-all">{{ __('home.founders.see_all') }} &#8594;</a>
    </div>
    <div class="founders-grid">
      @forelse($founders->take(2) as $founder)
        @include('partials.founder-card', ['founder' => $founder])
      @empty
        <p style="color: var(--ink-soft);">{{ __('home.founders.empty') }}</p>
      @endforelse
    </div>
  </div>
</section>

{{-- Programs / Pillars Preview Section --}}
@if($programs->isNotEmpty())
<section id="programs" style="background: var(--paper-dim);">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>{{ __('site.headings.pillars') }}</h2>
        <p style="color: var(--ink-soft); font-size: 0.95rem; margin-top: 4px;">{{ __('home.programs.subtitle') }}</p>
      </div>
      <a href="{{ route('programs') }}" class="see-all">{{ __('home.programs.see_all') }} &#8594;</a>
    </div>
    <div class="programs-grid">
      @foreach($programs->take(3) as $program)
        @include('partials.program-card', ['program' => $program])
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- Events Preview Section --}}
<section id="events">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>{{ __('site.nav.events') }}</h2>
        <p style="color: var(--ink-soft); font-size: 0.95rem; margin-top: 4px;">{{ __('home.events.subtitle') }}</p>
      </div>
      <a href="{{ route('events') }}" class="see-all">{{ __('home.events.see_all') }} &#8594;</a>
    </div>
    <div class="event-list">
      @forelse($events->take(3) as $event)
        @include('partials.event-card', [
          'event' => $event,
          'loopIndex' => $loop->iteration
        ])
      @empty
        <p style="color: var(--ink-soft);">{{ __('home.events.empty') }}</p>
      @endforelse
    </div>
  </div>
</section>

{{-- Media Gallery Preview Section --}}
@if($gallery->isNotEmpty())
<section id="gallery" style="background: var(--paper-dim);">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>{{ __('site.footer.gallery') }}</h2>
        <p style="color: var(--ink-soft); font-size: 0.95rem; margin-top: 4px;">{{ __('home.gallery.subtitle') }}</p>
      </div>
      <a href="{{ route('gallery') }}" class="see-all">{{ __('home.gallery.see_all') }} &#8594;</a>
    </div>
    <div class="gallery-grid">
      @foreach($gallery->take(2) as $item)
        @include('partials.gallery-card', ['item' => $item])
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- Partners Section --}}
<section id="partners">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>{{ __('home.partners.title') }}</h2>
        <p style="color: var(--ink-soft); font-size: 0.95rem; margin-top: 4px;">{{ __('home.partners.subtitle') }}</p>
      </div>
      <a href="{{ route('partners') }}" class="see-all">{{ __('home.partners.see_all') }} &#8594;</a>
    </div>
    <div class="partner-strip">
      @forelse($partners->take(5) as $partner)
        @include('partials.partner-card', ['partner' => $partner])
      @empty
        <div class="partner-mark">Harriet Tubman Institute</div>
        <div class="partner-mark">York University</div>
      @endforelse
    </div>
    <p class="partner-note">
      {{ __('home.partners.note') }}
    </p>
  </div>
</section>

{{-- Get Involved Section --}}
<section id="involved" style="background: var(--paper-dim);">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>{{ __('site.headings.involved') }}</h2>
        <p style="color: var(--ink-soft); font-size: 0.95rem; margin-top: 4px;">{{ $settings['involve_intro'] ?? '' }}</p>
      </div>
      <a href="{{ route('involved') }}" class="see-all">{{ __('home.involved.see_all') }} &#8594;</a>
    </div>
    <div class="involve-grid">
      @include('partials.involve-card', [
        'title' => $settings['involve_member_title'] ?? '',
        'text' => $settings['involve_member_text'] ?? '',
        'cta' => $settings['involve_member_cta'] ?? '',
        'url' => route('involved'),
      ])
      @include('partials.involve-card', [
        'title' => $settings['involve_sponsor_title'] ?? '',
        'text' => $settings['involve_sponsor_text'] ?? '',
        'cta' => $settings['involve_sponsor_cta'] ?? '',
        'url' => route('involved'),
      ])
      @include('partials.involve-card', [
        'title' => $settings['involve_volunteer_title'] ?? '',
        'text' => $settings['involve_volunteer_text'] ?? '',
        'cta' => $settings['involve_volunteer_cta'] ?? '',
        'url' => route('involved'),
      ])
    </div>
  </div>
</section>

{{-- FAQ Section --}}
@if($faqs->isNotEmpty())
<section id="faq">
  <div class="wrap">
    <div class="section-head">
      <div>
        <h2>{{ __('site.headings.faq') }}</h2>
        <p style="color: var(--ink-soft); font-size: 0.95rem; margin-top: 4px;">{{ __('home.faq.subtitle') }}</p>
      </div>
      <a href="{{ route('about') }}" class="see-all">{{ __('home.faq.see_all') }} &#8594;</a>
    </div>
    <div class="faq-list">
      @foreach($faqs->take(3) as $faq)
        @include('partials.faq-item', ['faq' => $faq, 'open' => $loop->first])
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- Quick Contact Callout --}}
<section style="background: var(--paper-dim); padding: 70px 0;">
  <div class="wrap" style="text-align: center; max-width: 640px; margin: 0 auto;">
    <h2 style="font-size: 2rem; margin-bottom: 12px;">{{ __('home.callout.title') }}</h2>
    <p style="color: var(--ink-soft); font-size: 1rem; line-height: 1.6; margin-bottom: 24px;">
      {{ __('home.callout.text') }}
    </p>
    <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
      <a href="{{ route('contact') }}" class="btn btn-primary">{{ __('home.callout.contact') }}</a>
      <a href="mailto:{{ $settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com' }}" class="btn btn-ghost" style="color: var(--ink); border-color: var(--line);">{{ __('home.callout.email') }}</a>
    </div>
  </div>
</section>

{{-- Newsletter Signup (Footer Area) --}}
@include('partials.newsletter')
@endsection