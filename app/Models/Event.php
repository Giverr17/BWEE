<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Event extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'tag', 'description', 'date_label', 'location', 'cta_text'];

    protected $fillable = [
        'sort_order',
        'is_active',
        'is_upcoming',
        'title',
        'tag',
        'description',
        'speakers',
        'date_label',
        'event_date',
        'location',
        'thumbnail',
        'video_url',
        'audio_url',
        'cta_text',
        'cta_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_upcoming' => 'boolean',
        'event_date' => 'date',
        'speakers' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail) {
            return null;
        }
        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://') || str_starts_with($this->thumbnail, '//')) {
            return $this->thumbnail;
        }
        if (str_starts_with($this->thumbnail, 'images/') || str_starts_with($this->thumbnail, 'storage/')) {
            return asset($this->thumbnail);
        }
        if (file_exists(public_path($this->thumbnail))) {
            return asset($this->thumbnail);
        }

        return asset('storage/'.$this->thumbnail);
    }

    /**
     * Formatted speakers with resolved localized titles and public image URLs.
     *
     * @return array<int, array{name: string, title: string, image: ?string, image_url: ?string}>
     */
    public function getFormattedSpeakersAttribute(): array
    {
        $locale = app()->getLocale();
        $speakers = $this->speakers ?? [];

        return array_map(function ($speaker) use ($locale) {
            $name = $speaker['name'] ?? '';
            $title = $speaker['title'] ?? '';
            if (is_array($title)) {
                $titleText = $title[$locale] ?? $title['en'] ?? (reset($title) ?: '');
            } else {
                $titleText = (string) $title;
            }

            $imagePath = $speaker['image'] ?? null;
            $imageUrl = null;
            if ($imagePath) {
                if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, '//')) {
                    $imageUrl = $imagePath;
                } elseif (str_starts_with($imagePath, 'images/') || str_starts_with($imagePath, 'storage/')) {
                    $imageUrl = asset($imagePath);
                } elseif (file_exists(public_path($imagePath))) {
                    $imageUrl = asset($imagePath);
                } else {
                    $imageUrl = asset('storage/'.$imagePath);
                }
            }

            // Calculate clean initials ignoring honorifics (Dr., Prof., etc.)
            $cleaned = preg_replace('/^(dr\.|prof\.|professor|doctor|mr\.|mrs\.|ms\.)\s+/i', '', trim($name));
            $words = array_values(array_filter(preg_split('/\s+/', (string) $cleaned)));
            if (count($words) >= 2) {
                $initials = mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[count($words) - 1], 0, 1));
            } elseif (count($words) === 1) {
                $initials = mb_strtoupper(mb_substr($words[0], 0, 2));
            } else {
                $initials = '?';
            }

            return [
                'name' => $name,
                'title' => $titleText,
                'image' => $imagePath,
                'image_url' => $imageUrl,
                'initials' => $initials,
            ];
        }, $speakers);
    }

    /**
     * Serialized payload for the interactive Alpine.js Event Details modal.
     *
     * @return array<string, mixed>
     */
    public function getModalDataAttribute(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'tag' => $this->tag ?? ($this->is_upcoming ? 'Upcoming' : 'Event'),
            'date_label' => $this->date_label,
            'location' => $this->location,
            'description' => $this->description,
            'thumbnail_url' => $this->thumbnail_url,
            'video_url' => $this->video_url,
            'cta_text' => $this->cta_text,
            'cta_url' => ($this->cta_url && $this->cta_url !== '#events') ? $this->cta_url : null,
            'speakers' => $this->formatted_speakers,
        ];
    }
}
