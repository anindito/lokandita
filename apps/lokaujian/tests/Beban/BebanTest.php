<?php

declare(strict_types=1);

namespace Tests\Beban;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Tests\Support\Beban\Mesin;
use Tests\Support\Beban\Murid;
use Tests\Support\UjianCase;

/**
 * Uji beban LokaUjian (SPEC-LOKAUJIAN.md §14; KONTRAK.md §12): satu angkatan mengerjakan satu
 * ujian sekaligus, lewat HTTP sungguhan ke kontainer tes dengan penegakan SEB aktif, MySQL dan
 * Redis sungguhan, penjadwal sungguhan.
 *
 * HANYA dijalankan lewat infra/uji-beban.sh (suite "Beban"); `infra/tes.sh lokaujian` tidak
 * menjalankannya. Seperti semua tes: hanya skema *_test dan Redis DB 15.
 *
 * Gerbangnya: TIDAK ADA JAWABAN HILANG. Setiap simpan yang dijawab server "200 ok" dicocokkan
 * dengan tabel `answers` dan dengan CSV panjang.
 *
 * Yang TIDAK dibuktikan di sini: peramban dan SEB sungguhan (header SEB dihitung sendiri),
 * jaringan kampus, dan TLS — permintaan masuk lewat Caddy tes dengan HTTP biasa.
 */
final class BebanTest extends UjianCase
{
    private const JEDA_MULAI = 75;   // detik: semua murid masuk dalam 60 detik pertama, lalu dosen menekan Mulai

    private const TAMBAHAN = 2;      // menit tambahan waktu untuk tiga murid

    private string $urlApl = '';

    private string $csrfDosen = '';

    private function env(string $nama, ?string $bawaan = null): string
    {
        $v = getenv($nama);
        if ($v === false || $v === '') {
            if ($bawaan === null) {
                $this->fail("{$nama} tidak ada: uji beban hanya dijalankan lewat infra/uji-beban.sh");
            }

            return $bawaan;
        }

        return (string) $v;
    }

    public function testSeluruhAngkatanMengerjakanSekaligusTanpaJawabanHilang(): void
    {
        $n            = (int) $this->env('BEBAN_N');
        $menit        = (int) $this->env('BEBAN_MENIT', '15');
        $this->urlApl = rtrim($this->env('BEBAN_APP_URL'), '/');
        $urlAuth      = rtrim($this->env('BEBAN_AUTH_URL'), '/');
        $asal         = rtrim((string) getenv('APP_URL'), '/');
        $this->assertGreaterThanOrEqual(2, $n);
        $this->assertGreaterThanOrEqual(2, $menit);

        // ---- dunia: satu mata kuliah, satu kelas, N mahasiswa, satu ujian 40 soal ---------------
        $pengampu = $this->user('dosen', 'Pengampu Beban');
        $tahun    = $this->tahun('2026/2027 Ganjil');
        $matkul   = $this->matkul('BBN101', 'Uji Beban', $pengampu);
        $kelas    = $this->kelas($matkul, $tahun, 'A');
        $dosen    = new Http($this->urlApl);
        $dosen->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $pengampu['id']);

        $butir = [];
        for ($i = 1; $i <= 10; $i++) {
            $butir[] = 's' . $this->buatSoal($dosen, $matkul, 'benar_salah', ['text' => "BS {$i}: pernyataan nomor {$i}"]);
        }
        for ($i = 1; $i <= 20; $i++) {
            $butir[] = 's' . $this->buatSoal($dosen, $matkul, 'pilihan_ganda', ['text' => "PG {$i}: pilih satu", 'opsi_teks[3]' => 'Opsi keempat']);
        }
        for ($i = 1; $i <= 6; $i++) {
            $butir[] = 's' . $this->buatSoal($dosen, $matkul, 'isian', ['text' => "ISIAN {$i}: isi singkat"]);
        }
        for ($i = 1; $i <= 3; $i++) {
            $butir[] = 's' . $this->buatSoal($dosen, $matkul, 'uraian', ['text' => "URAIAN {$i}: jelaskan"]);
        }
        $butir[] = 's' . $this->buatSoal($dosen, $matkul, 'uraian', ['text' => 'URAIAN 4: jelaskan gambar ini', 'alt' => 'Diagram uji'], ['gambar' => ['diagram.png', self::gambar('png', 1200, 800)]]);
        $ujian   = $this->buatDraf($dosen, $matkul, 'Ujian beban', $butir);
        $r       = $this->kirim($dosen, "/mata-kuliah/{$matkul}/ujian/{$ujian}", ['judul' => 'Ujian beban', 'jenis' => 'ujian', 'petunjuk' => 'Uji beban.', 'mode' => 'dikendalikan_dosen', 'durasi' => '', 'acak_soal' => '1', 'acak_opsi' => '1']);
        $this->assertContains($r->status, [302, 303]);
        $this->assertContains($this->kirim($dosen, "/mata-kuliah/{$matkul}/ujian/{$ujian}/terbit")->status, [302, 303]);
        $kunci  = $this->sebSiap($dosen, $matkul, $ujian);
        $sesi   = $this->beri($dosen, $matkul, $ujian, $kelas);
        $jumlah = (int) $this->db->query("SELECT COUNT(*) AS n FROM exam_questions WHERE exam_id = {$ujian}")->fetch_assoc()['n'];
        $this->assertSame(40, $jumlah);
        $this->csrfDosen = $this->csrf($dosen);

        // Satu hash untuk semua akun: yang diukur adalah LOGIN (Argon2id di server), bukan pembuatan akun.
        $sandi = 'Sandi-Beban-' . bin2hex(random_bytes(4));
        $hash  = password_hash($sandi, PASSWORD_ARGON2ID);
        $mesin = new Mesin();
        $murid = [];
        $ujianDetik = $menit * 60;
        for ($i = 0; $i < $n; $i++) {
            $akun = $this->user('mahasiswa', sprintf('Murid Beban %03d', $i + 1), ['password' => $sandi, 'password_hash' => $hash, 'nomor_induk' => sprintf('beban%04d', $i + 1)]);
            $this->daftar($akun, $kelas);
            // Tiap sepuluh murid: satu mengumpulkan sendiri; tiap dua puluh: satu putus sambungan 75
            // detik, satu menutup peramban dan melanjutkan.
            $rencana = [
                'kumpul_pada' => $i % 10 === 3 ? mt_rand((int) ($ujianDetik * 0.6), (int) ($ujianDetik * 0.9)) : null,
                'putus_pada'  => $i % 20 === 7 ? mt_rand((int) ($ujianDetik * 0.2), (int) ($ujianDetik * 0.5)) : null,
                'lanjut_pada' => $i % 20 === 12 ? mt_rand((int) ($ujianDetik * 0.2), (int) ($ujianDetik * 0.6)) : null,
            ];
            $m       = new Murid($mesin, $akun, $urlAuth, $this->urlApl, $asal, $ujian, $sesi, $kunci, self::SEB_BEK, sprintf('198.51.%d.%d', intdiv($i, 250), $i % 250 + 1), $rencana);
            $murid[] = $m;
            $mesin->nanti(mt_rand(0, 60000) / 1000, $m->masuk(...));
        }

        // ---- dosen: tampilan langsung tiap 5 detik, Mulai, tambahan waktu, CSV selagi berjalan ---
        $kueDosen = 'Cookie: lokandita_sid=' . $dosen->cookies['lokandita_sid'] . (isset($dosen->cookies['__Host-csrf']) ? '; __Host-csrf=' . $dosen->cookies['__Host-csrf'] : '');
        $hDosen   = ['X-Forwarded-Proto: https', 'X-Forwarded-For: 198.51.100.200', $kueDosen];
        $langsung = [];
        $selesai  = false;
        $tanya    = function () use (&$tanya, $mesin, $sesi, $hDosen, &$langsung, &$selesai): void {
            if ($selesai) {
                return;
            }
            $mesin->nanti(5, $tanya);
            $mesin->minta('dosen GET /sesi/S/langsung', 'GET', "{$this->urlApl}/sesi/{$sesi}/langsung", null, $hDosen, static function (int $kode, string $isi) use (&$langsung, $mesin): void {
                $j = $kode === 200 ? json_decode($isi, true) : null;
                if (is_array($j)) {
                    $langsung[] = [(int) $mesin->kini()] + $j['ringkas'];
                }
            });
        };
        $tanya();
        $formDosen = function (string $label, string $jalur, array $isian, callable $lalu) use ($mesin, $hDosen): void {
            $mesin->minta($label, 'POST', $this->urlApl . $jalur, http_build_query($isian + ['csrf' => $this->csrfDosen]), [...$hDosen, 'Content-Type: application/x-www-form-urlencoded'], $lalu);
        };
        $catatan = [];
        $mesin->nanti(self::JEDA_MULAI, function () use ($formDosen, $sesi, $menit, &$catatan, $mesin): void {
            $formDosen('dosen POST /sesi/S/mulai', "/sesi/{$sesi}/mulai", ['menit' => (string) $menit], static function (int $kode, string $isi, array $h) use (&$catatan, $mesin): void {
                $catatan['mulai'] = sprintf('detik %.0f: Mulai dijawab %d %s', $mesin->kini(), $kode, $h['location'][0] ?? '');
            });
        });
        $ditambah = array_map(static fn (Murid $m): string => (string) $m->akun['id'], array_slice($murid, 0, min(3, $n)));
        $mesin->nanti(self::JEDA_MULAI + $ujianDetik * 0.4, function () use ($formDosen, $sesi, $ditambah, &$catatan): void {
            $isian = ['mahasiswa' => $ditambah, 'menit' => (string) self::TAMBAHAN];
            $formDosen('dosen POST /sesi/S/tambah-waktu (periksa)', "/sesi/{$sesi}/tambah-waktu", $isian, function (int $kode) use ($formDosen, $sesi, $isian, &$catatan): void {
                $formDosen('dosen POST /sesi/S/tambah-waktu', "/sesi/{$sesi}/tambah-waktu", $isian + ['yakin' => '1'], static function (int $kode2, string $isi, array $h) use ($kode, &$catatan): void {
                    $catatan['tambahan'] = "tambahan waktu: periksa {$kode}, terapkan {$kode2} " . ($h['location'][0] ?? '');
                });
            });
        });
        $mesin->nanti(self::JEDA_MULAI + $ujianDetik * 0.5, function () use ($formDosen, $sesi, &$catatan): void {
            $formDosen('dosen POST /sesi/S/csv (lebar, selagi berjalan)', "/sesi/{$sesi}/csv", ['bentuk' => 'lebar'], static function (int $kode, string $isi) use (&$catatan): void {
                $catatan['csv_berjalan'] = "CSV lebar selagi berjalan: {$kode}, " . strlen($isi) . ' byte';
            });
        });

        // ---- jalan sampai semua halaman berhenti ------------------------------------------------
        $batas = self::JEDA_MULAI + $ujianDetik + self::TAMBAHAN * 60 + 90;
        $lapor = 0.0;
        $mesin->jalan(function () use ($mesin, $murid, $batas, &$lapor): bool {
            $t = $mesin->kini();
            if ($t - $lapor >= 60) {
                $lapor = $t;
                $tahap = array_count_values(array_map(static fn (Murid $m): string => $m->tahap, $murid));
                ksort($tahap);
                fwrite(STDERR, sprintf("  [beban %4.0f dtk] %s\n", $t, json_encode($tahap, JSON_UNESCAPED_UNICODE)));
            }
            if ($t > $batas) {
                return true;
            }
            foreach ($murid as $m) {
                if (! $m->berhenti) {
                    return false;
                }
            }

            return true;
        });
        $selesai   = true;
        $lamaJalan = $mesin->kini();

        // ---- penjadwal sungguhan menandai dan menilai semua pengerjaan ---------------------------
        $belum = -1;
        for ($i = 0; $i < 60 && $belum !== 0; $i++) {
            $belum = (int) $this->db->query("SELECT COUNT(*) AS n FROM attempts WHERE session_id = {$sesi} AND (submitted_at IS NULL OR score IS NULL)")->fetch_assoc()['n'];
            if ($belum !== 0) {
                sleep(1);
            }
        }

        // ---- pembuktian: setiap simpan yang diterima ada di `answers`, persis ----------------------
        $baris = [];
        foreach ($this->db->query("SELECT a.user_id, j.exam_question_id AS q, j.option_id, j.benar, j.text, j.ragu, j.counter FROM answers j JOIN attempts a ON a.id = j.attempt_id WHERE a.session_id = {$sesi}")->fetch_all(MYSQLI_ASSOC) as $b) {
            $baris[$b['user_id']][(int) $b['q']] = $b;
        }
        $hilang    = [];
        $diterima  = 0;
        $terkirim  = 0;
        $janggal   = [];
        $berhenti  = [];
        $tanpaKerja = 0;
        foreach ($murid as $m) {
            $siapa = (string) $m->akun['nomor_induk'];
            $tipe  = array_column($m->soal, 'tipe', 'id');
            if ($m->soal === []) {
                $tanpaKerja++;
            }
            foreach ($m->janggal as $j) {
                $janggal[] = "{$siapa}: {$j}";
            }
            $berhenti[$m->berhenti ? $m->alasanBerhenti : 'belum berhenti (' . $m->tahap . ')'] = ($berhenti[$m->berhenti ? $m->alasanBerhenti : 'belum berhenti (' . $m->tahap . ')'] ?? 0) + 1;
            foreach ($m->terkirim as $kiriman) {
                $terkirim += count($kiriman);
            }
            $diterima += $m->simpanOk;
            foreach ($m->diterima as $q => $nOk) {
                $b = $baris[$m->akun['id']][$q] ?? null;
                if ($b === null) {
                    $hilang[] = "{$siapa} soal {$q}: n={$nOk} diterima, tidak ada baris di answers";

                    continue;
                }
                $c = (int) $b['counter'];
                if ($c < $nOk || ! isset($m->terkirim[$q][$c])) {
                    $hilang[] = "{$siapa} soal {$q}: n={$nOk} diterima, answers.counter={$c}";

                    continue;
                }
                [$nilai, $ragu] = $m->terkirim[$q][$c];
                $sama           = match ($tipe[$q]) {
                    'benar_salah'   => ($nilai === null ? null : (int) ($nilai === 'benar')) === ($b['benar'] === null ? null : (int) $b['benar']) && $b['option_id'] === null,
                    'pilihan_ganda' => $nilai === ($b['option_id'] === null ? null : (int) $b['option_id']),
                    'isian'         => str_replace("\n", ' ', (string) $nilai) === (string) $b['text'],
                    default         => (string) $nilai === (string) $b['text'],
                };
                if (! $sama || (int) $b['ragu'] !== (int) $ragu) {
                    $hilang[] = "{$siapa} soal {$q}: isi answers berbeda dari kiriman n={$c}";
                }
            }
        }

        // ---- CSV sesudah selesai: panjang, lebar, dan "semua kelas" -------------------------------
        $csv = [];
        foreach (['panjang sesi' => ["/sesi/{$sesi}/csv", 'panjang'], 'lebar sesi' => ["/sesi/{$sesi}/csv", 'lebar'], 'panjang semua kelas' => ["/ujian/{$ujian}/csv", 'panjang'], 'lebar semua kelas' => ["/ujian/{$ujian}/csv", 'lebar']] as $nama => [$jalur, $bentuk]) {
            $saat = microtime(true);
            $r    = $dosen->post($jalur, ['bentuk' => $bentuk, 'csrf' => $this->csrfDosen]);
            $isi  = $r->body;
            $rek  = [];
            $f    = fopen('php://memory', 'w+');
            fwrite($f, substr($isi, 0, 3) === "\xEF\xBB\xBF" ? substr($isi, 3) : $isi);
            rewind($f);
            while (($b = fgetcsv($f, null, ';', '"', '')) !== false) {
                $rek[] = $b;
            }
            fclose($f);
            $csv[$nama] = ['kode' => $r->status, 'byte' => strlen($isi), 'baris' => max(0, count($rek) - 1), 'ms' => (int) round((microtime(true) - $saat) * 1000)];
            if ($nama === 'panjang sesi' && $r->status === 200) {
                // Setiap uraian di CSV sama dengan baris answers (yang di atas sudah dicocokkan dengan kiriman).
                // Kolom CSV panjang (KONTRAK.md §24): 1 = nomor induk, 5 = id soal beku, 9 = jawaban.
                $nim = array_column(array_map(static fn (Murid $m): array => $m->akun, $murid), 'id', 'nomor_induk');
                foreach (array_slice($rek, 1) as $b) {
                    $q = (int) $b[5];
                    $u = $nim[$b[1]] ?? null;
                    $d = $u === null ? null : ($baris[$u][$q] ?? null);
                    if ($d !== null && $d['text'] !== null && $d['text'] !== '' && $b[9] !== $d['text']) {
                        $hilang[] = "CSV panjang: {$b[1]} soal {$q} berbeda dari answers";
                    }
                }
            }
        }

        $pengerjaan = $this->db->query("SELECT COUNT(*) AS n, SUM(submitted_at IS NOT NULL) AS kumpul, SUM(score IS NOT NULL) AS nilai, SUM(extra_minutes > 0) AS tambahan FROM attempts WHERE session_id = {$sesi}")->fetch_assoc();
        $cara       = array_column($this->db->query("SELECT COALESCE(submit_how, 'belum') AS c, COUNT(*) AS n FROM attempts WHERE session_id = {$sesi} GROUP BY 1")->fetch_all(MYSQLI_ASSOC), 'n', 'c');
        $peristiwa  = array_column($this->db->query("SELECT kind, COUNT(*) AS n FROM integrity_events WHERE session_id = {$sesi} GROUP BY 1")->fetch_all(MYSQLI_ASSOC), 'n', 'kind');
        $hadirPuncak = $langsung === [] ? [] : array_reduce($langsung, static fn (array $a, array $l): array => ($l['mengerjakan'] ?? 0) > ($a['mengerjakan'] ?? -1) ? $l : $a, []);

        $laporan = [
            'murid' => $n, 'menit' => $menit, 'soal' => $jumlah, 'lama_jalan_detik' => (int) $lamaJalan,
            'simpan_terkirim' => $terkirim, 'simpan_diterima' => $diterima, 'simpan_diulang' => array_sum(array_map(static fn (Murid $m): int => $m->ulang, $murid)),
            'jawaban_hilang' => count($hilang), 'kejanggalan' => count($janggal),
            'murid_tanpa_halaman_ujian' => $tanpaKerja, 'halaman_dibuka_ulang' => array_sum(array_map(static fn (Murid $m): int => $m->lanjut, $murid)),
            'berhenti_karena' => $berhenti, 'pengerjaan' => $pengerjaan, 'cara_kumpul' => $cara, 'belum_ditandai_penjadwal' => $belum,
            'baris_answers' => array_sum(array_map('count', $baris)), 'peristiwa_integritas' => $peristiwa,
            'dosen' => $catatan, 'tampilan_langsung_puncak' => $hadirPuncak, 'tampilan_langsung_jawaban' => count($langsung),
            'csv' => $csv, 'permintaan' => $mesin->ringkas(),
            'galat_pertama' => array_slice($mesin->galat, 0, 40), 'galat_jumlah' => count($mesin->galat),
            'hilang_pertama' => array_slice($hilang, 0, 40), 'janggal_pertama' => array_slice($janggal, 0, 40),
        ];
        $teks = json_encode($laporan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        fwrite(STDERR, "\n== laporan uji beban\n{$teks}\n");

        // ---- gerbang ------------------------------------------------------------------------------
        $this->assertSame(0, $tanpaKerja, 'ada murid yang tidak pernah sampai ke halaman ujian');
        $this->assertSame($n, (int) $pengerjaan['n'], 'setiap murid punya satu pengerjaan');
        $this->assertGreaterThan($n * 10, $diterima, 'murid menyimpan jawaban');
        $this->assertSame([], $hilang, 'JAWABAN HILANG');
        $this->assertSame([], $janggal, 'kejanggalan (jawaban 4xx yang tidak semestinya, pencacah mundur saat lanjut)');
        $this->assertSame(0, $belum, 'penjadwal menandai dan menilai semua pengerjaan dalam 60 detik');
        $this->assertSame(200, $csv['panjang sesi']['kode']);
        $this->assertSame($n * $jumlah, $csv['panjang sesi']['baris'], 'CSV panjang: satu baris per murid per soal');
        $this->assertSame($n, $csv['lebar sesi']['baris']);
        $this->assertSame(200, $csv['panjang semua kelas']['kode']);
        $this->assertSame(200, $csv['lebar semua kelas']['kode']);
    }
}
