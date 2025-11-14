<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';

    public function canReceivePayment(): bool
    {
        return in_array($this, [self::PENDING, self::FAILED]);
    }

    public function isPaid(): bool
    {
        return $this === self::PAID;
    }
}
