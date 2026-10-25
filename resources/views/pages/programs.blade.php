@extends('layouts.app')

@section('title', __('programs.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('programs.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('programs.hero.eyebrow') }}</div>
    <h1>{{ __('programs.hero.heading') }}</h1>
    <p>
      {{ __('programs.hero.text') }}
    </p>
  </div>
</section>

<section style="padding: 80px 0;">
  <div class="wrap">
    <div class="programs-grid">
      @forelse($programs as $program)
        @include('partials.program-card', ['program' => $program])
      @empty
        <p style="color: var(--ink-soft);">{{ __('ui.empty.programs') }}</p>
      @endforelse
    </div>
  </div>
</section>

{{-- Future Roadmap / What's Next Section --}}
@if(isset($roadmapItems) && $roadmapItems->isNotEmpty())
<section id="roadmap" style="background: var(--paper-dim); padding: 80px 0; border-top: 1px solid var(--line);">
  <div class="wrap">
    <div class="section-head" style="margin-bottom: 40px;">
      <div>
        <div style="font-size: 0.82rem; font-weight: 700; color: var(--clay); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px;">{!! __('programs.roadmap.eyebrow') !!}</div>
        <h2 style="font-family: 'Fraunces', serif; font-size: clamp(1.8rem, 3vw, 2.4rem); line-height: 1.2; margin-bottom: 10px; color: var(--ink);">
          {{ __('programs.roadmap.heading') }}
        </h2>
        <p style="color: var(--ink-soft); font-size: 1rem; line-height: 1.6; max-width: 64ch;">
          {{ __('programs.roadmap.text') }}
        </p>
      </div>
    </div>

    <div class="programs-grid">
      @foreach($roadmapItems as $roadmapItem)
        @include('partials.roadmap-card', ['roadmapItem' => $roadmapItem])
      @endforeach
    </div>
  </div>
</section>
@endif

<section style="padding: 60px 0; border-top: 1px solid var(--line);">
  <div class="wrap" style="text-align: center; max-width: 680px; margin: 0 auto;">
    <h3 style="font-size: 1.5rem; margin-bottom: 12px;">{{ __('programs.cta.heading') }}</h3>
    <p style="color: var(--ink-soft); font-size: 0.98rem; line-height: 1.6; margin-bottom: 24px;">
      {{ __('programs.cta.text') }}
    </p>
    <a href="{{ route('involved') }}" class="btn btn-primary" style="display: inline-block;">{{ __('programs.cta.button') }} &rarr;</a>
  </div>
</section>
@endsection
