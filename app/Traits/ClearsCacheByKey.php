<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Store-agnostic cache clearing.
 *
 * The previous pattern used Cache::getRedis()->keys(), which fatals on
 * array/database/file cache stores (getRedis() returns null). Forget the
 * known keys directly instead — works on every store.
 */
trait ClearsCacheByKey
{
    protected function forgetCacheKeys(array $keys): void
    {
        foreach ($keys as $key) {
            if (is_string($key) && $key !== '') {
                Cache::forget($key);
            }
        }
    }
}
