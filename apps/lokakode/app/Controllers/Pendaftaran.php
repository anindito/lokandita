<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\Audit;
use App\Libraries\Notifikasi;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\AuthClient;
use Lokandita\Auth\UserDirectory;

/**
 * Reset kata sandi mahasiswa dari halaman kelas. Pendaftarannya sendiri (daftar, mundur, pindah,
 * lulus) sejak tahap A2 dikelola di Akun Lokandita (KONTRAK.md §18); nama kelas ini dipertahankan
 * karena rute dan tesnya memakainya.
 */
final class Pendaftaran extends BaseController
{
    /**
     * Reset kata sandi seorang mahasiswa (SPEC.md §3): superadmin, pengampu
     * mata kuliahnya, dan pengajar kelasnya — yaitu siapa pun yang boleh MELIHAT
     * kelas ini, untuk mahasiswa yang AKTIF di kelas ini saja.
     *
     * Dua langkah: tanpa `yakin=1` hanya halaman konfirmasi. Kata sandi
     * sementara ditampilkan sekali dan tidak disimpan atau dicatat di LokaKode.
     */
    public function resetSandi(int $kelasId): ResponseInterface
    {
        $kelas = $this->akademik()->kelas($kelasId);
        if ($kelas === null) {
            return $this->tidakDitemukan('class');
        }
        // Mahasiswa itu harus aktif DI KELAS INI; user_id dari peramban tidak dipercaya.
        $userId = (string) $this->request->getPost('user_id');
        $aktif  = array_filter($this->akademik()->pendaftaranKelas($kelasId), static fn ($e) => $e['user_id'] === $userId && $e['status'] === 'AKTIF');
        $mhs    = $aktif === [] ? null : (UserDirectory::byIds([$userId])[$userId] ?? null);
        if ($mhs === null) {
            return $this->tidakDitemukan('enrollment');
        }

        if ((string) $this->request->getPost('yakin') !== '1') {
            return $this->halaman('sandi_konfirmasi', ['judul' => lang('Kode.sandi.judul'), 'kelas' => $kelas, 'mhs' => $mhs]);
        }

        $pelaku = $this->akses()->identitas()->userId;
        $r      = (new AuthClient())->passwordReset($pelaku, $userId);
        $sandi  = $r['status'] === 200 ? (string) ($r['json']['temp_password'] ?? '') : '';
        if ($sandi === '') {
            // Kegagalan sudah dicatat oleh AuthClient. Katakan terus terang bahwa kata sandi TIDAK berubah.
            $kunci = $r['status'] === 403 ? 'Kode.sandi.bukanMahasiswa' : 'Kode.sandi.gagal';

            return $this->halaman('sandi_konfirmasi', [
                'judul' => lang('Kode.sandi.judul'), 'kelas' => $kelas, 'mhs' => $mhs,
                'pesan' => [['jenis' => 'galat', 'teks' => lang($kunci)]],
            ], $r['status'] === 403 ? 403 : 502);
        }

        Audit::catat('sandi.direset', 'user', $userId, ['kelas' => $kelas['name']], (int) $kelas['course_id'], $kelasId);
        // Mahasiswa itu diberi tahu di LokaKode siapa yang mereset dan kapan (SPEC.md §14, KONTRAK.md §17).
        Notifikasi::sandiDireset($userId, $pelaku, $kelas['matkul_kode'] . ' ' . $kelas['name'], (int) db_connect()->insertID());

        return $this->halaman('sandi_sementara', ['judul' => lang('Kode.sandi.judul'), 'kelas' => $kelas, 'mhs' => $mhs, 'sandi' => $sandi]);
    }
}
