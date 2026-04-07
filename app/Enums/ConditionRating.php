<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConditionRating: string implements HasLabel
{
    case NEW = 'NEW';
    case GOOD = 'GOOD';
    case FAIR = 'FAIR';
    case POOR = 'POOR';
    case BROKEN = 'BROKEN';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
