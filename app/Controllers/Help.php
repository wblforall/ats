<?php

namespace App\Controllers;

/** Halaman bantuan ringkas untuk pengguna internal. */
class Help extends BaseController
{
    public function index()
    {
        return $this->render('help/index', ['title' => 'Bantuan', 'active' => '']);
    }
}
