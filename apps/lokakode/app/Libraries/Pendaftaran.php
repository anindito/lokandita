<?php

declare(strict_types=1);

namespace App\Libraries;

use Lokandita\Auth\Aplikasi;
use Lokandita\Auth\UserDirectory;

/**
 * Pendaftaran mahasiswa ke kelas (SPEC.md §5).
 *
 * Kelas ini TIDAK memeriksa hak pengguna: pemanggilnya wajib sudah mendapat
 * baris kelas dari BaseController::kelasUntukDiubah(). LokaKode tidak pernah
 * membuat akun; nomor induk yang tidak dikenal hanya dilaporkan.
 */
final class Pendaftaran
{
    public const BATAS_BARIS = 2000;

    // Sama dengan pola nomor induk di LokaAuth.
    private const POLA_NOMOR = '/^[A-Za-z0-9._-]{3,40}$/';

    // Golongan yang benar-benar dikerjakan ketika pendaftaran dijalankan.
    public const DIKERJAKAN = ['siap', 'aktifkanLagi'];

    /**
     * Mengambil nomor induk dari teks bebas atau isi CSV: satu per baris, kolom
     * pertama, pemisah koma, titik koma atau tab. Baris judul `nomor_induk` dilewati.
     *
     * @return array{0: list<string>, 1: list<string>} [nomor yang bentuknya sah (tanpa kembaran), baris yang bentuknya salah]
     */
    public static function bacaNomor(string $teks): array
    {
        $teks  = (string) preg_replace('/^\xEF\xBB\xBF/', '', $teks);
        $sah   = [];
        $salah = [];
        foreach (preg_split('/\r\n|\r|\n/', $teks) ?: [] as $baris) {
            $kolom = trim((string) (preg_split('/[,;\t]/', $baris)[0] ?? ''), " \t\"'");
            if ($kolom === '' || strtolower($kolom) === 'nomor_induk') {
                continue;
            }
            if (preg_match(self::POLA_NOMOR, $kolom) === 1) {
                $sah[$kolom] = true;
            } else {
                $salah[mb_substr($kolom, 0, 60)] = true;
            }
        }

        return [array_map('strval', array_keys($sah)), array_map('strval', array_keys($salah))];
    }

    /**
     * Menggolongkan setiap nomor induk untuk kelas tujuan. Tidak menyimpan apa pun.
     *
     *   siap           akan didaftarkan
     *   aktifkanLagi   pernah mengundurkan diri di mata kuliah ini; akan diaktifkan lagi di kelas tujuan
     *   sudah          sudah aktif di kelas ini
     *   kelasLain      aktif di kelas lain mata kuliah yang sama (pakai Pindah kelas)
     *   lulus          sudah lulus di mata kuliah ini pada tahun ajaran ini
     *   statusLain     pendaftarannya berstatus lain (DIPINDAHKAN)
     *   bukanMahasiswa akun bukan mahasiswa
     *   tidakAda       tidak terdaftar di LokaAuth
     *
     * @param array<string, mixed> $kelas baris dari Akademik::kelas()
     * @param list<string>         $nomor
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function golongkan(array $kelas, array $nomor): array
    {
        $hasil = array_fill_keys(['siap', 'aktifkanLagi', 'sudah', 'kelasLain', 'lulus', 'statusLain', 'bukanMahasiswa', 'tidakAda'], []);
        $orang = UserDirectory::byNomorInduk($nomor);
        $ids   = array_column($orang, 'id');
        $akses = UserDirectory::withAccess($ids, Aplikasi::kode());

        $lama = [];
        if ($ids !== []) {
            $baris = db_connect()->query('
                SELECT e.id, e.user_id, e.class_id, e.status, k.name AS kelas_nama
                FROM enrollments e JOIN classes k ON k.id = e.class_id
                WHERE e.course_id = ? AND e.academic_year_id = ? AND e.user_id IN ?',
                [$kelas['course_id'], $kelas['academic_year_id'], $ids])->getResultArray();
            foreach ($baris as $b) {
                $lama[(string) $b['user_id']] = $b;
            }
        }

        foreach ($nomor as $n) {
            $o = $orang[$n] ?? null;
            if ($o === null) {
                $hasil['tidakAda'][] = ['nomor_induk' => $n];

                continue;
            }
            $butir = [
                'nomor_induk' => $n,
                'id'          => $o['id'],
                'nama'        => $o['nama'],
                'tanpaAkses'  => ! isset($akses[$o['id']]),
                'tidakAktif'  => $o['status'] !== 'active',
            ];
            $e = $lama[$o['id']] ?? null;

            if ($o['global_role'] !== 'mahasiswa') {
                $hasil['bukanMahasiswa'][] = $butir;
            } elseif ($e === null) {
                $hasil['siap'][] = $butir;
            } elseif ($e['status'] === 'AKTIF') {
                $golongan           = (int) $e['class_id'] === (int) $kelas['id'] ? 'sudah' : 'kelasLain';
                $hasil[$golongan][] = $butir + ['kelas_nama' => $e['kelas_nama']];
            } elseif ($e['status'] === 'MENGUNDURKAN_DIRI') {
                $hasil['aktifkanLagi'][] = $butir + ['kelas_nama' => $e['kelas_nama'], 'enrollment_id' => (int) $e['id'], 'dari_kelas' => (int) $e['class_id']];
            } elseif ($e['status'] === 'LULUS') {
                $hasil['lulus'][] = $butir + ['kelas_nama' => $e['kelas_nama']];
            } else {
                $hasil['statusLain'][] = $butir + ['kelas_nama' => $e['kelas_nama']];
            }
        }

        return $hasil;
    }

    /**
     * Mendaftarkan golongan `siap` dan mengaktifkan lagi golongan `aktifkanLagi`.
     * Golongan dihitung ulang di sini; isi pratinjau dari peramban tidak dipercaya.
     *
     * @param array<string, mixed> $kelas
     * @param list<string>         $nomor
     *
     * @return array{didaftarkan: int, diaktifkan: int}
     */
    public static function jalankan(array $kelas, array $nomor, string $pelakuId): array
    {
        $db       = db_connect();
        $golongan = self::golongkan($kelas, $nomor);
        $kelasId  = (int) $kelas['id'];
        $matkulId = (int) $kelas['course_id'];

        $db->transStart();
        foreach ($golongan['siap'] as $b) {
            $db->table('enrollments')->insert([
                'user_id' => $b['id'], 'course_id' => $matkulId, 'academic_year_id' => $kelas['academic_year_id'],
                'class_id' => $kelasId, 'status' => 'AKTIF', 'created_by' => $pelakuId,
                'created_at' => Waktu::kini(), 'updated_at' => Waktu::kini(),
            ]);
            Audit::catat('pendaftaran.dibuat', 'user', $b['id'], ['kelas' => $kelas['name'], 'enrollment_id' => (int) $db->insertID()], $matkulId, $kelasId);
        }
        foreach ($golongan['aktifkanLagi'] as $b) {
            $db->table('enrollments')->where(['id' => $b['enrollment_id'], 'status' => 'MENGUNDURKAN_DIRI'])
                ->update(['status' => 'AKTIF', 'class_id' => $kelasId, 'updated_at' => Waktu::kini()]);
            if ($b['dari_kelas'] !== $kelasId) {
                $db->table('enrollment_moves')->insert([
                    'enrollment_id' => $b['enrollment_id'], 'from_class_id' => $b['dari_kelas'], 'to_class_id' => $kelasId,
                    'moved_by' => $pelakuId, 'moved_at' => Waktu::kini(),
                ]);
            }
            Audit::catat('pendaftaran.diaktifkan', 'user', $b['id'], ['kelas' => $kelas['name'], 'enrollment_id' => $b['enrollment_id'], 'dari_kelas' => $b['dari_kelas']], $matkulId, $kelasId);
        }
        $db->transComplete();

        return ['didaftarkan' => count($golongan['siap']), 'diaktifkan' => count($golongan['aktifkanLagi'])];
    }

    /**
     * Mengubah status pendaftaran-pendaftaran DI KELAS INI dari $dari ke $ke.
     * id yang bukan milik kelas ini, atau yang statusnya bukan $dari, dilewati.
     *
     * @param array<string, mixed> $kelas
     * @param list<int>            $ids   id pendaftaran
     * @param array<string, mixed> $kolom kolom tambahan (mis. graduated_on)
     *
     * @return int jumlah yang benar-benar berubah
     */
    public static function ubahStatus(array $kelas, array $ids, string $dari, string $ke, string $aksiAudit, array $kolom = []): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn ($i) => $i > 0)));
        if ($ids === []) {
            return 0;
        }
        $db      = db_connect();
        $kelasId = (int) $kelas['id'];
        $jumlah  = 0;

        $db->transStart();
        $baris = $db->query('SELECT id, user_id FROM enrollments WHERE class_id = ? AND status = ? AND id IN ? FOR UPDATE', [$kelasId, $dari, $ids])->getResultArray();
        foreach ($baris as $b) {
            $db->table('enrollments')->where('id', $b['id'])->update(['status' => $ke, 'updated_at' => Waktu::kini()] + $kolom);
            Audit::catat($aksiAudit, 'user', $b['user_id'], ['kelas' => $kelas['name'], 'enrollment_id' => (int) $b['id']] + $kolom, (int) $kelas['course_id'], $kelasId);
            $jumlah++;
        }
        $db->transComplete();

        return $jumlah;
    }
}
