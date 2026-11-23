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

// Tanpa sesi (KONTRAK.md §4 "Public routes"): halaman depan publik LokaKode (SPEC.md §15)
// dan pilihan bahasa. Tidak ada soal, kelas atau data mahasiswa di sini.
$routes->get('/', 'Depan::index');
RuteBersama::publik($routes);
// Safe Exam Browser (SPEC.md §13.1), tanpa sesi: petunjuk, berkas .seb per mata kuliah, halaman
// periksa header, dan alamat keluar (quitURL). Yang dibaca hanya konfigurasi SEB (KONTRAK.md §8).
$routes->get('seb', 'Seb::petunjuk');
$routes->get('seb/periksa', 'Seb::periksa');
$routes->get('seb/keluar', 'Seb::keluar');
$routes->get('seb/unduh/(:segment)', 'Seb::unduh/$1');

// Filter `seb` (App\Filters\WajibSeb, KONTRAK.md §8) selalu SESUDAH `masuk`: ia hanya berlaku bagi
// peran mahasiswa dan melewatkan staf. Setiap rute yang bisa dibuka mahasiswa memakainya, kecuali
// ekspor data sendiri di bawah — hak atas data sendiri tidak bergantung pada SEB, dan SEB melarang unduhan.

// Semua pengguna yang masuk.
$routes->group('', ['filter' => ['masuk', 'seb']], static function (RouteCollection $routes): void {
    $routes->get('beranda', 'Beranda::index');
    // Hasil dan kemajuan pekerjaan judge milik pengguna itu sendiri (KONTRAK.md §6).
    $routes->get('api/executions/(:segment)', 'Eksekusi::lihat/$1');
});
$routes->group('', ['filter' => 'masuk'], static function (RouteCollection $routes): void {
    // Ekspor data sendiri (KONTRAK.md §14): selalu milik pengguna sesi ini.
    $routes->get('data-saya', 'DataSaya::index');
    $routes->post('data-saya/unduh', 'DataSaya::unduh');
});

// Mahasiswa saja (SPEC.md §11): ruang latihan. Lingkupnya — pertemuan kelasnya sendiri,
// kiriman dan drafnya sendiri — diperiksa App\Libraries\Ruang di dalam kueri.
$routes->group('', ['filter' => ['masuk:mahasiswa', 'seb']], static function (RouteCollection $routes): void {
    // Denyut SEB tiap 25 detik (KONTRAK.md §8): filter `seb` yang memeriksa dan memperpanjang sewa.
    $routes->post('api/seb/denyut', 'RuangLatihan::denyutSeb');
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
    // Klarifikasi soal (SPEC.md §14): bertanya, dan membaca jawaban untuk dirinya atau untuk kelasnya.
    $routes->get('api/soal/(:num)/klarifikasi', 'RuangLatihan::klarifikasi/$1');
    $routes->post('api/soal/(:num)/klarifikasi', 'RuangLatihan::tanya/$1');
    $routes->post('api/integritas', 'RuangLatihan::integritas');
    // Pemberitahuan milik sendiri (SPEC.md §14, KONTRAK.md §17): tidak ada rute yang menerima id.
    // Pengumuman kelasnya sendiri (SPEC.md §14); kelasnya dari pendaftaran, tidak dari URL.
    $routes->get('pengumuman', 'Pengumuman::saya');
    $routes->get('notifikasi', 'Notifikasi::index');
    $routes->post('notifikasi/baca', 'Notifikasi::baca');
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
    // Pertemuan ujian (SPEC.md §6): buka selama N menit — siapa pun yang melihat kelas itu;
    // merilis nilai — superadmin dan pengampu saja (pengajar 403).
    $routes->post('kelas/(:num)/pertemuan/(:num)/buka-selama', 'Jendela::bukaSelama/$1/$2');
    $routes->post('kelas/(:num)/pertemuan/(:num)/rilis', 'Jendela::rilis/$1/$2');
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
    // Peristiwa integritas per kelas dan ekspornya (SPEC.md §13.2, KONTRAK.md §17): siapa pun yang melihat kelas itu.
    $routes->get('kelas/(:num)/integritas', 'Integritas::index/$1');
    $routes->get('kelas/(:num)/integritas/ekspor/(:segment)', 'Integritas::ekspor/$1/$2');
    // Klarifikasi soal per kelas (SPEC.md §14, KONTRAK.md §17): siapa pun yang melihat kelas itu membaca dan menjawab.
    $routes->get('kelas/(:num)/klarifikasi', 'Klarifikasi::index/$1');
    $routes->post('kelas/(:num)/klarifikasi/(:num)/jawab', 'Klarifikasi::jawab/$1/$2');
    // Pengumuman kelas (SPEC.md §14, KONTRAK.md §17): siapa pun yang melihat kelas itu menulis dan menarik.
    $routes->get('kelas/(:num)/pengumuman', 'Pengumuman::index/$1');
    $routes->post('kelas/(:num)/pengumuman', 'Pengumuman::simpan/$1');
    $routes->post('kelas/(:num)/pengumuman/(:num)/tarik', 'Pengumuman::tarik/$1/$2');
    // Konfigurasi SEB mata kuliah (SPEC.md §13.1): superadmin dan pengampu; pengajar 403.
    $routes->get('mata-kuliah/(:num)/seb', 'SebKelola::lihat/$1');
    $routes->post('mata-kuliah/(:num)/seb', 'SebKelola::simpan/$1');
    $routes->post('mata-kuliah/(:num)/seb/kunci', 'SebKelola::kunci/$1');
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
