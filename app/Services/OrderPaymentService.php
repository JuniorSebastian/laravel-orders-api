<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function __construct(
        private PaymentGatewayService $paymentGateway
    ) {}

    /**
     * Procesa un pago para un pedido
     * 
     * @param Order $order
     * @return Payment
     * @throws \Exception
     */
    public function processPayment(Order $order): Payment
    {
        if (!$order->canReceivePayment()) {
            throw new \Exception("Order #{$order->id} cannot receive payments. Current status: {$order->status}");
        }

        return DB::transaction(function () use ($order) {
            // Llamar a la API externa
            $result = $this->paymentGateway->processPayment(
                (float) $order->total_amount,
                $order->id
            );

            // Crear el registro de pago
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => $result['success'] ? 'success' : 'failed',
                'response' => json_encode($result),
            ]);

            // Actualizar el estado del pedido
            if ($result['success']) {
                $order->update(['status' => 'paid']);
            } else {
                $order->update(['status' => 'failed']);
            }

            return $payment;
        });
    }
}
