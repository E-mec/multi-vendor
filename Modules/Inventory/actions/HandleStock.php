<?php

namespace Modules\Inventory\actions;

use Illuminate\Validation\ValidationException;
use Modules\Product\Enums\ProductStatusEnum;
use Modules\Product\Models\Product;

class HandleStock
{
    /**
     * @throws ValidationException
     */
    public function handle(array $data)
    {
        $product = Product::where('id', $data['product_id'])
            ->whereStatus(ProductStatusEnum::ACTIVE)
            ->lockForUpdate()
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => 'Product not found'
            ]);
        }

        if ($product->stock_quantity < $data['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock'
            ]);
        }

        $product->stock_quantity -= $data['quantity'];
        $product->save();

        return $product->refresh();
    }
}
