<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Dasar tes LokaUjian: HTTP sungguhan ke kontainer lokaujian-tes, yang membaca sesi dari
 * identity_test, berbicara dengan auth-tes, dan membaca roster lewat lima view di lokakode_test
 * sebagai roster_reader.
 *
 * Baris roster disiapkan tes lewat TES_ROSTER_TULIS_DSN (lokakode_app pada lokakode_test);
 * aplikasi yang diuji tidak memegang DSN itu (KONTRAK.md §12).
 */
abstract class UjianCase extends TestCase
{
    protected \mysqli $roster; // lokakode_test, sebagai lokakode_app — hanya untuk menyiapkan baris uji

    protected \mysqli $idn;    // identity_test

    protected \mysqli $db;     // lokaujian_test — skema aplikasi yang diuji

    /** Tabel LokaUjian, anak lebih dulu (urutan penghapusan mengikuti kunci asing). */
    protected const TABEL = ['exam_items', 'exams', 'question_options', 'questions', 'question_groups', 'images', 'audit_log'];

    protected function setUp(): void
    {
        $this->roster = TestSchema::connect((string) getenv('TES_ROSTER_TULIS_DSN'));
        $this->idn    = TestSchema::connect((string) getenv('TES_IDENTITY_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        foreach (['enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'] as $t) {
            $this->roster->query("DELETE FROM {$t}");
        }
        $this->kosongkanBank();
    }

    /**
     * Mengosongkan tabel LokaUjian sebelum setiap tes. Hanya skema *_test
     * (dijaga TestSchema::connect).
     */
    protected function kosongkanBank(): void
    {
        $this->db = TestSchema::connect((string) getenv('DB_DSN'));
        foreach (self::TABEL as $t) {
            $this->db->query("DELETE FROM {$t}");
        }
    }

    /**
     * Sidik seluruh isi tabel LokaUjian: dipakai untuk membuktikan bahwa sebuah permintaan yang
     * ditolak tidak mengubah apa pun.
     */
    protected function sidikBank(bool $denganAudit = true): string
    {
        $sidik = [];
        foreach ($denganAudit ? self::TABEL : array_diff(self::TABEL, ['audit_log']) as $t) {
            $sidik[] = $t . '=' . $this->db->query("CHECKSUM TABLE {$t}")->fetch_assoc()['Checksum']
                . '/' . $this->db->query("SELECT COUNT(*) AS n FROM {$t}")->fetch_assoc()['n'];
        }

        return implode(' ', $sidik);
    }

    protected function csrf(Http $http): string
    {
        return preg_match('/data-csrf="([^"]+)"/', $http->get('/beranda')->body, $m) === 1 ? $m[1] : '';
    }

    /**
     * POST form biasa dengan token CSRF pengguna itu.
     *
     * @param array<string, mixed> $isian
     */
    protected function kirim(Http $http, string $jalur, array $isian = []): Http
    {
        return $http->post($jalur, $isian + ['csrf' => $this->csrf($http)]);
    }

    /**
     * POST multipart dengan beberapa berkas, seperti form soal (gambar soal dan gambar opsi).
     * Isian berlarik ditulis dengan namanya sendiri: 'opsi_teks[0]' => '…'.
     *
     * @param array<string, string>                                $isian
     * @param array<string, array{0: string, 1: string}> $berkas nama isian => [nama berkas, isi]
     */
    protected function kirimBerkas(Http $http, string $jalur, array $isian, array $berkas): Http
    {
        $isian += ['csrf' => $this->csrf($http)];
        $batas  = '----tes' . bin2hex(random_bytes(8));
        $badan  = '';
        foreach ($isian as $nama => $nilai) {
            $badan .= "--{$batas}\r\nContent-Disposition: form-data; name=\"{$nama}\"\r\n\r\n{$nilai}\r\n";
        }
        foreach ($berkas as $nama => [$namaBerkas, $isi]) {
            $badan .= "--{$batas}\r\nContent-Disposition: form-data; name=\"{$nama}\"; filename=\"{$namaBerkas}\"\r\n"
                . "Content-Type: application/octet-stream\r\n\r\n{$isi}\r\n";
        }

        return $http->send('POST', $jalur, $badan . "--{$batas}--\r\n", ['Content-Type' => 'multipart/form-data; boundary=' . $batas]);
    }

    /**
     * Menjalankan `php spark <argumen>` di kontainer tes ini dengan $masukan sebagai stdin.
     * Perintahnya memakai skema *_test yang sama dengan aplikasi yang diuji; berkas gambar yang
     * ditulisnya ada di kontainer ini, bukan di kontainer lokaujian-tes.
     *
     * @param list<string> $argumen
     *
     * @return array{0: int, 1: string, 2: string} kode keluar, stdout, stderr
     */
    protected function spark(array $argumen, string $masukan = ''): array
    {
        $proses = proc_open(['php', 'spark', ...$argumen, '--no-header'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, ROOTPATH);
        $this->assertIsResource($proses);
        // Masukan besar ditulis sambil keluaran dibaca, supaya pipa tidak saling menunggu.
        stream_set_blocking($pipa[1], false);
        stream_set_blocking($pipa[2], false);
        $keluar = '';
        $galat  = '';
        for ($i = 0, $n = strlen($masukan); $i < $n;) {
            $tulis = fwrite($pipa[0], substr($masukan, $i, 65536));
            $i    += $tulis === false ? $n : $tulis;
            $keluar .= (string) stream_get_contents($pipa[1]);
            $galat  .= (string) stream_get_contents($pipa[2]);
        }
        fclose($pipa[0]);
        stream_set_blocking($pipa[1], true);
        stream_set_blocking($pipa[2], true);
        $keluar .= (string) stream_get_contents($pipa[1]);
        $galat  .= (string) stream_get_contents($pipa[2]);

        return [proc_close($proses), $keluar, $galat];
    }

    /**
     * Gambar uji buatan GD: persegi berwarna dengan tulisan, sebagai PNG, JPEG atau WebP.
     */
    protected static function gambar(string $jenis = 'png', int $lebar = 320, int $tinggi = 200, string $tulisan = 'uji'): string
    {
        $g = imagecreatetruecolor($lebar, $tinggi);
        imagefill($g, 0, 0, (int) imagecolorallocate($g, 240, 244, 250));
        imagefilledrectangle($g, 20, 20, $lebar - 20, $tinggi - 20, (int) imagecolorallocate($g, 40, 90, 160));
        imagestring($g, 5, 30, 30, $tulisan, (int) imagecolorallocate($g, 255, 255, 255));
        ob_start();
        match ($jenis) {
            'png'  => imagepng($g),
            'jpeg' => imagejpeg($g, null, 90),
            'webp' => imagewebp($g, null, 90),
        };

        return (string) ob_get_clean();
    }

    /**
     * Membuat satu soal lewat form sungguhan dan mengembalikan id-nya.
     *
     * @param array<string, string>                                $isian  isian form tambahan atau pengganti
     * @param array<string, array{0: string, 1: string}> $berkas
     */
    protected function buatSoal(Http $http, int $matkul, string $tipe, array $isian = [], array $berkas = [], ?int $kelompok = null): int
    {
        $bawaan = ['type' => $tipe, 'text' => 'Soal ' . $tipe . ' ' . bin2hex(random_bytes(3)), 'points' => '1'];
        if ($tipe === 'benar_salah') {
            $bawaan['answer'] = 'benar';
        }
        if ($tipe === 'pilihan_ganda') {
            $bawaan += ['opsi_teks[0]' => 'Opsi pertama', 'opsi_teks[1]' => 'Opsi kedua', 'opsi_teks[2]' => 'Opsi ketiga', 'benar' => '1'];
        }
        if ($kelompok !== null) {
            $bawaan['kelompok'] = (string) $kelompok;
        }
        $r = $this->kirimBerkas($http, "/mata-kuliah/{$matkul}/bank/soal", $isian + $bawaan, $berkas);
        $this->assertContains($r->status, [302, 303], 'membuat soal ' . $tipe . ': ' . substr(strip_tags($r->body), 0, 600));
        $id = (int) $this->db->query('SELECT MAX(id) AS id FROM questions')->fetch_assoc()['id'];
        $this->assertGreaterThan(0, $id);

        return $id;
    }

    /**
     * @param array<string, array{0: string, 1: string}> $berkas
     */
    protected function buatKelompok(Http $http, int $matkul, string $teks, array $berkas = [], string $alt = ''): int
    {
        $r = $this->kirimBerkas($http, "/mata-kuliah/{$matkul}/bank/kelompok", ['text' => $teks, 'alt' => $alt], $berkas);
        $this->assertContains($r->status, [302, 303], 'membuat kelompok: ' . substr(strip_tags($r->body), 0, 600));

        return (int) $this->db->query('SELECT MAX(id) AS id FROM question_groups')->fetch_assoc()['id'];
    }

    /**
     * Draf ujian dari butir bank ("s<id>" / "k<id>"); mengembalikan id ujian.
     *
     * @param list<string> $butir
     */
    protected function buatDraf(Http $http, int $matkul, string $judul, array $butir, string $jenis = 'ujian'): int
    {
        $r = $this->kirim($http, "/mata-kuliah/{$matkul}/ujian", ['judul' => $judul, 'jenis' => $jenis, 'butir' => $butir]);
        $this->assertContains($r->status, [302, 303]);
        $this->assertMatchesRegularExpression('#/ujian/\d+\?pesan=dibuat$#', (string) $r->header('Location'), 'draf tidak dibuat');

        return (int) $this->db->query('SELECT MAX(id) AS id FROM exams')->fetch_assoc()['id'];
    }

    /**
     * Pengguna dengan akses ke LokaUjian.
     *
     * @param array<string, mixed> $over
     *
     * @return array<string, mixed>
     */
    protected function user(string $peran, string $nama, array $over = []): array
    {
        return Fixtures::user($this->idn, $over + [
            'global_role' => $peran, 'nama' => $nama, 'mfa_enabled' => $peran === 'superadmin' ? 1 : 0,
            'apps' => ['portal', 'lokakode', 'lokaujian'],
        ]);
    }

    /**
     * Klien yang sudah masuk sebagai $user (sesi dibuat langsung di identity_test).
     *
     * @param array<string, mixed> $user
     */
    protected function sebagai(array $user): Http
    {
        $http = new Http((string) getenv('APP_TES_URL'));
        $http->cookies['lokandita_sid'] = Fixtures::session($this->idn, (string) $user['id']);

        return $http;
    }

    protected function tamu(): Http
    {
        return new Http((string) getenv('APP_TES_URL'));
    }

    private static function kini(): string
    {
        return gmdate('Y-m-d H:i:s');
    }

    protected function tahun(string $label, string $status = 'active', string $mulai = '2026-08-01', string $akhir = '2027-01-31'): int
    {
        Fixtures::insert($this->roster, 'academic_years', [
            'label' => $label, 'starts_on' => $mulai, 'ends_on' => $akhir, 'status' => $status,
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    /**
     * @param array<string, mixed> $pengampu
     */
    protected function matkul(string $kode, string $nama, array $pengampu): int
    {
        Fixtures::insert($this->roster, 'courses', [
            'code' => $kode, 'name' => $nama, 'owner_id' => $pengampu['id'],
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    protected function kelas(int $matkul, int $tahun, string $nama, bool $diarsipkan = false): int
    {
        Fixtures::insert($this->roster, 'classes', [
            'course_id' => $matkul, 'academic_year_id' => $tahun, 'name' => $nama,
            'archived_at' => $diarsipkan ? self::kini() : null,
            'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    /**
     * @param array<string, mixed> $dosen
     */
    protected function ajar(int $kelas, array $dosen): void
    {
        Fixtures::insert($this->roster, 'class_teachers', [
            'class_id' => $kelas, 'user_id' => $dosen['id'], 'assigned_by' => 'uji', 'assigned_at' => self::kini(),
        ]);
    }

    /**
     * @param array<string, mixed> $mahasiswa
     */
    protected function daftar(array $mahasiswa, int $kelas, string $status = 'AKTIF'): int
    {
        $k = $this->roster->query("SELECT course_id, academic_year_id FROM classes WHERE id = {$kelas}")->fetch_assoc();
        Fixtures::insert($this->roster, 'enrollments', [
            'user_id' => $mahasiswa['id'], 'course_id' => $k['course_id'], 'academic_year_id' => $k['academic_year_id'],
            'class_id' => $kelas, 'status' => $status, 'graduated_on' => $status === 'LULUS' ? '2026-11-01' : null,
            'created_by' => 'uji', 'created_at' => self::kini(), 'updated_at' => self::kini(),
        ]);

        return (int) $this->roster->insert_id;
    }

    /**
     * Isi halaman tanpa bagian yang memang berbeda di setiap permintaan (sisa sesi, token CSRF)
     * dan tanpa jalur yang diminta sendiri. Dipakai untuk membuktikan bahwa dua jawaban tidak
     * bisa dibedakan.
     *
     * PERHATIAN: Lokandita\Auth\Testing\Http mengembalikan DIRINYA SENDIRI. Salin isi jawaban
     * pertama (string) sebelum mengirim permintaan kedua.
     */
    protected static function inti(string $isi, string $jalur): string
    {
        $isi = (string) preg_replace(['/data-sesi-sisa="\d+"/', '/data-csrf="[0-9a-f]+"/', '/name="csrf" value="[0-9a-f]+"/'], ['', 'data-csrf=""', 'name="csrf" value=""'], $isi);

        return str_replace('value="' . $jalur . '"', 'value="JALUR"', $isi);
    }

    /**
     * Dunia uji: dua mata kuliah, masing-masing dengan pengampunya; mata kuliah X punya kelas XA
     * (pengajar A) dan XB (pengajar B), mata kuliah Y punya kelas YA. Ditambah yang TIDAK boleh
     * dihitung sebagai kelas seorang mahasiswa: kelas yang diarsipkan, tahun ajaran yang
     * diarsipkan, dan pendaftaran yang sudah lulus atau mengundurkan diri.
     *
     * @return array<string, mixed>
     */
    protected function dunia(): array
    {
        $d = [
            'superadmin' => $this->user('superadmin', 'Admin Uji'),
            'pengampuX'  => $this->user('dosen', 'Pengampu Xaverius'),
            'pengampuY'  => $this->user('dosen', 'Pengampu Yohana'),
            'pengajarA'  => $this->user('dosen', 'Pengajar Aminah'),
            'pengajarB'  => $this->user('dosen', 'Pengajar Bambang'),
            'mhsA'       => $this->user('mahasiswa', 'Mahasiswa Ani'),
            'mhsB'       => $this->user('mahasiswa', 'Mahasiswa Budi'),
            'mhsY'       => $this->user('mahasiswa', 'Mahasiswa Yudi'),
            'mhsLulus'   => $this->user('mahasiswa', 'Mahasiswa Lulusan'),
            'mhsMundur'  => $this->user('mahasiswa', 'Mahasiswa Mundur'),
            'mhsArsip'   => $this->user('mahasiswa', 'Mahasiswa Kelasarsip'),
            'mhsLama'    => $this->user('mahasiswa', 'Mahasiswa Tahunlama'),
        ];
        $d['tahun']     = $this->tahun('2026/2027 Ganjil');
        $d['tahunLama'] = $this->tahun('2025/2026 Genap', 'archived', '2026-02-01', '2026-07-31');
        $d['matkulX']   = $this->matkul('UJX101', 'Uji Ujian X', $d['pengampuX']);
        $d['matkulY']   = $this->matkul('UJY201', 'Uji Ujian Y', $d['pengampuY']);
        $d['kelasXA']   = $this->kelas($d['matkulX'], $d['tahun'], 'XA');
        $d['kelasXB']   = $this->kelas($d['matkulX'], $d['tahun'], 'XB');
        $d['kelasYA']   = $this->kelas($d['matkulY'], $d['tahun'], 'YA');
        $d['kelasXArsip'] = $this->kelas($d['matkulX'], $d['tahun'], 'XZ-arsip', true);
        $d['kelasXLama']  = $this->kelas($d['matkulX'], $d['tahunLama'], 'XL-lama');
        $this->ajar($d['kelasXA'], $d['pengajarA']);
        $this->ajar($d['kelasXB'], $d['pengajarB']);
        $this->ajar($d['kelasXLama'], $d['pengajarA']);
        $this->daftar($d['mhsA'], $d['kelasXA']);
        $this->daftar($d['mhsB'], $d['kelasXB']);
        $this->daftar($d['mhsY'], $d['kelasYA']);
        $this->daftar($d['mhsLulus'], $d['kelasXA'], 'LULUS');
        $this->daftar($d['mhsMundur'], $d['kelasXA'], 'MENGUNDURKAN_DIRI');
        $this->daftar($d['mhsArsip'], $d['kelasXArsip']);
        $this->daftar($d['mhsLama'], $d['kelasXLama']);

        return $d;
    }
}
