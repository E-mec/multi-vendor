<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authentication\Models\User;
use Modules\Authentication\Models\Vendor;
use Modules\Product\Enums\ProductStatusEnum;
use Modules\Product\Models\Product;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(TestCase::class, RefreshDatabase::class);

function createVendorWithToken(): array
{
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create([
        'user_id' => $user->id,
        'store_name' => fake()->company(),
    ]);
    $token = JWTAuth::fromUser($user);

    return [$user, $vendor, $token];
}

function productPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test Product',
        'description' => 'A test product description',
        'price' => 100,
        'stock_quantity' => 50,
        'status' => ProductStatusEnum::ACTIVE->value,
    ], $overrides);
}

test('authenticated user without a vendor profile cannot access vendor routes', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('api/vendor/products');

    $response->assertForbidden();
});

test('vendor can list their products', function () {
    [$user, $vendor, $token] = createVendorWithToken();

    Product::factory()->count(3)->create(['vendor_id' => $vendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('api/vendor/products');

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Products',
        ])
        ->assertJsonCount(3, 'data.data');
});

test('vendor only sees their own products', function () {
    [$user, $vendor, $token] = createVendorWithToken();
    [, $otherVendor] = createVendorWithToken();

    Product::factory()->count(2)->create(['vendor_id' => $vendor->id]);
    Product::factory()->count(3)->create(['vendor_id' => $otherVendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('api/vendor/products');

    $response->assertOk()
        ->assertJsonCount(2, 'data.data');
});

test('vendor can search their products by name', function () {
    [$user, $vendor, $token] = createVendorWithToken();

    Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Blue Sneakers']);
    Product::factory()->create(['vendor_id' => $vendor->id, 'name' => 'Red Hoodie']);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson('api/vendor/products?search=Blue');

    $response->assertOk()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.name', 'Blue Sneakers');
});

test('vendor can create a product', function () {
    [$user, $vendor, $token] = createVendorWithToken();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/vendor/products', productPayload());

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Product Created',
        ])
        ->assertJsonStructure([
            'data' => ['id', 'name', 'description', 'price', 'stock_quantity', 'status', 'vendor'],
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'vendor_id' => $vendor->id,
    ]);
});

test('product creation fails with missing required fields', function (string $field) {
    [$user, $vendor, $token] = createVendorWithToken();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/vendor/products', productPayload([$field => '']));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([$field]);

})->with(['name', 'description', 'price', 'stock_quantity', 'status']);

test('product creation fails with negative price', function () {
    [$user, $vendor, $token] = createVendorWithToken();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/vendor/products', productPayload(['price' => -10]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['price']);
});

test('product creation fails with invalid status', function () {
    [$user, $vendor, $token] = createVendorWithToken();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/vendor/products', productPayload(['status' => 'invalid_status']));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});


test('vendor can view their own product', function () {
    [$user, $vendor, $token] = createVendorWithToken();
    $product = Product::factory()->create(['vendor_id' => $vendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson("api/vendor/products/{$product->id}");

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Product',
        ]);
});

test('vendor cannot view another vendor product', function () {
    [$user, $vendor, $token] = createVendorWithToken();
    [, $otherVendor] = createVendorWithToken();

    $otherProduct = Product::factory()->create(['vendor_id' => $otherVendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->getJson("api/vendor/products/{$otherProduct->id}");

    $response->assertForbidden();
});

test('vendor can update their own product', function () {
    [$user, $vendor, $token] = createVendorWithToken();
    $product = Product::factory()->create(['vendor_id' => $vendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->putJson("api/vendor/products/{$product->id}", productPayload(['name' => 'Updated Name']));

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Product Updated',
        ])
        ->assertJsonPath('data.name', 'Updated Name');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
    ]);
});

test('vendor cannot update another vendor product', function () {
    [$user, $vendor, $token] = createVendorWithToken();
    [, $otherVendor] = createVendorWithToken();

    $otherProduct = Product::factory()->create(['vendor_id' => $otherVendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->putJson("api/vendor/products/{$otherProduct->id}", productPayload());

    $response->assertForbidden();
});


test('vendor can delete their own product', function () {
    [$user, $vendor, $token] = createVendorWithToken();
    $product = Product::factory()->create(['vendor_id' => $vendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->deleteJson("api/vendor/products/{$product->id}");

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Product Deleted',
        ]);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

test('vendor cannot delete another vendor product', function () {
    [$user, $vendor, $token] = createVendorWithToken();
    [, $otherVendor] = createVendorWithToken();

    $otherProduct = Product::factory()->create(['vendor_id' => $otherVendor->id]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->deleteJson("api/vendor/products/{$otherProduct->id}");

    $response->assertForbidden();

    $this->assertDatabaseHas('products', ['id' => $otherProduct->id]);
});

test('unauthenticated user cannot access vendor product routes', function (string $method, string $url) {
    $this->{$method}($url)->assertUnauthorized();
})->with([
    ['getJson',    'api/vendor/products'],
    ['postJson',   'api/vendor/products'],
    ['getJson',    'api/vendor/products/1'],
    ['putJson',    'api/vendor/products/1'],
    ['deleteJson', 'api/vendor/products/1'],
]);
