<?php

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function authenticate(User $user): string {
    return app('tymon.jwt.auth')->fromUser($user);
}

test('usuário autenticado pode criar pedido', function () {
    $user = User::factory()->create([
        'role' => 'user',
    ]);

    $token = authenticate($user);

    $payload = [
        'destination' => 'São Paulo',
        'departure_date' => now()->addDays(5)->format('Y-m-d'),
        'return_date' => now()->addDays(7)->format('Y-m-d'),
    ];

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/travel-orders', $payload);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'destination' => 'São Paulo',
            'status' => 'solicitado',
        ]);

    $this->assertDatabaseHas('travel_orders', [
        'user_id' => $user->id,
        'destination' => 'São Paulo',
        'status' => 'solicitado',
    ]);
});

test('usuário comum vê apenas seus pedidos', function () {
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);

    $ownOrder = TravelOrder::create([
        'user_id' => $user->id,
        'destination' => 'São Paulo',
        'departure_date' => now()->addDays(5)->format('Y-m-d'),
        'return_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => 'solicitado',
    ]);

    $otherOrder = TravelOrder::create([
        'user_id' => $otherUser->id,
        'destination' => 'Rio de Janeiro',
        'departure_date' => now()->addDays(10)->format('Y-m-d'),
        'return_date' => now()->addDays(12)->format('Y-m-d'),
        'status' => 'solicitado',
    ]);

    $token = authenticate($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/travel-orders');

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $ownOrder->id])
        ->assertJsonMissing(['id' => $otherOrder->id]);
});

test('admin vê todos os pedidos', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user1 = User::factory()->create(['role' => 'user']);
    $user2 = User::factory()->create(['role' => 'user']);

    $order1 = TravelOrder::create([
        'user_id' => $user1->id,
        'destination' => 'Salvador',
        'departure_date' => now()->addDays(3)->format('Y-m-d'),
        'return_date' => now()->addDays(6)->format('Y-m-d'),
        'status' => 'solicitado',
    ]);

    $order2 = TravelOrder::create([
        'user_id' => $user2->id,
        'destination' => 'Brasília',
        'departure_date' => now()->addDays(8)->format('Y-m-d'),
        'return_date' => now()->addDays(10)->format('Y-m-d'),
        'status' => 'solicitado',
    ]);

    $token = authenticate($admin);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/travel-orders');

    $response->assertStatus(200)
        ->assertJsonFragment(['id' => $order1->id])
        ->assertJsonFragment(['id' => $order2->id]);
});

test('usuário comum não pode alterar status', function () {
    $user = User::factory()->create(['role' => 'user']);
    $owner = User::factory()->create(['role' => 'user']);

    $order = TravelOrder::create([
        'user_id' => $owner->id,
        'destination' => 'Recife',
        'departure_date' => now()->addDays(4)->format('Y-m-d'),
        'return_date' => now()->addDays(6)->format('Y-m-d'),
        'status' => 'solicitado',
    ]);

    $token = authenticate($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->patchJson("/api/travel-orders/{$order->id}/status", [
            'status' => 'aprovado',
        ]);

    $response->assertStatus(403);
});

test('admin pode aprovar pedido', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'user']);

    $order = TravelOrder::create([
        'user_id' => $owner->id,
        'destination' => 'Curitiba',
        'departure_date' => now()->addDays(4)->format('Y-m-d'),
        'return_date' => now()->addDays(8)->format('Y-m-d'),
        'status' => 'solicitado',
    ]);

    $token = authenticate($admin);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->patchJson("/api/travel-orders/{$order->id}/status", [
            'status' => 'aprovado',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('travel_orders', [
        'id' => $order->id,
        'status' => 'aprovado',
    ]);
});

test('pedido aprovado não pode ser cancelado', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create(['role' => 'user']);

    $order = TravelOrder::create([
        'user_id' => $owner->id,
        'destination' => 'Florianópolis',
        'departure_date' => now()->addDays(4)->format('Y-m-d'),
        'return_date' => now()->addDays(8)->format('Y-m-d'),
        'status' => 'aprovado',
    ]);

    $token = authenticate($admin);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->patchJson("/api/travel-orders/{$order->id}/status", [
            'status' => 'cancelado',
        ]);

    $response->assertStatus(422);
});