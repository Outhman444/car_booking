<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'display_name', 'group', 'type', 'sort_order'];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Get multiple settings by group.
     */
    public static function getByGroup(string $group)
    {
        return \Illuminate\Support\Facades\Cache::remember("settings_group_{$group}", 3600, function () use ($group) {
            return self::where('group', $group)->orderBy('sort_order')->get();
        });
    }

    /**
     * Clear all settings cache.
     */
    public static function clearCache()
    {
        \Illuminate\Support\Facades\Cache::flush();
    }
}
