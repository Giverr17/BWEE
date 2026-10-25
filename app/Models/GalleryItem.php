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
        'photos',
        'youtube_id',
        'event_id',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'recorded_at' => 'date',
            'photos' => 'array',
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
     * Cover URL: first photo in the album, or the legacy photo_path, or null.
     */
    public function getCoverUrlAttribute(): ?string
    {
        $photos = $this->photos ?? [];
        if (! empty($photos)) {
            return Storage::disk('public')->url($photos[0]);
        }

        if ($this->photo_path) {
            return Storage::disk('public')->url($this->photo_path);
        }

        return null;
    }

    /**
     * Full public URL for the cover photo (legacy accessor, points to cover_url).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->cover_url;
    }

    /**
     * Ordered array of public URLs for all photos in the album.
     *
     * @return string[]
     */
    public function getPhotoUrlsAttribute(): array
    {
        $photos = $this->photos ?? [];
        if (! empty($photos)) {
            return array_map(
                fn (string $path) => Storage::disk('public')->url($path),
                $photos,
            );
        }

        if ($this->photo_path) {
            return [Storage::disk('public')->url($this->photo_path)];
        }

        return [];
    }

    /**
     * Number of photos in this item (album or single).
     */
    public function getPhotoCountAttribute(): int
    {
        $photos = $this->photos ?? [];
        if (! empty($photos)) {
            return count($photos);
        }

        return $this->photo_path ? 1 : 0;
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
