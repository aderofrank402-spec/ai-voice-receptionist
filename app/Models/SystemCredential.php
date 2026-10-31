<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemCredential extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'value',
        'provider',
        'is_active',
        'is_valid',
        'last_tested_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'encrypted',
        'is_active' => 'boolean',
        'is_valid' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    /**
     * Internal request-level cache to prevent multiple DB hits for the same key.
     */
    protected static $staticCache = [];

    /**
     * Get a credential value by name, with fallback to env()
     */
    public static function getValue($name, $default = null)
    {
        // 1. Check static cache for current request
        if (array_key_exists($name, self::$staticCache)) {
            return self::$staticCache[$name];
        }

        // 2. Fetch from database
        $credential = self::where('name', $name)
            ->where('is_active', true)
            ->first();

        if ($credential && $credential->value !== 'placeholder') {
            $value = $credential->value;
        } else {
            // 3. Fallback to env
            $value = env(strtoupper($name), $default);
        }

        // 4. Store in static cache and return
        return self::$staticCache[$name] = $value;
    }

    /**
     * Get all credentials for a provider
     */
    public static function forProvider($provider)
    {
        return self::where('provider', $provider)->get();
    }

    /**
     * Get all active credentials in a flat key => value array
     */
    public static function allActive()
    {
        return self::where('is_active', true)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->name => $item->value];
            })
            ->toArray();
    }
}
