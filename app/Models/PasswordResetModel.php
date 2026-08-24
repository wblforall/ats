<?php

namespace App\Models;

use CodeIgniter\Model;

class PasswordResetModel extends Model
{
    protected $table         = 'password_resets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['user_id', 'token_hash', 'expires_at', 'used_at'];
    protected $useTimestamps = true;
    protected $updatedField  = '';

    /**
     * Buat token baru untuk satu user. Mengembalikan token ASLI (untuk dikirim
     * di tautan); yang disimpan hanya hash-nya. Token lama user ini dibatalkan.
     */
    public function issue(int $userId, int $ttlMinutes = 60): string
    {
        $this->where('user_id', $userId)->where('used_at', null)->set('used_at', date('Y-m-d H:i:s'))->update();

        $token = bin2hex(random_bytes(32));
        $this->insert([
            'user_id'    => $userId,
            'token_hash' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + $ttlMinutes * 60),
        ]);

        return $token;
    }

    /** Cari token valid (belum dipakai & belum kedaluwarsa); null bila tidak ada. */
    public function findValid(string $token): ?array
    {
        return $this->where('token_hash', hash('sha256', $token))
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
    }

    public function consume(int $id): void
    {
        $this->update($id, ['used_at' => date('Y-m-d H:i:s')]);
    }
}
