<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'locale',
        'source',
        'name',
        'subscribed_at',
        'consented_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'consented_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    /**
     * Scope to only active (not unsubscribed) subscribers.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('unsubscribed_at');
    }
}
