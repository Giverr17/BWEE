<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class FaqItem extends Model
{
    use HasTranslations;

    public array $translatable = ['question', 'answer'];

    protected $fillable = [
        'sort_order',
        'is_active',
        'question',
        'answer',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
