@extends('layouts.app')

@section('title', session('already_registered') ? __('registration.already_registered_title') . ' | ' . $event->title : __('registration.success_title') . ' | ' . $event->title)
@section('meta_description', __('registration.success_message', ['event' => $event->title]))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('registration.form_heading') }}</div>
    <h1>{{ $event->title }}</h1>
    <p>
      @if(session('already_registered'))
        {{ __('registration.already_registered_title') }}
      @else
        {{ __('registration.success_title') }}
      @endif
    </p>
  </div>
</section>

<section style="padding: 90px 0; min-height: 50vh;">
  <div class="wrap" style="max-width: 680px; text-align: center; margin: 0 auto;">
    <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--line); border-radius: 18px; padding: 48px 32px; box-shadow: 0 16px 40px rgba(0,0,0,0.25);">
      @if(session('already_registered'))
        <div style="font-size: 3rem; margin-bottom: 20px;">ℹ️</div>

        <h2 style="font-family: 'Fraunces', serif; font-size: 1.8rem; color: var(--marigold); margin-bottom: 16px;">
          {{ __('registration.already_registered_title') }}
        </h2>

        <p style="font-size: 1.05rem; color: var(--ink); line-height: 1.6; margin-bottom: 32px;">
          {{ __('registration.already_registered_message', ['event' => $event->title]) }}
        </p>
      @else
        <div style="font-size: 3rem; margin-bottom: 20px;">✅</div>

        <h2 style="font-family: 'Fraunces', serif; font-size: 1.8rem; color: #34d399; margin-bottom: 16px;">
          {{ __('registration.success_heading') }}
        </h2>

        <p style="font-size: 1.05rem; color: var(--ink); line-height: 1.6; margin-bottom: 24px;">
          {{ __('registration.success_message', ['event' => $event->title]) }}
        </p>

        {{-- Event details --}}
        @if($event->date_label || $event->location)
          <div style="background: rgba(255,255,255,0.05); border: 1px solid var(--line); border-radius: 10px; padding: 18px 22px; margin-bottom: 24px; text-align: left;">
            <p style="font-weight: 600; font-size: 1rem; margin-bottom: 10px; color: var(--on-dark);">{{ $event->title }}</p>
            @if($event->date_label)
              <p style="font-size: 0.9rem; color: var(--ink-soft); margin: 4px 0;">
                📅 {{ $event->date_label }}
              </p>
            @endif
            @if($event->location)
              <p style="font-size: 0.9rem; color: var(--ink-soft); margin: 4px 0;">
                📍 {{ $event->location }}
              </p>
            @endif
          </div>
        @endif

        {{-- QR code --}}
        @php
          try {
              $qrService = app(\App\Services\QrCodeService::class);
              $qrUrl = route('events.register', $event);
              $qrSvg = $qrService->generateSvg($qrUrl);
          } catch (\Throwable $e) {
              $qrSvg = null;
          }
        @endphp

        @if($qrSvg)
          <div style="margin-bottom: 24px;">
            <p style="font-size: 0.85rem; color: var(--ink-soft); margin-bottom: 10px;">{{ __('registration.qr_label') }}</p>
            <div style="display: inline-block; background: #ffffff; padding: 16px; border-radius: 10px; max-width: 200px; width: 100%;">
              <div style="width: 100%; height: auto; max-width: 160px; margin: 0 auto; line-height: 0;">
                {!! $qrSvg !!}
              </div>
            </div>
          </div>
        @endif

        {{-- Spam folder note --}}
        <p style="font-size: 0.85rem; color: var(--ink-soft); line-height: 1.5; margin-bottom: 24px;">
          {{ __('registration.spam_folder_note') }}
        </p>
      @endif

      <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
        <a href="{{ route('events') }}" class="btn btn-primary" style="padding: 12px 28px; text-decoration: none;">
          &larr; {{ __('registration.back_to_events') }}
        </a>
      </div>
    </div>
  </div>
</section>
@endsection
