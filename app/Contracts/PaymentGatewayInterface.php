<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Process a payment through the external gateway
     *
     * @param float $amount The amount to charge
     * @param int $orderId The order identifier
     * @return array Payment result with success status and details
     */
    public function processPayment(float $amount, int $orderId): array;
}
