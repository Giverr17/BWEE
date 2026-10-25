@extends('layouts.app')

@section('title', __('gallery.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('gallery.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('gallery.hero.eyebrow') }}</div>
    <h1>{!! __('gallery.hero.heading') !!}</h1>
    <p>
      {{ __('gallery.hero.text') }}
    </p>
  </div>
</section>

<section style="padding: 80px 0;">
  <div class="wrap">
    @if($gallery->isNotEmpty())
      <div class="gallery-grid">
        @foreach($gallery as $item)
          @include('partials.gallery-card', ['item' => $item])
        @endforeach
      </div>
    @else
      <div style="background: var(--paper-dim); border: 1px solid var(--line); border-radius: var(--radius); padding: 48px; text-align: center;">
        <div class="stack-icon lg" style="margin-bottom: 16px;"><span></span><span></span><span>&#10022;</span></div>
        <h3 style="font-size: 1.3rem; margin-bottom: 8px;">{{ __('gallery.empty.heading') }}</h3>
        <p style="color: var(--ink-soft); max-width: 50ch; margin: 0 auto; line-height: 1.6;">
          {{ __('gallery.empty.text') }}
        </p>
      </div>
    @endif
  </div>
</section>

<section style="background: var(--paper-dim); padding: 60px 0; text-align: center;">
  <div class="wrap" style="max-width: 680px; margin: 0 auto;">
    <h3 style="font-size: 1.5rem; margin-bottom: 12px;">{{ __('gallery.cta.heading') }}</h3>
    <p style="color: var(--ink-soft); font-size: 0.96rem; line-height: 1.6; margin-bottom: 24px;">
      {{ __('gallery.cta.text') }}
    </p>
    <a href="{{ route('contact') }}" class="btn btn-primary" style="display: inline-block;">{{ __('gallery.cta.button') }} &rarr;</a>
  </div>
</section>
@endsection
