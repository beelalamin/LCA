<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case ADMIN = 'ADMIN';
    case TECHNICIAN = 'TECHNICIAN';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
