<?php

namespace Modules\Product\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsVendor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !$user->vendor) {
            return failureResponse('You are not a vendor.', 403);
        }
        return $next($request);
    }
}
