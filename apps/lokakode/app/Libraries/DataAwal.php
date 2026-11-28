<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\ReaderDb;
use Lokandita\Auth\UserDirectory;

/**
 * Pencocokan berkas CSV sumber dengan basis data (SPEC.md §17, KONTRAK.md §13). Hanya membaca.
 * Menugaskan pengajar dan mendaftarkan mahasiswa dari berkas sejak tahap A2 dilakukan di
 * Akun Lokandita: `docker exec -i auth php spark data:pengajar` dan `data:pendaftaran`.
 *
 * Semua fungsi mengembalikan baris laporan yang hanya berisi jumlah, nama kelas,
 * nomor baris berkas dan alasan — tidak pernah nama atau nomor induk mahasiswa.
 */
final class DataAwal
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
        $teks  = (string) preg_replace('/^\xEF\xBB\xBF/', '', $teks);
        $garis = preg_split('/\r\n|\r|\n/', $teks) ?: [];
        $judul = (string) array_shift($garis);
        $pisah = substr_count($judul, ';') > substr_count($judul, ',') ? ';' : ',';
        $kolom = array_map(static fn ($k) => strtolower(trim((string) $k)), str_getcsv($judul, $pisah, '"', ''));
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
        foreach (db_connect()->query("
            SELECT k.id, k.course_id, k.academic_year_id, k.name, c.code
            FROM classes k JOIN courses c ON c.id = k.course_id JOIN academic_years t ON t.id = k.academic_year_id
            WHERE t.status = 'active' AND k.archived_at IS NULL ORDER BY c.code, k.name")->getResultArray() as $k) {
            $hasil[self::kunci((string) $k['code'], (string) $k['name'])] = $k;
        }

        return $hasil;
    }

    /**
     * Mencocokkan sebuah berkas sumber dengan basis data. Hanya membaca.
     *
     * @param 'mahasiswa'|'dosen'|'pengajar'  $jenis
     * @param list<array<string, string|int>> $baris
     *
     * @return array{baris: list<string>, cocok: bool}
     */
    public static function cocokkan(string $jenis, array $baris): array
    {
        return match ($jenis) {
            'mahasiswa' => self::cocokMahasiswa($baris),
            'dosen'     => self::cocokDosen($baris),
            'pengajar'  => self::cocokPengajar($baris),
        };
    }

    /**
     * @param list<array<string, string|int>> $baris
     *
     * @return array{baris: list<string>, cocok: bool}
     */
    private static function cocokMahasiswa(array $baris): array
    {
        $db    = db_connect();
        $kelas = self::kelasAktif();
        $nomor = array_map(static fn ($b) => (string) $b['nomor_induk'], $baris);
        $orang = UserDirectory::byNomorInduk($nomor);
        $akses = UserDirectory::withAccess(array_column($orang, 'id'), Aplikasi::kode());
        $lapor = [];
        $beda  = [];

        // Akun: setiap baris berkas adalah mahasiswa aktif dengan akses ke aplikasi ini.
        $akunBaik = 0;
        foreach ($baris as $b) {
            $o = $orang[(string) $b['nomor_induk']] ?? null;
            $galat = match (true) {
                $o === null                        => 'akunTidakAda',
                $o['global_role'] !== 'mahasiswa'  => 'bukanMahasiswa',
                $o['status'] !== 'active'          => 'akunTidakAktif',
                ! isset($akses[$o['id']])          => 'tanpaAkses',
                default                            => null,
            };
            if ($galat === null) {
                $akunBaik++;
            } else {
                $beda[] = sprintf('  baris %d: %s', $b['_baris'], $galat);
            }
        }
        $unik    = count(array_unique($nomor));
        $akunDb  = self::hitungDirektori('mahasiswa');
        $lapor[] = self::banding('akun mahasiswa: baris berkas ↔ akun yang benar', count($baris), $akunBaik);
        $lapor[] = self::banding('akun mahasiswa: nomor induk unik di berkas ↔ mahasiswa aktif di LokaAuth', $unik, $akunDb);

        // Pendaftaran per kelas: jumlah berkas ↔ AKTIF di kelas itu, dan orangnya sama.
        $perKelas = [];
        foreach ($baris as $b) {
            $perKelas[self::kunci((string) $b['mata_kuliah'], (string) $b['kelas'])][] = $b;
        }
        ksort($perKelas);
        $cocok = $akunBaik === count($baris) && $unik === $akunDb;
        foreach ($perKelas as $kunci => $isi) {
            $k = $kelas[$kunci] ?? null;
            if ($k === null) {
                $cocok   = false;
                $lapor[] = sprintf('  BEDA   kelas %s: %d di berkas, kelasnya tidak ada di tahun ajaran aktif', str_replace('|', ' ', $kunci), count($isi));

                continue;
            }
            $aktif = array_column($db->query("SELECT user_id FROM enrollments WHERE class_id = ? AND status = 'AKTIF'", [$k['id']])->getResultArray(), 'user_id', 'user_id');
            $tepat = 0;
            foreach ($isi as $b) {
                $o = $orang[(string) $b['nomor_induk']] ?? null;
                if ($o !== null && isset($aktif[$o['id']])) {
                    $tepat++;
                } else {
                    $beda[] = sprintf('  baris %d (%s %s): tidakTerdaftarAktifDiKelasIni', $b['_baris'], $k['code'], $k['name']);
                }
            }
            $sama    = $tepat === count($isi) && count($aktif) === count($isi);
            $cocok   = $cocok && $sama;
            $lapor[] = sprintf('  %s kelas %s %s: berkas %d · AKTIF di basis data %d · orang yang sama %d', $sama ? 'COCOK ' : 'BEDA  ', $k['code'], $k['name'], count($isi), count($aktif), $tepat);
        }
        // Tidak boleh ada pendaftaran AKTIF di tahun aktif di luar berkas (mis. sisa kelas uji).
        $semuaAktif = (int) $db->query("
            SELECT COUNT(*) AS n FROM enrollments e JOIN academic_years t ON t.id = e.academic_year_id
            WHERE t.status = 'active' AND e.status = 'AKTIF'")->getRowArray()['n'];
        $lapor[] = self::banding('pendaftaran: baris berkas ↔ semua pendaftaran AKTIF tahun ajaran aktif', count($baris), $semuaAktif);
        $cocok   = $cocok && $semuaAktif === count($baris);

        return ['baris' => array_merge($lapor, $beda), 'cocok' => $cocok && $beda === []];
    }

    /**
     * @param list<array<string, string|int>> $baris
     *
     * @return array{baris: list<string>, cocok: bool}
     */
    private static function cocokDosen(array $baris): array
    {
        $orang = UserDirectory::byNomorInduk(array_map(static fn ($b) => (string) $b['nomor_induk'], $baris));
        $beda  = [];
        $baik  = 0;
        foreach ($baris as $b) {
            $o = $orang[(string) $b['nomor_induk']] ?? null;
            if ($o !== null && $o['global_role'] === 'dosen' && $o['status'] === 'active') {
                $baik++;
            } else {
                $beda[] = sprintf('  baris %d: %s', $b['_baris'], $o === null ? 'akunTidakAda' : ($o['global_role'] !== 'dosen' ? 'bukanDosen' : 'akunTidakAktif'));
            }
        }
        $db = self::hitungDirektori('dosen');

        return [
            'baris' => array_merge([
                self::banding('akun dosen: baris berkas ↔ akun yang benar', count($baris), $baik),
                self::banding('akun dosen: baris berkas ↔ dosen aktif di LokaAuth', count($baris), $db),
            ], $beda),
            'cocok' => $baik === count($baris) && $db === count($baris),
        ];
    }

    /**
     * @param list<array<string, string|int>> $baris
     *
     * @return array{baris: list<string>, cocok: bool}
     */
    private static function cocokPengajar(array $baris): array
    {
        $db    = db_connect();
        $kelas = self::kelasAktif();
        $dosen = UserDirectory::byNomorInduk(array_map(static fn ($b) => (string) $b['nip_pengajar'], $baris));
        $harap = [];
        foreach ($baris as $b) {
            $harap[self::kunci((string) $b['mata_kuliah'], (string) $b['kelas'])][] = $dosen[(string) $b['nip_pengajar']]['id'] ?? ('?' . $b['_baris']);
        }
        ksort($harap);
        $lapor = [];
        $cocok = true;
        foreach ($harap as $kunci => $ids) {
            $k   = $kelas[$kunci] ?? null;
            $ada = $k === null ? [] : array_column($db->query('SELECT user_id FROM class_teachers WHERE class_id = ?', [$k['id']])->getResultArray(), 'user_id');
            sort($ids);
            sort($ada);
            $sama    = $k !== null && $ids === $ada;
            $cocok   = $cocok && $sama;
            $lapor[] = sprintf('  %s kelas %s: berkas %d pengajar · basis data %d%s', $sama ? 'COCOK ' : 'BEDA  ', str_replace('|', ' ', $kunci), count($ids), count($ada),
                $k === null ? ' (kelasnya tidak ada di tahun ajaran aktif)' : ($sama || count($ids) !== count($ada) ? '' : ' (orangnya berbeda)'));
        }
        $semua = (int) $db->query("
            SELECT COUNT(*) AS n FROM class_teachers ct JOIN classes k ON k.id = ct.class_id JOIN academic_years t ON t.id = k.academic_year_id
            WHERE t.status = 'active' AND k.archived_at IS NULL")->getRowArray()['n'];
        $lapor[] = self::banding('penugasan: baris berkas ↔ semua penugasan di kelas aktif', count($baris), $semua);
        $lapor[] = self::banding('kelas: kelas di berkas ↔ kelas aktif tahun ajaran aktif', count($harap), count($kelas));

        return ['baris' => $lapor, 'cocok' => $cocok && $semua === count($baris) && count($harap) === count($kelas)];
    }

    private static function hitungDirektori(string $peran): int
    {
        $stmt = ReaderDb::conn()->prepare("SELECT COUNT(*) FROM user_directory WHERE global_role = ? AND status = 'active'");
        $stmt->execute([$peran]);

        return (int) $stmt->get_result()->fetch_row()[0];
    }

    private static function banding(string $apa, int $berkas, int $db): string
    {
        return sprintf('  %s %s: %d ↔ %d', $berkas === $db ? 'COCOK ' : 'BEDA  ', $apa, $berkas, $db);
    }

    private static function kunci(string $kode, string $kelas): string
    {
        return strtoupper(trim($kode)) . '|' . trim($kelas);
    }
}
