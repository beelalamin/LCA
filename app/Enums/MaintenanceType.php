<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaintenanceType: string implements HasLabel
{
    case REPAIR = 'REPAIR';
    case UPGRADE = 'UPGRADE';
    case INSPECTION = 'INSPECTION';
    case WARRANTY_CLAIM = 'WARRANTY_CLAIM';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
