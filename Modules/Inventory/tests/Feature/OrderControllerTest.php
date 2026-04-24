<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authentication\Models\User;
use Modules\Authentication\Models\Vendor;
use Modules\Product\Enums\ProductStatusEnum;
use Modules\Product\Models\Product;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(TestCase::class, RefreshDatabase::class);

function createProductWithStock(int $stock = 100, array $overrides = []): Collection|Model
{
    $user = User::factory()->create();
    $vendor = Vendor::factory()->create([
        'user_id' => $user->id,
        'store_name' => fake()->company(),
    ]);

    return Product::factory()->create(array_merge([
        'vendor_id' => $vendor->id,
        'status' => ProductStatusEnum::ACTIVE,
        'stock_quantity' => $stock,
        'price' => 40,
    ], $overrides));
}

test('authenticated user can place an order', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock(100);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Order placed',
        ])
        ->assertJsonStructure([
            'data' => [
                'id',
                'quantity',
                'total_price',
                'product' => [
                    'id',
                    'name',
                    'stock_quantity',
                    'vendor',
                ],
            ],
        ]);
});

test('order is saved to the database', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

//    dd($response->json());

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Order placed',
        ]);

    $this->assertDatabaseHas('orders', [
        'product_id' => $product->id,
        'user_id' => $user->id,
        'quantity' => 3,
        'total_price' => $product->price * 3,
    ]);
});

test('stock is decremented after order is placed', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock(100);

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock_quantity' => 90,
    ]);
});

test('total price is correctly calculated', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock(100, ['price' => 40]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.total_price', 200);
});


test('guest can place an order', function () {
    $product = createProductWithStock(100);

    $response = $this->postJson('api/order', [
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Order placed',
        ]);

    $this->assertDatabaseHas('orders', [
        'product_id' => $product->id,
        'user_id' => null,
    ]);
});

test('order fails when quantity exceeds available stock', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock(5);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

test('stock is not decremented when order fails', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock(5);

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'stock_quantity' => 5,
    ]);
});

test('order fails for inactive product', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock(100, ['status' => ProductStatusEnum::INACTIVE]);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id']);
});

test('order fails for non existent product', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => 999999,
            'quantity' => 1,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id']);
});

test('order fails when product_id is missing', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'quantity' => 1,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['product_id']);
});

test('order fails when quantity is missing', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

test('order fails when quantity is less than 1', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

test('order fails when quantity is not an integer', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = createProductWithStock();

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/order', [
            'product_id' => $product->id,
            'quantity' => 1.5,
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['quantity']);
});

test('stock does not go negative under concurrent orders', function () {
    $product = createProductWithStock(5);

    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        $token = JWTAuth::fromUser($user);
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('api/order', [
                'product_id' => $product->id,
                'quantity' => 3,
            ]);
    }

    $remainingStock = $product->fresh()->stock_quantity;

    expect($remainingStock)->toBeGreaterThanOrEqual(0);
});
