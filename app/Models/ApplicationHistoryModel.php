<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationHistoryModel extends Model
{
    protected $table         = 'application_history';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['application_id', 'from_stage_id', 'to_stage_id', 'user_id', 'note'];
    protected $useTimestamps = true;
    protected $updatedField  = '';

    /** Linimasa satu lamaran, terbaru di atas. */
    public function timeline(int $applicationId): array
    {
        return $this->select('application_history.*,
                sf.name AS from_stage, st.name AS to_stage, st.color AS to_color,
                users.name AS user_name')
            ->join('stages sf', 'sf.id = application_history.from_stage_id', 'left')
            ->join('stages st', 'st.id = application_history.to_stage_id')
            ->join('users', 'users.id = application_history.user_id', 'left')
            ->where('application_id', $applicationId)
            ->orderBy('application_history.created_at', 'DESC')
            ->orderBy('application_history.id', 'DESC')
            ->findAll();
    }

    /** Rata-rata hari yang dihabiskan di tiap tahap — bahan laporan efisiensi. */
    public function avgDaysPerStage(?int $unitId = null): array
    {
        $sql = 'SELECT s.name AS stage_name, s.sort_order,
                       AVG(TIMESTAMPDIFF(HOUR, h.created_at, nx.created_at)) / 24 AS avg_days,
                       COUNT(*) AS moves
                FROM application_history h
                JOIN stages s ON s.id = h.to_stage_id
                JOIN applications a ON a.id = h.application_id
                JOIN jobs j ON j.id = a.job_id
                JOIN application_history nx ON nx.application_id = h.application_id
                     AND nx.id = (SELECT MIN(n2.id) FROM application_history n2
                                  WHERE n2.application_id = h.application_id AND n2.id > h.id)
                WHERE a.deleted_at IS NULL';

        $params = [];
        if ($unitId !== null) {
            $sql .= ' AND j.unit_id = ?';
            $params[] = $unitId;
        }

        $sql .= ' GROUP BY s.id, s.name, s.sort_order ORDER BY s.sort_order';

        return $this->db->query($sql, $params)->getResultArray();
    }
}
