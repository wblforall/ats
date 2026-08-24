<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationNoteModel extends Model
{
    protected $table         = 'application_notes';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['application_id', 'user_id', 'note'];
    protected $useTimestamps = true;

    protected $validationRules    = ['note' => 'required'];
    protected $validationMessages = ['note' => ['required' => 'Catatan tidak boleh kosong.']];

    public function forApplication(int $applicationId): array
    {
        return $this->select('application_notes.*, users.name AS user_name')
            ->join('users', 'users.id = application_notes.user_id', 'left')
            ->where('application_id', $applicationId)
            ->orderBy('application_notes.created_at', 'DESC')
            ->findAll();
    }
}
