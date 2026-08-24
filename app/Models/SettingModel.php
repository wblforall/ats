<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table         = 'settings';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['key', 'value'];
    protected $useTimestamps = true;

    private static ?array $cache = null;

    /** Semua pengaturan sebagai array key => value, dibaca sekali per request. */
    public function all(): array
    {
        if (self::$cache === null) {
            self::$cache = array_column($this->findAll(), 'value', 'key');
        }

        return self::$cache;
    }

    /* Dinamai value()/put(), bukan get()/set(), karena kedua nama itu sudah
       dipakai CodeIgniter\Model dengan tanda tangan berbeda. */
    public function value(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    public function put(string $key, ?string $value): void
    {
        $existing = $this->where('key', $key)->first();

        if ($existing) {
            $this->update($existing['id'], ['value' => $value]);
        } else {
            $this->insert(['key' => $key, 'value' => $value]);
        }

        self::$cache = null;
    }

    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->put($key, $value === null ? null : (string) $value);
        }
    }
}
