<?php

namespace App\Exceptions;

use Exception;

class PaymentProcessingException extends Exception
{
    public static function orderCannotReceivePayment(int $orderId, \App\Enums\OrderStatus $status): self
    {
        return new self(
            "Order #{$orderId} cannot receive payments. Current status: {$status->value}",
            422
        );
    }

    public static function gatewayError(string $message): self
    {
        return new self("Payment gateway error: {$message}", 500);
    }
}
