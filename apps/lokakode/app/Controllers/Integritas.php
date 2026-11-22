<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Analitik;
use App\Libraries\Audit;
use App\Libraries\Integritas as Pustaka;
use App\Libraries\Tabel;
use App\Libraries\Waktu;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\UserDirectory;

/**
 * Halaman peristiwa integritas per kelas dan ekspornya (SPEC.md §13.2, KONTRAK.md §17):
 * siapa pun yang melihat kelas itu — superadmin, pengampu, pengajar kelas tersebut.
 */
final class Integritas extends BaseController
{
    public function index(int $kelasId): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        $saring  = Pustaka::saringan($this->request->getGet());
        $halaman = max(1, (int) $this->request->getGet('halaman'));
        // Satu baris lebih banyak dari yang ditampilkan: cukup untuk tahu ada halaman berikutnya.
        $baris = (new Pustaka($this->akses()))->daftar($kelasId, $saring, Pustaka::PER_HALAMAN + 1, ($halaman - 1) * Pustaka::PER_HALAMAN);
        $lagi  = count($baris) > Pustaka::PER_HALAMAN;
        $baris = array_slice($baris, 0, Pustaka::PER_HALAMAN);

        $terdaftar = array_filter($this->akademik()->pendaftaranKelas($kelasId), static fn (array $e): bool => in_array($e['status'], ['AKTIF', 'LULUS'], true));
        $orang     = UserDirectory::byIds(array_values(array_unique(array_column($terdaftar, 'user_id'))));
        uasort($orang, static fn (array $a, array $b): int => strcmp((string) $a['nama'], (string) $b['nama']));
        $this->response->setHeader('Cache-Control', 'no-store');

        return $this->halaman('integritas_kelas', [
            'judul'     => lang('Integritas.judul') . ' — ' . $kelas['matkul_kode'] . ' ' . $kelas['name'],
            'kelas'     => $kelas,
            'baris'     => $baris,
            'saring'    => $saring,
            'query'     => http_build_query(array_filter($saring, static fn ($v): bool => $v !== null)),
            'halaman'   => $halaman,
            'lagi'      => $lagi,
            'orang'     => $orang,
            'pertemuan' => (new Analitik($this->akses()))->pertemuanKelas($kelasId),
        ]);
    }

    public function ekspor(int $kelasId, string $format): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        if (! in_array($format, ['csv', 'xlsx'], true)) {
            return $this->tidakDitemukan('format');
        }
        $saring = Pustaka::saringan($this->request->getGet());
        $baris  = (new Pustaka($this->akses()))->daftar($kelasId, $saring, Pustaka::EKSPOR_MAKS);
        $orang  = UserDirectory::byIds(array_values(array_unique(array_column($baris, 'user_id'))));

        $judul = [
            lang('Integritas.waktu'), lang('Papan.nomorInduk'), lang('Papan.nama'), lang('Integritas.jenisJudul'), lang('Integritas.pertemuan'),
            lang('Integritas.soal'), lang('Integritas.rincian'), lang('Integritas.ip'), lang('Integritas.peramban'),
        ];
        $isi = array_map(static fn (array $b): array => [
            Waktu::lokal($b['created_at']), $orang[$b['user_id']]['nomor_induk'] ?? '', $orang[$b['user_id']]['nama'] ?? '',
            lang('Integritas.jenis.' . $b['kind']), $b['number'] === null ? null : (int) $b['number'], Pustaka::soal($b),
            Pustaka::uraian((string) $b['kind'], $b['detail']), (string) $b['ip'], (string) $b['user_agent'],
        ], $baris);

        // Ekspor memuat data pribadi banyak mahasiswa sekaligus: dicatat siapa yang mengambilnya.
        Audit::catat('integritas.diekspor', 'class', $kelasId, ['format' => $format, 'baris' => count($isi)], (int) $kelas['course_id'], $kelasId);

        $badan = $format === 'csv' ? Tabel::csv($judul, $isi) : Tabel::xlsx([lang('Integritas.judul') => [$judul, $isi]]);

        return Tabel::unduh($this->response, $kelas['matkul_kode'] . '-' . $kelas['name'] . '-integritas', $format, $badan);
    }
}
