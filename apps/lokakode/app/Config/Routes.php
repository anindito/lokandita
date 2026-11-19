<?php

use CodeIgniter\Router\RouteCollection;
use Lokandita\Auth\Routes as RuteBersama;

/** @var RouteCollection $routes */

// KONTRAK.md §11: dipakai infra/deploy.sh untuk memastikan commit yang berjalan.
$routes->get('healthz', '\Lokandita\Auth\Controllers\Healthz::index');

// Menu akun: keluar, perpanjang sesi, preferensi (KONTRAK.md §4).
RuteBersama::akun($routes);

// Setiap rute baru WAJIB didaftarkan juga di tests/Policy/MatriksAksesTest.php;
// tes itu gagal bila ada rute di sini yang tidak ada di matriksnya.

// Tanpa sesi: akar hanya mengalihkan ke beranda. Halaman depan publik LokaKode
// (SPEC.md §15) dibangun di tahap 11.
$routes->get('/', 'Beranda::akar');

// Semua pengguna yang masuk.
$routes->group('', ['filter' => 'masuk'], static function (RouteCollection $routes): void {
    $routes->get('beranda', 'Beranda::index');
    // Hasil dan kemajuan pekerjaan judge milik pengguna itu sendiri (KONTRAK.md §6).
    $routes->get('api/executions/(:segment)', 'Eksekusi::lihat/$1');
});

// Mahasiswa saja (SPEC.md §11): ruang latihan. Lingkupnya — pertemuan kelasnya sendiri,
// kiriman dan drafnya sendiri — diperiksa App\Libraries\Ruang di dalam kueri.
$routes->group('', ['filter' => 'masuk:mahasiswa'], static function (RouteCollection $routes): void {
    $routes->get('latihan/(:num)', 'RuangLatihan::pertemuan/$1');
    // Papan peringkat kelasnya sendiri saja (SPEC.md §3).
    $routes->get('latihan/(:num)/papan', 'RuangLatihan::papan/$1');
    $routes->get('latihan/soal/(:num)', 'RuangLatihan::soal/$1');
    $routes->get('api/soal/(:num)/keadaan', 'RuangLatihan::keadaan/$1');
    $routes->post('api/soal/(:num)/draf', 'RuangLatihan::draf/$1');
    $routes->post('api/soal/(:num)/jalankan', 'RuangLatihan::jalankan/$1');
    $routes->post('api/soal/(:num)/kirim', 'RuangLatihan::kirim/$1');
    $routes->get('api/soal/(:num)/riwayat', 'RuangLatihan::riwayat/$1');
    $routes->get('api/soal/(:num)/riwayat/(:num)', 'RuangLatihan::kiriman/$1/$2');
    $routes->post('api/integritas', 'RuangLatihan::integritas');
    $routes->post('api/panel', 'RuangLatihan::panel');
});

// Staf (superadmin dan dosen). Peran global diperiksa filter; lingkupnya
// (mata kuliah dan kelas yang mana) diperiksa App\Policy\Akses di dalam kueri.
$routes->group('', ['filter' => 'masuk:superadmin,dosen'], static function (RouteCollection $routes): void {
    $routes->get('mata-kuliah', 'MataKuliah::index');
    $routes->get('mata-kuliah/(:num)', 'MataKuliah::lihat/$1');
    $routes->get('kelas/(:num)', 'Kelas::lihat/$1');
    // Mengubah kelas: superadmin dan pengampu (BaseController::kelasUntukDiubah).
    $routes->post('mata-kuliah/(:num)/kelas', 'Kelas::simpan/$1');
    $routes->get('kelas/(:num)/ubah', 'Kelas::sunting/$1');
    $routes->post('kelas/(:num)', 'Kelas::perbarui/$1');
    $routes->post('kelas/(:num)/arsip', 'Kelas::arsip/$1');
    $routes->post('kelas/(:num)/hapus', 'Kelas::hapus/$1');
    $routes->post('kelas/(:num)/pengajar', 'Kelas::tambahPengajar/$1');
    $routes->post('kelas/(:num)/pengajar/hapus', 'Kelas::hapusPengajar/$1');
    // Pendaftaran mahasiswa: superadmin dan pengampu.
    $routes->post('kelas/(:num)/daftar', 'Pendaftaran::pratinjau/$1');
    $routes->post('kelas/(:num)/daftar/jalankan', 'Pendaftaran::jalankan/$1');
    $routes->post('kelas/(:num)/mundur', 'Pendaftaran::mundur/$1');
    $routes->post('kelas/(:num)/pindah', 'Pendaftaran::pindah/$1');
    $routes->post('kelas/(:num)/lulus', 'Pendaftaran::lulus/$1');
    $routes->post('kelas/(:num)/batal-lulus', 'Pendaftaran::batalLulus/$1');
    // Reset kata sandi mahasiswa: siapa pun yang boleh melihat kelas itu (termasuk pengajarnya).
    $routes->post('kelas/(:num)/reset-sandi', 'Pendaftaran::resetSandi/$1');
    // Pertemuan dan konsep (SPEC.md §6 minimal, §9): superadmin dan pengampu; pengajar 403.
    $routes->get('mata-kuliah/(:num)/pertemuan', 'Pertemuan::index/$1');
    $routes->post('mata-kuliah/(:num)/pertemuan', 'Pertemuan::simpan/$1');
    $routes->post('mata-kuliah/(:num)/konsep-dasar', 'Pertemuan::konsepDasar/$1');
    $routes->get('pertemuan/(:num)', 'Pertemuan::lihat/$1');
    $routes->post('pertemuan/(:num)', 'Pertemuan::perbarui/$1');
    // Pengaturan latihan dan penerbitan (SPEC.md §6): superadmin dan pengampu; pengajar 403.
    $routes->post('pertemuan/(:num)/latihan', 'Pertemuan::latihan/$1');
    $routes->post('pertemuan/(:num)/terbitkan', 'Pertemuan::terbitkan/$1');
    $routes->post('pertemuan/(:num)/tarik', 'Pertemuan::tarik/$1');
    // Jendela per kelas dan pra-pembangkitan (SPEC.md §3, §6, §8.2): siapa pun yang
    // melihat kelas itu — superadmin, pengampu, dan pengajar kelas tersebut.
    $routes->get('kelas/(:num)/pertemuan', 'Jendela::index/$1');
    $routes->get('kelas/(:num)/pertemuan/(:num)', 'Jendela::lihat/$1/$2');
    $routes->post('kelas/(:num)/pertemuan/(:num)', 'Jendela::simpan/$1/$2');
    $routes->post('kelas/(:num)/pertemuan/(:num)/status', 'Jendela::status/$1/$2');
    $routes->post('kelas/(:num)/pertemuan/(:num)/siapkan', 'Jendela::siapkan/$1/$2');
    // Papan peringkat (SPEC.md §3, §12): per kelas untuk siapa pun yang melihat kelas itu;
    // per mata kuliah untuk superadmin dan pengampu (pengajar 403).
    $routes->get('kelas/(:num)/pertemuan/(:num)/papan', 'Peringkat::kelas/$1/$2');
    $routes->get('pertemuan/(:num)/papan', 'Peringkat::matkul/$1');
    // Analitik, kiriman mahasiswa dan ekspor nilai (SPEC.md §3, §12). Per kelas: siapa pun yang
    // melihat kelas itu. Lintas kelas (mata-kuliah/…): superadmin dan pengampu; pengajar 403.
    $routes->get('kelas/(:num)/analitik', 'Analitik::kelasRingkas/$1');
    $routes->get('kelas/(:num)/pertemuan/(:num)/analitik', 'Analitik::pertemuan/$1/$2');
    $routes->get('kelas/(:num)/pertemuan/(:num)/kiriman', 'Analitik::kirimanDaftar/$1/$2');
    $routes->get('kelas/(:num)/kiriman/(:num)', 'Analitik::kiriman/$1/$2');
    $routes->get('kelas/(:num)/ekspor/(:segment)', 'Analitik::eksporKelas/$1/$2');
    $routes->get('mata-kuliah/(:num)/analitik', 'Analitik::matkul/$1');
    $routes->get('mata-kuliah/(:num)/ekspor/(:segment)', 'Analitik::eksporMatkul/$1/$2');
    // Bank soal (SPEC.md §8.1): superadmin dan pengampu; pengajar 403.
    $routes->get('soal/(:num)', 'Soal::lihat/$1');
    $routes->post('soal/(:num)/setujui', 'Soal::setujui/$1');
    $routes->post('soal/(:num)/tolak', 'Soal::tolak/$1');
    $routes->post('soal/(:num)/terjemahan', 'Soal::terjemahan/$1');
    $routes->get('soal/(:num)/ubah', 'Soal::sunting/$1');
    $routes->post('soal/(:num)/ubah', 'Soal::simpan/$1');
    // Laporan satu impor: superadmin; pengampu hanya untuk suntingan di mata kuliahnya.
    $routes->get('impor/(:segment)', 'ImporSoal::lihat/$1');
});

// Superadmin saja (SPEC.md §3): tahun ajaran dan mata kuliah.
$routes->group('', ['filter' => 'masuk:superadmin'], static function (RouteCollection $routes): void {
    $routes->get('tahun-ajaran', 'TahunAjaran::index');
    $routes->post('tahun-ajaran', 'TahunAjaran::simpan');
    $routes->get('tahun-ajaran/baru', 'TahunAjaran::baruForm');
    $routes->post('tahun-ajaran/baru', 'TahunAjaran::baruRingkas');
    $routes->post('tahun-ajaran/baru/jalankan', 'TahunAjaran::baruJalankan');
    $routes->get('tahun-ajaran/(:num)', 'TahunAjaran::sunting/$1');
    $routes->post('tahun-ajaran/(:num)', 'TahunAjaran::perbarui/$1');
    $routes->post('tahun-ajaran/(:num)/aktifkan', 'TahunAjaran::aktifkan/$1');
    $routes->get('audit', 'AuditLog::index');
    // Unggah soal JSON: superadmin saja (SPEC.md §3, §8.3).
    $routes->get('impor', 'ImporSoal::index');
    $routes->post('impor', 'ImporSoal::unggah');
    $routes->get('mata-kuliah/baru', 'MataKuliah::baru');
    $routes->post('mata-kuliah', 'MataKuliah::simpan');
    $routes->get('mata-kuliah/(:num)/ubah', 'MataKuliah::sunting/$1');
    $routes->post('mata-kuliah/(:num)', 'MataKuliah::perbarui/$1');
});
