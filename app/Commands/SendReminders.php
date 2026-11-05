<?php

namespace App\Commands;

use App\Libraries\EmailQueue;
use App\Models\InterviewModel;
use App\Models\SettingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Antre + kirim pengingat interview untuk jadwal yang jatuh dalam N jam ke
 * depan (N dari pengaturan interview_reminder_hours). Idempoten: satu jadwal
 * hanya diingatkan sekali, ditandai lewat email_outbox.
 *
 *   0 * * * *  cd /path/ke/wbl-ats && php spark interview:remind >> writable/logs/cron.log 2>&1
 */
class SendReminders extends BaseCommand
{
    protected $group       = 'ATS';
    protected $name        = 'interview:remind';
    protected $description = 'Kirim pengingat untuk interview yang akan datang.';

    public function run(array $params): int
    {
        helper('ui'); // tgl() dipakai untuk placeholder tanggal

        $hours = (int) (model(SettingModel::class)->value('interview_reminder_hours', '24') ?: 24);
        $due   = model(InterviewModel::class)->dueForReminder($hours);

        $queue  = new EmailQueue();
        $outbox = model(\App\Models\EmailOutboxModel::class);
        $antre  = 0;

        foreach ($due as $iv) {
            /* Cegah ganda: lewati bila pengingat untuk lamaran ini sudah pernah
               diantrekan hari ini. */
            $sudah = $outbox->where('application_id', $iv['application_id'])
                ->where('template_code', 'interview_reminder')
                ->where('created_at >=', date('Y-m-d 00:00:00'))
                ->countAllResults() > 0;
            if ($sudah) {
                continue;
            }

            $iv = model(InterviewModel::class)->withNonce($iv);
            $queue->queueForApplication('interview_reminder', (int) $iv['application_id'], [
                'tanggal_interview' => tgl($iv['scheduled_at']),
                'jam_interview'     => date('H:i', strtotime($iv['scheduled_at'])),
                'lokasi'            => $iv['location'] ?: '-',
                'link_meeting'      => $iv['meeting_link'] ?: '-',
                'tombol_jawaban'    => \App\Libraries\InterviewLink::buttonsHtml($iv),
            ]);
            $antre++;
        }

        $hasil = $queue->smtpReady() ? $queue->flush(50) : ['sent' => 0, 'failed' => 0];

        CLI::write(sprintf('[%s] Pengingat diantrekan: %d, terkirim: %d, gagal: %d%s',
            date('Y-m-d H:i:s'), $antre, $hasil['sent'], $hasil['failed'],
            $queue->smtpReady() ? '' : ' (SMTP belum diatur — menunggu di antrean)'), 'green');

        return EXIT_SUCCESS;
    }
}
