<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>@yield('title', $settings['site_name'] ?? 'Black Women’s Enterprise Exchange (BWEE)')</title>
  <meta name="description" content="@yield('meta_description', $settings['mission_paragraph_1'] ?? $settings['site_tagline'] ?? '')">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="{{ asset('images/bwee-logo.svg') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

<body data-page="{{ request()->routeIs('home') ? 'home' : 'subpage' }}">

  <header class="site-header {{ $headerClass ?? (request()->routeIs('home') ? '' : 'header-solid') }}" id="siteHeader">
    <div class="wrap nav-wrap">

      {{-- Brand Logo --}}
      <a href="{{ route('home') }}" class="logo-chip" aria-label="BWEE Home">
        <img src="{{ asset($settings['site_logo'] ?? 'images/bwee-logo.svg') }}" alt="Black Women’s Enterprise Exchange (BWEE)" class="logo-img">
      </a>

      {{-- Desktop Navigation Links --}}
      <nav class="nav-desktop" aria-label="{{ __('site.nav.home') }}">
        <ul class="nav-links" id="navLinks">
          <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">{{ __('site.nav.home') }}</a></li>
          <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">{{ __('site.nav.about') }}</a></li>
          <li><a href="{{ route('founders') }}" class="{{ request()->routeIs('founders') ? 'active' : '' }}">{{ __('site.nav.founders') }}</a></li>
          <li><a href="{{ route('programs') }}" class="{{ request()->routeIs('programs') ? 'active' : '' }}">{{ __('site.nav.programs') }}</a></li>
          <li><a href="{{ route('events') }}" class="{{ request()->routeIs('events') ? 'active' : '' }}">{{ __('site.nav.events') }}</a></li>
          <li><a href="{{ route('gallery') }}" class="{{ request()->routeIs('gallery') ? 'active' : '' }}">{{ __('site.nav.gallery') }}</a></li>
          <li><a href="{{ route('partners') }}" class="{{ request()->routeIs('partners') ? 'active' : '' }}">{{ __('site.nav.partners') }}</a></li>
          <li><a href="{{ route('involved') }}" class="{{ request()->routeIs('involved') ? 'active' : '' }}">{{ __('site.nav.involved') }}</a></li>
          <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">{{ __('site.nav.contact') }}</a></li>
        </ul>
      </nav>

      {{-- Right Action Cluster (Language Switcher, CTA, Mobile Menu Toggle) --}}
      <div class="nav-actions">
        <div class="lang-switch" role="group" aria-label="Language selection">
          <a href="{{ route('locale.set', 'en') }}" class="lang-pill {{ app()->getLocale() === 'en' ? 'active' : '' }}" lang="en" title="English">EN</a>
          <span class="lang-divider" aria-hidden="true">|</span>
          <a href="{{ route('locale.set', 'fr') }}" class="lang-pill {{ app()->getLocale() === 'fr' ? 'active' : '' }}" lang="fr" title="Français">FR</a>
        </div>

        <a href="{{ route('involved') }}" class="nav-cta" id="navCta">
          {{ app()->getLocale() === 'fr' ? ($settings['nav_cta_fr'] ?? $settings['nav_cta'] ?? __('site.nav.cta')) : ($settings['nav_cta_en'] ?? $settings['nav_cta'] ?? $settings['involve_sponsor_title'] ?? __('site.nav.cta')) }}
        </a>

        <button class="menu-btn" id="menuBtn" type="button" aria-expanded="false" aria-controls="mobileDrawer" aria-label="Toggle Menu">
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
          <span class="menu-bar"></span>
        </button>
      </div>
    </div>
  </header>

  {{-- Mobile Drawer Backdrop & Drawer Panel (placed at body level so backdrop-filter on header never confines it) --}}
  <div class="mobile-drawer-backdrop" id="mobileDrawerBackdrop" aria-hidden="true"></div>
  <aside class="mobile-drawer" id="mobileDrawer" aria-label="Mobile Navigation" aria-hidden="true">
    <div class="mobile-drawer-top">
      <a href="{{ route('home') }}" class="mobile-drawer-logo" aria-label="BWEE Home">
        <img src="{{ asset($settings['site_logo'] ?? 'images/bwee-logo.svg') }}" alt="BWEE" class="logo-img">
        <span class="mobile-drawer-brand">BWEE</span>
      </a>
      <button class="drawer-close-btn" id="drawerCloseBtn" type="button" aria-label="Close Menu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>

    <div class="mobile-drawer-body">
      <ul class="mobile-nav-links">
        <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.home') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.about') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('founders') }}" class="{{ request()->routeIs('founders') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.founders') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('programs') }}" class="{{ request()->routeIs('programs') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.programs') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('events') }}" class="{{ request()->routeIs('events') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.events') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('gallery') }}" class="{{ request()->routeIs('gallery') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.gallery') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('partners') }}" class="{{ request()->routeIs('partners') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.partners') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('involved') }}" class="{{ request()->routeIs('involved') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.involved') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
        <li><a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}"><span class="nav-label">{{ __('site.nav.contact') }}</span><span class="nav-arrow" aria-hidden="true">&rarr;</span></a></li>
      </ul>
    </div>

    <div class="mobile-drawer-footer">
      <a href="{{ route('involved') }}" class="btn btn-primary drawer-cta-btn">
        {{ app()->getLocale() === 'fr' ? ($settings['nav_cta_fr'] ?? $settings['nav_cta'] ?? __('site.nav.cta')) : ($settings['nav_cta_en'] ?? $settings['nav_cta'] ?? $settings['involve_sponsor_title'] ?? __('site.nav.cta')) }}
      </a>
      <div class="drawer-lang-row">
        <span class="drawer-lang-label">{{ app()->getLocale() === 'fr' ? 'Langue' : 'Language' }}</span>
        <div class="lang-switch" role="group" aria-label="Language selection">
          <a href="{{ route('locale.set', 'en') }}" class="lang-pill {{ app()->getLocale() === 'en' ? 'active' : '' }}" lang="en" title="English">EN</a>
          <span class="lang-divider" aria-hidden="true">|</span>
          <a href="{{ route('locale.set', 'fr') }}" class="lang-pill {{ app()->getLocale() === 'fr' ? 'active' : '' }}" lang="fr" title="Français">FR</a>
        </div>
      </div>
    </div>
  </aside>

  <main>
    @yield('content')
  </main>

  <footer>
    <div class="wrap">
      <div>
        <a href="{{ route('home') }}" class="logo-chip" style="margin-bottom: 16px;">
          <img src="{{ asset($settings['site_logo'] ?? 'images/bwee-logo.svg') }}" alt="BWEE Logo" class="logo-img">
        </a>
        <p style="max-width: 38ch; font-size: 0.92rem; line-height: 1.5; color: rgba(251,247,241,0.7);">
          {{ $settings['site_tagline'] ?? '' }}
        </p>
      </div>
      <div class="cols">
        <div>
          <h4>{{ __('site.footer.explore') }}</h4>
          <ul>
            <li><a href="{{ route('about') }}">{{ __('site.footer.about') }}</a></li>
            <li><a href="{{ route('founders') }}">{{ __('site.footer.founders') }}</a></li>
            <li><a href="{{ route('programs') }}">{{ __('site.footer.pillars') }}</a></li>
            <li><a href="{{ route('events') }}">{{ __('site.footer.events') }}</a></li>
            <li><a href="{{ route('gallery') }}">{{ __('site.footer.gallery') }}</a></li>
            <li><a href="{{ route('partners') }}">{{ __('site.footer.allies') }}</a></li>
          </ul>
        </div>
        <div>
          <h4>{{ __('site.footer.connect') }}</h4>
          <ul>
            <li><a href="{{ route('involved') }}">{{ __('site.footer.involved') }}</a></li>
            <li><a href="{{ route('contact') }}">{{ __('site.footer.contact') }}</a></li>
            <li><a href="mailto:{{ $settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com' }}">{{ $settings['contact_email'] ?? 'blackwomenenterpriseexchange@gmail.com' }}</a></li>
            <li>{{ __('site.footer.institute') }}</li>
            <li>{{ __('site.footer.york') }}</li>
          </ul>
        </div>
      </div>
    </div>
    <div class="foot-bottom">
      {{ __('site.footer.rights', ['year' => date('Y')]) }}
    </div>
  </footer>

  @livewireScripts
</body>

</html>