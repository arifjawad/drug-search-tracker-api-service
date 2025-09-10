<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class Helper
{

    /**
     * Put a value in the cache
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return mixed
     */
    public static function cachePut($key, $value, $ttl)
    {
         return Cache::put($key, $value, $ttl);
    }

    /**
     * Get a value from the cache
     * @param string $key
     * @return mixed
     */
    public static function cacheGet($key)
    {
        if(Cache::has($key)) {
            return Cache::get($key);
        }
        return null;
    }

    /**
     * Delete a value from the cache
     * @param string $key
     * @return mixed
     */
    public static function cacheDelete($key)
    {
        if(Cache::has($key)) {
            return Cache::forget($key);
        }
        return null;
    }
}
