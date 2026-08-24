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
}
