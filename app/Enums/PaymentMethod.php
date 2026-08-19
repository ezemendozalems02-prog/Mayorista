<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case TRANSFER = 'transfer';
    case CARD = 'card';
    case CRYPTO = 'crypto';
    case OTHER = 'other';
}
