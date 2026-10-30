<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\Intl\Countries;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'full_name',
        'email',
        'phone',
        'country_of_residence',
        'nationality',
        'profession',
        'organization',
        'expectations',
        'consent_at',
        'locale',
        'newsletter_consent',
    ];

    protected $casts = [
        'consent_at' => 'datetime',
        'newsletter_consent' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Localized country of residence name based on current application locale.
     */
    public function getCountryOfResidenceNameAttribute(): string
    {
        $code = strtoupper((string) $this->country_of_residence);
        $locale = app()->getLocale();

        try {
            if (Countries::exists($code)) {
                return Countries::getName($code, $locale);
            }
        } catch (\Throwable) {
            // Fallback if intl collation fails
        }

        return $code;
    }

    /**
     * Localized nationality country name based on current application locale.
     */
    public function getNationalityNameAttribute(): string
    {
        $code = strtoupper((string) $this->nationality);
        $locale = app()->getLocale();

        try {
            if (Countries::exists($code)) {
                return Countries::getName($code, $locale);
            }
        } catch (\Throwable) {
            // Fallback if intl collation fails
        }

        return $code;
    }

    /**
     * English country of residence name (for CSV export).
     */
    public function getCountryOfResidenceNameEnAttribute(): string
    {
        $code = strtoupper((string) $this->country_of_residence);

        try {
            if (Countries::exists($code)) {
                return Countries::getName($code, 'en');
            }
        } catch (\Throwable) {
            // Fallback
        }

        return $code;
    }

    /**
     * English nationality country name (for CSV export).
     */
    public function getNationalityNameEnAttribute(): string
    {
        $code = strtoupper((string) $this->nationality);

        try {
            if (Countries::exists($code)) {
                return Countries::getName($code, 'en');
            }
        } catch (\Throwable) {
            // Fallback
        }

        return $code;
    }
}
