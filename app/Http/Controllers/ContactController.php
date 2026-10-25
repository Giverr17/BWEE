<?php

namespace App\Http\Controllers;

use App\Mail\NewMessageNotification;
use App\Models\Message;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'interest' => ['nullable', 'string', 'in:membership,partner,volunteer,other'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create($validated);

        $recipients = SiteSetting::where('key', 'contact_notification_emails')->value('value');
        $emails = array_filter(array_map('trim', explode(',', $recipients ?? '')));

        if (! empty($emails)) {
            Mail::to($emails)->send(new NewMessageNotification($message));
        }

        return redirect()
            ->back()
            ->with('contact_success', __('ui.messages.contact_success'));
    }
}
