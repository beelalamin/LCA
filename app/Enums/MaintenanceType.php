<?php

namespace App\Enums;

enum MaintenanceType: string
{
    case REPAIR = 'REPAIR';
    case UPGRADE = 'UPGRADE';
    case INSPECTION = 'INSPECTION';
    case WARRANTY_CLAIM = 'WARRANTY_CLAIM';
}
