<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Policy\Akses;
use CodeIgniter\Database\BaseConnection;
use Lokandita\Auth\Waktu;

/**
 * Ujian dan kuis satu mata kuliah (SPEC-LOKAUJIAN.md §6).
 *
 *   draf    butirnya menunjuk ke bank (exam_items) dan mengikuti suntingan di sana;
 *   terbit  isinya SALINAN BEKU (exam_groups, exam_questions, exam_options) yang dibuat saat
 *           diterbitkan; suntingan bank tidak pernah sampai ke sini;
 *   arsip   seperti terbit, tetapi tidak tampil di daftar mahasiswa.
 *
 * lihat() mengembalikan butir dalam bentuk yang sama untuk ketiganya, sehingga pratinjau, ekspor
 * dan halaman mahasiswa memakai satu bentuk.
 *
 * Seperti App\Libraries\Bank, setiap kueri diikat ke mata kuliah yang diminta DAN ke
 * Akses::diKelola().
 */
final class Ujian
{
    private readonly BaseConnection $db;

    private readonly Bank $bank;

    public function __construct(private readonly Akses $akses)
    {
        $this->db   = db_connect();
        $this->bank = new Bank($akses);
    }

    /**
     * Jumlah soal dan poin sebuah daftar butir: total, yang dinilai otomatis (benar/salah dan
     * pilihan ganda) dan yang dinilai dosen (isian dan uraian) — SPEC-LOKAUJIAN.md §6.1.
     *
     * @param list<array<string, mixed>> $butir
     *
     * @return array{soal: int, poin: float, otomatis: float, manual: float}
     */
    public static function ringkasan(array $butir): array
    {
        $r = ['soal' => 0, 'poin' => 0.0, 'otomatis' => 0.0, 'manual' => 0.0];
        foreach ($butir as $b) {
            foreach ($b['jenis'] === 'soal' ? [$b['soal']] : $b['kelompok']['questions'] as $s) {
                $r['soal']++;
                $r['poin'] += (float) $s['points'];
                $r[in_array($s['type'], ['benar_salah', 'pilihan_ganda'], true) ? 'otomatis' : 'manual'] += (float) $s['points'];
            }
        }

        return $r;
    }

    /**
     * Butir sebuah ujian, berurutan, dengan soal atau kelompoknya lengkap.
     *
     * @return list<array<string, mixed>>
     */
    private function butir(int $matkulId, int $ujianId, string $status = 'draf'): array
    {
        if ($status !== 'draf') {
            return self::beku($this->db, $ujianId);
        }
        $baris = $this->db->query('SELECT question_id, group_id FROM exam_items WHERE exam_id = ? ORDER BY position', [$ujianId])->getResultArray();
        $butir = [];
        foreach ($baris as $b) {
            if ($b['question_id'] !== null) {
                $s = $this->bank->soal($matkulId, (int) $b['question_id']);
                if ($s !== null) {
                    $butir[] = ['jenis' => 'soal', 'soal' => $s];
                }
            } else {
                $k = $this->bank->kelompok($matkulId, (int) $b['group_id']);
                if ($k !== null) {
                    $butir[] = ['jenis' => 'kelompok', 'kelompok' => $k];
                }
            }
        }

        return $butir;
    }

    /**
     * Ujian mata kuliah itu, terbaru di atas, masing-masing dengan ringkasan poinnya.
     *
     * @return list<array<string, mixed>>
     */
    public function daftar(int $matkulId, bool $arsip = false): array
    {
        $ujian = $this->db->query(
            "SELECT * FROM exams WHERE course_id = ? AND status " . ($arsip ? "= 'arsip'" : "<> 'arsip'") . " AND {$this->akses->diKelola()} ORDER BY id DESC",
            [$matkulId],
        )->getResultArray();
        foreach ($ujian as &$u) {
            $u['ringkasan']  = self::ringkasan($this->butir($matkulId, (int) $u['id'], (string) $u['status']));
            $u['pengerjaan'] = $this->jumlahPengerjaan((int) $u['id']);
        }

        return $ujian;
    }

    /**
     * Satu ujian dengan butirnya; null bila tidak ada, milik mata kuliah lain, atau di luar hak.
     *
     * @return array<string, mixed>|null
     */
    public function lihat(int $matkulId, int $id): ?array
    {
        $u = $this->db->query(
            "SELECT * FROM exams WHERE id = ? AND course_id = ? AND {$this->akses->diKelola()}",
            [$id, $matkulId],
        )->getRowArray();
        if ($u === null) {
            return null;
        }
        $u['butir']      = $this->butir($matkulId, $id, (string) $u['status']);
        $u['ringkasan']  = self::ringkasan($u['butir']);
        $u['pengerjaan'] = $this->jumlahPengerjaan($id);

        return $u;
    }

    /**
     * Membuat draf ujian dari butir bank. Mengembalikan id ujian, atau kode galat:
     * `tanpa_butir`, `butir_asing` (bukan soal lepas atau kelompok mata kuliah ini),
     * `kelompok_kosong`, `terlalu_banyak` (lebih dari 200 soal).
     *
     * @param array{title: string, kind: string, instructions: string, mode: string, duration_minutes: ?int, shuffle_questions: bool, shuffle_options: bool} $setelan hasil Aturan::ujian()
     * @param list<array{0: 'soal'|'kelompok', 1: int}> $butir   menurut urutan ujian
     * @param array<string, mixed>                      $detail  tambahan untuk baris audit
     */
    public function buat(int $matkulId, array $setelan, array $butir, string $aksi = 'ujian.dibuat', array $detail = []): int|string
    {
        if ($butir === []) {
            return 'tanpa_butir';
        }

        return $this->bank->transaksi(function () use ($matkulId, $setelan, $butir, $aksi, $detail): int|string {
            $jumlah = 0;
            $lihat  = [];
            foreach ($butir as [$jenis, $id]) {
                // Butir yang sama dua kali tidak bermakna: satu soal muncul sekali dalam satu ujian.
                if (isset($lihat[$jenis . $id])) {
                    return 'butir_asing';
                }
                $lihat[$jenis . $id] = true;
                if ($jenis === 'soal') {
                    $s = $this->bank->soal($matkulId, $id);
                    if ($s === null || $s['group_id'] !== null) {
                        return 'butir_asing';
                    }
                    $jumlah++;
                } else {
                    $k = $this->bank->kelompok($matkulId, $id);
                    if ($k === null) {
                        return 'butir_asing';
                    }
                    if ($k['questions'] === []) {
                        return 'kelompok_kosong';
                    }
                    $jumlah += count($k['questions']);
                }
            }
            if ($jumlah > Aturan::SOAL_UJIAN_MAKS) {
                return 'terlalu_banyak';
            }
            $pelaku = $this->akses->identitas()->userId;
            $kini   = Waktu::kini();
            $this->db->table('exams')->insert([
                'course_id' => $matkulId, 'title' => $setelan['title'], 'kind' => $setelan['kind'], 'instructions' => $setelan['instructions'],
                'mode' => $setelan['mode'], 'duration_minutes' => $setelan['duration_minutes'],
                'shuffle_questions' => (int) $setelan['shuffle_questions'], 'shuffle_options' => (int) $setelan['shuffle_options'],
                'status' => 'draf', 'created_by' => $pelaku, 'created_at' => $kini, 'updated_at' => $kini,
            ]);
            $ujianId = (int) $this->db->insertID();
            foreach ($butir as $i => [$jenis, $id]) {
                $this->db->table('exam_items')->insert([
                    'exam_id' => $ujianId, 'position' => $i + 1,
                    'question_id' => $jenis === 'soal' ? $id : null, 'group_id' => $jenis === 'kelompok' ? $id : null,
                ]);
            }
            Audit::catat($aksi, 'ujian', $ujianId, ['judul' => $setelan['title'], 'butir' => count($butir), 'soal' => $jumlah] + $detail, $matkulId);

            return $ujianId;
        });
    }

    private function jumlahPengerjaan(int $ujianId): int
    {
        return (int) $this->db->query('SELECT COUNT(*) AS n FROM attempts WHERE exam_id = ?', [$ujianId])->getRowArray()['n'];
    }

    /**
     * Isi BEKU sebuah ujian terbit, dalam bentuk butir yang sama dengan butir draf. Pemanggil
     * sudah memastikan pengguna berhak atas ujian itu (staf lewat lihat(), mahasiswa lewat
     * App\Libraries\Pengerjaan); fungsi ini sendiri tidak memeriksa hak.
     *
     * id soal, kelompok dan opsi di sini adalah id salinan beku, bukan id bank.
     *
     * @return list<array<string, mixed>>
     */
    public static function beku(BaseConnection $db, int $ujianId): array
    {
        $gambar = static fn (array $b): ?array => $b['image_id'] === null ? null : [
            'id' => (int) $b['image_id'], 'alt' => (string) $b['image_alt'], 'kunci' => (string) $b['g_kunci'], 'jenis' => (string) $b['g_jenis'],
            'sha256' => (string) $b['g_sha256'], 'lebar' => (int) $b['g_lebar'], 'tinggi' => (int) $b['g_tinggi'],
        ];
        $pilih    = 'i.kunci AS g_kunci, i.jenis AS g_jenis, i.sha256 AS g_sha256, i.lebar AS g_lebar, i.tinggi AS g_tinggi';
        $kelompok = [];
        foreach ($db->query("SELECT g.*, {$pilih} FROM exam_groups g LEFT JOIN images i ON i.id = g.image_id WHERE g.exam_id = ?", [$ujianId])->getResultArray() as $g) {
            $kelompok[(int) $g['id']] = ['id' => (int) $g['id'], 'text' => (string) $g['text'], 'image' => $gambar($g), 'questions' => []];
        }
        $opsi = [];
        $baris = $db->query("
            SELECT o.*, {$pilih} FROM exam_options o
            JOIN exam_questions q ON q.id = o.exam_question_id
            LEFT JOIN images i ON i.id = o.image_id
            WHERE q.exam_id = ? ORDER BY o.exam_question_id, o.position", [$ujianId])->getResultArray();
        foreach ($baris as $o) {
            $opsi[(int) $o['exam_question_id']][] = ['id' => (int) $o['id'], 'text' => (string) $o['text'], 'correct' => (bool) $o['correct'], 'image' => $gambar($o)];
        }
        $butir   = [];
        $tempat  = []; // id kelompok beku => indeks di $butir
        $soalnya = $db->query("SELECT q.*, {$pilih} FROM exam_questions q LEFT JOIN images i ON i.id = q.image_id WHERE q.exam_id = ? ORDER BY q.position", [$ujianId])->getResultArray();
        foreach ($soalnya as $q) {
            $soal = [
                'id' => (int) $q['id'], 'group_id' => $q['exam_group_id'] === null ? null : (int) $q['exam_group_id'], 'position' => (int) $q['position'],
                'type' => (string) $q['type'], 'text' => (string) $q['text'], 'points' => (string) $q['points'],
                'answer' => $q['answer'] === null ? null : (bool) $q['answer'], 'image' => $gambar($q), 'options' => $opsi[(int) $q['id']] ?? [],
            ];
            if ($soal['group_id'] === null) {
                $butir[] = ['jenis' => 'soal', 'soal' => $soal];

                continue;
            }
            if (! isset($tempat[$soal['group_id']])) {
                $tempat[$soal['group_id']] = count($butir);
                $butir[]                   = ['jenis' => 'kelompok', 'kelompok' => $kelompok[$soal['group_id']]];
            }
            $butir[$tempat[$soal['group_id']]]['kelompok']['questions'][] = $soal;
        }

        return $butir;
    }

    /**
     * Draf yang boleh diubah; null bila tidak ada, di luar hak, atau bukan draf.
     *
     * @return array<string, mixed>|null
     */
    private function draf(int $matkulId, int $id): ?array
    {
        return $this->db->query(
            "SELECT * FROM exams WHERE id = ? AND course_id = ? AND status = 'draf' AND {$this->akses->diKelola()} FOR UPDATE",
            [$id, $matkulId],
        )->getRowArray();
    }

    /**
     * Menyimpan judul, jenis, petunjuk dan setelan sebuah DRAF.
     *
     * @param array{title: string, kind: string, instructions: string, mode: string, duration_minutes: ?int, shuffle_questions: bool, shuffle_options: bool} $setelan hasil Aturan::ujian()
     */
    public function simpanSetelan(int $matkulId, int $id, array $setelan): bool
    {
        return $this->bank->transaksi(function () use ($matkulId, $id, $setelan): bool {
            if ($this->draf($matkulId, $id) === null) {
                return false;
            }
            $this->db->table('exams')->where('id', $id)->update([
                'title' => $setelan['title'], 'kind' => $setelan['kind'], 'instructions' => $setelan['instructions'],
                'mode' => $setelan['mode'], 'duration_minutes' => $setelan['duration_minutes'],
                'shuffle_questions' => (int) $setelan['shuffle_questions'], 'shuffle_options' => (int) $setelan['shuffle_options'],
                'updated_at' => Waktu::kini(),
            ]);

            return true;
        });
    }

    /**
     * Menulis ulang urutan butir sebuah draf. Semua baris diganti, supaya UNIQUE(exam_id, position)
     * tidak pernah dilanggar di tengah jalan.
     *
     * @param list<array{question_id: ?int, group_id: ?int}> $butir
     */
    private function tulisButir(int $ujianId, array $butir): void
    {
        $this->db->query('DELETE FROM exam_items WHERE exam_id = ?', [$ujianId]);
        foreach (array_values($butir) as $i => $b) {
            $this->db->table('exam_items')->insert(['exam_id' => $ujianId, 'position' => $i + 1, 'question_id' => $b['question_id'], 'group_id' => $b['group_id']]);
        }
        $this->db->table('exams')->where('id', $ujianId)->update(['updated_at' => Waktu::kini()]);
    }

    /**
     * @return list<array{question_id: ?int, group_id: ?int}>
     */
    private function barisButir(int $ujianId): array
    {
        return array_map(
            static fn (array $b): array => ['question_id' => $b['question_id'] === null ? null : (int) $b['question_id'], 'group_id' => $b['group_id'] === null ? null : (int) $b['group_id']],
            $this->db->query('SELECT question_id, group_id FROM exam_items WHERE exam_id = ? ORDER BY position', [$ujianId])->getResultArray(),
        );
    }

    /**
     * Menggeser butir ke-$posisi sebuah draf satu langkah (`naik` atau `turun`), atau membuangnya
     * (`hapus`). Soalnya tetap di bank.
     *
     * @return 'ok'|'tidak_ada'|'terakhir' `terakhir`: butir satu-satunya tidak dibuang (ujian tanpa soal dihapus saja)
     */
    public function ubahButir(int $matkulId, int $id, int $posisi, string $apa): string
    {
        return $this->bank->transaksi(function () use ($matkulId, $id, $posisi, $apa): string {
            if ($this->draf($matkulId, $id) === null) {
                return 'tidak_ada';
            }
            $butir = $this->barisButir($id);
            $i     = $posisi - 1;
            if (! isset($butir[$i])) {
                return 'tidak_ada';
            }
            if ($apa === 'hapus') {
                if (count($butir) === 1) {
                    return 'terakhir';
                }
                array_splice($butir, $i, 1);
            } else {
                $j = $apa === 'naik' ? $i - 1 : $i + 1;
                if (! isset($butir[$j])) {
                    return 'ok'; // sudah di ujung: tidak ada yang berubah
                }
                [$butir[$i], $butir[$j]] = [$butir[$j], $butir[$i]];
            }
            $this->tulisButir($id, $butir);

            return 'ok';
        });
    }

    /**
     * Menambah butir bank ke akhir sebuah draf. Kode galatnya sama dengan buat().
     *
     * @param list<array{0: 'soal'|'kelompok', 1: int}> $tambahan
     *
     * @return 'ok'|'tidak_ada'|'tanpa_butir'|'butir_asing'|'kelompok_kosong'|'terlalu_banyak'
     */
    public function tambahButir(int $matkulId, int $id, array $tambahan): string
    {
        if ($tambahan === []) {
            return 'tanpa_butir';
        }

        return $this->bank->transaksi(function () use ($matkulId, $id, $tambahan): string {
            if ($this->draf($matkulId, $id) === null) {
                return 'tidak_ada';
            }
            $butir  = $this->barisButir($id);
            $ada    = [];
            $jumlah = self::ringkasan($this->butir($matkulId, $id))['soal'];
            foreach ($butir as $b) {
                $ada[$b['question_id'] !== null ? 'soal' . $b['question_id'] : 'kelompok' . $b['group_id']] = true;
            }
            foreach ($tambahan as [$jenis, $butirId]) {
                if (isset($ada[$jenis . $butirId])) {
                    return 'butir_asing'; // satu soal muncul sekali dalam satu ujian
                }
                $ada[$jenis . $butirId] = true;
                if ($jenis === 'soal') {
                    $s = $this->bank->soal($matkulId, $butirId);
                    if ($s === null || $s['group_id'] !== null) {
                        return 'butir_asing';
                    }
                    $jumlah++;
                    $butir[] = ['question_id' => $butirId, 'group_id' => null];
                } else {
                    $k = $this->bank->kelompok($matkulId, $butirId);
                    if ($k === null) {
                        return 'butir_asing';
                    }
                    if ($k['questions'] === []) {
                        return 'kelompok_kosong';
                    }
                    $jumlah += count($k['questions']);
                    $butir[] = ['question_id' => null, 'group_id' => $butirId];
                }
            }
            if ($jumlah > Aturan::SOAL_UJIAN_MAKS) {
                return 'terlalu_banyak';
            }
            $this->tulisButir($id, $butir);

            return 'ok';
        });
    }

    /**
     * Apa yang masih kurang sebelum ujian boleh diterbitkan (SPEC-LOKAUJIAN.md §6.1). Kosong = siap.
     *
     * @param array<string, mixed> $ujian hasil lihat() sebuah draf
     *
     * @return list<array{kode: string, nomor: int}> nomor = nomor soal di ujian, 0 bila bukan tentang satu soal
     */
    public static function kekurangan(array $ujian): array
    {
        $kurang = [];
        $nomor  = 0;
        foreach ($ujian['butir'] as $b) {
            foreach ($b['jenis'] === 'soal' ? [$b['soal']] : $b['kelompok']['questions'] as $s) {
                $nomor++;
                if ($s['type'] === 'pilihan_ganda') {
                    $benar = count(array_filter($s['options'], static fn (array $o): bool => (bool) $o['correct']));
                    if ($benar !== 1 || count($s['options']) < Aturan::OPSI_MIN) {
                        $kurang[] = ['kode' => 'satuBenar', 'nomor' => $nomor];
                    }
                } elseif ($s['type'] === 'benar_salah' && $s['answer'] === null) {
                    $kurang[] = ['kode' => 'tanpaKunci', 'nomor' => $nomor];
                }
            }
        }
        if ($nomor === 0) {
            $kurang[] = ['kode' => 'tanpaSoal', 'nomor' => 0];
        }
        if ($ujian['mode'] === 'per_mahasiswa' && $ujian['duration_minutes'] === null) {
            $kurang[] = ['kode' => 'tanpaDurasi', 'nomor' => 0];
        }

        return $kurang;
    }

    /**
     * Menerbitkan sebuah draf: setiap soal, opsi, kunci, poin dan rujukan gambar DISALIN ke
     * tabel beku. Sesudah ini suntingan di bank tidak pernah sampai ke ujian ini.
     *
     * @return 'ok'|'tidak_ada'|list<array{kode: string, nomor: int}> daftar kekurangan bila ditolak
     */
    public function terbitkan(int $matkulId, int $id): string|array
    {
        return $this->bank->transaksi(function () use ($matkulId, $id): string|array {
            if ($this->draf($matkulId, $id) === null) {
                return 'tidak_ada';
            }
            $ujian  = (array) $this->lihat($matkulId, $id);
            $kurang = self::kekurangan($ujian);
            if ($kurang !== []) {
                return $kurang;
            }
            $posisi = 0;
            $salin  = function (array $s, ?int $kelompokBeku) use ($id, &$posisi): void {
                $this->db->table('exam_questions')->insert([
                    'exam_id' => $id, 'position' => ++$posisi, 'exam_group_id' => $kelompokBeku, 'type' => $s['type'], 'text' => $s['text'],
                    'points' => $s['points'], 'answer' => $s['answer'] === null ? null : (int) $s['answer'],
                    'image_id' => $s['image']['id'] ?? null, 'image_alt' => $s['image']['alt'] ?? null, 'source_question_id' => $s['id'],
                ]);
                $soalBeku = (int) $this->db->insertID();
                foreach ($s['options'] as $i => $o) {
                    $this->db->table('exam_options')->insert([
                        'exam_question_id' => $soalBeku, 'position' => $i + 1, 'text' => $o['text'], 'correct' => (int) $o['correct'],
                        'image_id' => $o['image']['id'] ?? null, 'image_alt' => $o['image']['alt'] ?? null,
                    ]);
                }
            };
            foreach ($ujian['butir'] as $b) {
                if ($b['jenis'] === 'soal') {
                    $salin($b['soal'], null);

                    continue;
                }
                $k = $b['kelompok'];
                $this->db->table('exam_groups')->insert([
                    'exam_id' => $id, 'text' => $k['text'], 'image_id' => $k['image']['id'] ?? null, 'image_alt' => $k['image']['alt'] ?? null, 'source_group_id' => $k['id'],
                ]);
                $kelompokBeku = (int) $this->db->insertID();
                foreach ($k['questions'] as $s) {
                    $salin($s, $kelompokBeku);
                }
            }
            $kini = Waktu::kini();
            $this->db->table('exams')->where('id', $id)->update(['status' => 'terbit', 'published_at' => $kini, 'published_by' => $this->akses->identitas()->userId, 'updated_at' => $kini]);
            Audit::catat('ujian.diterbitkan', 'ujian', $id, ['judul' => $ujian['title'], 'soal' => $posisi], $matkulId);

            return 'ok';
        });
    }

    /**
     * Baris ujian yang dikunci untuk perubahan status, dengan jumlah pengerjaannya.
     *
     * @return array<string, mixed>|null
     */
    private function kunciUjian(int $matkulId, int $id): ?array
    {
        $u = $this->db->query("SELECT * FROM exams WHERE id = ? AND course_id = ? AND {$this->akses->diKelola()} FOR UPDATE", [$id, $matkulId])->getRowArray();
        if ($u !== null) {
            $u['pengerjaan'] = $this->jumlahPengerjaan($id);
        }

        return $u;
    }

    /**
     * Menarik ujian terbit kembali ke draf. Hanya selama belum ada satu pun pengerjaan
     * (SPEC-LOKAUJIAN.md §6.1). Salinan bekunya dibuang; sesi yang sudah dibuat tetap ada dan
     * tampil lagi bagi mahasiswa ketika ujian diterbitkan ulang.
     *
     * @return 'ok'|'tidak_ada'|'ada_pengerjaan'
     */
    public function tarik(int $matkulId, int $id): string
    {
        return $this->bank->transaksi(function () use ($matkulId, $id): string {
            $u = $this->kunciUjian($matkulId, $id);
            if ($u === null || $u['status'] !== 'terbit') {
                return 'tidak_ada';
            }
            if ($u['pengerjaan'] > 0) {
                return 'ada_pengerjaan';
            }
            $this->db->query('DELETE FROM exam_questions WHERE exam_id = ?', [$id]); // opsinya ikut (ON DELETE CASCADE)
            $this->db->query('DELETE FROM exam_groups WHERE exam_id = ?', [$id]);
            $this->db->table('exams')->where('id', $id)->update(['status' => 'draf', 'published_at' => null, 'published_by' => null, 'updated_at' => Waktu::kini()]);
            Audit::catat('ujian.ditarik', 'ujian', $id, ['judul' => $u['title']], $matkulId);

            return 'ok';
        });
    }

    /**
     * Mengarsipkan ujian terbit, atau mengembalikannya. Ditolak selama masih ada pengerjaan yang
     * berjalan: mahasiswa yang sedang mengerjakan tidak boleh kehilangan ujiannya dari daftar.
     *
     * @return 'ok'|'tidak_ada'|'sedang_berjalan'
     */
    public function arsipkan(int $matkulId, int $id, bool $arsip): string
    {
        return $this->bank->transaksi(function () use ($matkulId, $id, $arsip): string {
            $u = $this->kunciUjian($matkulId, $id);
            if ($u === null || $u['status'] !== ($arsip ? 'terbit' : 'arsip')) {
                return 'tidak_ada';
            }
            if ($arsip) {
                $jalan = (int) $this->db->query(
                    'SELECT COUNT(*) AS n FROM attempts a JOIN sessions s ON s.id = a.session_id JOIN exams x ON x.id = a.exam_id WHERE a.exam_id = ? AND ' . Tenggat::berjalan(),
                    [$id],
                )->getRowArray()['n'];
                if ($jalan > 0) {
                    return 'sedang_berjalan';
                }
            }
            $this->db->table('exams')->where('id', $id)->update(['status' => $arsip ? 'arsip' : 'terbit', 'updated_at' => Waktu::kini()]);
            Audit::catat($arsip ? 'ujian.diarsipkan' : 'ujian.dipulihkan', 'ujian', $id, ['judul' => $u['title']], $matkulId);

            return 'ok';
        });
    }

    /**
     * Menghapus ujian yang BELUM punya pengerjaan, apa pun statusnya; sesi-sesinya ikut terhapus.
     * Ujian yang punya pengerjaan tidak pernah dihapus, hanya diarsipkan (SPEC-LOKAUJIAN.md §6.1).
     * Soal-soalnya tetap di bank.
     *
     * @return 'ok'|'tidak_ada'|'ada_pengerjaan'
     */
    public function hapus(int $matkulId, int $id): string
    {
        return $this->bank->transaksi(function () use ($matkulId, $id): string {
            $u = $this->lihat($matkulId, $id);
            if ($u === null || $this->kunciUjian($matkulId, $id) === null) {
                return 'tidak_ada';
            }
            if ($u['pengerjaan'] > 0) {
                return 'ada_pengerjaan';
            }
            $this->db->query('DELETE FROM sessions WHERE exam_id = ?', [$id]);
            $this->db->table('exams')->where('id', $id)->where('course_id', $matkulId)->delete(); // butir dan salinan bekunya ikut (ON DELETE CASCADE)
            Audit::catat('ujian.dihapus', 'ujian', $id, ['judul' => $u['title'], 'soal' => $u['ringkasan']['soal']], $matkulId);

            return 'ok';
        });
    }
}
