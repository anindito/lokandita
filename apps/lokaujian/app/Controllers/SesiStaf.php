<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Jadwal;
use App\Libraries\Sesi;
use App\Libraries\Ujian;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Sesi dari sisi staf (SPEC-LOKAUJIAN.md §7, tahap U3: kendali paling sedikit untuk menjalankan
 * kedua mode waktu). Tampilan langsung per mahasiswa, tambahan waktu dan buka ulang adalah U5.
 *
 *   mengatur    — memberikan ujian ke kelas, jadwal, hapus sesi: yang mengelola mata kuliahnya;
 *   menjalankan — halaman sesi, buka, tutup, mulai, atur akhir, akhiri, pratinjau: yang melihat
 *                 kelasnya (superadmin, pengampu, pengajar kelas itu).
 * Sesi di luar lingkup dijawab 404, sama dengan nomor yang tidak ada.
 */
final class SesiStaf extends BaseController
{
    private const PESAN = [
        'dibuat'        => ['sukses', 'Sesi.pesan.dibuat'],
        'dijadwalkan'   => ['sukses', 'Sesi.pesan.dijadwalkan'],
        'dibuka'        => ['sukses', 'Sesi.pesan.dibuka'],
        'ditutup'       => ['sukses', 'Sesi.pesan.ditutup'],
        'dimulai'       => ['sukses', 'Sesi.pesan.dimulai'],
        'akhir'         => ['sukses', 'Sesi.pesan.akhir'],
        'diakhiri'      => ['sukses', 'Sesi.pesan.diakhiri'],
        'g_jadwal'      => ['galat', 'Sesi.galat.jadwal'],
        'g_akhir'       => ['galat', 'Sesi.galat.akhir'],
        'g_sudah'       => ['galat', 'Sesi.galat.sudah'],
        'g_mode'        => ['galat', 'Sesi.galat.mode'],
        'g_diarsipkan'  => ['galat', 'Sesi.galat.diarsipkan'],
        'g_ada_pengerjaan' => ['galat', 'Sesi.galat.adaPengerjaan'],
    ];

    /**
     * Memberikan ujian terbit kepada satu kelas mata kuliah itu.
     */
    public function buat(string $matkulId, string $ujianId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $kembali = '/mata-kuliah/' . (int) $matkul['id'] . '/ujian/' . (int) $ujianId;
        // Kelas dibaca dari roster DENGAN syarat hak; kelas mata kuliah lain ditolak di Sesi::buat.
        $kelas = $this->roster()->kelas((int) $this->request->getPost('kelas'));
        if ($kelas === null) {
            return redirect()->to($kembali . '?pesan=kelas_asing');
        }
        $hasil = (new Sesi($this->akses()))->buat(
            (int) $matkul['id'],
            (int) $ujianId,
            $kelas,
            Sesi::keUtc((string) $this->request->getPost('buka')),
            Sesi::keUtc((string) $this->request->getPost('tutup')),
        );
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('exam');
        }

        return redirect()->to($kembali . '?pesan=' . (is_int($hasil) ? 'sesi_dibuat' : $hasil));
    }

    public function lihat(string $id): ResponseInterface
    {
        // Halaman yang membaca hasil memutakhirkan keadaan tersimpan sendiri, supaya tetap benar
        // ketika penjadwal mati (SPEC-LOKAUJIAN.md §7.5).
        Jadwal::lintasan();
        $pustaka = new Sesi($this->akses());
        $sesi    = $pustaka->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $kelas = $this->roster()->kelas((int) $sesi['class_id']);
        if ($kelas === null) {
            return $this->tidakDitemukan('session_class');
        }

        return $this->halaman('sesi_lihat', [
            'judul'    => $sesi['title'] . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'sesi'     => $sesi,
            'kelas'    => $kelas,
            'hitungan' => $pustaka->hitungan((int) $sesi['id']),
            'aktif'    => count($this->roster()->mahasiswaAktif((int) $sesi['class_id'])),
            'denyut'   => Jadwal::denyut(),
            'pesan'    => $this->pesanDariQuery(self::PESAN),
        ]);
    }

    /**
     * Pratinjau ujian terbit sebagaimana dilihat mahasiswa, bagi siapa pun yang boleh menjalankan
     * sesi ini — termasuk pengajar, yang tidak punya jalan ke bank soal (SPEC-LOKAUJIAN.md §3).
     * Tanpa kunci jawaban; tidak membuat pengerjaan.
     */
    public function pratinjau(string $id): ResponseInterface
    {
        $sesi = (new Sesi($this->akses()))->lihat((int) $id);
        if ($sesi === null) {
            return $this->tidakDitemukan('session');
        }
        $butir = Ujian::beku(db_connect(), (int) $sesi['exam_id']);

        return $this->halaman('sesi_pratinjau', [
            'judul' => lang('Bank.pratinjau') . ' — ' . $sesi['title'],
            'sesi'  => $sesi,
            'butir' => $butir,
        ]);
    }

    private function selesai(string $id, string $hasil, string $pesanOk): ResponseInterface
    {
        if ($hasil === 'tidak_ada') {
            return $this->tidakDitemukan('session');
        }

        return redirect()->to('/sesi/' . (int) $id . '?pesan=' . ($hasil === 'ok' ? $pesanOk : 'g_' . $hasil));
    }

    public function jadwal(string $id): ResponseInterface
    {
        $hasil = (new Sesi($this->akses()))->jadwal((int) $id, Sesi::keUtc((string) $this->request->getPost('buka')), Sesi::keUtc((string) $this->request->getPost('tutup')));

        return $this->selesai($id, $hasil, 'dijadwalkan');
    }

    public function buka(string $id): ResponseInterface
    {
        return $this->selesai($id, (new Sesi($this->akses()))->buka((int) $id), 'dibuka');
    }

    public function tutup(string $id): ResponseInterface
    {
        return $this->selesai($id, (new Sesi($this->akses()))->tutup((int) $id), 'ditutup');
    }

    public function mulai(string $id): ResponseInterface
    {
        $hasil = (new Sesi($this->akses()))->mulai((int) $id, (string) $this->request->getPost('menit'), (string) $this->request->getPost('jam'));

        return $this->selesai($id, $hasil, 'dimulai');
    }

    public function akhir(string $id): ResponseInterface
    {
        $hasil = (new Sesi($this->akses()))->aturAkhir((int) $id, (string) $this->request->getPost('menit'), (string) $this->request->getPost('jam'));

        return $this->selesai($id, $hasil, 'akhir');
    }

    public function akhiri(string $id): ResponseInterface
    {
        return $this->selesai($id, (new Sesi($this->akses()))->akhiri((int) $id), 'diakhiri');
    }

    public function hapus(string $id): ResponseInterface
    {
        $pustaka = new Sesi($this->akses());
        $sesi    = $pustaka->lihat((int) $id);
        $hasil   = $pustaka->hapus((int) $id);
        if ($hasil !== 'ok' || $sesi === null) {
            return $this->selesai($id, $hasil, '');
        }

        return redirect()->to('/mata-kuliah/' . (int) $sesi['course_id'] . '/ujian/' . (int) $sesi['exam_id'] . '?pesan=sesi_dihapus');
    }
}
