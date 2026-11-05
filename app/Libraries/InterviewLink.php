<?php

namespace App\Libraries;

/**
 * Tautan jawaban pelamar atas undangan interview (tanpa login).
 *
 * Tautan = id jadwal + tanda tangan HMAC atas response_nonce memakai
 * encryption.key, jadi tidak ada token yang perlu disimpan dan bocornya tabel
 * tidak memberi tautan yang sah. Nonce diganti setiap jadwal berubah, sehingga
 * tombol di email lama otomatis tidak berlaku.
 *
 * Tombol di email hanya MEMBUKA halaman; aksinya tetap lewat POST di halaman
 * itu. Pemindai tautan di layanan email (yang membuka setiap URL) dengan
 * begitu tidak bisa mengonfirmasi atas nama pelamar.
 */
class InterviewLink
{
    public static function newNonce(): string
    {
        return bin2hex(random_bytes(16));
    }

    public static function signature(int $interviewId, string $nonce): string
    {
        return substr(hash_hmac('sha256', "interview:{$interviewId}:{$nonce}", self::key()), 0, 40);
    }

    public static function verify(array $interview, string $signature): bool
    {
        $nonce = (string) ($interview['response_nonce'] ?? '');

        return $nonce !== '' && hash_equals(self::signature((int) $interview['id'], $nonce), $signature);
    }

    /** @param string $aksi '' | 'hadir' | 'ulang' | 'batal' — bagian halaman yang disorot. */
    public static function url(array $interview, string $aksi = ''): string
    {
        $url = site_url('jadwal-interview/' . (int) $interview['id'] . '/'
            . self::signature((int) $interview['id'], (string) $interview['response_nonce']));

        return $aksi === '' ? $url : $url . '?aksi=' . $aksi;
    }

    /**
     * Placeholder email untuk satu jadwal: waktu, tempat, dan tombol jawaban.
     * Satu sumber untuk undangan otomatis maupun email yang dikirim manual HR.
     */
    public static function placeholders(array $interview): array
    {
        helper('ui');
        $at = $interview['scheduled_at'];

        return [
            'tanggal_interview' => tgl($at),
            'jam_interview'     => date('H:i', strtotime($at)),
            'lokasi'            => $interview['location'] ?: '-',
            'link_meeting'      => $interview['meeting_link'] ?: '-',
            'tombol_jawaban'    => self::buttonsHtml($interview),
        ];
    }

    /** Dua tombol untuk badan email; tabel + gaya inline supaya tampil di klien email mana pun. */
    public static function buttonsHtml(array $interview): string
    {
        $hadir = esc(self::url($interview, 'hadir'));
        $ulang = esc(self::url($interview, 'ulang'));
        $link  = esc(self::url($interview));
        $font  = 'font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;text-decoration:none;display:inline-block;padding:11px 20px;border-radius:8px;';

        return '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:18px 0 6px;"><tr>'
            . '<td style="border-radius:8px;background:#16A34A;"><a href="' . $hadir . '" style="' . $font . 'color:#ffffff;background:#16A34A;border:1px solid #16A34A;">Konfirmasi Hadir</a></td>'
            . '<td style="width:10px;"></td>'
            . '<td style="border-radius:8px;background:#ffffff;"><a href="' . $ulang . '" style="' . $font . 'color:#1E5099;background:#ffffff;border:1px solid #2D69CB;">Minta Jadwal Ulang</a></td>'
            . '</tr></table>'
            . '<p style="font-size:12px;color:#6B7280;margin:6px 0 16px;">Tidak bisa hadir sama sekali? <a href="' . esc(self::url($interview, 'batal')) . '" style="color:#6B7280;">Batalkan interview</a> agar kami bisa menyesuaikan jadwal.<br>'
            . 'Tombol tidak berfungsi? Buka tautan ini: ' . $link . '</p>';
    }

    private static function key(): string
    {
        $key = (string) config('Encryption')->key;
        if ($key === '') {
            throw new \RuntimeException('encryption.key belum diatur di .env — tautan jawaban interview butuh kunci ini.');
        }

        return $key;
    }
}
