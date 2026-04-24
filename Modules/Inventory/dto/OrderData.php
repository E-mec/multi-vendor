<?php

namespace Modules\Inventory\dto;

use Modules\Product\dto\ProductData;
use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        public int $id,
        public ProductData $product,
        public int $quantity,
        public float $total_price,
    )
    {}
}
