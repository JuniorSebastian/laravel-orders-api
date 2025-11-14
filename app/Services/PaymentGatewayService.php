<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService implements PaymentGatewayInterface
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = env('PAYMENT_GATEWAY_URL', 'https://reqres.in/api');
        $this->apiKey = env('PAYMENT_GATEWAY_API_KEY', 'reqres-free-v1');
    }

    /**
     * Procesa un pago a través de la API externa
     * 
     * @param float $amount
     * @param int $orderId
     * @return array
     */
    public function processPayment(float $amount, int $orderId): array
    {
        try {
            // Simulación: POST a reqres.in retorna 201 con API key
            // Usaremos el endpoint /users que acepta cualquier dato
            $response = Http::timeout(10)
                ->withoutVerifying() // Deshabilitar verificación SSL en desarrollo
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->apiUrl}/users", [
                    'name' => "Payment Order {$orderId}",
                    'job' => 'payment',
                    'amount' => $amount,
                    'order_id' => $orderId,
                    'currency' => 'USD',
                ]);

            // Simulamos éxito si el status es 201 y tiene respuesta válida
            $success = $response->successful() && 
                       $response->status() === 201 && 
                       $response->json('id') !== null;

            $result = [
                'success' => $success,
                'transaction_id' => $response->json('id'),
                'response_data' => $response->json(),
                'status_code' => $response->status(),
            ];

            Log::info('Payment processed', [
                'order_id' => $orderId,
                'amount' => $amount,
                'success' => $success,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'response_data' => null,
            ];
        }
    }
}
