<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Aturan;
use App\Libraries\Bank as Pustaka;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Bank soal satu mata kuliah (SPEC-LOKAUJIAN.md §5.4): daftar soal lepas dan kelompok dengan
 * tipe, poin, kata-kata pertama dan ujian yang memakainya; cari dan saring menurut tipe.
 * Hanya superadmin dan pengampu mata kuliah itu; pengajar mata kuliah itu mendapat 403.
 */
final class Bank extends BaseController
{
    private const PESAN = [
        'dihapus' => ['sukses', 'Bank.pesan.dihapus'],
    ];

    public function daftar(string $matkulId): ResponseInterface
    {
        $matkul = $this->matkulDikelola((int) $matkulId);
        if ($matkul instanceof ResponseInterface) {
            return $matkul;
        }
        $tipe   = (string) $this->request->getGet('tipe');
        $saring = [
            'tipe'  => in_array($tipe, [...Aturan::TIPE, 'kelompok'], true) ? $tipe : '',
            'q'     => mb_substr(trim((string) $this->request->getGet('q')), 0, 100),
            'arsip' => (string) $this->request->getGet('arsip') === '1',
        ];
        $bank = new Pustaka($this->akses());

        return $this->halaman('bank_daftar', [
            'judul'   => lang('Bank.judul') . ' — ' . $matkul['code'],
            'matkul'  => $matkul,
            'butir'   => $bank->daftar((int) $matkul['id'], $saring),
            'dipakai' => $bank->pemakaian((int) $matkul['id']),
            'saring'  => $saring,
            'pesan'   => $this->pesanDariQuery(self::PESAN),
        ]);
    }
}
