<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AssetStatus: string implements HasLabel
{
    case PURCHASED = 'PURCHASED';
    case AVAILABLE = 'AVAILABLE';
    case ASSIGNED = 'ASSIGNED';
    case IN_REPAIR = 'IN_REPAIR';
    case RETIRED = 'RETIRED';
    case DISPOSED = 'DISPOSED';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
