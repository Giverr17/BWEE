<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Program extends Model
{
    use HasTranslations;

    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'sort_order',
        'is_active',
        'slug',
        'title',
        'description',
        'icon',
        'colour',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }
}
