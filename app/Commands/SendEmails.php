<?php

namespace App\Commands;

use App\Libraries\EmailQueue;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Kirim email yang menunggu di antrean (email_outbox).
 *
 * Dipakai lewat cron agar email yang gagal saat aksi HR (mis. SMTP sempat
 * mati) dikirim ulang belakangan, dan agar email yang sengaja hanya
 * diantrekan (bukan dikirim seketika) tetap terkirim:
 *
 *   * / 5 * * * *  cd /path/ke/wbl-ats && php spark email:send >> writable/logs/cron-email.log 2>&1
 */
class SendEmails extends BaseCommand
{
    protected $group       = 'ATS';
    protected $name        = 'email:send';
    protected $description  = 'Kirim email yang menunggu di antrean (email_outbox).';
    protected $usage       = 'email:send [--limit N]';
    protected $options     = ['--limit' => 'Jumlah email maksimal per jalankan (default 25).'];

    public function run(array $params): int
    {
        $limit = (int) (CLI::getOption('limit') ?: ($params[0] ?? 25));
        $limit = $limit > 0 ? $limit : 25;

        $queue = new EmailQueue();

        if (! $queue->smtpReady()) {
            CLI::error('SMTP belum dikonfigurasi (email.SMTPHost kosong di .env). Tidak ada yang dikirim.');

            return EXIT_ERROR;
        }

        $hasil = $queue->flush($limit);

        CLI::write(sprintf(
            '[%s] Terkirim: %d, gagal: %d.',
            date('Y-m-d H:i:s'),
            $hasil['sent'],
            $hasil['failed']
        ), $hasil['failed'] > 0 ? 'yellow' : 'green');

        return EXIT_SUCCESS;
    }
}
