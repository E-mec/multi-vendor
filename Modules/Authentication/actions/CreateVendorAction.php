<?php

namespace Modules\Authentication\actions;

use Modules\Authentication\Models\Vendor;

class CreateVendorAction
{
    public function handle(array $data)
    {
       $vendor = Vendor::create([
            'user_id' => auth('api')->id(),
            'store_name' => $data['store_name'],
        ]);

       return $vendor->load(['user']);
    }
}
