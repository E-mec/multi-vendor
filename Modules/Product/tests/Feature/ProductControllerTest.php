<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Authentication\Models\User;
use Modules\Authentication\Models\Vendor;
use Modules\Product\Enums\ProductStatusEnum;
use Modules\Product\Models\Product;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);


function createVendorWithProducts(int $count = 1, array $productOverrides = []): Vendor
{
    $user = User::factory()->create();
    $vendor = Vendor::create([
        'user_id' => $user->id,
        'store_name' => fake()->company(),
    ]);

    Product::factory()->count($count)->create(array_merge(
        ['vendor_id' => $vendor->id, 'status' => ProductStatusEnum::ACTIVE],
        $productOverrides
    ));

    return $vendor;
}

test('guest can list all active products', function () {
    createVendorWithProducts(3);

    $response = $this->getJson('api/products');

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Products',
        ])
        ->assertJsonCount(3, 'data.data');
});

test('inactive products are not returned in public listing', function () {
    createVendorWithProducts(2); // active
    createVendorWithProducts(2, ['status' => ProductStatusEnum::INACTIVE]); // inactive

    $response = $this->getJson('api/products');

    $response->assertOk()
        ->assertJsonCount(2, 'data.data');
});

test('public product listing returns correct structure', function () {
    createVendorWithProducts(1);

    $response = $this->getJson('api/products');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock_quantity',
                        'status',
                        'vendor',
                    ],
                ],
                'current_page',
                'total',
                'per_page',
            ],
        ]);
});

test('guest can search active products by name', function () {
    $vendor = createVendorWithProducts();
    Product::factory()->create([
        'vendor_id' => $vendor->id,
        'name' => 'Blue Sneakers',
        'status' => ProductStatusEnum::ACTIVE,
    ]);
    Product::factory()->create([
        'vendor_id' => $vendor->id,
        'name' => 'Red Hoodie',
        'status' => ProductStatusEnum::ACTIVE,
    ]);

    $response = $this->getJson('api/products?search=Blue');

    $response->assertOk()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.name', 'Blue Sneakers');
});

test('search does not return inactive products', function () {
    $vendor = createVendorWithProducts();
    Product::factory()->create([
        'vendor_id' => $vendor->id,
        'name' => 'Blue Sneakers',
        'status' => ProductStatusEnum::INACTIVE,
    ]);

    $response = $this->getJson('api/products?search=Blue');

    $response->assertOk()
        ->assertJsonCount(0, 'data.data');
});

test('guest can view an active product', function () {
    $vendor = createVendorWithProducts();
    $product = $vendor->products()->first();

    $response = $this->getJson("api/products/{$product->id}");

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Product',
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'price',
                'stock_quantity',
                'status',
                'vendor',
            ],
        ]);
});

test('inactive product returns 404 on public show', function () {
    $vendor = createVendorWithProducts(1, ['status' => ProductStatusEnum::INACTIVE]);
    $product = $vendor->products()->first();

    $response = $this->getJson("api/products/{$product->id}");

    $response->assertNotFound()
        ->assertJson([
            'status' => 'failure',
            'message' => 'Product not available',
        ]);
});

test('non existent product returns 404', function () {
    $response = $this->getJson('api/products/999999');

    $response->assertNotFound();
});

test('public product listing is cached', function () {
    createVendorWithProducts(3);

    $this->getJson('api/products')->assertOk();

    expect(Cache::has('products:search:none:page:1'))->toBeTrue();
});

test('public product search result is cached with correct key', function () {
    createVendorWithProducts(3);

    $this->getJson('api/products?search=blue&page=2')->assertOk();

    expect(Cache::has('products:search:blue:page:2'))->toBeTrue();
});

test('public product listing cache is separate per page', function () {
    createVendorWithProducts(3);

    $this->getJson('api/products?page=1')->assertOk();
    $this->getJson('api/products?page=2')->assertOk();

    expect(Cache::has('products:search:none:page:1'))->toBeTrue();
    expect(Cache::has('products:search:none:page:2'))->toBeTrue();
});
