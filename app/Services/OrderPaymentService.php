<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\PaymentProcessingException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class OrderPaymentService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * Process a payment for an order
     * 
     * @param Order $order The order to process payment for
     * @return Payment The created payment record
     * @throws PaymentProcessingException If order cannot receive payments
     */
    public function processPayment(Order $order): Payment
    {
        if (!$order->canReceivePayment()) {
            throw PaymentProcessingException::orderCannotReceivePayment(
                $order->id, 
                $order->status
            );
        }

        return DB::transaction(function () use ($order) {
            // Llamar a la API externa
            $result = $this->paymentGateway->processPayment(
                (float) $order->total_amount,
                $order->id
            );

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'status' => $result['success'] ? PaymentStatus::SUCCESS : PaymentStatus::FAILED,
                'response' => json_encode($result),
            ]);

            // Update order status based on payment result
            $order->update([
                'status' => $result['success'] ? OrderStatus::PAID : OrderStatus::FAILED
            ]);

            return $payment;
        });
    }
}
