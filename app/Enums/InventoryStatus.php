<?php

namespace App\Enums;

enum InventoryStatus: string
{
    case IN_STOCK = 'in_stock';
    case RESERVED = 'reserved';
    case SOLD = 'sold';
    case IN_SERVICE = 'in_service';
    case TRADED_IN = 'traded_in';
    case ARCHIVED = 'archived';
}
