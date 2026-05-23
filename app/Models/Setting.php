<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    public const LABEL_DEFAULTS = [
        'label_width' => 140,
        'label_height' => 75,
        'label_padding' => 10,
        'columns_per_page' => 4,
        'print_type' => 'qrcode',
    ];

    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        return $setting->value;
    }

    public static function set(string $key, $value, string $type = 'string')
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
    }

    public static function labelDefault(string $key)
    {
        return self::LABEL_DEFAULTS[$key] ?? null;
    }
}
