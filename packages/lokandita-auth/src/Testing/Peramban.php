<?php

declare(strict_types=1);

namespace Lokandita\Auth\Testing;

/**
 * Pengendali kecil untuk Chromium tanpa layar lewat Chrome DevTools Protocol
 * (WebSocket), khusus tes. Tanpa pustaka tambahan: yang dipakai hanya
 * membuka halaman, menjalankan JavaScript, dan mengirim tombol serta klik
 * SUNGGUHAN (peristiwa masukan peramban, bukan peristiwa buatan skrip).
 *
 * Perambannya dijalankan infra/tes.sh (peramban-tes) dan hanya mengenal host
 * Lokandita yang dipetakan ke kontainer tes (KONTRAK.md §12): untuk tes LokaKode
 * hanya https://lokakode.lokandita.com; untuk tes Portal juga lokandita.com dan
 * auth.lokandita.com. $asal memilih aplikasi yang dibuka `buka()`.
 */
class Peramban
{
    public const ASAL = 'https://lokakode.lokandita.com';

    // Pengubah tombol CDP.
    public const CTRL = 2;

    public const SHIFT = 8;

    /** @var resource */
    private $soket;

    private int $nomor = 0;

    private string $sesi = '';

    private string $target = '';

    /** @var list<array<string, mixed>> peristiwa CDP yang diterima sambil menunggu jawaban */
    public array $peristiwa = [];

    public function __construct(protected string $asal = self::ASAL)
    {
        $dasar = (string) getenv('PERAMBAN_TES_URL');
        if ($dasar === '') {
            throw new \RuntimeException('PERAMBAN_TES_URL kosong: tes peramban hanya berjalan lewat infra/tes.sh lokakode atau portal.');
        }
        $u = parse_url((string) self::alamatWebSocket($dasar));
        $soket = stream_socket_client("tcp://{$u['host']}:{$u['port']}", $no, $pesan, 10);
        if ($soket === false) {
            throw new \RuntimeException("tidak bisa menyambung ke peramban: {$pesan}");
        }
        $this->soket = $soket;
        stream_set_timeout($this->soket, 30);
        $kunci = base64_encode(random_bytes(16));
        fwrite($this->soket, "GET {$u['path']} HTTP/1.1\r\nHost: {$u['host']}:{$u['port']}\r\nUpgrade: websocket\r\nConnection: Upgrade\r\n"
            . "Sec-WebSocket-Key: {$kunci}\r\nSec-WebSocket-Version: 13\r\n\r\n");
        $kepala = '';
        while (! str_contains($kepala, "\r\n\r\n")) {
            $baris = fgets($this->soket);
            if ($baris === false) {
                throw new \RuntimeException('peramban menutup sambungan saat jabat tangan WebSocket');
            }
            $kepala .= $baris;
        }
        if (! str_starts_with($kepala, 'HTTP/1.1 101')) {
            throw new \RuntimeException('jabat tangan WebSocket ditolak: ' . strtok($kepala, "\r\n"));
        }

        // Satu tab baru dengan konteks peramban sendiri: kuki dan izin tidak terbawa dari tes lain.
        $konteks      = $this->perintah('Target.createBrowserContext', [], false)['browserContextId'];
        $this->target = $this->perintah('Target.createTarget', ['url' => 'about:blank', 'browserContextId' => $konteks], false)['targetId'];
        $this->sesi   = $this->perintah('Target.attachToTarget', ['targetId' => $this->target, 'flatten' => true], false)['sessionId'];
        $this->perintah('Browser.grantPermissions', ['permissions' => ['clipboardReadWrite', 'clipboardSanitizedWrite'], 'origin' => $this->asal, 'browserContextId' => $konteks], false);
        foreach (['Page.enable', 'Runtime.enable', 'Network.enable'] as $m) {
            $this->perintah($m);
        }
        $this->perintah('Emulation.setFocusEmulationEnabled', ['enabled' => true]);
        $this->ukuran(1400, 900);
        // Dipasang sebelum skrip halaman mana pun: mencatat pelanggaran CSP dan galat skrip,
        // dan menjawab "ya" pada window.confirm sambil menghitung berapa kali ia ditanyakan.
        $this->perintah('Page.addScriptToEvaluateOnNewDocument', ['source' => <<<'JS'
            window.__csp = []; window.__galat = []; window.__konfirmasi = 0;
            document.addEventListener('securitypolicyviolation', function (e) { window.__csp.push(e.effectiveDirective + ' ' + e.blockedURI); });
            window.addEventListener('error', function (e) { window.__galat.push(String(e.message)); });
            window.addEventListener('unhandledrejection', function (e) { window.__galat.push('janji: ' + String(e.reason)); });
            window.confirm = function () { window.__konfirmasi += 1; return true; };
            JS]);
    }

    /**
     * Alamat WebSocket peramban, dari GET /json/version. Dibaca sendiri sepanjang
     * Content-Length: Chromium membiarkan sambungannya terbuka, dan file_get_contents
     * akan menunggu 60 detik sampai sambungan itu ditutup.
     */
    private static function alamatWebSocket(string $dasar): string
    {
        $d     = parse_url($dasar);
        $soket = stream_socket_client("tcp://{$d['host']}:{$d['port']}", $no, $pesan, 10);
        if ($soket === false) {
            throw new \RuntimeException("peramban tidak bisa dihubungi di {$dasar}: {$pesan}");
        }
        stream_set_timeout($soket, 10);
        fwrite($soket, "GET /json/version HTTP/1.1\r\nHost: {$d['host']}:{$d['port']}\r\nConnection: close\r\n\r\n");
        $panjang = null;
        while (($baris = fgets($soket)) !== false && rtrim($baris) !== '') {
            if (stripos($baris, 'content-length:') === 0) {
                $panjang = (int) trim(substr($baris, 15));
            }
        }
        $isi = '';
        while ($panjang !== null && strlen($isi) < $panjang && ($potong = fread($soket, $panjang - strlen($isi))) !== false && $potong !== '') {
            $isi .= $potong;
        }
        fclose($soket);
        $versi = json_decode($isi, true, 16, JSON_THROW_ON_ERROR);

        return (string) $versi['webSocketDebuggerUrl'];
    }

    public function __destruct()
    {
        try {
            if ($this->target !== '') {
                // Halaman soal mengirim draf terakhir saat tab ditutup. Di akhir tes kiriman itu
                // akan tiba sesudah tes berikutnya mengosongkan tabel, jadi dimatikan dulu.
                $this->perintah('Runtime.evaluate', ['expression' => 'navigator.sendBeacon = function () { return false; }; true']);
                $this->perintah('Target.closeTarget', ['targetId' => $this->target], false);
            }
        } catch (\Throwable) {
            // Peramban sudah tidak ada: tidak ada yang perlu ditutup.
        }
        if (is_resource($this->soket)) {
            fclose($this->soket);
        }
    }

    // ---- WebSocket ----------------------------------------------------------------

    private function tulis(string $teks, int $opcode = 0x1): void
    {
        $n      = strlen($teks);
        $kepala = chr(0x80 | $opcode);
        if ($n < 126) {
            $kepala .= chr(0x80 | $n);
        } elseif ($n < 65536) {
            $kepala .= chr(0x80 | 126) . pack('n', $n);
        } else {
            $kepala .= chr(0x80 | 127) . pack('J', $n);
        }
        $topeng = random_bytes(4);
        $isi    = $teks ^ str_pad('', $n, $topeng);
        $semua  = $kepala . $topeng . $isi;
        for ($terkirim = 0; $terkirim < strlen($semua);) {
            $w = fwrite($this->soket, substr($semua, $terkirim));
            if ($w === false || $w === 0) {
                throw new \RuntimeException('sambungan ke peramban terputus saat menulis');
            }
            $terkirim += $w;
        }
    }

    private function bacaTepat(int $n): string
    {
        $data = '';
        while (strlen($data) < $n) {
            $potong = fread($this->soket, $n - strlen($data));
            if ($potong === false || $potong === '') {
                $info = stream_get_meta_data($this->soket);

                throw new \RuntimeException($info['timed_out'] ? 'peramban tidak menjawab dalam 30 detik' : 'sambungan ke peramban terputus');
            }
            $data .= $potong;
        }

        return $data;
    }

    /**
     * Satu pesan teks utuh (bingkai lanjutan disambung; ping dijawab).
     */
    private function baca(): string
    {
        $pesan = '';
        while (true) {
            [$b1, $b2] = array_values(unpack('C2', $this->bacaTepat(2)));
            $opcode    = $b1 & 0x0F;
            $n         = $b2 & 0x7F;
            if ($n === 126) {
                $n = unpack('n', $this->bacaTepat(2))[1];
            } elseif ($n === 127) {
                $n = unpack('J', $this->bacaTepat(8))[1];
            }
            $topeng = ($b2 & 0x80) !== 0 ? $this->bacaTepat(4) : '';
            $isi    = $n > 0 ? $this->bacaTepat($n) : '';
            if ($topeng !== '') {
                $isi ^= str_pad('', $n, $topeng);
            }
            if ($opcode === 0x9) {
                $this->tulis($isi, 0xA);

                continue;
            }
            if ($opcode === 0x8) {
                throw new \RuntimeException('peramban menutup sambungan WebSocket');
            }
            if ($opcode === 0xA) {
                continue;
            }
            $pesan .= $isi;
            if (($b1 & 0x80) !== 0) {
                return $pesan;
            }
        }
    }

    // ---- CDP ------------------------------------------------------------------------

    /**
     * @param array<string, mixed> $param
     *
     * @return array<string, mixed>
     */
    public function perintah(string $metode, array $param = [], bool $diTab = true): array
    {
        $id    = ++$this->nomor;
        $pesan = ['id' => $id, 'method' => $metode, 'params' => (object) $param];
        if ($diTab) {
            $pesan['sessionId'] = $this->sesi;
        }
        $this->tulis(json_encode($pesan, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        while (true) {
            $m = json_decode($this->baca(), true, 512, JSON_THROW_ON_ERROR);
            if (($m['id'] ?? null) === $id) {
                if (isset($m['error'])) {
                    throw new \RuntimeException("{$metode}: " . json_encode($m['error']));
                }

                return $m['result'] ?? [];
            }
            if (isset($m['method'])) {
                $this->peristiwa[] = $m;
            }
        }
    }

    /**
     * Menjalankan JavaScript di halaman dan mengembalikan nilainya (janji ditunggu).
     */
    public function js(string $ekspresi): mixed
    {
        $r = $this->perintah('Runtime.evaluate', ['expression' => $ekspresi, 'returnByValue' => true, 'awaitPromise' => true, 'userGesture' => true]);
        if (isset($r['exceptionDetails'])) {
            throw new \RuntimeException('JavaScript: ' . json_encode($r['exceptionDetails']['exception']['description'] ?? $r['exceptionDetails']));
        }

        return $r['result']['value'] ?? null;
    }

    /**
     * Menunggu sampai ekspresi JavaScript bernilai benar; melempar bila lewat $detik.
     */
    public function tunggu(string $ekspresi, float $detik = 20, string $apa = ''): void
    {
        $batas = microtime(true) + $detik;
        do {
            try {
                if ($this->js("!!({$ekspresi})") === true) {
                    return;
                }
            } catch (\RuntimeException $e) {
                // Halaman sedang berganti (dokumennya belum ada): dicoba lagi. Sambungan yang putus tetap dilempar.
                if (! str_starts_with($e->getMessage(), 'JavaScript:')) {
                    throw $e;
                }
            }
            usleep(150_000);
        } while (microtime(true) < $batas);

        throw new \RuntimeException('tidak tercapai dalam ' . $detik . ' detik: ' . ($apa !== '' ? $apa : $ekspresi)
            . ' | galat halaman: ' . json_encode($this->js('window.__galat || []')) . ' | CSP: ' . json_encode($this->js('window.__csp || []')));
    }

    public function kuki(string $nama, string $nilai): void
    {
        $r = $this->perintah('Network.setCookie', ['name' => $nama, 'value' => $nilai, 'domain' => '.lokandita.com', 'path' => '/', 'secure' => true, 'httpOnly' => true, 'sameSite' => 'Lax']);
        if (($r['success'] ?? false) !== true) {
            throw new \RuntimeException("kuki {$nama} tidak terpasang");
        }
    }

    /**
     * Membuka jalur di aplikasi tes ($asal) dan menunggu halamannya selesai dimuat. Mengembalikan status HTTP dokumennya.
     */
    public function buka(string $jalur): int
    {
        $this->peristiwa = [];
        $r               = $this->perintah('Page.navigate', ['url' => $this->asal . $jalur]);
        if (isset($r['errorText'])) {
            throw new \RuntimeException("{$jalur}: {$r['errorText']}");
        }
        $this->tunggu("document.readyState === 'complete'", 30, "memuat {$jalur}");
        foreach ($this->peristiwa as $p) {
            if ($p['method'] === 'Network.responseReceived' && ($p['params']['type'] ?? '') === 'Document') {
                return (int) $p['params']['response']['status'];
            }
        }

        return 0;
    }

    /**
     * Galat navigasi ke alamat mana pun (untuk membuktikan peramban tes tidak bisa menjangkau host lain).
     */
    public function galatBuka(string $url): string
    {
        return (string) ($this->perintah('Page.navigate', ['url' => $url])['errorText'] ?? '');
    }

    /**
     * Tangkapan layar halaman yang sedang terbuka, sebagai isi berkas PNG.
     */
    public function tangkap(?int $lebar = null, ?int $tinggi = null): string
    {
        $param = ['format' => 'png'];
        if ($lebar !== null && $tinggi !== null) {
            // Hanya bagian atas halaman, selebar dan setinggi itu.
            $param['clip'] = ['x' => 0, 'y' => 0, 'width' => $lebar, 'height' => $tinggi, 'scale' => 1];
        }

        return (string) base64_decode((string) $this->perintah('Page.captureScreenshot', $param)['data'], true);
    }

    public function ukuran(int $lebar, int $tinggi): void
    {
        $this->perintah('Emulation.setDeviceMetricsOverride', ['width' => $lebar, 'height' => $tinggi, 'deviceScaleFactor' => 1, 'mobile' => false]);
    }

    public function media(string $media): void
    {
        $this->perintah('Emulation.setEmulatedMedia', ['media' => $media]);
    }

    /**
     * Tombol sungguhan. $perintah: perintah sunting peramban yang biasanya dipicu
     * tombol itu (mis. ['paste'] untuk Ctrl+V), seperti pada papan ketik nyata.
     *
     * @param list<string> $perintah
     */
    public function tekan(string $key, string $code, int $kodeVk, int $pengubah = 0, array $perintah = [], string $teks = ''): void
    {
        $dasar = ['key' => $key, 'code' => $code, 'windowsVirtualKeyCode' => $kodeVk, 'nativeVirtualKeyCode' => $kodeVk, 'modifiers' => $pengubah];
        $turun = $dasar + ['type' => $teks !== '' ? 'keyDown' : 'rawKeyDown'];
        if ($teks !== '') {
            $turun['text'] = $teks;
        }
        if ($perintah !== []) {
            $turun['commands'] = $perintah;
        }
        $this->perintah('Input.dispatchKeyEvent', $turun);
        $this->perintah('Input.dispatchKeyEvent', $dasar + ['type' => 'keyUp']);
    }

    /**
     * Mengetik huruf demi huruf, seperti di papan ketik.
     */
    public function ketik(string $teks): void
    {
        foreach (mb_str_split($teks) as $huruf) {
            if ($huruf === "\n") {
                $this->tekan('Enter', 'Enter', 13, 0, [], "\r");

                continue;
            }
            $this->perintah('Input.dispatchKeyEvent', ['type' => 'keyDown', 'key' => $huruf, 'text' => $huruf]);
            $this->perintah('Input.dispatchKeyEvent', ['type' => 'keyUp', 'key' => $huruf]);
        }
    }

    /**
     * Titik tengah elemen pertama yang cocok dengan $pemilih.
     *
     * @return array{0: float, 1: float}
     */
    public function tengah(string $pemilih): array
    {
        $kotak = $this->js('(function () { var e = document.querySelector(' . json_encode($pemilih) . '); if (!e) { return null; }'
            . ' e.scrollIntoView({ block: "center" }); var k = e.getBoundingClientRect(); return [k.left + k.width / 2, k.top + k.height / 2, k.width, k.height]; })()');
        if (! is_array($kotak) || $kotak[2] <= 0 || $kotak[3] <= 0) {
            throw new \RuntimeException("elemen tidak ada atau tidak terlihat: {$pemilih}");
        }

        return [(float) $kotak[0], (float) $kotak[1]];
    }

    /**
     * Klik sungguhan di tengah elemen. $tombol: left, middle atau right.
     */
    public function klik(string $pemilih, string $tombol = 'left'): void
    {
        [$x, $y] = $this->tengah($pemilih);
        $dasar   = ['x' => $x, 'y' => $y, 'button' => $tombol, 'clickCount' => 1, 'buttons' => ['left' => 1, 'right' => 2, 'middle' => 4][$tombol]];
        $this->perintah('Input.dispatchMouseEvent', ['type' => 'mouseMoved', 'x' => $x, 'y' => $y]);
        $this->perintah('Input.dispatchMouseEvent', $dasar + ['type' => 'mousePressed']);
        $this->perintah('Input.dispatchMouseEvent', ['buttons' => 0] + $dasar + ['type' => 'mouseReleased']);
    }

    /**
     * Menulis teks ke papan klip peramban dan membacanya kembali.
     */
    public function papanKlip(?string $tulis = null): string
    {
        if ($tulis !== null) {
            $this->js('navigator.clipboard.writeText(' . json_encode($tulis) . ').then(function () { return true; })');
        }

        return (string) $this->js('navigator.clipboard.readText()');
    }
}
