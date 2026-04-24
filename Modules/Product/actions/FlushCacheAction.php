<?php

namespace Modules\Product\actions;

use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\Product;

class FlushCacheAction
{
    public function execute(?int $productId = null): void
    {
        if (auth('api')->check() && auth('api')->user()->vendor) {
            $vendorId = auth('api')->user()->vendor->id;
            $this->flushByRegistry('vendor:' . $vendorId . ':cache_keys');
        }

        // Flush global products cache
        $this->flushByRegistry('products:cache_keys');

        if ($productId) {
            Cache::forget('product:' . $productId);
        }
    }

    private function flushByRegistry(string $registryKey): void
    {
        $keys = Cache::get($registryKey, []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget($registryKey);
    }
}
