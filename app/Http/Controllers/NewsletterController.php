<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Store a newly created subscriber in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        NewsletterSubscriber::firstOrCreate(
            ['email' => $email],
            [
                'name' => $validated['name'] ?? null,
                'subscribed_at' => now(),
            ]
        );

        return back()->with('newsletter_success', __('ui.messages.newsletter_success'));
    }
}
