<?php

namespace App\Controllers;

use App\Models\ApplicationHistoryModel;
use App\Models\ApplicationModel;
use App\Models\InterviewModel;
use App\Models\SettingModel;
use App\Models\StageModel;

/**
 * Pelacakan lamaran untuk pelamar — tanpa akun.
 *
 * Nomor lamaran berurutan sehingga mudah ditebak; karena itu email pelamar
 * ikut diminta sebagai pasangan kunci. Keduanya sudah ada di tangan pelamar
 * (dikirim ke inbox mereka), tapi tidak bisa ditebak orang lain.
 */
class Track extends BaseController
{
    public function form()
    {
        return view('career/track', [
            'title'    => 'Lacak Lamaran',
            'settings' => model(SettingModel::class)->all(),
        ]);
    }

    public function check()
    {
        $ref   = strtoupper(trim((string) $this->request->getPost('ref')));
        $email = strtolower(trim((string) $this->request->getPost('email')));

        $id = $this->parseReference($ref);

        if ($id === null || $email === '') {
            return redirect()->back()->withInput()
                ->with('error', 'Nomor lamaran atau email belum benar. Contoh nomor: WBL-2026-000123.');
        }

        $apps        = model(ApplicationModel::class);
        $application = $apps->findDetailed($id);

        /* Jeda kecil agar percobaan tebak nomor secara massal jadi mahal. */
        usleep(300000);

        if (! $application || strtolower($application['email']) !== $email) {
            return redirect()->back()->withInput()
                ->with('error', 'Data tidak ditemukan. Pastikan nomor lamaran dan email sama persis dengan yang Anda terima.');
        }

        $stages  = model(StageModel::class)->active();
        $history = model(ApplicationHistoryModel::class)->timeline($id);

        /* Tahap mana saja yang sudah pernah dilalui — dasar penanda linimasa. */
        $visited = array_map('intval', array_column($history, 'to_stage_id'));

        $interview = model(InterviewModel::class)
            ->where('application_id', $id)
            ->where('status', 'scheduled')
            ->where('scheduled_at >=', date('Y-m-d H:i:s'))
            ->orderBy('scheduled_at', 'ASC')
            ->first();

        return view('career/track_result', [
            'title'       => 'Status Lamaran ' . $ref,
            'ref'         => $ref,
            'application' => $application,
            'stages'      => $stages,
            'visited'     => $visited,
            'history'     => $history,
            'interview'   => $interview,
            'settings'    => model(SettingModel::class)->all(),
        ]);
    }

    /** Ambil id dari nomor bergaya WBL-2026-000123; null bila formatnya salah. */
    private function parseReference(string $ref): ?int
    {
        if (! preg_match('/^WBL-\d{4}-(\d{4,8})$/', $ref, $m)) {
            return null;
        }

        $id = (int) $m[1];

        return $id > 0 ? $id : null;
    }
}
