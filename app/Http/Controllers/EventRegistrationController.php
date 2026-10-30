<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmation;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use App\Services\MailLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\Intl\Countries;

class EventRegistrationController extends Controller
{
    /**
     * Display the registration form for an event or closed notice if closed.
     */
    public function create(Event $event)
    {
        $settings = rescue(fn () => SiteSetting::forLocale(), []);

        if (! $event->isRegistrationOpen()) {
            return response()->view('pages.events.register-closed', compact('event', 'settings'), 200);
        }

        $locale = app()->getLocale();
        try {
            $countries = Countries::getNames($locale);
        } catch (\Throwable) {
            $countries = Countries::getNames('en');
        }

        return view('pages.events.register', compact('event', 'countries', 'settings'));
    }

    /**
     * Store a new event registration.
     */
    public function store(Request $request, Event $event, MailLogService $mailLog)
    {
        if (! $event->isRegistrationOpen()) {
            return redirect()->route('events.register', $event);
        }

        // Honeypot check: if filled, pretend success and store nothing
        if (! empty($request->input('website'))) {
            return redirect()->route('events.register.thanks', $event)->with('registration_success', true);
        }

        try {
            $validCountryCodes = array_keys(Countries::getNames('en'));
        } catch (\Throwable) {
            $validCountryCodes = [];
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9\s\-()]{7,20}$/'],
            'country_of_residence' => ['required', 'string', 'size:2', Rule::in($validCountryCodes)],
            'nationality' => ['required', 'string', 'size:2', Rule::in($validCountryCodes)],
            'profession' => ['required', 'string', 'min:2', 'max:150'],
            'organization' => ['nullable', 'string', 'max:150'],
            'expectations' => ['required', 'string', 'min:5', 'max:1000'],
            'consent' => ['accepted'],
            'newsletter_opt_in' => ['nullable', 'boolean'],
            'newsletter_consent' => ['nullable', 'boolean'],
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));

        // Check duplicate for the same event
        $alreadyRegistered = EventRegistration::where('event_id', $event->id)
            ->where('email', $normalizedEmail)
            ->exists();

        if ($alreadyRegistered) {
            return redirect()->route('events.register.thanks', $event)->with('already_registered', true);
        }

        $locale = in_array(app()->getLocale(), ['en', 'fr'], true) ? app()->getLocale() : 'en';

        $isOptedIn = ! empty($validated['newsletter_opt_in']) || ! empty($validated['newsletter_consent']);

        $registration = $event->registrations()->create([
            'full_name' => trim($validated['full_name']),
            'email' => $normalizedEmail,
            'phone' => trim($validated['phone']),
            'country_of_residence' => strtoupper($validated['country_of_residence']),
            'nationality' => strtoupper($validated['nationality']),
            'profession' => trim($validated['profession']),
            'organization' => ! empty($validated['organization']) ? trim($validated['organization']) : null,
            'expectations' => trim($validated['expectations']),
            'consent_at' => now(),
            'locale' => $locale,
            'newsletter_consent' => $isOptedIn,
            'newsletter_opt_in' => $isOptedIn,
            'consented_at' => $isOptedIn ? now() : null,
        ]);

        if ($isOptedIn) {
            $subscriber = NewsletterSubscriber::where('email', $normalizedEmail)->first();
            if ($subscriber) {
                if (! $subscriber->unsubscribed_at) {
                    $subscriber->update([
                        'name' => $subscriber->name ?: trim($validated['full_name']),
                        'locale' => $locale,
                        'source' => $subscriber->source ?: 'registration',
                        'consented_at' => $subscriber->consented_at ?: now(),
                    ]);
                }
            } else {
                NewsletterSubscriber::create([
                    'email' => $normalizedEmail,
                    'name' => trim($validated['full_name']),
                    'locale' => $locale,
                    'source' => 'registration',
                    'subscribed_at' => now(),
                    'consented_at' => now(),
                ]);
            }
        }

        // Queue confirmation email - never let a mail failure break the registration
        try {
            Mail::to($registration->email)
                ->locale($locale)
                ->queue(new RegistrationConfirmation($registration));
            $mailLog->record($registration->email, 'transactional', RegistrationConfirmation::class);
        } catch (\Throwable $e) {
            Log::error('Failed to queue registration confirmation email', [
                'registration_id' => $registration->id,
                'email' => $registration->email,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('events.register.thanks', $event)->with('registration_success', true);
    }

    /**
     * Display the thank you / confirmation page.
     */
    public function thanks(Event $event)
    {
        $settings = rescue(fn () => SiteSetting::forLocale(), []);

        return view('pages.events.register-thanks', compact('event', 'settings'));
    }
}
