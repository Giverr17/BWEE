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
}
