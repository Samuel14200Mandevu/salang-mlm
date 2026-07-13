<?php
// app/Models/MlmSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlmSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function getFloat(string $key, float $default = 0): float
    {
        return (float) self::getValue($key, $default);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        return (int) self::getValue($key, $default);
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        return filter_var(self::getValue($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function getArray(string $key, array $default = []): array
    {
        $value = self::getValue($key);
        if ($value) {
            return json_decode($value, true) ?? $default;
        }
        return $default;
    }

    public function getValueTypedAttribute()
    {
        if (is_numeric($this->value)) {
            return (float) $this->value;
        }
        if ($this->value === 'true' || $this->value === 'false') {
            return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
        }
        if (json_decode($this->value) !== null) {
            return json_decode($this->value, true);
        }
        return $this->value;
    }
}