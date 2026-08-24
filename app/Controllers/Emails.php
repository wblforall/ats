<?php

namespace App\Controllers;

use App\Libraries\EmailQueue;
use App\Models\EmailOutboxModel;
use App\Models\EmailTemplateModel;

/**
 * Kelola email: antrean kiriman (outbox) dan template. Hanya manajer HR.
 * Superadmin & hr_admin melihat semua unit; email tidak diikat unit karena
 * outbox mencatat alamat pelamar langsung.
 */
class Emails extends BaseController
{
    public function index()
    {
        $status = (string) $this->request->getGet('status');

        $outbox = model(EmailOutboxModel::class)
            ->select('email_outbox.*, users.name AS sender_name')
            ->join('users', 'users.id = email_outbox.created_by', 'left');

        if ($status !== '' && in_array($status, ['queued', 'sent', 'failed'], true)) {
            $outbox->where('email_outbox.status', $status);
        }

        $ready = (new EmailQueue())->smtpReady();

        return $this->render('emails/index', [
            'title'    => 'Email',
            'active'   => 'emails',
            'rows'     => $outbox->orderBy('email_outbox.id', 'DESC')->findAll(100),
            'counts'   => $this->statusCounts(),
            'filter'   => $status,
            'smtpReady' => $ready,
        ]);
    }

    /** Kirim ulang satu email (atau kirim yang masih antre). */
    public function send(int $id)
    {
        $queue = new EmailQueue();

        if (! $queue->smtpReady()) {
            return redirect()->back()->with('error', 'SMTP belum dikonfigurasi di .env, email belum bisa dikirim.');
        }

        $terkirim = $queue->sendNow($id);

        return redirect()->back()->with(
            $terkirim ? 'success' : 'error',
            $terkirim ? 'Email terkirim.' : 'Email gagal terkirim, coba lagi nanti.'
        );
    }

    /** Kirim semua yang tertunda sekaligus. */
    public function flush()
    {
        $queue = new EmailQueue();

        if (! $queue->smtpReady()) {
            return redirect()->back()->with('error', 'SMTP belum dikonfigurasi di .env.');
        }

        $hasil = $queue->flush(50);

        return redirect()->back()->with('success',
            "Selesai memproses antrean — terkirim: {$hasil['sent']}, gagal: {$hasil['failed']}.");
    }

    public function view(int $id)
    {
        $mail = model(EmailOutboxModel::class)
            ->select('email_outbox.*, users.name AS sender_name')
            ->join('users', 'users.id = email_outbox.created_by', 'left')
            ->where('email_outbox.id', $id)->first();

        if (! $mail) {
            return redirect()->to(site_url('email'))->with('error', 'Email tidak ditemukan.');
        }

        return $this->render('emails/view', [
            'title'  => 'Email',
            'active' => 'emails',
            'mail'   => $mail,
        ]);
    }

    // ---- Template ----------------------------------------------------------

    public function templates()
    {
        return $this->render('emails/templates', [
            'title'     => 'Template Email',
            'active'    => 'emails',
            'templates' => model(EmailTemplateModel::class)->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    public function editTemplate(int $id)
    {
        $template = model(EmailTemplateModel::class)->find($id);
        if (! $template) {
            return redirect()->to(site_url('email/template'))->with('error', 'Template tidak ditemukan.');
        }

        return $this->render('emails/template_form', [
            'title'    => 'Ubah Template',
            'active'   => 'emails',
            'template' => $template,
        ]);
    }

    public function updateTemplate(int $id)
    {
        $templates = model(EmailTemplateModel::class);
        if (! $templates->find($id)) {
            return redirect()->to(site_url('email/template'))->with('error', 'Template tidak ditemukan.');
        }

        $data = [
            'name'      => trim((string) $this->request->getPost('name')),
            'subject'   => trim((string) $this->request->getPost('subject')),
            'body'      => (string) $this->request->getPost('body'),
            'auto_send' => $this->request->getPost('auto_send') ? 1 : 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (! $templates->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $templates->errors());
        }

        return redirect()->to(site_url('email/template'))->with('success', 'Template diperbarui.');
    }

    private function statusCounts(): array
    {
        $model = model(EmailOutboxModel::class);

        return [
            'all'    => $model->countAll(),
            'queued' => $model->where('status', 'queued')->countAllResults(),
            'sent'   => $model->where('status', 'sent')->countAllResults(),
            'failed' => $model->where('status', 'failed')->countAllResults(),
        ];
    }
}
