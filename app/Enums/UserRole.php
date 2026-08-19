<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case OWNER = 'owner';
    case MANAGER = 'manager';
    case SELLER = 'seller';
    case TECHNICIAN = 'technician';
}
