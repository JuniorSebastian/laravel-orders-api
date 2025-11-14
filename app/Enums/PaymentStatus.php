<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';

    public function isSuccessful(): bool
    {
        return $this === self::SUCCESS;
    }
}
