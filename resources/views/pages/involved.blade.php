@extends('layouts.app')

@section('title', __('involved.hero.title', ['site' => $settings['site_name'] ?? 'BWEE']))
@section('meta_description', __('involved.hero.meta'))

@section('content')
<section class="subpage-hero">
  <div class="wrap">
    <div class="hero-eyebrow">{{ __('involved.hero.eyebrow') }}</div>
    <h1>{{ __('involved.hero.heading') }}</h1>
    <p>
      {{ __('involved.hero.text') }}
    </p>
  </div>
</section>

<section style="padding: 80px 0;">
  <div class="wrap">
    <div class="involve-grid">
      @include('partials.involve-card', [
        'title' => $settings['involve_member_title'] ?? __('involved.cards.member_title'),
        'text'  => $settings['involve_member_text']  ?? __('involved.cards.member_text'),
        'cta'   => $settings['involve_member_cta']   ?? __('involved.cards.member_cta'),
        'url'   => $settings['involve_member_url']   ?? 'mailto:' . ($settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com') . '?subject=Membership%20Interest',
      ])

      @include('partials.involve-card', [
        'title' => $settings['involve_sponsor_title'] ?? __('involved.cards.sponsor_title'),
        'text'  => $settings['involve_sponsor_text']  ?? __('involved.cards.sponsor_text'),
        'cta'   => $settings['involve_sponsor_cta']   ?? __('involved.cards.sponsor_cta'),
        'url'   => $settings['involve_sponsor_url']   ?? 'mailto:' . ($settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com') . '?subject=Sponsorship%20Inquiry',
      ])

      @include('partials.involve-card', [
        'title' => $settings['involve_volunteer_title'] ?? __('involved.cards.volunteer_title'),
        'text'  => $settings['involve_volunteer_text']  ?? __('involved.cards.volunteer_text'),
        'cta'   => $settings['involve_volunteer_cta']   ?? __('involved.cards.volunteer_cta'),
        'url'   => $settings['involve_volunteer_url']   ?? 'mailto:' . ($settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com') . '?subject=Volunteer%20or%20Mentor',
      ])
    </div>
  </div>
</section>

<section style="background: var(--paper-dim); padding: 70px 0;">
  <div class="wrap" style="max-width: 720px; margin: 0 auto; text-align: center;">
    <h3 style="font-size: 1.6rem; margin-bottom: 14px;">{{ __('involved.custom.heading') }}</h3>
    <p style="color: var(--ink-soft); font-size: 1rem; line-height: 1.6; margin-bottom: 24px;">
      {{ __('involved.custom.text') }}
    </p>
    <a href="{{ route('contact') }}" class="btn btn-primary" style="display: inline-block;">{{ __('involved.custom.button') }} &rarr;</a>
  </div>
</section>
@endsection