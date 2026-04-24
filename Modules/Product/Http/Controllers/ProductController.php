<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Product\actions\RegisterCacheKeyAction;
use Modules\Product\dto\ProductData;
use Modules\Product\Enums\ProductStatusEnum;
use Modules\Product\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {

        $cacheKey = sprintf(
            'products:search:%s:page:%s',
            $request->input('search', 'none'),
            $request->input('page', 1)
        );

        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request) {
            return Product::with('vendor')
                ->search($request->input('search'))
                ->active()
                ->paginate();
        });

        // Register key in global products registry
        app(RegisterCacheKeyAction::class)->execute('products:cache_keys', $cacheKey);

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
