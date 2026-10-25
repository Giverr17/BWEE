@extends('layouts.app')

@section('title', __('partners.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('partners.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('partners.hero.eyebrow') }}</div>
    <h1>{!! __('partners.hero.heading') !!}</h1>
    <p>
      {{ __('partners.hero.text') }}
    </p>
  </div>
</section>

<section style="padding: 80px 0;">
  <div class="wrap">
    <div class="partners-cards-grid">
      @forelse($partners as $partner)
        <div style="border: 1px solid var(--line); border-radius: var(--radius); padding: 28px; background: var(--paper-dim); display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="min-height: 48px; display: flex; align-items: center; margin-bottom: 16px;">
              @include('partials.partner-card', ['partner' => $partner])
            </div>
            <h3 style="font-size: 1.15rem; margin-bottom: 8px;">{{ $partner->name }}</h3>
            @if($partner->note)
              <p style="color: var(--ink-soft); font-size: 0.9rem; line-height: 1.5;">{{ $partner->note }}</p>
            @endif
          </div>
          @if($partner->url)
            <div style="margin-top: 20px;">
              <a href="{{ $partner->url }}" target="_blank" rel="noopener" style="color: var(--clay); font-weight: 600; font-size: 0.88rem; text-decoration: none; border-bottom: 1px solid var(--clay); padding-bottom: 2px;">{{ __('ui.buttons.visit_website') }} &rarr;</a>
            </div>
          @endif
        </div>
      @empty
        <p style="color: var(--ink-soft);">{{ __('ui.empty.partners') }}</p>
      @endforelse
    </div>
  </div>
</section>

{{-- Host Institution Callout --}}
<section style="background: var(--paper-dim); padding: 70px 0;">
  <div class="wrap" style="max-width: 800px; margin: 0 auto; text-align: center;">
    <div style="font-size: 0.82rem; font-weight: 700; color: var(--clay); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px;">{{ __('partners.anchor.eyebrow') }}</div>
    <h2 style="font-size: 1.8rem; margin-bottom: 16px;">{{ __('partners.anchor.heading') }}</h2>
    <p style="color: var(--ink-soft); font-size: 1rem; line-height: 1.65; margin-bottom: 28px;">
      {{ __('partners.anchor.text') }}
    </p>
    <a href="{{ route('involved') }}" class="btn btn-primary" style="display: inline-block;">{{ __('partners.anchor.button') }} &rarr;</a>
  </div>
</section>
@endsection
