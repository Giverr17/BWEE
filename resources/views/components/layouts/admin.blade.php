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

    {{-- Shared Toast Component --}}
    <div x-data="{
        toasts: [],
        add(type, message) {
            if (!message) return;
            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-init="
        @if (session('status'))
            add('success', {{ json_encode(session('status')) }});
        @elseif (session('success'))
            add('success', {{ json_encode(session('success')) }});
        @elseif (session('error'))
            add('error', {{ json_encode(session('error')) }});
        @elseif (session('notify'))
            add('info', {{ json_encode(session('notify')) }});
        @endif
    "
    @notify.window="add('success', typeof $event.detail === 'string' ? $event.detail : ($event.detail?.message || ($event.detail && $event.detail[0]) || 'Saved.'))"
    @toast.window="add($event.detail?.type || 'success', $event.detail?.message || (typeof $event.detail === 'string' ? $event.detail : 'Action completed.'))"
    role="status"
    aria-live="polite"
    style="position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; max-width: 380px; width: calc(100vw - 40px);"
    >
        <template x-for="t in toasts" :key="t.id">
            <div x-show="true"
                 x-transition
                 style="pointer-events: auto; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; font-weight: 500; display: flex; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.12);"
                 :style="t.type === 'error'
                     ? 'background: var(--danger-bg); color: var(--danger); border: 1px solid #F3C9C6;'
                     : (t.type === 'info'
                         ? 'background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;'
                         : 'background: var(--ok-bg); color: var(--ok); border: 1px solid #BFE0CF;')"
            >
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span x-text="t.type === 'error' ? '✕' : (t.type === 'info' ? 'ℹ' : '✓')" style="font-weight: 700;"></span>
                    <span x-text="t.message"></span>
                </div>
                <button type="button" @click="remove(t.id)" aria-label="Dismiss notification" style="background: none; border: none; cursor: pointer; font-size: 1.1rem; line-height: 1; padding: 0 4px; color: inherit; opacity: 0.7;">&times;</button>
            </div>
        </template>
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
