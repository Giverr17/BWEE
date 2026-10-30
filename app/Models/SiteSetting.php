<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
    ];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("site_setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    /**
     * Set (upsert) a setting value by key.
     */
    public static function set(string $key, mixed $value, string $type = 'text', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );
        Cache::forget("site_setting:{$key}");
    }

    /**
     * Get all settings in a group, keyed by 'key'.
     *
     * @return array<string, string>
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function forLocale(): array
    {
        $settings = static::pluck('value', 'key')->toArray();

        if (app()->getLocale() === 'fr') {
            foreach ($settings as $key => $value) {
                if (str_ends_with($key, '_fr') && filled($value)) {
                    $settings[substr($key, 0, -3)] = $value;
                }
            }
        }

        foreach ([1, 2, 3] as $i) {
            if (isset($settings["stat_{$i}_number"]) && ! isset($settings["stat_{$i}_value"])) {
                $settings["stat_{$i}_value"] = $settings["stat_{$i}_number"];
            }
        }

        return $settings;
    }
}
