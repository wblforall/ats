<?php

namespace App\Database\Seeds;

/**
 * Template email untuk jawaban pelamar atas undangan interview. Dipakai oleh
 * migrasi AddInterviewResponse (database yang sudah berjalan) dan
 * EmailTemplateSeeder (instalasi baru) supaya isinya sama.
 */
class InterviewResponseTemplates
{
    public static function all(): array
    {
        return [
            [
                'interview_reschedule_hr',
                'Permintaan Jadwal Ulang (ke HR)',
                'Permintaan jadwal ulang interview: {{nama_kandidat}} ({{posisi}})',
                "<p>Halo {{nama_penerima}},</p>\n<p><b>{{nama_kandidat}}</b> meminta jadwal ulang interview untuk posisi <b>{{posisi}}</b> ({{unit}}, {{kode_lamaran}}).</p>\n<p><b>Jadwal saat ini:</b> {{tanggal_interview}}, {{jam_interview}} WITA<br><b>Usulan pelamar:</b> {{usulan_jadwal}} WITA<br><b>Alasan:</b> {{alasan}}</p>\n<p>Setujui atau tolak permintaan ini di halaman pelamar:<br><a href=\"{{link_pelamar}}\">{{link_pelamar}}</a></p>\n<p>— WBL ATS</p>",
                'Dikirim otomatis ke HR yang menjadwalkan saat pelamar meminta jadwal ulang.',
            ],
            [
                'interview_reschedule_declined',
                'Jadwal Ulang Tidak Disetujui',
                'Jadwal interview {{posisi}} tetap pada {{tanggal_interview}}',
                "<p>Halo {{nama_kandidat}},</p>\n<p>Terima kasih atas kabarnya. Mohon maaf, kami belum dapat memindahkan jadwal wawancara Anda untuk posisi <b>{{posisi}}</b>. Jadwal tetap pada:</p>\n<p><b>Hari/Tanggal:</b> {{tanggal_interview}}<br><b>Waktu:</b> {{jam_interview}} WITA<br><b>Tempat:</b> {{lokasi}}<br><b>Tautan daring:</b> {{link_meeting}}</p>\n{{pesan_hr}}\n<p>Mohon konfirmasi kehadiran Anda lewat tombol di bawah ini.</p>\n{{tombol_jawaban}}\n<p>Salam,<br>{{nama_hr}}<br>HRD {{perusahaan}} — {{kontak_hr}}</p>",
                'Dikirim saat HR menolak permintaan jadwal ulang dari pelamar.',
            ],
            [
                'interview_canceled_hr',
                'Interview Dibatalkan Pelamar (ke HR)',
                'Interview dibatalkan pelamar: {{nama_kandidat}} ({{posisi}})',
                "<p>Halo {{nama_penerima}},</p>\n<p><b>{{nama_kandidat}}</b> membatalkan interview untuk posisi <b>{{posisi}}</b> ({{unit}}, {{kode_lamaran}}) yang dijadwalkan <b>{{tanggal_interview}}, {{jam_interview}} WITA</b>.</p>\n<p><b>Alasan:</b> {{alasan}}</p>\n<p>Jadwal sudah otomatis berstatus Dibatalkan. Tindak lanjut lamaran ini di halaman pelamar:<br><a href=\"{{link_pelamar}}\">{{link_pelamar}}</a></p>\n<p>— WBL ATS</p>",
                'Dikirim otomatis ke HR yang menjadwalkan saat pelamar membatalkan interview.',
            ],
        ];
    }
}
