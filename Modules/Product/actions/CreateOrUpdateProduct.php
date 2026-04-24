<?php

namespace Modules\Product\actions;

use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\Product;

class CreateOrUpdateProduct
{
    public function handle(array $data, ?Product $product = null): Product
    {
        $data['vendor_id'] = auth('api')->user()->vendor->id;
        $result = $product ?? new Product();
        $result->fill($data);
        $result->save();

        app(FlushCacheAction::class)->execute($result->id);

        return $result->refresh()->load(['vendor']);
    }
}
