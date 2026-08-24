<?php

namespace App\Controllers;

use App\Models\UserModel;

/** Masuk & keluar aplikasi internal. */
class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login', ['title' => 'Masuk']);
    }

    public function attempt()
    {
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        $users = model(UserModel::class);
        $user  = $users->findActiveByEmail($email);

        /* Pesan sengaja dibuat sama untuk email salah maupun sandi salah,
           supaya tidak bisa dipakai menebak email mana yang terdaftar. */
        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()
                ->with('error', 'Email atau kata sandi tidak cocok.');
        }

        $unitName = null;
        if ($user['unit_id']) {
            $unitName = model(\App\Models\UnitModel::class)->find($user['unit_id'])['name'] ?? null;
        }

        $this->session->regenerate();
        $this->session->set([
            'user_id'    => (int) $user['id'],
            'user_name'  => $user['name'],
            'user_email' => $user['email'],
            'user_role'  => $user['role'],
            'unit_id'    => $user['unit_id'] !== null ? (int) $user['unit_id'] : null,
            'unit_name'  => $unitName ?? 'Semua Unit',
        ]);

        $users->touchLogin((int) $user['id']);

        $target = $this->session->get('redirect_after_login') ?: site_url('dashboard');
        $this->session->remove('redirect_after_login');

        return redirect()->to($target)->with('success', 'Selamat datang, ' . $user['name'] . '.');
    }

    public function logout()
    {
        $this->session->destroy();

        return redirect()->to(site_url('login'))->with('info', 'Anda telah keluar.');
    }

    // ---- Lupa & reset kata sandi -------------------------------------------

    public function forgot()
    {
        return view('auth/forgot', ['title' => 'Lupa Sandi']);
    }

    /**
     * Kirim tautan reset. Pesannya SELALU sama, ada-tidaknya email, supaya
     * halaman ini tidak bisa dipakai menebak email mana yang terdaftar.
     */
    public function sendReset()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $user  = model(\App\Models\UserModel::class)->findActiveByEmail($email);

        if ($user) {
            $token = model(\App\Models\PasswordResetModel::class)->issue((int) $user['id']);
            $link  = site_url('reset/' . $token);

            $settings = model(\App\Models\SettingModel::class);
            model(\App\Models\EmailOutboxModel::class)->queue([
                'to_email' => $user['email'],
                'to_name'  => $user['name'],
                'subject'  => 'Reset Kata Sandi ' . $settings->value('company_name', 'ATS WBL'),
                'body'     => '<p>Halo ' . esc($user['name']) . ',</p>'
                    . '<p>Kami menerima permintaan reset kata sandi akun ATS Anda. Klik tautan di bawah '
                    . '(berlaku 1 jam) untuk membuat sandi baru:</p>'
                    . '<p><a href="' . $link . '">' . $link . '</a></p>'
                    . '<p>Bila Anda tidak meminta ini, abaikan email ini.</p>',
            ]);

            /* Kirim langsung bila SMTP siap; jika tidak, menunggu di antrean. */
            $queue = new \App\Libraries\EmailQueue();
            if ($queue->smtpReady()) {
                $queue->flush(5);
            }
        }

        return redirect()->to(site_url('login'))
            ->with('info', 'Bila email terdaftar, tautan reset telah dikirim. Periksa kotak masuk Anda.');
    }

    public function resetForm(string $token)
    {
        $row = model(\App\Models\PasswordResetModel::class)->findValid($token);
        if (! $row) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Tautan reset tidak valid atau sudah kedaluwarsa.');
        }

        return view('auth/reset', ['title' => 'Reset Sandi', 'token' => $token]);
    }

    public function resetSave(string $token)
    {
        $resets = model(\App\Models\PasswordResetModel::class);
        $row    = $resets->findValid($token);
        if (! $row) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Tautan reset tidak valid atau sudah kedaluwarsa.');
        }

        $password = (string) $this->request->getPost('password');
        if (mb_strlen($password) < 6) {
            return redirect()->back()->with('error', 'Kata sandi minimal 6 karakter.');
        }
        if ($password !== (string) $this->request->getPost('password_confirm')) {
            return redirect()->back()->with('error', 'Konfirmasi kata sandi tidak cocok.');
        }

        model(\App\Models\UserModel::class)->update((int) $row['user_id'], ['password' => $password]);
        $resets->consume((int) $row['id']);

        return redirect()->to(site_url('login'))->with('success', 'Kata sandi berhasil diubah. Silakan masuk.');
    }
}
