<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authentication\Models\User;
use Modules\Authentication\Models\Vendor;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(TestCase::class, RefreshDatabase::class);

test('authenticated user can register as a vendor', function () {

    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/vendor', [
            'store_name' => 'MyStore',
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id',
                'store_name',
                'user',
            ],
        ]);

    $this->assertDatabaseHas('vendors', [
        'user_id' => $user->id,
        'store_name' => 'MyStore',
    ]);
});

test('vendor registration fails if store_name is missing', function () {

    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/vendor', [
            'store_name' => '',
        ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['store_name']);
});

test('unauthenticated user cannot register as a vendor', function () {

    $response = $this->postJson('api/vendor', [
        'store_name' => 'MyStore',
    ]);

    $response->assertUnauthorized();
});

test('vendor is associated with the authenticated user', function () {

    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/vendor', [
            'store_name' => 'MyStore',
        ]);

    $vendor = Vendor::where('store_name', 'MyStore')->first();

    expect($vendor)->not->toBeNull()
        ->and($vendor->user_id)->toBe($user->id);
});
