@props(['title' => 'Dashboard'])
@php
    $unreadMessages = \App\Models\Message::whereNull('read_at')->count();
    $totalRegistrations = \App\Models\EventRegistration::count();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · BWEE Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    @include('admin._styles')
    @livewireStyles
</head>

<body>
    <div class="adm">
        <div class="adm-mobile-header">
            <div class="adm-brand">BWEE<span>.</span> Admin</div>
            <button type="button" class="adm-nav-toggle" id="admNavToggle" aria-label="Toggle navigation menu" aria-expanded="false">
                <span class="adm-nav-toggle-icon">&#9776;</span>
                <span>Menu</span>
                @if ($unreadMessages > 0)
                    <span style="background:var(--gold);color:var(--purple-deep);font-size:.65rem;font-weight:700;padding:2px 6px;border-radius:99px;line-height:1.2">{{ $unreadMessages }}</span>
                @endif
            </button>
        </div>

        <aside class="adm-side" id="admSide">
            <div class="adm-brand adm-brand-desktop">BWEE<span>.</span> Admin</div>
            <nav class="adm-nav">
                <a class="adm-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                    href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="adm-link {{ request()->routeIs('admin.faqs') ? 'active' : '' }}"
                    href="{{ route('admin.faqs') }}">FAQ</a>
                <a class="adm-link {{ request()->routeIs('admin.partners') ? 'active' : '' }}"
                    href="{{ route('admin.partners') }}">Partners</a>
                <a class="adm-link {{ request()->routeIs('admin.programs') ? 'active' : '' }}"
                    href="{{ route('admin.programs') }}">Programs</a>
                <a class="adm-link {{ request()->routeIs('admin.roadmap') ? 'active' : '' }}"
                    href="{{ route('admin.roadmap') }}">Roadmap</a>
                <a class="adm-link {{ request()->routeIs('admin.founders') ? 'active' : '' }}"
                    href="{{ route('admin.founders') }}">Founders</a>

                <a class="adm-link {{ request()->routeIs('admin.events') ? 'active' : '' }}"
                    href="{{ route('admin.events') }}">Events</a>

                <a class="adm-link {{ request()->routeIs('admin.registrations') ? 'active' : '' }}"
                    href="{{ route('admin.registrations') }}">
                    Registrations
                    <span
                        style="background:var(--gold);color:var(--purple-deep);font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:99px;line-height:1.4">{{ $totalRegistrations }}</span>
                </a>

                <a class="adm-link {{ request()->routeIs('admin.gallery') ? 'active' : '' }}"
                    href="{{ route('admin.gallery') }}">Gallery</a>

                <a class="adm-link {{ request()->routeIs('admin.messages') ? 'active' : '' }}"
                    href="{{ route('admin.messages') }}">
                    Messages
                    @if ($unreadMessages > 0)
                        <span
                            style="background:var(--gold);color:var(--purple-deep);font-size:.6rem;font-weight:700;padding:2px 6px;border-radius:99px;line-height:1.4">{{ $unreadMessages }}</span>
                    @endif
                </a>
                <a class="adm-link {{ request()->routeIs('admin.subscribers') ? 'active' : '' }}"
                    href="{{ route('admin.subscribers') }}">Subscribers</a>

                <a class="adm-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                    href="{{ route('admin.settings') }}">Settings</a>
                <a class="adm-link {{ request()->routeIs('admin.newsletter') ? 'active' : '' }}"
                    href="{{ route('admin.newsletter') }}">Newsletter</a>
            </nav>

        </aside>

        <div class="adm-main">
            <header class="adm-top">
                <h1>{{ $title }}</h1>
                <div class="adm-user">
                    <span>{{ auth()->user()->name }}</span>
                    <a href="{{ route('admin.password.edit') }}" class="btn-ghost"
                        style="text-decoration:none;display:inline-block">Change Password</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="btn-ghost">Sign out</button>
                    </form>
                </div>

            </header>
            <main class="adm-content">{{ $slot }}</main>
        </div>
    </div>
    @livewireScripts
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('admNavToggle');
            const side = document.getElementById('admSide');
            if (toggle && side) {
                toggle.addEventListener('click', function () {
                    const isOpen = side.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', isOpen);
                    const icon = toggle.querySelector('.adm-nav-toggle-icon');
                    if (icon) icon.innerHTML = isOpen ? '&times;' : '&#9776;';
                });
            }
        });
    </script>
</body>

</html>