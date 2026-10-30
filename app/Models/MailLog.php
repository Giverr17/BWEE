<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'type',
        'recipient_email',
        'mailable_class',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Count emails sent in the last 24 hours.
     */
    public static function countLast24Hours(): int
    {
        return static::where('sent_at', '>=', now()->subDay())->count();
    }
}
