<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
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
            // Simulación: POST a reqres.in siempre retorna 201
            // Usaremos el endpoint /users que acepta cualquier dato
            $response = Http::timeout(10)
                ->withHeaders(['x-api-key' => $this->apiKey])
                ->post("{$this->apiUrl}/users", [
                    'amount' => $amount,
                    'order_id' => $orderId,
                    'currency' => 'USD',
                ]);

            // Simulamos éxito si el status es 201
            $success = $response->successful() && $response->status() === 201;

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
