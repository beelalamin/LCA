<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasUuids, HasTranslations;

    protected $guarded = [];

    public $translatable = ['department'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'employee_id');
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'employee_id')->where('is_active', true);
    }
}
