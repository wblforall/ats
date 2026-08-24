<?php

namespace App\Controllers;

use App\Models\SettingModel;

/** Pengaturan aplikasi yang bisa diubah HR tanpa menyentuh .env. */
class Settings extends BaseController
{
    /** Field yang boleh diubah lewat halaman ini. */
    private const FIELDS = [
        'company_name', 'company_address', 'hr_email', 'hr_phone',
        'career_tagline', 'career_intro', 'cv_max_mb', 'cv_allowed_ext',
        'auto_email_on_apply', 'interview_reminder_hours',
        'potential_w_interview', 'potential_w_recommendation', 'potential_w_rating',
        'potential_w_experience', 'potential_w_education',
    ];

    public function index()
    {
        return $this->render('settings/index', [
            'title'    => 'Pengaturan',
            'active'   => 'settings',
            'settings' => model(SettingModel::class)->all(),
        ]);
    }

    public function update()
    {
        $pairs = [];
        foreach (self::FIELDS as $key) {
            if ($this->request->getPost($key) !== null) {
                $pairs[$key] = trim((string) $this->request->getPost($key));
            }
        }
        /* Checkbox tak terkirim saat mati; paksa nilainya. */
        $pairs['auto_email_on_apply'] = $this->request->getPost('auto_email_on_apply') ? '1' : '0';

        model(SettingModel::class)->setMany($pairs);

        return redirect()->to(site_url('pengaturan'))->with('success', 'Pengaturan disimpan.');
    }
}
