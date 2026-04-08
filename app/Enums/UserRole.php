<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel
{
    case ADMIN = 'admin';
    case TECHNICIAN = 'technician';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
