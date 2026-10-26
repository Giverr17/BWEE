<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminLoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'That does not look like a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput($request->only('email', 'remember'))
                ->with('retry_after', $seconds)
                ->withErrors(['throttle' => "Too many attempts. Try again in {$seconds} seconds."]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);
            $left = RateLimiter::remaining($throttleKey, self::MAX_ATTEMPTS);

            $message = 'The email or password is incorrect.';
            if ($left > 0 && $left <= 2) {
                $message .= " {$left} attempt".($left === 1 ? '' : 's').' left before a temporary lock.';
            }

            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['credentials' => $message]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'You have been signed out.');
    }
}
