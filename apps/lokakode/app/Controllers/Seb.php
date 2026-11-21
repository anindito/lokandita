<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Seb\Seb as Pustaka;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Locale;
use Lokandita\Auth\Publik;

/**
 * Halaman publik Safe Exam Browser (SPEC.md §13.1): petunjuk, unduhan berkas .seb, halaman
 * periksa, dan tautan keluar. Tanpa sesi. Yang dibaca dari basis data hanya kode dan nama
 * mata kuliah yang punya konfigurasi, dan konfigurasi itu sendiri (KONTRAK.md §4, §8).
 */
final class Seb extends Controller
{
    private const UNDUH_SEB = 'https://safeexambrowser.org/download_en.html';

    public function petunjuk(): ResponseInterface
    {
        Locale::apply($this->request);
        $matkul = db_connect()->query('SELECT c.code, c.name FROM seb_configs s JOIN courses c ON c.id = s.course_id WHERE s.retired_at IS NULL ORDER BY c.code')->getResultArray();

        return $this->response->setBody(view('seb_petunjuk', Publik::data(lang('Seb.petunjuk.judul'), lang('Seb.petunjuk.deskripsi')) + [
            'matkul'      => $matkul,
            'unduhSeb'    => self::UNDUH_SEB,
            'diberlakukan' => Pustaka::diberlakukan(),
        ]));
    }

    /**
     * Berkas konfigurasi satu mata kuliah: /seb/unduh/<KODE>.seb
     */
    public function unduh(string $nama): ResponseInterface
    {
        $berkas = preg_match('/^([A-Za-z0-9-]{1,20})\.seb$/', $nama, $m) === 1 ? Pustaka::berkas($m[1]) : null;
        if ($berkas === null) {
            Locale::apply($this->request);

            return $this->response->setStatusCode(404)->setBody(view('Lokandita\Auth\Views\pesan', [
                'judul' => lang('Lokandita.tidakDitemukan'), 'teks' => lang('Seb.unduh.tidakAda'),
            ]));
        }

        return $this->response
            ->setHeader('Content-Type', 'application/seb')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $berkas['nama'] . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($berkas['isi']);
    }

    /**
     * Halaman periksa: apakah peramban ini mengirim kedua header, dan untuk mata kuliah mana
     * keduanya cocok. Tidak menampilkan kunci apa pun — hanya cocok atau tidak.
     */
    public function periksa(): ResponseInterface
    {
        Locale::apply($this->request);
        $hasil = Pustaka::periksa($this->request, Pustaka::semuaAktif());

        return $this->response->setHeader('Cache-Control', 'no-store')->setBody(view('seb_periksa', [
            'judul'      => lang('Seb.periksa.judul'), // tanpa $publik: tata letak menandainya noindex
            'adaKonfig'  => $this->request->getHeaderLine(Pustaka::HEADER_KONFIG) !== '',
            'adaPeramban' => $this->request->getHeaderLine(Pustaka::HEADER_PERAMBAN) !== '',
            'hasil'      => $hasil,
            'url'        => Pustaka::url(),
        ]));
    }

    /**
     * Alamat "quitURL" konfigurasi: SEB menutup dirinya saat alamat ini dibuka. Di peramban
     * biasa halaman ini hanya menjelaskan hal itu.
     */
    public function keluar(): ResponseInterface
    {
        Locale::apply($this->request);

        return $this->response->setBody(view('Lokandita\Auth\Views\pesan', [
            'judul' => lang('Seb.keluar.judul'), 'teks' => lang('Seb.keluar.teks'),
        ]));
    }
}
