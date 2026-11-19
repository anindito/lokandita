<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Instans;
use App\Libraries\Jendela as PustakaJendela;
use App\Libraries\Latihan;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Jendela pertemuan per kelas dan pra-pembangkitan instans (SPEC.md §6, §8.2).
 *
 * Boleh: siapa pun yang melihat kelas itu — superadmin, pengampu mata
 * kuliahnya, pengajar kelas tersebut (SPEC.md §3). Kelas di luar lingkup
 * dijawab 404, sama dengan kelas yang tidak ada. Tahun ajaran yang sudah
 * diarsipkan hanya bisa dibaca oleh dosen.
 */
final class Jendela extends BaseController
{
    private const PESAN = [
        'disimpan'  => ['sukses', 'Latihan.pesan.jendelaDisimpan'],
        'status'    => ['sukses', 'Latihan.pesan.statusDiubah'],
        'disiapkan' => ['sukses', 'Latihan.pesan.disiapkan'],
    ];

    private const TAMPAK = ['hidden', 'review_only'];

    private const TELAT = ['reject', 'multiplier'];

    /**
     * @return array<string, mixed>|ResponseInterface baris kelas, atau penolakan
     */
    private function kelasBoleh(int $kelasId, bool $mengubah): array|ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        if ($mengubah && $kelas['tahun_status'] !== 'active' && ! $this->akses()->superadmin()) {
            return $this->terlarang('Kode.galat.tahunArsip');
        }

        return $kelas;
    }

    public function index(int $kelasId): ResponseInterface
    {
        $kelas = $this->kelasBoleh($kelasId, false);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }

        return $this->halaman('jendela_daftar', [
            'judul'     => lang('Latihan.jendela.judul') . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'     => $kelas,
            'pertemuan' => (new PustakaJendela($this->akses()))->daftar($kelasId),
        ]);
    }

    public function lihat(int $kelasId, int $pertemuanId): ResponseInterface
    {
        $kelas = $this->kelasBoleh($kelasId, false);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $j = (new PustakaJendela($this->akses()))->satu($kelasId, $pertemuanId);
        if ($j === null) {
            return $this->tidakDitemukan('meeting');
        }

        return $this->tampil($kelas, $j, [
            'open_at' => Waktu::keIsian($j['open_at']), 'close_at' => Waktu::keIsian($j['close_at']),
            'post_close_visibility' => $j['post_close_visibility'] ?? 'hidden',
            'late_policy' => $j['late_policy'] ?? 'reject', 'late_factor' => angka((float) ($j['late_factor'] ?? 0.5)),
            'late_until'  => Waktu::keIsian($j['late_until']),
        ], []);
    }

    /**
     * @param array<string, mixed>  $kelas
     * @param array<string, mixed>  $j
     * @param array<string, string> $isi
     * @param array<string, string> $galat
     */
    private function tampil(array $kelas, array $j, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        // Isian yang tidak dikirim permintaan ini (mis. form buka/tutup manual yang salah) diambil dari jendelanya.
        $isi += [
            'open_at' => Waktu::keIsian($j['open_at']), 'close_at' => Waktu::keIsian($j['close_at']),
            'post_close_visibility' => $j['post_close_visibility'] ?? 'hidden',
            'late_policy' => $j['late_policy'] ?? 'reject', 'late_factor' => angka((float) ($j['late_factor'] ?? 0.5)),
            'late_until'  => Waktu::keIsian($j['late_until']),
        ];
        $maju = Instans::kemajuan((int) $j['pertemuan_id'], (int) $kelas['id']);
        // Selama masih ada instans yang dibuat, halaman memuat ulang dirinya (tanpa JavaScript).
        if ($galat === [] && $maju['antre'] > 0) {
            $this->response->setHeader('Refresh', '4; url=/kelas/' . (int) $kelas['id'] . '/pertemuan/' . (int) $j['pertemuan_id']);
        }

        return $this->halaman('jendela_lihat', [
            'judul'   => lang('Soal.pertemuan.ke', [(int) $j['number']]) . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'   => $kelas,
            'j'       => $j,
            'keadaan' => Latihan::keadaan($j),
            'maju'    => $maju,
            'isi'     => $isi,
            'galat'   => $galat,
            'bolehUbah' => $kelas['tahun_status'] === 'active' || $this->akses()->superadmin(),
            'pesan'   => $galat !== [] ? [['jenis' => 'galat', 'teks' => lang('Kode.pesan.periksa')]] : $this->pesanDariQuery(self::PESAN),
        ], $status);
    }

    /**
     * Kelas dan pertemuan untuk DIUBAH.
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}|ResponseInterface
     */
    private function sasaran(int $kelasId, int $pertemuanId): array|ResponseInterface
    {
        $kelas = $this->kelasBoleh($kelasId, true);
        if ($kelas instanceof ResponseInterface) {
            return $kelas;
        }
        $j = (new PustakaJendela($this->akses()))->satu($kelasId, $pertemuanId);

        return $j === null ? $this->tidakDitemukan('meeting') : [$kelas, $j];
    }

    /**
     * Jadwal buka dan tutup (waktu Asia/Jakarta) dan apa yang terlihat sesudah tutup.
     */
    public function simpan(int $kelasId, int $pertemuanId): ResponseInterface
    {
        $s = $this->sasaran($kelasId, $pertemuanId);
        if ($s instanceof ResponseInterface) {
            return $s;
        }
        [$kelas, $j] = $s;
        $isi = [
            'open_at'  => trim((string) $this->request->getPost('open_at')),
            'close_at' => trim((string) $this->request->getPost('close_at')),
            'post_close_visibility' => (string) $this->request->getPost('post_close_visibility'),
            // Permintaan tanpa isian kebijakan terlambat tidak mengubah kebijakan yang ada.
            'late_policy' => (string) ($this->request->getPost('late_policy') ?? $j['late_policy'] ?? 'reject'),
            'late_factor' => str_replace(',', '.', trim((string) ($this->request->getPost('late_factor') ?? angka((float) ($j['late_factor'] ?? 0.5))))),
            'late_until'  => trim((string) ($this->request->getPost('late_until') ?? Waktu::keIsian($j['late_until']))),
        ];

        $galat = [];
        $buka  = $isi['open_at'] === '' ? null : Waktu::dariIsian($isi['open_at']);
        $tutup = $isi['close_at'] === '' ? null : Waktu::dariIsian($isi['close_at']);
        if ($isi['open_at'] !== '' && $buka === null) {
            $galat['open_at'] = 'waktu';
        }
        if ($isi['close_at'] !== '' && $tutup === null) {
            $galat['close_at'] = 'waktu';
        }
        // Dua-duanya diisi atau dua-duanya kosong (kosong = hanya dibuka dan ditutup manual).
        if ($galat === [] && ($buka === null) !== ($tutup === null)) {
            $galat[$buka === null ? 'open_at' : 'close_at'] = 'berpasangan';
        }
        if ($galat === [] && $buka !== null && $tutup <= $buka) {
            $galat['close_at'] = 'urutan';
        }
        if (! in_array($isi['post_close_visibility'], self::TAMPAK, true)) {
            $galat['post_close_visibility'] = 'tampak';
        }
        // Kebijakan terlambat (SPEC.md §6, §7). Kiriman terlambat hanya ada sesudah waktu tutup
        // TERJADWAL lewat; faktor dan batas akhirnya hanya berarti untuk kebijakan `multiplier`.
        $telat  = $isi['late_policy'] === 'multiplier';
        $faktor = (float) ($j['late_factor'] ?? 0.5);
        $sampai = null;
        if (! in_array($isi['late_policy'], self::TELAT, true)) {
            $galat['late_policy'] = 'kebijakan';
        } elseif ($telat) {
            if (preg_match('/^(0(\.\d{1,2})?|1(\.0{1,2})?)$/', $isi['late_factor']) !== 1) {
                $galat['late_factor'] = 'faktor';
            } else {
                $faktor = (float) $isi['late_factor'];
            }
            if ($isi['late_until'] !== '') {
                $sampai = Waktu::dariIsian($isi['late_until']);
                if ($sampai === null) {
                    $galat['late_until'] = 'waktu';
                } elseif ($tutup === null || $sampai <= $tutup) {
                    $galat['late_until'] = 'telatUrutan';
                }
            }
        }
        if ($galat !== []) {
            return $this->tampil($kelas, $j, $isi, $galat, 422);
        }

        $baru = [
            'open_at' => $buka, 'close_at' => $tutup, 'post_close_visibility' => $isi['post_close_visibility'],
            'late_policy' => $isi['late_policy'], 'late_factor' => $faktor, 'late_until' => $telat ? $sampai : null,
        ];
        $db   = db_connect();
        $db->transStart();
        (new PustakaJendela($this->akses()))->simpan($kelasId, $j, $baru);
        Audit::catat('jendela.disimpan', 'meeting', $pertemuanId, [
            'sebelum' => [
                'open_at' => $j['open_at'], 'close_at' => $j['close_at'], 'post_close_visibility' => $j['post_close_visibility'],
                'late_policy' => $j['late_policy'], 'late_factor' => $j['late_factor'] === null ? null : (float) $j['late_factor'], 'late_until' => $j['late_until'],
            ],
            'sesudah' => $baru,
        ], (int) $kelas['course_id'], $kelasId);
        $db->transComplete();

        $this->siapkanBilaTerbit($j, $kelasId);

        return redirect()->to("/kelas/{$kelasId}/pertemuan/{$pertemuanId}?pesan=disimpan");
    }

    /**
     * Buka atau tutup manual, atau kembali mengikuti jadwal (SPEC.md §6).
     */
    public function status(int $kelasId, int $pertemuanId): ResponseInterface
    {
        $s = $this->sasaran($kelasId, $pertemuanId);
        if ($s instanceof ResponseInterface) {
            return $s;
        }
        [$kelas, $j] = $s;
        $ke = (string) $this->request->getPost('override');
        if (! in_array($ke, ['none', 'open', 'closed'], true)) {
            return $this->tampil($kelas, $j, ['open_at' => Waktu::keIsian($j['open_at']), 'close_at' => Waktu::keIsian($j['close_at']),
                'post_close_visibility' => $j['post_close_visibility'] ?? 'hidden'], ['override' => 'override'], 422);
        }

        $db = db_connect();
        $db->transStart();
        (new PustakaJendela($this->akses()))->simpan($kelasId, $j, ['override' => $ke]);
        Audit::catat(['none' => 'jendela.ikutJadwal', 'open' => 'jendela.dibuka', 'closed' => 'jendela.ditutup'][$ke], 'meeting', $pertemuanId,
            ['sebelum' => $j['override'], 'sesudah' => $ke], (int) $kelas['course_id'], $kelasId);
        $db->transComplete();

        if ($ke !== 'closed') {
            $this->siapkanBilaTerbit($j, $kelasId);
        }

        return redirect()->to("/kelas/{$kelasId}/pertemuan/{$pertemuanId}?pesan=status");
    }

    /**
     * Pra-pembangkitan atas permintaan pengajar (SPEC.md §8.2).
     */
    public function siapkan(int $kelasId, int $pertemuanId): ResponseInterface
    {
        $s = $this->sasaran($kelasId, $pertemuanId);
        if ($s instanceof ResponseInterface) {
            return $s;
        }
        [$kelas, $j] = $s;
        if ($j['published_at'] === null) {
            return $this->response->setStatusCode(409)->setBody(view('Lokandita\Auth\Views\pesan', [
                'judul' => lang('Latihan.jendela.belumTerbit'),
                'teks'  => lang('Latihan.jendela.belumTerbitInfo'),
            ]));
        }
        $n = Instans::siapkan($pertemuanId, $kelasId, $this->akses()->identitas()->userId);
        Audit::catat('instans.disiapkan', 'meeting', $pertemuanId, ['diminta' => $n], (int) $kelas['course_id'], $kelasId);

        return redirect()->to("/kelas/{$kelasId}/pertemuan/{$pertemuanId}?pesan=disiapkan&n={$n}");
    }

    /**
     * Instans mulai dibuat begitu jendela diatur untuk pertemuan yang sudah
     * terbit (keputusan pemilik, tahap 9a): tidak ada proses latar di sisi web
     * yang bisa melakukannya tepat pada waktu buka.
     *
     * @param array<string, mixed> $j
     */
    private function siapkanBilaTerbit(array $j, int $kelasId): void
    {
        if ($j['published_at'] !== null) {
            Instans::siapkan((int) $j['pertemuan_id'], $kelasId, $this->akses()->identitas()->userId);
        }
    }
}
