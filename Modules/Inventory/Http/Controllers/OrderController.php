<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\actions\CreateOrder;
use Modules\Inventory\actions\HandleStock;
use Modules\Inventory\Http\Requests\OrderRequest;

class OrderController extends Controller
{

    public function store(OrderRequest $request, CreateOrder $action): JsonResponse
    {
        $order = $action->handle($request->validated());

        return successResponse('Order placed', $order);
    }
}
