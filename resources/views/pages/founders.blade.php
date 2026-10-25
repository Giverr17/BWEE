@extends('layouts.app')

@section('title', __('founders.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('founders.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('founders.hero.eyebrow') }}</div>
    <h1>{{ __('founders.hero.heading') }}</h1>
    <p>
      {{ __('founders.hero.text') }}
    </p>
  </div>
</section>

<section style="padding: 80px 0;">
  <div class="wrap">
    <div class="founders-grid" style="grid-template-columns: 1fr; gap: 48px; max-width: 900px; margin: 0 auto;">
      @forelse($founders as $founder)
        <div style="background: var(--paper-dim); border: 1px solid var(--line); border-radius: var(--radius); padding: 36px;">
          @include('partials.founder-card', ['founder' => $founder])
        </div>
      @empty
        <p style="color: var(--ink-soft);">{{ __('ui.empty.founders') }}</p>
      @endforelse
    </div>
  </div>
</section>

<section style="background: var(--paper-dim); padding: 60px 0; text-align: center;">
  <div class="wrap" style="max-width: 680px; margin: 0 auto;">
    <h3 style="font-size: 1.5rem; margin-bottom: 14px;">{{ __('founders.cta.heading') }}</h3>
    <p style="color: var(--ink-soft); font-size: 0.98rem; line-height: 1.6; margin-bottom: 24px;">
      {{ __('founders.cta.text') }}
    </p>
    <a href="{{ route('contact') }}" class="btn btn-primary" style="display: inline-block;">{{ __('founders.cta.button') }} &rarr;</a>
  </div>
</section>
@endsection
