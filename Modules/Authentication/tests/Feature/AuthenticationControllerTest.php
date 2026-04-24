<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Authentication\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(TestCase::class, RefreshDatabase::class);

test('user can register', function () {

    $payload = [
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'name' => 'John Doe',
    ];

    $response = $this->postJson('/api/auth/register', $payload);

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Registration successful',
        ])
        ->assertJsonStructure([
            'message',
            'data' => [
                'token',
                'user' => [
                    'name',
                    'email',
                ]
            ]
        ]);

    $this->assertDatabaseCount('users', 1);

});

test('user can login', function () {

    User::factory()->create([
        'email' => 'test@example.com'
    ]);

    $payload = [
        'email' => 'test@example.com',
        'password' => 'password',
    ];

    $response = $this->postJson('/api/auth/login', $payload);

    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Logged in successfully.',
        ])
        ->assertJsonStructure([
            'message',
            'data' => [
                'token',
                'user' => [
                    'name',
                    'email',
                ]
            ]
        ]);

});

test('user can logout', function () {

    $user = User::factory()->create([
        'email' => 'test@example.com'
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->postJson('api/auth/logout');


    $response->assertOk()
        ->assertJson([
            'status' => 'ok',
            'message' => 'Logged out successfully.',
        ]);

});
