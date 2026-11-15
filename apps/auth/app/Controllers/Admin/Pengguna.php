<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Audit;
use App\Libraries\Brankas;
use App\Libraries\Clock;
use App\Libraries\Mfa;
use App\Libraries\Passwords;
use App\Libraries\Sessions;
use App\Libraries\Tabular;
use App\Libraries\UserAdmin;
use App\Libraries\Users;
use App\Libraries\Uuid;
use CodeIgniter\HTTP\ResponseInterface;
use Lokandita\Auth\Current;

/**
 * Pengelolaan pengguna — hanya superadmin (filter `sesi:superadmin` di Routes.php).
 */
final class Pengguna extends BaseController
{
    private const PER_HALAMAN = [50, 100, 200];

    private const MAKS_MASSAL = 500;

    /**
     * @return array{q: string, peran: string, status: string}
     */
    private function saring(): array
    {
        return [
            'q'      => trim((string) $this->request->getGet('q')),
            'peran'  => (string) $this->request->getGet('peran'),
            'status' => (string) $this->request->getGet('status'),
        ];
    }

    public function index(): ResponseInterface
    {
        $saring = $this->saring();
        $per    = (int) $this->request->getGet('per');
        $per    = in_array($per, self::PER_HALAMAN, true) ? $per : 50;
        $db     = db_connect();
        $jumlah = UserAdmin::filter($db->table('users'), $saring)->countAllResults();
        $akhir  = max(1, (int) ceil($jumlah / $per));
        $hal    = min($akhir, max(1, (int) $this->request->getGet('hal')));

        $baris = UserAdmin::filter($db->table('users'), $saring)
            ->select('id, nomor_induk, nama, email, global_role, status, mfa_enabled, locked_until, last_login_at')
            ->orderBy('nomor_induk')->limit($per, ($hal - 1) * $per)->get()->getResultArray();

        $akses = [];
        if ($baris !== []) {
            foreach ($db->table('app_access')->select('user_id, app')->whereIn('user_id', array_column($baris, 'id'))->where('enabled', 1)->orderBy('app')->get()->getResultArray() as $a) {
                $akses[$a['user_id']][] = $a['app'];
            }
        }

        return $this->halaman('admin/pengguna_daftar', [
            'judul'  => lang('Admin.pengguna'),
            'baris'  => $baris,
            'akses'  => $akses,
            'saring' => $saring,
            'per'    => $per,
            'hal'    => $hal,
            'akhir'  => $akhir,
            'jumlah' => $jumlah,
            'pesan'  => $this->pesanDariQuery(),
        ]);
    }

    public function ekspor(): ResponseInterface
    {
        $format = $this->request->getGet('format') === 'xlsx' ? 'xlsx' : 'csv';
        $db     = db_connect();
        $baris  = UserAdmin::filter($db->table('users'), $this->saring())
            ->select('id, nomor_induk, nama, email, global_role, status, last_login_at, created_at')->orderBy('nomor_induk')->get()->getResultArray();
        $akses = [];
        foreach ($db->table('app_access')->select('user_id, app')->where('enabled', 1)->orderBy('app')->get()->getResultArray() as $a) {
            $akses[$a['user_id']][] = $a['app'];
        }
        $data = [];
        foreach ($baris as $b) {
            $data[] = [$b['nomor_induk'], $b['nama'], (string) $b['email'], $b['global_role'], $b['status'], implode('|', $akses[$b['id']] ?? []),
                $b['last_login_at'] === null ? '' : Clock::lokal($b['last_login_at']), Clock::lokal($b['created_at'])];
        }
        Audit::log('user.exported', Current::identity()->userId, null, ['format' => $format, 'rows' => count($data)]);

        return Tabular::download($this->response, $format, 'pengguna-lokandita-' . gmdate('Ymd-His'),
            ['nomor_induk', 'nama', 'email', 'global_role', 'status', 'apps', 'login_terakhir', 'dibuat'], $data);
    }

    public function baru(): ResponseInterface
    {
        return $this->form(null, ['nomor_induk' => '', 'nama' => '', 'email' => '', 'global_role' => 'mahasiswa', 'apps' => ['portal', 'lokakode']], []);
    }

    public function simpan(): ResponseInterface
    {
        $in    = $this->isian();
        $galat = UserAdmin::validate($in, null, true);
        if ($galat !== []) {
            return $this->form(null, $in, $galat, 422);
        }
        $sandi = Passwords::random();
        $id    = UserAdmin::create($in, Passwords::hashTemporary($sandi), Current::identity()->userId, 'form');

        return $this->sandiSementara(Users::find($id), $sandi, 'dibuat');
    }

    public function sunting(string $id): ResponseInterface
    {
        $user = $this->cari($id);
        if ($user === null) {
            return $this->tidakAda();
        }

        return $this->form($user, $user + ['apps' => UserAdmin::apps($id)], []);
    }

    public function perbarui(string $id): ResponseInterface
    {
        $user = $this->cari($id);
        if ($user === null) {
            return $this->tidakAda();
        }
        $aktor = Current::identity()->userId;
        $in    = $this->isian();
        // Nomor induk tidak bisa diubah: aplikasi lain dan arsip nilai merujuknya.
        $in['nomor_induk'] = $user['nomor_induk'];

        $galat = UserAdmin::validate($in, $user, true);
        if ($galat === []) {
            $kunci = UserAdmin::updateProfile($user, $in, $aktor);
            if ($kunci !== null) {
                $galat['global_role'] = $kunci;
            }
        }
        if ($galat === []) {
            $kunci = UserAdmin::setStatus(Users::find($id), (string) $this->request->getPost('status'), $aktor);
            if ($kunci !== null) {
                $galat['status'] = $kunci;
            }
        }
        if ($galat !== []) {
            return $this->form($user, $in, $galat, 422);
        }
        foreach (UserAdmin::APPS as $app) {
            UserAdmin::setAccess($id, $app, in_array($app, $in['apps'], true), $aktor);
        }
        UserAdmin::setPasteAllowed(Users::find($id), $this->request->getPost('paste_allowed') === '1', $aktor);

        return redirect()->to('/admin/pengguna/' . $id . '?pesan=disimpan');
    }

    public function resetSandi(string $id): ResponseInterface
    {
        $user = $this->cari($id);
        if ($user === null) {
            return $this->tidakAda();
        }

        return $this->sandiSementara($user, UserAdmin::resetPassword($user, Current::identity()->userId), 'direset');
    }

    public function hapusMfa(string $id): ResponseInterface
    {
        $user = $this->cari($id);
        if ($user === null) {
            return $this->tidakAda();
        }
        Mfa::clear($id);
        $dicabut = Sessions::revokeAllFor($id);
        Audit::log('mfa.cleared', Current::identity()->userId, $id, ['sessions_revoked' => $dicabut]);

        return redirect()->to('/admin/pengguna/' . $id . '?pesan=mfa-dihapus');
    }

    /**
     * POST /admin/pengguna/massal — pertama menampilkan konfirmasi berisi jumlah baris,
     * baru dijalankan bila dikirim lagi dengan yakin=1.
     */
    public function massal(): ResponseInterface
    {
        $aksi = (string) $this->request->getPost('aksi');
        $ids  = array_values(array_unique(array_filter((array) $this->request->getPost('ids'), static fn ($i) => is_string($i) && Uuid::isValid($i))));
        if (! $this->aksiSah($aksi) || $ids === [] || count($ids) > self::MAKS_MASSAL) {
            return redirect()->to('/admin/pengguna?pesan=massal-kosong');
        }
        $pengguna = db_connect()->table('users')->whereIn('id', $ids)->orderBy('nomor_induk')->get()->getResultArray();

        if ($this->request->getPost('yakin') !== '1') {
            return $this->halaman('admin/massal_konfirmasi', [
                'judul'    => lang('Admin.massal.konfirmasiJudul'),
                'aksi'     => $aksi,
                'pengguna' => $pengguna,
            ]);
        }

        $aktor     = Current::identity()->userId;
        $berhasil  = 0;
        $ditolak   = [];
        $kredensial = [];
        [$jenis, $a, $b] = array_pad(explode(':', $aksi), 3, '');
        foreach ($pengguna as $u) {
            if ($jenis === 'akses') {
                UserAdmin::setAccess((string) $u['id'], $b, $a === '+', $aktor);
                $berhasil++;
            } elseif ($jenis === 'status') {
                $kunci = UserAdmin::setStatus($u, $a, $aktor);
                if ($kunci === null) {
                    $berhasil++;
                } else {
                    $ditolak[] = ['nomor_induk' => $u['nomor_induk'], 'alasan' => $kunci];
                }
            } else {
                $kredensial[] = ['nomor_induk' => $u['nomor_induk'], 'nama' => $u['nama'], 'password' => UserAdmin::resetPassword($u, $aktor)];
                $berhasil++;
            }
        }
        Audit::log('user.bulk_action', $aktor, null, ['action' => $aksi, 'selected' => count($ids), 'done' => $berhasil, 'refused' => count($ditolak)]);

        return $this->halaman('admin/massal_hasil', [
            'judul'    => lang('Admin.massal.hasilJudul'),
            'berhasil' => $berhasil,
            'ditolak'  => $ditolak,
            'token'    => $kredensial === [] ? null : Brankas::simpan('kredensial', $aktor, $kredensial),
        ])->setHeader('Cache-Control', 'no-store');
    }

    private function aksiSah(string $aksi): bool
    {
        [$jenis, $a, $b] = array_pad(explode(':', $aksi), 3, '');

        return match ($jenis) {
            'akses'  => in_array($a, ['+', '-'], true) && in_array($b, UserAdmin::APPS, true),
            'status' => in_array($a, UserAdmin::STATUS, true) && $b === '',
            'reset'  => $a === '' && $b === '',
            default  => false,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function isian(): array
    {
        return [
            'nomor_induk' => trim((string) $this->request->getPost('nomor_induk')),
            'nama'        => trim((string) $this->request->getPost('nama')),
            'email'       => trim((string) $this->request->getPost('email')),
            'global_role' => (string) $this->request->getPost('global_role'),
            'apps'        => array_values(array_filter((array) $this->request->getPost('apps'), 'is_string')),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function cari(string $id): ?array
    {
        return Uuid::isValid($id) ? Users::find($id) : null;
    }

    private function tidakAda(): ResponseInterface
    {
        return $this->halaman('galat', ['judul' => lang('Lokandita.tidakDitemukan'), 'teks' => lang('Admin.penggunaTidakAda')], 404);
    }

    /**
     * @param array<string, mixed>|null $user
     * @param array<string, mixed>      $isi
     * @param array<string, string>     $galat
     */
    private function form(?array $user, array $isi, array $galat, int $status = 200): ResponseInterface
    {
        return $this->halaman('admin/pengguna_form', [
            'judul' => $user === null ? lang('Admin.tambah') : lang('Admin.sunting'),
            'user'  => $user,
            'isi'   => $isi,
            'galat' => $galat,
            'pesan' => $galat !== [] ? $this->galat(lang('Auth.periksaIsian')) : $this->pesanDariQuery(),
        ], $status);
    }

    /**
     * Kata sandi sementara ditampilkan sekali, di jawaban ini saja.
     *
     * @param array<string, mixed> $user
     */
    private function sandiSementara(array $user, string $sandi, string $peristiwa): ResponseInterface
    {
        return $this->halaman('admin/sandi_sementara', [
            'judul'     => lang('Admin.sandiSementara.judul'),
            'user'      => $user,
            'sandi'     => $sandi,
            'peristiwa' => $peristiwa,
        ])->setHeader('Cache-Control', 'no-store');
    }

    /**
     * @return list<array{jenis: string, teks: string}>
     */
    private function pesanDariQuery(): array
    {
        $peta = [
            'disimpan'     => ['sukses', 'Admin.pesan.disimpan'],
            'mfa-dihapus'  => ['sukses', 'Admin.pesan.mfaDihapus'],
            'massal-kosong' => ['galat', 'Admin.pesan.massalKosong'],
        ];
        $k = (string) $this->request->getGet('pesan');

        return isset($peta[$k]) ? [['jenis' => $peta[$k][0], 'teks' => lang($peta[$k][1])]] : [];
    }
}
