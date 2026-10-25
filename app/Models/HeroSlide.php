<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class HeroSlide extends Model
{
    use HasTranslations;

    public array $translatable = ['eyebrow', 'heading', 'sub_heading', 'bio', 'cta_primary_text', 'cta_secondary_text'];

    protected $fillable = [
        'sort_order',
        'is_active',
        'layout',
        'eyebrow',
        'heading',
        'sub_heading',
        'bio',
        'cta_primary_text',
        'cta_primary_url',
        'cta_secondary_text',
        'cta_secondary_url',
        'bg_image',
        'bg_image_left',
        'bg_image_right',
        'photo_path_left',
        'photo_path_right',
        'brand_logo',
        'video_url',
        'audio_url',
        'bg_color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    protected static function resolveUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }
        if (str_starts_with($path, 'images/') || str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    /** Full public URL for bg_image */
    public function getBgImageUrlAttribute(): ?string
    {
        return static::resolveUrl($this->bg_image);
    }

    public function getBgImageLeftUrlAttribute(): ?string
    {
        return static::resolveUrl($this->bg_image_left ?? $this->photo_path_left);
    }

    public function getBgImageRightUrlAttribute(): ?string
    {
        return static::resolveUrl($this->bg_image_right ?? $this->photo_path_right);
    }

    public function getBrandLogoUrlAttribute(): ?string
    {
        return static::resolveUrl($this->brand_logo);
    }
}
