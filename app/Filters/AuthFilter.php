<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Penjaga halaman internal. Argumen filter dipakai sebagai daftar peran yang
 * diizinkan, mis. `auth:superadmin,hr_admin`; tanpa argumen berarti cukup login.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = service('session');

        if (! $session->has('user_id')) {
            $session->set('redirect_after_login', current_url());

            return redirect()->to(site_url('login'))
                ->with('error', 'Silakan masuk terlebih dahulu.');
        }

        if ($arguments && ! in_array($session->get('user_role'), $arguments, true)) {
            return redirect()->to(site_url('dashboard'))
                ->with('error', 'Anda tidak punya akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
