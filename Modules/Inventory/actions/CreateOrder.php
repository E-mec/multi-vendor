<?php

namespace Modules\Inventory\actions;

use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\Order;
use Modules\Product\actions\FlushCacheAction;

class CreateOrder
{
    public function handle(array $data)
    {
        return DB::transaction(function () use ($data) {

            $product = app(HandleStock::class)->handle($data);

            $price = $product->price * $data['quantity'];

            $order = Order::create([
                'product_id' => $data['product_id'],
                'user_id' => auth('api')->id() ?? null,
                'quantity' => $data['quantity'],
                'total_price' => $price,
            ]);

            app(FlushCacheAction::class)->execute($product->id);

            return $order->load(['product.vendor']);
        });
    }
}
