@extends('layouts.app')

@section('title', __('registration.closed_title') . ' | ' . ($event->title ?? 'BWEE'))
@section('meta_description', __('registration.closed_message', ['event' => $event->title]))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('registration.form_heading') }}</div>
    <h1>{{ $event->title }}</h1>
    <p>
      {{ __('registration.closed_message', ['event' => $event->title]) }}
    </p>
  </div>
</section>

<section style="padding: 90px 0; min-height: 50vh;">
  <div class="wrap" style="max-width: 680px; text-align: center; margin: 0 auto;">
    <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--line); border-radius: 18px; padding: 48px 32px; box-shadow: 0 16px 40px rgba(0,0,0,0.25);">
      <div style="font-size: 3rem; margin-bottom: 20px;">🔒</div>

      <h2 style="font-family: 'Fraunces', serif; font-size: 1.8rem; color: var(--on-dark); margin-bottom: 16px;">
        {{ __('registration.closed_heading') }}
      </h2>

      {{-- Bilingual friendly notices --}}
      <p style="font-size: 1.05rem; color: var(--ink); line-height: 1.6; margin-bottom: 12px;">
        Registration for <strong>{{ $event->title }}</strong> is currently closed.
      </p>
      <p style="font-size: 0.95rem; color: var(--ink-soft); line-height: 1.6; margin-bottom: 32px;">
        Les inscriptions pour <strong>{{ $event->title }}</strong> sont actuellement closes.
      </p>

      <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
        <a href="{{ route('events') }}" class="btn btn-primary" style="padding: 12px 28px; text-decoration: none;">
          &larr; {{ __('registration.back_to_events') }}
        </a>
      </div>
    </div>
  </div>
</section>
@endsection
