<?php

namespace Modules\Inventory\actions;

use Illuminate\Validation\ValidationException;
use Modules\Product\Models\Product;

class HandleStock
{
    /**
     * @throws ValidationException
     */
    public function handle(array $data)
    {
        $product = Product::where('id', $data['product_id'])
            ->lockForUpdate()
            ->first();

        if (!$product) {
            throw ValidationException::withMessages([
                'product_id' => 'Product not found'
            ]);
        }

        if ($product->stock < $data['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock'
            ]);
        }

        $product->stock -= $data['quantity'];
        $product->save();

        return $product;
    }
}
