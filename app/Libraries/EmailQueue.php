<?php

namespace App\Libraries;

use App\Models\ApplicationModel;
use App\Models\EmailOutboxModel;
use App\Models\EmailTemplateModel;
use App\Models\SettingModel;

/**
 * Penyusun & pengantre email ke pelamar.
 *
 * Alurnya sengaja dua langkah: isi template -> masuk antrean (email_outbox),
 * lalu proses antrean mengirimkannya. Dengan begitu SMTP yang sedang bermasalah
 * tidak pernah menggagalkan aksi HR, dan setiap kiriman punya jejak yang bisa
 * ditelusuri serta dikirim ulang.
 */
class EmailQueue
{
    /**
     * Susun template lalu masukkan ke antrean untuk satu lamaran.
     *
     * @param array $extra Placeholder tambahan tanpa kurung kurawal,
     *                     mis. ['tanggal_interview' => '25 Agustus 2026'].
     *
     * @return int|null id antrean, atau null bila template tidak ada/nonaktif.
     */
    public function queueForApplication(string $templateCode, int $applicationId, array $extra = [], ?int $userId = null): ?int
    {
        $template = model(EmailTemplateModel::class)->findByCode($templateCode);
        if (! $template) {
            return null;
        }

        $apps        = model(ApplicationModel::class);
        $application = $apps->findDetailed($applicationId);
        if (! $application) {
            return null;
        }

        $values = array_merge($this->baseValues($application, $userId), $extra);

        return model(EmailOutboxModel::class)->queue([
            'application_id' => $applicationId,
            'template_code'  => $templateCode,
            'to_email'       => $application['email'],
            'to_name'        => $application['full_name'],
            'subject'        => $this->fill($template['subject'], $values),
            'body'           => $this->fill($template['body'], $values),
            'created_by'     => $userId,
        ]);
    }

    /** Nilai placeholder standar yang selalu tersedia untuk sebuah lamaran. */
    public function baseValues(array $application, ?int $userId = null): array
    {
        $settings = model(SettingModel::class);

        $senderName = $settings->value('company_name', 'HRD');
        if ($userId) {
            $senderName = model(\App\Models\UserModel::class)->find($userId)['name'] ?? $senderName;
        }

        return [
            'nama_kandidat'     => $application['full_name'],
            'posisi'            => $application['job_title'],
            'unit'              => $application['unit_name'] ?? '-',
            'perusahaan'        => $settings->value('company_name', 'PT. Wulandari Bangun Laksana Tbk'),
            'kode_lamaran'      => model(ApplicationModel::class)
                ->referenceCode((int) $application['id'], $application['applied_at']),
            'nama_hr'           => $senderName,
            'kontak_hr'         => trim((string) $settings->value('hr_email') . ' ' . $settings->value('hr_phone')),
            'tanggal_interview' => '-',
            'jam_interview'     => '-',
            'lokasi'            => '-',
            'link_meeting'      => '-',
        ];
    }

    /** Ganti {{placeholder}} dengan nilainya; yang tak dikenal dibiarkan apa adanya. */
    public function fill(string $text, array $values): string
    {
        foreach ($values as $key => $value) {
            $text = str_replace('{{' . $key . '}}', (string) $value, $text);
        }

        return $text;
    }

    /**
     * Kirim antrean yang tertunda. Dipanggil dari perintah CLI
     * (`php spark email:send`) supaya bisa dijadwalkan lewat cron.
     *
     * @return array{sent:int, failed:int}
     */
    public function flush(int $limit = 25): array
    {
        $outbox = model(EmailOutboxModel::class);
        $rows   = $outbox->pending($limit);
        $sent   = 0;
        $failed = 0;

        foreach ($rows as $row) {
            $mailer = service('email', null, false);
            $mailer->setTo($row['to_email'], $row['to_name'] ?? '');
            $mailer->setSubject($row['subject']);
            $mailer->setMessage($row['body']);

            try {
                if ($mailer->send(false)) {
                    $outbox->markSent((int) $row['id']);
                    $sent++;
                } else {
                    $outbox->markFailed((int) $row['id'], strip_tags($mailer->printDebugger(['headers'])), (int) $row['attempts']);
                    $failed++;
                }
            } catch (\Throwable $e) {
                $outbox->markFailed((int) $row['id'], $e->getMessage(), (int) $row['attempts']);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }
}
