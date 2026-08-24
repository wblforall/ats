<?php

namespace App\Commands;

use App\Models\JobModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Tutup lowongan yang sudah melewati tanggal tutup. Portal karir sebenarnya
 * sudah menyaringnya dari tampilan publik, tapi ini merapikan status di sisi
 * HR agar tidak ada lowongan "Dibuka" yang sebenarnya kedaluwarsa.
 *
 *   0 1 * * *  cd /path/ke/wbl-ats && php spark jobs:close-expired >> writable/logs/cron.log 2>&1
 */
class CloseExpiredJobs extends BaseCommand
{
    protected $group       = 'ATS';
    protected $name        = 'jobs:close-expired';
    protected $description = 'Tutup lowongan yang sudah lewat tanggal tutup.';

    public function run(array $params): int
    {
        $jobs = model(JobModel::class);

        $expired = $jobs->where('status', 'open')
            ->where('closing_date IS NOT NULL')
            ->where('closing_date <', date('Y-m-d'))
            ->findAll();

        foreach ($expired as $job) {
            $jobs->update($job['id'], ['status' => 'closed']);
        }

        CLI::write(sprintf('[%s] Lowongan ditutup otomatis: %d.', date('Y-m-d H:i:s'), count($expired)),
            count($expired) > 0 ? 'yellow' : 'green');

        return EXIT_SUCCESS;
    }
}
