<?php

namespace App\Enums;

enum AssetStatus: string
{
    case PURCHASED = 'PURCHASED';
    case AVAILABLE = 'AVAILABLE';
    case ASSIGNED = 'ASSIGNED';
    case IN_REPAIR = 'IN_REPAIR';
    case RETIRED = 'RETIRED';
    case DISPOSED = 'DISPOSED';
}
