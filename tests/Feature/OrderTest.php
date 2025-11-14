<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order(): void
    {
        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Juan Perez',
            'total_amount' => 100.50,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_name',
                    'total_amount',
                    'status',
                    'payment_attempts',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'data' => [
                    'customer_name' => 'Juan Perez',
                    'total_amount' => '100.50',
                    'status' => 'pending',
                    'payment_attempts' => 0,
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Juan Perez',
            'total_amount' => 100.50,
            'status' => 'pending',
        ]);
    }

    public function test_can_list_orders(): void
    {
        Order::factory()->count(3)->create();

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_order_with_payments(): void
    {
        $order = Order::factory()->create();

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'customer_name',
                    'total_amount',
                    'status',
                    'payment_attempts',
                    'payments',
                ]
            ]);
    }

    public function test_order_validation_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name', 'total_amount']);
    }
}
