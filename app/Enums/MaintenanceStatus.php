<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaintenanceStatus: string implements HasLabel
{
    case OPEN = 'OPEN';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
