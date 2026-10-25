<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Founder extends Model
{
    use HasTranslations;

    public array $translatable = ['role', 'bio'];

    protected $fillable = [
        'sort_order',
        'is_active',
        'name',
        'role',
        'bio',
        'photo',
        'video_url',
        'audio_url',
        'linkedin_url',
        'twitter_url',
        'email',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }
        if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://') || str_starts_with($this->photo, '//')) {
            return $this->photo;
        }
        if (str_starts_with($this->photo, 'images/') || str_starts_with($this->photo, 'storage/')) {
            return asset($this->photo);
        }
        if (file_exists(public_path($this->photo))) {
            return asset($this->photo);
        }

        return asset('storage/'.$this->photo);
    }
}
