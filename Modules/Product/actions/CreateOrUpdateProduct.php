<?php

namespace Modules\Product\actions;

use Modules\Product\Models\Product;

class CreateOrUpdateProduct
{
    public function handle(array $data, ?Product $product = null): Product
    {
        $data['vendor_id'] = auth()->user()->vendor->id;
        $result = $product ?? new Product();
        $result->fill($data);
        $result->save();

        return $result->refresh()->load(['vendor']);
    }
}
