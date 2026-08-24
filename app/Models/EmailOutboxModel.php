<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailOutboxModel extends Model
{
    protected $table         = 'email_outbox';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'application_id', 'template_code', 'to_email', 'to_name', 'subject',
        'body', 'status', 'attempts', 'last_error', 'created_by', 'sent_at',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'to_email' => 'required|valid_email',
        'subject'  => 'required|max_length[255]',
        'body'     => 'required',
    ];

    /** Masukkan ke antrean; pengiriman sebenarnya ditangani EmailService. */
    public function queue(array $data): int
    {
        $data['status']   = 'queued';
        $data['attempts'] = 0;

        return (int) $this->insert($data, true);
    }

    /** Antrean yang masih boleh dicoba — gagal 3x dianggap perlu campur tangan HR. */
    public function pending(int $limit = 25): array
    {
        return $this->groupStart()
                ->where('status', 'queued')
                ->orGroupStart()->where('status', 'failed')->where('attempts <', 3)->groupEnd()
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->findAll($limit);
    }

    public function markSent(int $id): void
    {
        $this->update($id, [
            'status'     => 'sent',
            'sent_at'    => date('Y-m-d H:i:s'),
            'last_error' => null,
        ]);
    }

    public function markFailed(int $id, string $error, int $attempts): void
    {
        $this->update($id, [
            'status'     => 'failed',
            'attempts'   => $attempts + 1,
            'last_error' => mb_substr($error, 0, 500),
        ]);
    }

    public function forApplication(int $applicationId): array
    {
        return $this->select('email_outbox.*, users.name AS sender_name')
            ->join('users', 'users.id = email_outbox.created_by', 'left')
            ->where('application_id', $applicationId)
            ->orderBy('email_outbox.id', 'DESC')
            ->findAll();
    }
}
