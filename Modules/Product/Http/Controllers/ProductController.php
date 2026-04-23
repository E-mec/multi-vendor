<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\dto\ProductData;
use Modules\Product\Enums\ProductStatusEnum;
use Modules\Product\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::with('vendor')
            ->search($request->input('search'))
            ->active()
            ->paginate();

        return successResponse('Products', ProductData::collect($products));
    }

    public function show(Product $product): JsonResponse
    {
        if ($product->status !== ProductStatusEnum::ACTIVE) {
            return failureResponse('Product not available', 404);
        }

        return successResponse(
            'Product',
            ProductData::from($product->load('vendor'))
        );
    }
}
