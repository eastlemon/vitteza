<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Параметры системы (ТЗ §15.4, §16.2). Простой key→value.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];
    public $timestamps = true;

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::find($key);
        return $row ? $row->value : $default;
    }

    public static function getFloat(string $key, float $default): float
    {
        $v = static::get($key);
        return $v === null ? $default : (float) $v;
    }

    public static function getString(string $key, string $default): string
    {
        $v = static::get($key);
        return $v === null ? $default : (string) $v;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : (string) $value],
        );
    }
}
