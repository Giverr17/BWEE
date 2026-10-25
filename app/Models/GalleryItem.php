<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class GalleryItem extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'caption'];

    protected $fillable = [
        'sort_order',
        'is_active',
        'type',
        'title',
        'caption',
        'photo_path',
        'youtube_id',
        'event_id',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'recorded_at' => 'date',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeActive($query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Full public URL for the photo, or null for video-only items.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->photo_path);
    }

    /**
     * Embeddable YouTube iframe src URL, or null for photo items.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (! $this->youtube_id) {
            return null;
        }

        return "https://www.youtube.com/embed/{$this->youtube_id}";
    }
}
