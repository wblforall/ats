<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Template email standar HR. Placeholder yang dikenali:
 *   {{nama_kandidat}} {{posisi}} {{unit}} {{perusahaan}} {{kode_lamaran}}
 *   {{tanggal_interview}} {{jam_interview}} {{lokasi}} {{link_meeting}}
 *   {{nama_hr}} {{kontak_hr}}
 * Hanya `application_received` yang auto_send — sisanya dikirim manual supaya
 * HR sempat memeriksa isinya sebelum sampai ke pelamar.
 */
class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $templates = [
            [
                'application_received',
                'Konfirmasi Lamaran Diterima',
                'Lamaran Anda untuk posisi {{posisi}} telah kami terima',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Terima kasih telah melamar posisi <b>{{posisi}}</b> di {{unit}} — {{perusahaan}}. Lamaran Anda sudah masuk ke sistem kami dengan nomor <b>{{kode_lamaran}}</b>.</p>\n<p>Tim rekrutmen kami akan menyeleksi berkas Anda. Bila profil Anda sesuai, kami akan menghubungi Anda untuk tahap berikutnya.</p>\n<p>Salam,<br>{{nama_hr}}<br>HRD {{perusahaan}}</p>",
                'Dikirim otomatis saat pelamar mengirim lamaran dari portal karir.',
                1,
            ],
            [
                'document_request',
                'Permintaan Kelengkapan Berkas',
                'Kelengkapan berkas lamaran {{posisi}}',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Terkait lamaran Anda untuk posisi <b>{{posisi}}</b>, kami membutuhkan kelengkapan berkas berikut:</p>\n<ul><li>(tuliskan berkas yang diminta di sini)</li></ul>\n<p>Mohon dikirim balik ke email ini paling lambat 3 hari kerja sejak email ini diterima.</p>\n<p>Salam,<br>{{nama_hr}}<br>HRD {{perusahaan}}</p>",
                'Dipakai saat seleksi berkas bila dokumen pelamar kurang.',
                0,
            ],
            [
                'interview_invitation',
                'Undangan Interview',
                'Undangan interview posisi {{posisi}} di {{perusahaan}}',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Selamat! Berkas Anda lolos seleksi untuk posisi <b>{{posisi}}</b>. Kami mengundang Anda mengikuti wawancara pada:</p>\n<p><b>Hari/Tanggal:</b> {{tanggal_interview}}<br><b>Waktu:</b> {{jam_interview}} WITA<br><b>Tempat:</b> {{lokasi}}<br><b>Tautan daring:</b> {{link_meeting}}</p>\n<p>Mohon membawa CV, fotokopi KTP, dan ijazah terakhir. Konfirmasi kehadiran Anda dengan membalas email ini.</p>\n<p>Salam,<br>{{nama_hr}}<br>HRD {{perusahaan}} — {{kontak_hr}}</p>",
                'Undangan wawancara; isi tanggal & tempat terisi dari jadwal interview.',
                0,
            ],
            [
                'interview_reminder',
                'Pengingat Jadwal Interview',
                'Pengingat: interview {{posisi}} pada {{tanggal_interview}}',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Mengingatkan jadwal wawancara Anda untuk posisi <b>{{posisi}}</b>:</p>\n<p><b>Hari/Tanggal:</b> {{tanggal_interview}}<br><b>Waktu:</b> {{jam_interview}} WITA<br><b>Tempat:</b> {{lokasi}}<br><b>Tautan daring:</b> {{link_meeting}}</p>\n<p>Bila berhalangan, mohon kabari kami sesegera mungkin.</p>\n<p>Salam,<br>{{nama_hr}}<br>HRD {{perusahaan}}</p>",
                'Dikirim H-1 sebelum jadwal wawancara.',
                0,
            ],
            [
                'offer_letter',
                'Penawaran Kerja',
                'Penawaran bergabung sebagai {{posisi}} di {{perusahaan}}',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Setelah melalui proses seleksi, kami dengan senang hati menawarkan Anda posisi <b>{{posisi}}</b> di {{unit}} — {{perusahaan}}.</p>\n<p>Rincian penawaran (gaji, tunjangan, dan tanggal mulai kerja) kami lampirkan pada surat penawaran terpisah. Mohon konfirmasi kesediaan Anda paling lambat 3 hari kerja.</p>\n<p>Salam,<br>{{nama_hr}}<br>HRD {{perusahaan}} — {{kontak_hr}}</p>",
                'Dikirim saat lamaran masuk tahap Penawaran.',
                0,
            ],
            [
                'hired_welcome',
                'Selamat Bergabung',
                'Selamat bergabung di {{perusahaan}}',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Selamat! Anda resmi diterima sebagai <b>{{posisi}}</b> di {{unit}} — {{perusahaan}}.</p>\n<p>Tim HRD akan menghubungi Anda mengenai berkas kepegawaian dan hari pertama kerja.</p>\n<p>Sampai jumpa,<br>{{nama_hr}}<br>HRD {{perusahaan}}</p>",
                'Dikirim setelah kandidat menerima penawaran.',
                0,
            ],
            [
                'rejection',
                'Pemberitahuan Hasil Seleksi',
                'Hasil seleksi posisi {{posisi}}',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Terima kasih atas waktu dan minat Anda pada posisi <b>{{posisi}}</b> di {{perusahaan}}.</p>\n<p>Setelah pertimbangan yang cermat, kami memutuskan untuk melanjutkan proses dengan kandidat lain yang lebih sesuai dengan kebutuhan saat ini. Data Anda kami simpan, dan kami akan menghubungi Anda bila ada lowongan yang cocok di kemudian hari.</p>\n<p>Sukses selalu untuk Anda.</p>\n<p>Salam,<br>{{nama_hr}}<br>HRD {{perusahaan}}</p>",
                'Pemberitahuan bagi pelamar yang tidak lolos.',
                0,
            ],
        ];

        foreach ($templates as [$code, $name, $subject, $body, $desc, $auto]) {
            $this->db->table('email_templates')->ignore(true)->insert([
                'code'        => $code,
                'name'        => $name,
                'subject'     => $subject,
                'body'        => $body,
                'description' => $desc,
                'auto_send'   => $auto,
                'is_active'   => 1,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }
}
