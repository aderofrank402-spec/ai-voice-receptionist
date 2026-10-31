<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Internal request-level cache to prevent multiple DB hits for the same key.
     */
    protected static $staticCache = [];

    /**
     * Get a setting by key
     */
    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$staticCache)) {
            return self::$staticCache[$key];
        }

        $setting = self::where('key', $key)->first();
        return self::$staticCache[$key] = $setting ? $setting->value : $default;
    }

    /**
     * Set a setting by key
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }
}
