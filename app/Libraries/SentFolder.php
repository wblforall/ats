<?php

namespace App\Libraries;

/**
 * Simpan salinan email yang sudah terkirim ke folder Sent kotak surat pengirim.
 *
 * Kiriman SMTP tidak meninggalkan jejak di kotak surat, sehingga tanpa ini
 * email ke pelamar tidak terlihat dari webmail/Outlook. Ditulis langsung lewat
 * socket IMAP (LOGIN + APPEND) karena ekstensi imap tidak tersedia di PHP 8.4.
 *
 * Bersifat terbaik-usaha: gagal menyimpan hanya dicatat ke log, tidak pernah
 * menggagalkan pengiriman yang sudah berhasil.
 */
class SentFolder
{
    private const TIMEOUT = 10;

    /** @param string $raw Pesan mentah lengkap (header + isi) persis seperti dikirim. */
    public function save(string $raw): bool
    {
        $config = config('Email');
        if ($config->IMAPHost === '' || $config->SMTPUser === '') {
            return false;
        }

        $raw = preg_replace("/\r?\n/", "\r\n", rtrim($raw)) . "\r\n";

        try {
            $socket = @stream_socket_client(
                'ssl://' . $config->IMAPHost . ':' . $config->IMAPPort,
                $errno,
                $errstr,
                self::TIMEOUT
            );
            if (! $socket) {
                throw new \RuntimeException("koneksi gagal: {$errstr}");
            }
            stream_set_timeout($socket, self::TIMEOUT);

            $this->expectOk($socket, null);
            $this->command($socket, 'a1', 'LOGIN ' . $this->quote($config->SMTPUser) . ' ' . $this->quote($config->SMTPPass));
            $this->command(
                $socket,
                'a2',
                'APPEND ' . $this->quote($config->IMAPSentFolder) . ' (\Seen) {' . strlen($raw) . "+}\r\n" . $raw
            );
            fwrite($socket, "a3 LOGOUT\r\n");
            fclose($socket);

            return true;
        } catch (\Throwable $e) {
            log_message('warning', 'Salinan ke folder Sent gagal: {msg}', ['msg' => $e->getMessage()]);

            return false;
        }
    }

    /** @param resource $socket */
    private function command($socket, string $tag, string $command): void
    {
        fwrite($socket, "{$tag} {$command}\r\n");
        $this->expectOk($socket, $tag);
    }

    /**
     * Baca balasan sampai baris bertanda $tag (atau salam pembuka bila null).
     *
     * @param resource $socket
     */
    private function expectOk($socket, ?string $tag): void
    {
        while (($line = fgets($socket, 8192)) !== false) {
            if ($tag === null) {
                if (str_starts_with($line, '* OK')) {
                    return;
                }

                break;
            }
            if (str_starts_with($line, $tag . ' ')) {
                if (str_starts_with($line, $tag . ' OK')) {
                    return;
                }

                break;
            }
        }

        throw new \RuntimeException(trim($line ?: 'koneksi terputus'));
    }

    private function quote(string $value): string
    {
        return '"' . addcslashes($value, '"\\') . '"';
    }
}
