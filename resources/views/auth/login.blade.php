<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · BWEE Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @include('admin._styles')
</head>
<body>
<div class="auth">

    <aside class="auth-brand">
        <div class="auth-logo">
            <img src="{{ asset('storage/hero/bwee-logo-slide1.jpg') }}" alt="Black Women's Enterprise Exchange"
                 onerror="this.parentElement.style.display='none'">
        </div>
        <div>
            <h2>Manage the Exchange.</h2>
            <p>Update events, programs, founders, and everything visitors see on the BWEE website.</p>
        </div>
        <div class="auth-foot">Harriet Tubman Institute for Research on Africa and Its Diaspora, York University</div>
    </aside>

    <main class="auth-panel">
        <div class="auth-card">
            <h1>Welcome back</h1>
            <p class="auth-sub">Sign in to manage BWEE content.</p>

            @if (session('status'))
                <div class="alert alert-ok" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->has('credentials') || $errors->has('throttle'))
                <div class="alert alert-error" role="alert">
                    <span id="alert-msg">{{ $errors->first('throttle') ?: $errors->first('credentials') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" id="login-form" novalidate>
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           class="input @error('email') is-invalid @enderror"
                           autocomplete="username" required
                           @unless (old('email')) autofocus @endunless
                           @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                    @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input id="password" name="password" type="password"
                               class="input @error('password') is-invalid @enderror"
                               autocomplete="current-password" required
                               @if (old('email')) autofocus @endif
                               @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                        <button type="button" class="toggle-pw" id="toggle-pw" aria-pressed="false">Show</button>
                    </div>
                    @error('password') <p class="field-error" id="password-error">{{ $message }}</p> @enderror
                </div>

                <label class="check">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Keep me signed in on this device
                </label>

                <button type="submit" class="btn-primary" id="submit-btn">Sign in</button>
            </form>

            <p class="auth-note">Authorized administrators only.</p>
        </div>
    </main>
</div>

<script>
(function () {
    const form = document.getElementById('login-form');
    const btn = document.getElementById('submit-btn');
    const pw = document.getElementById('password');
    const toggle = document.getElementById('toggle-pw');
    const alertMsg = document.getElementById('alert-msg');
    let left = {{ (int) session('retry_after', 0) }};

    toggle.addEventListener('click', function () {
        const show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        toggle.textContent = show ? 'Hide' : 'Show';
        toggle.setAttribute('aria-pressed', show);
    });

    form.addEventListener('submit', function () {
        btn.disabled = true;
        btn.textContent = 'Signing in...';
    });

    window.addEventListener('pageshow', function (e) {
        if (e.persisted && left <= 0) { btn.disabled = false; btn.textContent = 'Sign in'; }
    });

    if (left > 0) {
        btn.disabled = true;
        (function tick() {
            if (left <= 0) {
                btn.disabled = false;
                btn.textContent = 'Sign in';
                if (alertMsg) alertMsg.textContent = 'You can try again now.';
                return;
            }
            btn.textContent = 'Try again in ' + left + 's';
            if (alertMsg) alertMsg.textContent = 'Too many attempts. Try again in ' + left + ' seconds.';
            left--;
            setTimeout(tick, 1000);
        })();
    }
})();
</script>
</body>
</html>