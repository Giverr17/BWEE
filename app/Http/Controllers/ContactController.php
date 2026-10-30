<?php

namespace App\Http\Controllers;

use App\Mail\NewMessageNotification;
use App\Models\Message;
use App\Models\SiteSetting;
use App\Services\MailLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request, MailLogService $mailLog): RedirectResponse
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
            try {
                foreach ($emails as $recipientEmail) {
                    Mail::to($recipientEmail)->queue(new NewMessageNotification($message));
                    $mailLog->record($recipientEmail, 'transactional', NewMessageNotification::class);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to queue contact notification email', [
                    'message_id' => $message->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('contact_success', __('ui.messages.contact_success'));
    }
}
