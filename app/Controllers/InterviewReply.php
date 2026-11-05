<?php

namespace App\Controllers;

use App\Libraries\EmailQueue;
use App\Libraries\InterviewLink;
use App\Models\ApplicationNoteModel;
use App\Models\InterviewModel;
use App\Models\SettingModel;
use App\Models\UserModel;

/**
 * Jawaban pelamar atas undangan interview — tanpa akun, lewat tautan bertanda
 * tangan dari email (lihat InterviewLink). Pelamar bisa konfirmasi hadir sampai
 * jam interview, atau meminta jadwal ulang dengan usulan waktu bebas paling
 * lambat H-1, atau membatalkan interview (sampai jam interview) supaya HR tidak
 * menunggu. Jadwal ulang baru berlaku setelah HR menyetujui & mengirim undangan
 * baru; HR yang menjadwalkan diberi tahu lewat email untuk keduanya.
 */
class InterviewReply extends BaseController
{
    public function show(int $id, string $signature)
    {
        $interview = $this->find($id, $signature);
        if (! $interview) {
            return $this->invalid();
        }

        return view('career/interview_reply', [
            'title'      => 'Konfirmasi Interview',
            'settings'   => model(SettingModel::class)->all(),
            'iv'         => $interview,
            'action'     => site_url('jadwal-interview/' . $id . '/' . $signature),
            'aksi'       => (string) $this->request->getGet('aksi'),
            'bolehHadir' => InterviewModel::canConfirm($interview),
            'bolehUlang' => InterviewModel::canReschedule($interview),
        ]);
    }

    public function confirm(int $id, string $signature)
    {
        $interview = $this->find($id, $signature);
        if (! $interview) {
            return $this->invalid();
        }
        $back = site_url('jadwal-interview/' . $id . '/' . $signature);

        if (! InterviewModel::canConfirm($interview)) {
            return redirect()->to($back)->with('error', 'Jadwal ini sudah tidak bisa dikonfirmasi.');
        }

        model(InterviewModel::class)->update($id, [
            'response_status'        => 'confirmed',
            'responded_at'           => date('Y-m-d H:i:s'),
            'reschedule_proposed_at' => null,
            'response_reason'        => null,
        ]);
        $this->note($interview, 'Pelamar mengonfirmasi hadir lewat email.');

        return redirect()->to($back)->with('success', 'Terima kasih, kehadiran Anda sudah kami catat. Sampai jumpa di hari interview.');
    }

    public function reschedule(int $id, string $signature)
    {
        $interview = $this->find($id, $signature);
        if (! $interview) {
            return $this->invalid();
        }
        $back = site_url('jadwal-interview/' . $id . '/' . $signature) . '?aksi=ulang';

        if (! InterviewModel::canReschedule($interview)) {
            return redirect()->to($back)->with('error', 'Permintaan jadwal ulang hanya bisa diajukan paling lambat H-1 sebelum interview.');
        }

        $tanggal = trim((string) $this->request->getPost('tanggal'));
        $jam     = trim((string) $this->request->getPost('jam'));
        $alasan  = trim((string) $this->request->getPost('alasan'));
        $usulan  = $tanggal !== '' && $jam !== '' ? date_create_immutable_from_format('Y-m-d H:i', $tanggal . ' ' . $jam) : false;

        if (! $usulan || $usulan->format('Y-m-d H:i') !== $tanggal . ' ' . $jam) {
            return redirect()->to($back)->withInput()->with('error', 'Isi tanggal dan jam usulan dengan benar.');
        }
        if ($usulan->format('Y-m-d') <= date('Y-m-d')) {
            return redirect()->to($back)->withInput()->with('error', 'Usulan jadwal paling cepat besok.');
        }
        if ($alasan === '' || mb_strlen($alasan) > 500) {
            return redirect()->to($back)->withInput()->with('error', 'Tuliskan alasan singkat (maksimal 500 karakter).');
        }

        $usulanAt = $usulan->format('Y-m-d H:i:00');
        model(InterviewModel::class)->update($id, [
            'response_status'        => 'reschedule_requested',
            'responded_at'           => date('Y-m-d H:i:s'),
            'reschedule_proposed_at' => $usulanAt,
            'response_reason'        => $alasan,
        ]);
        $this->note($interview, 'Pelamar meminta jadwal ulang ke ' . hari($usulanAt) . ', ' . tgl($usulanAt, true)
            . ' WITA. Alasan: ' . $alasan);
        $this->notifyHr('interview_reschedule_hr', $interview, [
            'usulan_jadwal' => hari($usulanAt) . ', ' . tgl($usulanAt, true),
            'alasan'        => nl2br(esc($alasan)),
        ]);

        return redirect()->to(site_url('jadwal-interview/' . $id . '/' . $signature))
            ->with('success', 'Permintaan jadwal ulang terkirim. Tim HRD akan meninjau dan mengabari Anda lewat email.');
    }

    /** Pelamar tidak bisa hadir sama sekali: jadwal langsung dibatalkan dan HR diberi tahu. */
    public function cancel(int $id, string $signature)
    {
        $interview = $this->find($id, $signature);
        if (! $interview) {
            return $this->invalid();
        }
        $back = site_url('jadwal-interview/' . $id . '/' . $signature);

        if (! InterviewModel::canConfirm($interview)) {
            return redirect()->to($back)->with('error', 'Jadwal ini sudah tidak aktif.');
        }

        $alasan = trim((string) $this->request->getPost('alasan_batal'));
        if ($alasan === '' || mb_strlen($alasan) > 500) {
            return redirect()->to($back . '?aksi=batal')->withInput()->with('error', 'Tuliskan alasan pembatalan (maksimal 500 karakter).');
        }

        model(InterviewModel::class)->update($id, [
            'status'                 => 'canceled',
            'response_status'        => 'canceled_by_candidate',
            'responded_at'           => date('Y-m-d H:i:s'),
            'reschedule_proposed_at' => null,
            'response_reason'        => $alasan,
        ]);
        $this->note($interview, 'Pelamar membatalkan interview. Alasan: ' . $alasan);
        $this->notifyHr('interview_canceled_hr', $interview, ['alasan' => nl2br(esc($alasan))]);

        return redirect()->to($back)->with('success', 'Interview sudah dibatalkan dan tim HRD sudah kami beri tahu. Terima kasih atas kabarnya.');
    }

    /** Email ke HR yang menjadwalkan; bila akunnya tak ada/nonaktif, ke email HRD di pengaturan. */
    private function notifyHr(string $template, array $interview, array $extra): void
    {
        $hr = $interview['created_by'] ? model(UserModel::class)->find((int) $interview['created_by']) : null;
        if ($hr && (int) $hr['is_active'] === 1) {
            [$email, $nama] = [$hr['email'], $hr['name']];
        } else {
            [$email, $nama] = [(string) model(SettingModel::class)->value('hr_email'), 'Tim HRD'];
        }

        $queue    = new EmailQueue();
        $outboxId = $queue->queueForStaff($template, (int) $interview['application_id'], $email, $nama, $extra + [
            'tanggal_interview' => tgl($interview['scheduled_at']),
            'jam_interview'     => date('H:i', strtotime($interview['scheduled_at'])),
            'link_pelamar'      => site_url('pelamar/' . $interview['application_id']) . '#interview',
        ]);

        if ($outboxId !== null && $queue->smtpReady()) {
            $queue->sendNow($outboxId);
        }
    }

    private function note(array $interview, string $text): void
    {
        model(ApplicationNoteModel::class)->insert([
            'application_id' => (int) $interview['application_id'],
            'user_id'        => null,
            'note'           => 'Interview putaran ' . (int) $interview['round'] . ': ' . $text,
        ]);
    }

    private function find(int $id, string $signature): ?array
    {
        $interview = model(InterviewModel::class)->detailed()->where('interviews.id', $id)->first();

        return $interview && InterviewLink::verify($interview, $signature) ? $interview : null;
    }

    /** Tautan salah atau sudah diganti karena jadwal berubah. */
    private function invalid()
    {
        return $this->response->setStatusCode(404)->setBody(view('career/interview_reply', [
            'title'    => 'Tautan Tidak Berlaku',
            'settings' => model(SettingModel::class)->all(),
            'iv'       => null,
        ]));
    }
}
