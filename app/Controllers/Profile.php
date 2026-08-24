<?php

namespace App\Controllers;

use App\Models\UserModel;

/** Profil akun sendiri: ubah nama, kontak, dan kata sandi. */
class Profile extends BaseController
{
    public function index()
    {
        return $this->render('profile/index', [
            'title'  => 'Profil Saya',
            'active' => '',
            'me'     => model(UserModel::class)->find($this->auth['id']),
        ]);
    }

    public function update()
    {
        $users = model(UserModel::class);

        $data = [
            'name'     => trim((string) $this->request->getPost('name')),
            'position' => $this->request->getPost('position') ?: null,
            'phone'    => $this->request->getPost('phone') ?: null,
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            if ($password !== (string) $this->request->getPost('password_confirm')) {
                return redirect()->back()->with('error', 'Konfirmasi kata sandi tidak cocok.');
            }
            $data['password'] = $password;
        }

        if (! $users->update($this->auth['id'], $data)) {
            return redirect()->back()->withInput()->with('errors', $users->errors());
        }

        /* Segarkan nama di sesi agar topbar ikut berubah. */
        session()->set('user_name', $data['name']);

        return redirect()->to(site_url('profil'))->with('success', 'Profil diperbarui.');
    }
}
