<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Product\actions\CreateOrUpdateProduct;
use Modules\Product\dto\ProductData;
use Modules\Product\Http\Requests\ProductRequest;
use Modules\Product\Models\Product;

class VendorProductController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $products = Product::with('vendor')
            ->whereVendorId($user->vendor->id)
            ->search($request->input('search'))
            ->paginate();

        return successResponse('Products', ProductData::collect($products));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request, CreateOrUpdateProduct $action): JsonResponse
    {
        $result = $action->handle($request->validated());
        return successResponse('Product Created', ProductData::from($result));
    }



    public function show(Product $product): JsonResponse
    {
        if (!Gate::allows('update', $product)) {
            return failureResponse('Unauthorized', 403);
        }
        return successResponse('Product', ProductData::from($product));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product, CreateOrUpdateProduct $action): JsonResponse
    {
        if (!Gate::allows('update', $product)) {
            return failureResponse('Unauthorized', 403);
        }
        $result = $action->handle($request->all(), $product);
        return successResponse('Product Updated', ProductData::from($result));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        if (!Gate::allows('update', $product)) {
            return failureResponse('Unauthorized', 403);
        }
        $product->delete();
        return successResponse('Product Deleted');

    }
}
