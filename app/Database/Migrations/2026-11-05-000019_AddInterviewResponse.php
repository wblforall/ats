<?php

namespace App\Database\Migrations;

use App\Database\Seeds\InterviewResponseTemplates;
use CodeIgniter\Database\Migration;

/**
 * Jawaban pelamar atas undangan interview: konfirmasi hadir atau minta jadwal
 * ulang lewat tombol di email, tanpa login, atau membatalkan interview supaya HR
 * tidak menunggu. response_reason menyimpan alasan jadwal ulang / pembatalan.
 *
 * response_nonce menjadi bahan tanda tangan tautan (HMAC dengan encryption.key);
 * nonce diganti setiap jadwal berubah sehingga tautan dari email lama mati.
 * Template undangan & pengingat ikut diperbarui: kalimat "balas email ini"
 * diganti tombol {{tombol_jawaban}}, plus dua template baru.
 */
class AddInterviewResponse extends Migration
{
    private const OLD_INVITATION = 'Konfirmasi kehadiran Anda dengan membalas email ini.</p>';
    private const NEW_INVITATION = "Mohon konfirmasi kehadiran Anda lewat tombol di bawah ini. Bila berhalangan, Anda dapat meminta jadwal ulang paling lambat H-1.</p>\n{{tombol_jawaban}}";
    private const OLD_REMINDER   = '<p>Bila berhalangan, mohon kabari kami sesegera mungkin.</p>';
    private const NEW_REMINDER   = "<p>Bila belum, mohon konfirmasi kehadiran Anda lewat tombol di bawah ini.</p>\n{{tombol_jawaban}}";

    public function up(): void
    {
        $this->forge->addColumn('interviews', [
            'response_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'confirmed', 'reschedule_requested', 'canceled_by_candidate'],
                'default'    => 'pending',
                'null'       => false,
                'after'      => 'status',
            ],
            'response_nonce'         => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true, 'after' => 'response_status'],
            'invited_at'             => ['type' => 'DATETIME', 'null' => true, 'after' => 'response_nonce'],
            'responded_at'           => ['type' => 'DATETIME', 'null' => true, 'after' => 'invited_at'],
            'reschedule_proposed_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'responded_at'],
            'response_reason'        => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'reschedule_proposed_at'],
        ]);
        $this->db->query('ALTER TABLE interviews ADD INDEX response_status (response_status)');

        $this->replaceInTemplate('interview_invitation', self::OLD_INVITATION, self::NEW_INVITATION);
        $this->replaceInTemplate('interview_reminder', self::OLD_REMINDER, self::NEW_REMINDER);

        $now = date('Y-m-d H:i:s');
        foreach (InterviewResponseTemplates::all() as [$code, $name, $subject, $body, $desc]) {
            $this->db->table('email_templates')->ignore(true)->insert([
                'code'        => $code,
                'name'        => $name,
                'subject'     => $subject,
                'body'        => $body,
                'description' => $desc,
                'auto_send'   => 0,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->table('email_templates')
            ->whereIn('code', array_column(InterviewResponseTemplates::all(), 0))
            ->delete();
        $this->replaceInTemplate('interview_invitation', self::NEW_INVITATION, self::OLD_INVITATION);
        $this->replaceInTemplate('interview_reminder', self::NEW_REMINDER, self::OLD_REMINDER);

        $this->forge->dropColumn('interviews', [
            'response_status', 'response_nonce', 'invited_at', 'responded_at',
            'reschedule_proposed_at', 'response_reason',
        ]);
    }

    /** Hanya mengubah template yang masih memuat kalimat bawaan; yang sudah disunting HR dibiarkan. */
    private function replaceInTemplate(string $code, string $from, string $to): void
    {
        $row = $this->db->table('email_templates')->where('code', $code)->get()->getRowArray();
        if ($row && str_contains($row['body'], $from)) {
            $this->db->table('email_templates')->where('code', $code)
                ->update(['body' => str_replace($from, $to, $row['body']), 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }
}
