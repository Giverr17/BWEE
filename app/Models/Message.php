<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'name',
        'email',
        'locale',
        'subject',
        'interest',
        'body',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function scopeUnread($query): void
    {
        $query->where('is_read', false)->latest();
    }

    public function markRead(): void
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
