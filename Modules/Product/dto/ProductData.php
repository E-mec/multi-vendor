<?php

namespace Modules\Product\dto;

use Modules\Authentication\dto\VendorData;
use Modules\Product\Enums\ProductStatusEnum;
use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $stock_quantity,
        public string $description,
        public float $price,
        public ProductStatusEnum $status,
        public ?VendorData $vendor,
    )
    {}
}
