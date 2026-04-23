<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\actions\CreateVendorAction;
use Modules\Authentication\dto\VendorData;
use Modules\Authentication\Http\Requests\CreateVendor;

class VendorController extends Controller
{
    public function store(CreateVendor $request, CreateVendorAction $action)
    {
        return DB::transaction(function () use ($request, $action) {
            $result = $action->handle($request->all());

            return successResponse('vendor registered', VendorData::from($result));
        });
    }
}
