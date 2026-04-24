<?php

namespace Modules\Product\actions;

use Illuminate\Support\Facades\Cache;

class RegisterCacheKeyAction
{
    public function execute(string $registryKey, string $cacheKey, int $ttlMinutes = 10): void
    {
        $keys = Cache::get($registryKey, []);
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put($registryKey, $keys, now()->addMinutes($ttlMinutes));
        }
    }
}
