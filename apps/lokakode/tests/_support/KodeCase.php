<?php

declare(strict_types=1);

namespace Tests\Support;

use Lokandita\Auth\Testing\Fixtures;
use Lokandita\Auth\Testing\Http;
use Lokandita\Auth\Testing\TestSchema;
use PHPUnit\Framework\TestCase;

/**
 * Dasar tes LokaKode: HTTP sungguhan ke kontainer lokakode-tes, yang membaca
 * sesi dari identity_test dan berbicara dengan auth-tes; SQL langsung ke
 * lokakode_test dan identity_test untuk menyiapkan dan memeriksa data.
 */
abstract class KodeCase extends TestCase
{
    protected \mysqli $db;  // lokakode_test

    protected \mysqli $idn; // identity_test

    protected function setUp(): void
    {
        $this->db  = TestSchema::connect((string) getenv('DB_DSN'));
        $this->idn = TestSchema::connect((string) getenv('TES_IDENTITY_DSN'));
        // Urutan mengikuti kunci asing. Hanya skema *_test (dijaga TestSchema).
        $tabel = ['ui_prefs', 'integrity_events', 'drafts', 'meeting_scores', 'problem_scores', 'submissions', 'meeting_class_windows', 'judge_jobs', 'audit_log', 'problem_instances', 'meeting_problems', 'import_items', 'imports', 'problem_versions', 'problems', 'meeting_concepts', 'course_base_concepts', 'meetings', 'enrollment_moves', 'enrollments', 'class_teachers', 'classes', 'courses', 'academic_years'];
        // Permintaan terakhir peramban tes sebelumnya (simpan draf otomatis) bisa tiba di tengah
        // pengosongan dan membuat kunci asing menolak; diulang sampai tiga kali.
        for ($coba = 1; ; $coba++) {
            try {
                foreach ($tabel as $t) {
                    $this->db->query("DELETE FROM {$t}");
                }
                break;
            } catch (\mysqli_sql_exception $e) {
                if ($coba === 3) {
                    throw $e;
                }
                usleep(300_000);
            }
        }
    }

    /**
     * @param array<string, mixed> $over
     *
     * @return array<string, mixed>
     */
    protected function user(string $peran, string $nama, array $over = []): array
    {
        return Fixtures::user($this->idn, $over + ['global_role' => $peran, 'nama' => $nama, 'mfa_enabled' => $peran === 'superadmin' ? 1 : 0]);
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

    /**
     * Token CSRF klien ini, dari halaman beranda.
     */
    protected function csrf(Http $http): string
    {
        return preg_match('/data-csrf="([^"]+)"/', $http->get('/beranda')->body, $m) === 1 ? $m[1] : '';
    }

    protected function tahun(string $label, string $status = 'active', string $mulai = '2026-08-01', string $akhir = '2027-01-31'): int
    {
        Fixtures::insert($this->db, 'academic_years', [
            'label' => $label, 'starts_on' => $mulai, 'ends_on' => $akhir, 'status' => $status,
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * @param array<string, mixed> $pengampu
     */
    protected function matkul(string $kode, string $nama, array $pengampu): int
    {
        Fixtures::insert($this->db, 'courses', [
            'code' => $kode, 'name' => $nama, 'owner_id' => $pengampu['id'],
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    protected function kelas(int $matkul, int $tahun, string $nama, ?int $kapasitas = null): int
    {
        Fixtures::insert($this->db, 'classes', [
            'course_id' => $matkul, 'academic_year_id' => $tahun, 'name' => $nama, 'capacity' => $kapasitas,
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * @param array<string, mixed> $dosen
     */
    protected function ajar(int $kelas, array $dosen): void
    {
        Fixtures::insert($this->db, 'class_teachers', [
            'class_id' => $kelas, 'user_id' => $dosen['id'], 'assigned_by' => 'uji', 'assigned_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $mahasiswa
     */
    protected function daftar(array $mahasiswa, int $kelas, string $status = 'AKTIF'): int
    {
        $k = $this->row("SELECT course_id, academic_year_id FROM classes WHERE id = {$kelas}");
        Fixtures::insert($this->db, 'enrollments', [
            'user_id' => $mahasiswa['id'], 'course_id' => $k['course_id'], 'academic_year_id' => $k['academic_year_id'],
            'class_id' => $kelas, 'status' => $status, 'created_by' => 'uji',
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * Pertemuan dengan konsep tambahannya.
     *
     * @param list<string> $konsep
     */
    protected function pertemuan(int $matkul, int $nomor, string $bahasa = 'cpp', array $konsep = [], string $judul = 'Pertemuan uji'): int
    {
        Fixtures::insert($this->db, 'meetings', [
            'course_id' => $matkul, 'number' => $nomor, 'title' => $judul, 'language' => $bahasa, 'enabled' => 1,
            'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insert_id;
        foreach ($konsep as $kode) {
            Fixtures::insert($this->db, 'meeting_concepts', ['meeting_id' => $id, 'concept_code' => $kode]);
        }

        return $id;
    }

    /**
     * Satu versi soal, disisipkan langsung (tanpa judge).
     *
     * @param array<string, mixed> $over
     */
    protected function versiBaris(int $soal, int $nomor, string $status = 'PENDING_REVIEW', array $over = []): int
    {
        Fixtures::insert($this->db, 'problem_versions', $over + [
            'problem_id' => $soal, 'version' => $nomor, 'status' => $status, 'language' => 'cpp', 'difficulty' => 'MUDAH',
            'comparison_mode' => 'trimmed', 'time_limit_ms' => 2000, 'samples_count' => 2, 'hidden_count' => 10,
            'params' => '{"n": 100}',
            'translations' => json_encode([
                'id' => ['title' => 'Jumlah Dua Bilangan', 'statement' => 'Diberikan **dua** bilangan sampai {{n}}. <script>alert(1)</script>',
                    'input_format' => 'Satu baris.', 'output_format' => 'Satu bilangan.', 'constraints' => ''],
                'en' => ['title' => 'Sum of Two Numbers', 'statement' => 'Given two numbers.', 'input_format' => '', 'output_format' => '', 'constraints' => '', 'machine' => true],
            ], JSON_UNESCAPED_UNICODE),
            'generator' => "import os\nprint(os.environ['SEED'], os.environ['INDEX'])\n",
            'reference_solution' => "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<'\\n';}\n",
            'concepts' => '["IO_DASAR","VARIABEL_TIPE","ARITMETIKA"]', 'required_in_submission' => '[]',
            'content_hash' => str_repeat('0', 64), 'created_by' => 'uji', 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * Satu soal dengan versi pertamanya.
     *
     * @return array{soal: int, versi: int}
     */
    protected function soalBaris(int $pertemuan, string $slug, string $status = 'PENDING_REVIEW'): array
    {
        Fixtures::insert($this->db, 'problems', ['meeting_id' => $pertemuan, 'slug' => $slug, 'created_at' => gmdate('Y-m-d H:i:s')]);
        $soal = (int) $this->db->insert_id;

        return ['soal' => $soal, 'versi' => $this->versiBaris($soal, 1, $status)];
    }

    // Generator dan solusi sungguhan untuk soal yang benar-benar dikerjakan judge: dua bilangan, jumlahnya.
    protected const GEN_ACAK = "import os, random\nr = random.Random(int(os.environ[\"SEED\"]) * 1000 + int(os.environ[\"INDEX\"]))\nprint(r.randint(1, 1000), r.randint(1, 1000))\n";

    protected const SOLUSI_JUMLAH = "#include <iostream>\nint main(){long a,b;std::cin>>a>>b;std::cout<<a+b<<\"\\n\";}\n";

    /**
     * Soal yang sudah DISETUJUI dan dipakai pertemuannya, dengan generator sungguhan.
     *
     * @param array<string, mixed> $over kolom problem_versions yang diganti
     *
     * @return array{soal: int, versi: int, mp: int}
     */
    protected function soalSiap(int $pertemuan, string $slug, string $tingkat = 'MUDAH', array $over = []): array
    {
        Fixtures::insert($this->db, 'problems', ['meeting_id' => $pertemuan, 'slug' => $slug, 'created_at' => gmdate('Y-m-d H:i:s')]);
        $soal  = (int) $this->db->insert_id;
        $versi = $this->versiBaris($soal, 1, 'APPROVED', $over + ['difficulty' => $tingkat, 'generator' => self::GEN_ACAK, 'reference_solution' => self::SOLUSI_JUMLAH]);
        Fixtures::insert($this->db, 'meeting_problems', ['meeting_id' => $pertemuan, 'problem_id' => $soal, 'problem_version_id' => $versi, 'updated_at' => gmdate('Y-m-d H:i:s')]);

        return ['soal' => $soal, 'versi' => $versi, 'mp' => (int) $this->db->insert_id];
    }

    /**
     * Tes sebuah instans uji: 2 contoh lalu 10 tes tersembunyi, "a b" -> a + b. Angkanya
     * khas, supaya bisa dicari di jawaban server: tes tersembunyi tidak boleh pernah muncul.
     *
     * @return list<array{in: string, out: string}>
     */
    protected static function tesInstans(int $bibit = 0): array
    {
        $tes = [];
        for ($i = 0; $i < 12; $i++) {
            $a     = 900001 + $bibit * 1000 + $i * 13;
            $b     = 800007 + $bibit * 1000 + $i * 17;
            $tes[] = ['in' => "{$a} {$b}\n", 'out' => ($a + $b) . "\n"];
        }

        return $tes;
    }

    /**
     * Instans $mhs untuk soal itu, disisipkan langsung (seperti yang ditulis worker). Mengembalikan id-nya.
     *
     * @param array<string, mixed> $mhs
     * @param array<string, int>   $soal dari soalSiap()
     */
    protected function instans(array $mhs, array $soal, int $bibit = 0): int
    {
        Fixtures::insert($this->db, 'problem_instances', [
            'user_id' => $mhs['id'], 'meeting_problem_id' => $soal['mp'], 'problem_version_id' => $soal['versi'], 'seed' => 1 + $bibit,
            'tests' => json_encode(self::tesInstans($bibit)), 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * Kiriman yang sudah DINILAI, disisipkan langsung bersama pekerjaan judge-nya, lalu nilainya dihitung.
     *
     * @param array<string, mixed> $mhs
     * @param array<string, int>   $soal dari soalSiap()
     */
    protected function kirimanDinilai(array $mhs, array $soal, int $instans, int $lulus, int $total = 10, string $sumber = 'int main(){}'): int
    {
        $job   = Fixtures::uuid();
        $tes   = [];
        for ($i = 0; $i < $total; $i++) {
            $tes[] = ['index' => $i, 'verdict' => $i < $lulus ? 'AC' : 'WA', 'duration_ms' => 2];
        }
        $hasil = ['verdict' => $lulus === $total ? 'AC' : 'WA', 'compile_log' => '', 'tests' => $tes, 'passed' => $lulus, 'total' => $total, 'concepts' => null, 'missing' => []];
        Fixtures::insert($this->db, 'judge_jobs', [
            'id' => $job, 'kind' => 'SUBMIT', 'priority' => 'normal', 'status' => 'DONE', 'payload' => '{}', 'result' => json_encode($hasil),
            'created_by' => $mhs['id'], 'created_at' => gmdate('Y-m-d H:i:s'), 'finished_at' => gmdate('Y-m-d H:i:s'),
        ]);
        Fixtures::insert($this->db, 'submissions', [
            'user_id' => $mhs['id'], 'meeting_problem_id' => $soal['mp'], 'problem_version_id' => $soal['versi'], 'instance_id' => $instans,
            'language' => 'cpp', 'source' => $sumber, 'job_id' => $job, 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insert_id;
        \App\Libraries\Penilaian::selesaikan((string) $mhs['id']);

        return $id;
    }

    /**
     * Melupakan pembatas laju Run dan Submit (Redis DB 15), supaya tes tidak perlu menunggu 10 detik.
     */
    protected function lupakanLaju(): void
    {
        $r = \App\Libraries\Antrean::redis();
        foreach ($r->keys('lokakode:laju:*') as $kunci) {
            $r->del($kunci);
        }
    }

    /**
     * Menandai pertemuan sudah terbit (langsung di basis data), dengan pengaturan latihan yang diganti $over.
     *
     * @param array<string, int|string> $over
     */
    protected function terbit(int $pertemuan, array $over = []): void
    {
        $set = "published_at = UTC_TIMESTAMP(), published_by = 'uji'";
        foreach ($over as $kolom => $nilai) {
            $set .= ", {$kolom} = '" . $this->db->real_escape_string((string) $nilai) . "'";
        }
        $this->db->query("UPDATE meetings SET {$set} WHERE id = {$pertemuan}");
    }

    /**
     * Jendela pertemuan untuk satu kelas; bawaannya dibuka manual.
     *
     * @param array<string, mixed> $over
     */
    protected function jendela(int $pertemuan, int $kelas, array $over = []): int
    {
        $k = $this->row("SELECT course_id FROM classes WHERE id = {$kelas}");
        Fixtures::insert($this->db, 'meeting_class_windows', $over + [
            'meeting_id' => $pertemuan, 'class_id' => $kelas, 'course_id' => $k['course_id'], 'override' => 'open',
            'updated_by' => 'uji', 'created_at' => gmdate('Y-m-d H:i:s'), 'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insert_id;
    }

    /**
     * Isi halaman tanpa bagian yang memang berbeda di setiap permintaan: sisa sesi,
     * token CSRF (diacak tiap kali ditulis), dan jalur yang diminta (dipakai menu
     * akun untuk kembali). Dipakai untuk membuktikan bahwa dua jawaban tidak bisa dibedakan.
     *
     * PERHATIAN: Lokandita\Auth\Testing\Http mengembalikan DIRINYA SENDIRI. Salin isi
     * jawaban pertama (string) sebelum mengirim permintaan kedua; membandingkan dua
     * variabel Http berarti membandingkan satu obyek dengan dirinya sendiri.
     */
    protected static function inti(string $isi, string $jalur): string
    {
        $isi = (string) preg_replace(['/data-sesi-sisa="\d+"/', '/data-csrf="[0-9a-f]+"/', '/name="csrf" value="[0-9a-f]+"/'], ['', 'data-csrf=""', 'name="csrf" value=""'], $isi);

        return str_replace('value="' . $jalur . '"', 'value="JALUR"', $isi);
    }

    /**
     * Menunggu sampai $syarat benar (untuk pekerjaan judge sungguhan); gagal bila lewat $detik.
     */
    protected function tungguSampai(callable $syarat, int $detik = 120, string $apa = 'syarat'): void
    {
        $batas = microtime(true) + $detik;
        while (microtime(true) < $batas) {
            if ($syarat()) {
                return;
            }
            usleep(250_000);
        }
        $this->fail("{$apa} tidak tercapai dalam {$detik} detik");
    }

    /**
     * Satu baris impor yang sudah selesai, tanpa butir.
     *
     * @param array<string, mixed> $pelaku
     */
    protected function imporBaris(string $sumber, array $pelaku, int $matkul, int $pertemuan): string
    {
        $id = Fixtures::uuid();
        Fixtures::insert($this->db, 'imports', [
            'id' => $id, 'source' => $sumber, 'file_name' => 'RAHASIA-IMPOR.json', 'actor_id' => $pelaku['id'], 'course_id' => $matkul,
            'meeting_id' => $pertemuan, 'status' => 'SELESAI', 'created_at' => gmdate('Y-m-d H:i:s'), 'finished_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return $id;
    }

    /**
     * Baris judge_jobs yang sudah selesai, milik $pembuat (null = pekerjaan sistem). Tidak diantrekan.
     *
     * @param array<string, mixed>|null $pembuat
     */
    protected function job(?array $pembuat, string $status = 'DONE'): string
    {
        $id = Fixtures::uuid();
        Fixtures::insert($this->db, 'judge_jobs', [
            'id' => $id, 'kind' => 'RUN', 'priority' => 'normal', 'status' => $status,
            'payload' => '{"language":"c","source":"SUMBER-RAHASIA","stdin":"MASUKAN-RAHASIA"}',
            'progress' => '{"state":"done","current":1,"total":1}',
            'result' => '{"verdict":"OK","stdout":"hasil\\n"}',
            'created_by' => $pembuat['id'] ?? null, 'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        return $id;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function row(string $sql): ?array
    {
        return $this->db->query($sql)->fetch_assoc();
    }

    protected function hitung(string $sql): int
    {
        return (int) $this->db->query($sql)->fetch_row()[0];
    }

    /**
     * Dunia uji baku: dua mata kuliah, tiga kelas, dan satu orang untuk setiap peran lingkup.
     *
     *   UJX101 (pengampuX): kelas XA (pengajarA, mahasiswa mhsA), kelas XB (pengajarB, mahasiswa mhsB)
     *   UJY201 (pengampuY): kelas YA (tanpa pengajar, mahasiswa mhsY)
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
        ];
        $d['tahun']   = $this->tahun('2026/2027 Ganjil');
        $d['matkulX'] = $this->matkul('UJX101', 'Uji Pemrograman X', $d['pengampuX']);
        $d['matkulY'] = $this->matkul('UJY201', 'Uji Pemrograman Y', $d['pengampuY']);
        $d['kelasXA'] = $this->kelas($d['matkulX'], $d['tahun'], 'XA');
        $d['kelasXB'] = $this->kelas($d['matkulX'], $d['tahun'], 'XB');
        $d['kelasYA'] = $this->kelas($d['matkulY'], $d['tahun'], 'YA');
        $this->ajar($d['kelasXA'], $d['pengajarA']);
        $this->ajar($d['kelasXB'], $d['pengajarB']);
        $d['daftarA'] = $this->daftar($d['mhsA'], $d['kelasXA']);
        $d['daftarB'] = $this->daftar($d['mhsB'], $d['kelasXB']);
        $d['daftarY'] = $this->daftar($d['mhsY'], $d['kelasYA']);
        $d['jobA']    = $this->job($d['mhsA']);
        // Pertemuan 1 dan 2 mata kuliah X (C++), pertemuan 1 mata kuliah Y (Python).
        $d['pertemuanX1'] = $this->pertemuan($d['matkulX'], 1, 'cpp', ['IO_DASAR', 'VARIABEL_TIPE', 'ARITMETIKA']);
        $d['pertemuanX2'] = $this->pertemuan($d['matkulX'], 2, 'cpp', ['KONVERSI_TIPE']);
        $d['pertemuanY1'] = $this->pertemuan($d['matkulY'], 1, 'python', ['IO_DASAR', 'VARIABEL_TIPE', 'ARITMETIKA']);
        // Satu soal menunggu tinjauan di pertemuan 1 mata kuliah X, dan dua impor yang sudah selesai.
        ['soal' => $d['soalX'], 'versi' => $d['versiX']] = $this->soalBaris($d['pertemuanX1'], 'jumlah-dua');
        $d['imporWeb']     = $this->imporBaris('web', $d['superadmin'], $d['matkulX'], $d['pertemuanX1']);
        $d['imporSunting'] = $this->imporBaris('sunting', $d['pengampuX'], $d['matkulX'], $d['pertemuanX1']);

        return $d;
    }
}
