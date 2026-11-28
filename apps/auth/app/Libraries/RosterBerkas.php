<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * Roster dari berkas CSV (SPEC.md §17, KONTRAK.md §13): penugasan pengajar dan pendaftaran
 * mahasiswa untuk banyak kelas sekaligus. Dipakai perintah `spark data:pengajar` dan
 * `data:pendaftaran`, yang sampai tahap A1 berada di LokaKode.
 *
 * Laporannya hanya berisi jumlah, nama kelas, nomor baris berkas dan alasan — tidak pernah nama
 * atau nomor induk mahasiswa. Aturannya sama dengan halaman kelas: pendaftaran memakai
 * RosterDaftar::golongkan/jalankan, penugasan memakai Roster::dosenAktif.
 */
final class RosterBerkas
{
    /**
     * CSV dengan baris judul; pemisah koma atau titik koma; BOM dan CRLF diterima.
     * Setiap baris mendapat kunci `_baris` (baris judul = 1).
     *
     * @param list<string> $wajib nama kolom yang harus ada
     *
     * @return list<array<string, string|int>>
     *
     * @throws \RuntimeException dengan pesan yang menyebut apa yang salah
     */
    public static function bacaCsv(string $teks, array $wajib): array
    {
        $teks   = (string) preg_replace('/^\xEF\xBB\xBF/', '', $teks);
        $garis  = preg_split('/\r\n|\r|\n/', $teks) ?: [];
        $judul  = (string) array_shift($garis);
        $pisah  = substr_count($judul, ';') > substr_count($judul, ',') ? ';' : ',';
        $kolom  = array_map(static fn ($k) => strtolower(trim((string) $k)), str_getcsv($judul, $pisah, '"', ''));
        $kurang = array_diff($wajib, $kolom);
        if ($kurang !== []) {
            throw new \RuntimeException('kolom tidak ada di baris judul: ' . implode(', ', $kurang) . '. Kolom yang dibutuhkan: ' . implode(', ', $wajib));
        }
        $baris = [];
        foreach ($garis as $i => $g) {
            if (trim($g) === '') {
                continue;
            }
            $sel = str_getcsv($g, $pisah, '"', '');
            $b   = ['_baris' => $i + 2];
            foreach ($kolom as $k => $nama) {
                $b[$nama] = trim((string) ($sel[$k] ?? ''));
            }
            $baris[] = $b;
        }
        if ($baris === []) {
            throw new \RuntimeException('berkas kosong, atau hanya berisi baris judul');
        }

        return $baris;
    }

    /**
     * Kelas tahun ajaran aktif yang tidak diarsipkan, dikunci "KODE|NAMA".
     *
     * @return array<string, array<string, mixed>>
     */
    public static function kelasAktif(): array
    {
        $hasil = [];
        $baris = Roster::db()->query('
            SELECT k.id, k.course_id, k.academic_year_id, k.name, c.code
            FROM ' . Roster::t('classes') . ' k
            JOIN ' . Roster::t('courses') . ' c ON c.id = k.course_id
            JOIN ' . Roster::t('academic_years') . " t ON t.id = k.academic_year_id
            WHERE t.status = 'active' AND k.archived_at IS NULL ORDER BY c.code, k.name")->getResultArray();
        foreach ($baris as $k) {
            $hasil[self::kunci((string) $k['code'], (string) $k['name'])] = $k;
        }

        return $hasil;
    }

    /**
     * Penugasan pengajar. Kolom: mata_kuliah, kelas, nip_pengajar.
     * Satu baris bermasalah membatalkan seluruh penulisan.
     *
     * @param list<array<string, string|int>> $baris
     *
     * @return array{baris: list<string>, salah: int, ditulis: int}
     */
    public static function pengajar(array $baris, string $pelakuId, bool $tulis): array
    {
        $db     = Roster::db();
        $kelas  = self::kelasAktif();
        $lapor  = [];
        $siap   = [];
        $sudah  = 0;
        $salah  = 0;
        $pernah = [];

        foreach ($baris as $b) {
            $k     = $kelas[self::kunci((string) $b['mata_kuliah'], (string) $b['kelas'])] ?? null;
            $d     = Roster::dosenAktif((string) $b['nip_pengajar']);
            $galat = match (true) {
                $k === null                               => 'kelasTidakAda',
                is_string($d)                             => 'pengajar' . ucfirst($d),
                isset($pernah[$k['id'] . '|' . $d['id']]) => 'barisGanda',
                default                                   => null,
            };
            if ($galat !== null) {
                $salah++;
                $lapor[] = sprintf('  baris %d (%s %s): %s', $b['_baris'], $b['mata_kuliah'], $b['kelas'], $galat);

                continue;
            }
            $pernah[$k['id'] . '|' . $d['id']] = true;
            $ada = (int) $db->query('SELECT COUNT(*) AS n FROM ' . Roster::t('class_teachers') . ' WHERE class_id = ? AND user_id = ?', [$k['id'], $d['id']])->getRow()->n;
            if ($ada > 0) {
                $sudah++;
            } else {
                $siap[] = [$k, $d];
            }
        }
        array_unshift($lapor, sprintf('penugasan baru: %d · sudah ada: %d · bermasalah: %d', count($siap), $sudah, $salah));

        $ditulis = 0;
        if ($tulis && $salah === 0) {
            Roster::mulai();
            foreach ($siap as [$k, $d]) {
                $db->table(Roster::t('class_teachers'))->insert(['class_id' => $k['id'], 'user_id' => $d['id'], 'assigned_by' => $pelakuId, 'assigned_at' => Clock::now()]);
                Roster::audit('pengajar.ditugaskan', $pelakuId, 'user', $d['id'], ['kelas' => $k['name'], 'lewat' => 'cli'], (int) $k['course_id'], (int) $k['id']);
                $ditulis++;
            }
            $db->transComplete();
        }

        return ['baris' => $lapor, 'salah' => $salah, 'ditulis' => $ditulis];
    }

    /**
     * Pendaftaran mahasiswa. Kolom: nomor_induk, mata_kuliah, kelas (kolom lain diabaikan).
     *
     * Yang boleh ada hanya: siap, aktifkanLagi, sudah. Golongan lain (akun tidak ada, bukan
     * mahasiswa, aktif di kelas lain, lulus) membatalkan seluruh penulisan.
     *
     * @param list<array<string, string|int>> $baris
     *
     * @return array{baris: list<string>, salah: int, ditulis: int}
     */
    public static function pendaftaran(array $baris, string $pelakuId, bool $tulis): array
    {
        $kelas       = self::kelasAktif();
        $lapor       = [];
        $salah       = 0;
        $perKelas    = []; // kunci kelas => [nomor induk => nomor baris]
        $matkulOrang = []; // "KODE|nomor" => true, untuk baris ganda dalam satu mata kuliah

        foreach ($baris as $b) {
            $kunci = self::kunci((string) $b['mata_kuliah'], (string) $b['kelas']);
            $nomor = (string) $b['nomor_induk'];
            $ganda = strtoupper((string) $b['mata_kuliah']) . '|' . $nomor;
            $galat = match (true) {
                ! isset($kelas[$kunci])                              => 'kelasTidakAda',
                preg_match('/^[A-Za-z0-9._-]{3,40}$/', $nomor) !== 1 => 'nomorInduk',
                isset($matkulOrang[$ganda])                          => 'gandaDalamMataKuliah',
                default                                              => null,
            };
            if ($galat !== null) {
                $salah++;
                $lapor[] = sprintf('  baris %d (%s %s): %s', $b['_baris'], $b['mata_kuliah'], $b['kelas'], $galat);

                continue;
            }
            $matkulOrang[$ganda]      = true;
            $perKelas[$kunci][$nomor] = (int) $b['_baris'];
        }

        $jumlah  = ['siap' => 0, 'aktifkanLagi' => 0, 'sudah' => 0];
        $ringkas = [];
        foreach ($perKelas as $kunci => $orang) {
            $k        = $kelas[$kunci];
            $golongan = RosterDaftar::golongkan($k, array_map('strval', array_keys($orang)));
            foreach ($golongan as $nama => $isi) {
                if (isset($jumlah[$nama])) {
                    $jumlah[$nama] += count($isi);

                    continue;
                }
                foreach ($isi as $butir) {
                    $salah++;
                    $lapor[] = sprintf('  baris %d (%s %s): %s', $orang[$butir['nomor_induk']], $k['code'], $k['name'], $nama);
                }
            }
            $catatan = 0;
            foreach (['siap', 'aktifkanLagi', 'sudah'] as $n) {
                $catatan += count(array_filter($golongan[$n], static fn ($x) => $x['tanpaAkses'] !== [] || $x['tidakAktif']));
            }
            $ringkas[] = sprintf('  %s %s: %d di berkas — %d baru, %d diaktifkan lagi, %d sudah terdaftar%s', $k['code'], $k['name'], count($orang),
                count($golongan['siap']), count($golongan['aktifkanLagi']), count($golongan['sudah']),
                $catatan > 0 ? " — PERHATIAN: {$catatan} akun tidak aktif atau belum punya akses ke semua aplikasi pembaca roster" : '');
        }
        $lapor = array_merge(
            [sprintf('pendaftaran baru: %d · diaktifkan lagi: %d · sudah terdaftar: %d · bermasalah: %d', $jumlah['siap'], $jumlah['aktifkanLagi'], $jumlah['sudah'], $salah)],
            $ringkas,
            $lapor,
        );

        $ditulis = 0;
        if ($tulis && $salah === 0) {
            foreach ($perKelas as $kunci => $orang) {
                $h = RosterDaftar::jalankan($kelas[$kunci], array_map('strval', array_keys($orang)), $pelakuId, 'cli');
                $ditulis += $h['didaftarkan'] + $h['diaktifkan'];
            }
        }

        return ['baris' => $lapor, 'salah' => $salah, 'ditulis' => $ditulis];
    }

    private static function kunci(string $kode, string $kelas): string
    {
        return strtoupper(trim($kode)) . '|' . trim($kelas);
    }
}
