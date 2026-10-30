<?php

namespace App\Services;

use App\Models\MailLog;
use App\Models\SiteSetting;

class MailLogService
{
    /**
     * Record a sent email in the log.
     */
    public function record(string $recipientEmail, string $type = 'transactional', string $mailableClass = ''): void
    {
        MailLog::create([
            'type' => $type,
            'recipient_email' => $recipientEmail,
            'mailable_class' => $mailableClass,
            'sent_at' => now(),
        ]);
    }

    /**
     * Count emails sent in the last 24 hours.
     */
    public function countToday(): int
    {
        return MailLog::countLast24Hours();
    }

    /**
     * Get the configured daily limit.
     */
    public function dailyLimit(): int
    {
        return (int) SiteSetting::get('mail_daily_limit', 400);
    }

    /**
     * Check whether sending $count more bulk emails would exceed the daily limit.
     */
    public function bulkWouldExceedLimit(int $count = 1): bool
    {
        return ($this->countToday() + $count) > $this->dailyLimit();
    }

    /**
     * How many bulk emails can still be sent today.
     */
    public function remainingToday(): int
    {
        return max(0, $this->dailyLimit() - $this->countToday());
    }
}
