<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Partner extends Model
{
    use HasTranslations;

    public array $translatable = ['note'];

    protected $fillable = [
        'sort_order',
        'is_active',
        'name',
        'logo',
        'url',
        'note',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }
        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://') || str_starts_with($this->logo, '//')) {
            return $this->logo;
        }
        if (str_starts_with($this->logo, 'images/') || str_starts_with($this->logo, 'storage/')) {
            return asset($this->logo);
        }
        if (file_exists(public_path($this->logo))) {
            return asset($this->logo);
        }

        return asset('storage/'.$this->logo);
    }
}
