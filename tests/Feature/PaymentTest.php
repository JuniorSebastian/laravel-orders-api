<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_payment_updates_order_to_paid(): void
    {
        // Mock de la API externa
        Http::fake([
            'https://reqres.in/api/users' => Http::response([
                'id' => '123',
                'createdAt' => now()->toISOString(),
            ], 201),
        ]);

        $order = Order::factory()->create([
            'status' => 'pending',
            'total_amount' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_id',
                    'amount',
                    'status',
                    'created_at',
                ]
            ])
            ->assertJson([
                'data' => [
                    'status' => 'success',
                ]
            ]);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals(1, $order->payments->count());
    }

    public function test_failed_payment_updates_order_to_failed(): void
    {
        // Mock de API externa que falla completamente (excepción de red)
        Http::fake(function () {
            throw new \Exception('Connection timeout');
        });

        $order = Order::factory()->create([
            'status' => 'pending',
            'total_amount' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(201);

        $order->refresh();
        $this->assertEquals('failed', $order->status);
        $this->assertEquals(1, $order->payments()->count());
        $this->assertEquals('failed', $order->payments()->first()->status);
    }

    public function test_failed_order_can_receive_new_payment_attempt(): void
    {
        Http::fake([
            'https://reqres.in/api/users' => Http::response([
                'id' => '123',
                'createdAt' => now()->toISOString(),
            ], 201),
        ]);

        $order = Order::factory()->create([
            'status' => 'failed',
            'total_amount' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(201);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    public function test_paid_order_cannot_receive_new_payment(): void
    {
        $order = Order::factory()->create([
            'status' => 'paid',
            'total_amount' => 100.00,
        ]);

        $response = $this->postJson('/api/payments', [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Payment processing failed',
            ]);
    }

    public function test_payment_validation_fails_without_order_id(): void
    {
        $response = $this->postJson('/api/payments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }
}
