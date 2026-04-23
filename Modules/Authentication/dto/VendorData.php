<?php

namespace Modules\Authentication\dto;

use Spatie\LaravelData\Data;

class VendorData extends Data
{
    public function __construct(
        public int $id,
        public string $store_name,
        public ?UserData $user
    )
    {}
}
