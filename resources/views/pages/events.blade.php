@extends('layouts.app')

@section('title', __('events.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('events.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('events.hero.eyebrow') }}</div>
    <h1>{{ __('events.hero.heading') }}</h1>
    <p>
      {{ __('events.hero.text') }}
    </p>
  </div>
</section>

{{-- Upcoming Events --}}
@php
  $upcomingEvents = $events->where('is_upcoming', true);
  $pastEvents = $events->where('is_upcoming', false);
@endphp

<section style="padding: 70px 0;">
  <div class="wrap">
    <div class="section-head">
      <div>
        <div style="font-size: 0.82rem; font-weight: 700; color: var(--marigold); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">{{ __('events.upcoming.eyebrow') }}</div>
        <h2>{{ __('events.upcoming.heading') }}</h2>
      </div>
    </div>

    @if($upcomingEvents->isNotEmpty())
      <div class="event-list" style="margin-bottom: 50px;">
        @foreach($upcomingEvents as $event)
          @include('partials.event-card', ['event' => $event])
        @endforeach
      </div>
    @else
      <div style="background: var(--paper-dim); border: 1px solid var(--line); border-radius: var(--radius); padding: 32px; text-align: center; margin-bottom: 50px;">
        <h3 style="font-size: 1.15rem; margin-bottom: 8px;">{{ __('events.upcoming.empty_heading') }}</h3>
        <p style="color: var(--ink-soft); font-size: 0.92rem;">{{ __('events.upcoming.empty_text') }}</p>
      </div>
    @endif

    {{-- Past Gatherings & Workshops --}}
    @if($pastEvents->isNotEmpty())
      <div class="section-head" style="margin-top: 30px;">
        <div>
          <div style="font-size: 0.82rem; font-weight: 700; color: var(--clay); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 6px;">{{ __('events.past.eyebrow') }}</div>
          <h2>{{ __('events.past.heading') }}</h2>
        </div>
      </div>

      <div class="event-list">
        @foreach($pastEvents as $event)
          @include('partials.event-card', ['event' => $event])
        @endforeach
      </div>
    @endif
  </div>
</section>

<section style="background: var(--paper-dim); padding: 60px 0; text-align: center;">
  <div class="wrap" style="max-width: 680px; margin: 0 auto;">
    <h3 style="font-size: 1.5rem; margin-bottom: 12px;">{{ __('events.cta.heading') }}</h3>
    <p style="color: var(--ink-soft); font-size: 0.96rem; line-height: 1.6; margin-bottom: 24px;">
      {{ __('events.cta.text') }}
    </p>
    <a href="{{ route('contact') }}" class="btn btn-primary" style="display: inline-block;">{{ __('events.cta.button') }} &rarr;</a>
  </div>
</section>
@endsection
