// Perilaku kecil bersama ketiga aplikasi. Tanpa pustaka, tanpa eval: aman untuk CSP ketat.
(function () {
  'use strict';

  // Toast: bisa ditutup, dan hilang sendiri setelah 8 detik.
  document.querySelectorAll('[data-toast]').forEach(function (toast) {
    var tutup = function () { toast.remove(); };
    var tombol = toast.querySelector('[data-toast-tutup]');
    if (tombol) { tombol.addEventListener('click', tutup); }
    window.setTimeout(tutup, 8000);
  });

  // Menu akun: tutup dengan Escape atau klik di luar.
  document.querySelectorAll('[data-menu-akun]').forEach(function (menu) {
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && menu.open) {
        menu.open = false;
        menu.querySelector('summary').focus();
      }
    });
    document.addEventListener('click', function (e) {
      if (menu.open && !menu.contains(e.target)) { menu.open = false; }
    });
  });

  // Sesi: peringatan 2 menit sebelum habis, tombol "Tetap masuk", dan keluar-otomatis
  // setelah 30 menit tanpa aktivitas (SPEC.md §4.4). Server tetap yang menentukan;
  // skrip ini hanya membuat pengguna tidak terkejut.
  var badan = document.body;
  var peringatan = document.querySelector('[data-sesi-peringatan]');
  if (badan.dataset.sesiSisa && peringatan) {
    var PERINGATAN_DETIK = 120;
    var IDLE_DETIK = 30 * 60;
    var KABARI_SETIAP = 5 * 60;
    var habisPada = Date.now() + Number(badan.dataset.sesiSisa) * 1000;
    var aktifTerakhir = Date.now();
    var kabarTerakhir = Date.now();
    var pakaiIdle = badan.dataset.sesiIdle === '1';

    var perpanjang = function (diam) {
      return fetch('/sesi/perpanjang', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'csrf=' + encodeURIComponent(badan.dataset.csrf)
      }).then(function (jawab) {
        if (!jawab.ok) { throw new Error('status ' + jawab.status); }
        return jawab.json();
      }).then(function (data) {
        habisPada = Date.now() + data.sisa * 1000;
        kabarTerakhir = Date.now();
        peringatan.hidden = true;
      }).catch(function () {
        if (!diam) {
          peringatan.querySelector('[data-sesi-teks]').textContent = peringatan.dataset.pesanGagal;
          peringatan.hidden = false;
        }
      });
    };

    peringatan.querySelector('[data-sesi-tetap]').addEventListener('click', function () { perpanjang(false); });

    ['keydown', 'pointerdown', 'scroll'].forEach(function (jenis) {
      window.addEventListener(jenis, function () {
        aktifTerakhir = Date.now();
        // Selama pengguna aktif, server dikabari paling sering 5 menit sekali.
        if (Date.now() - kabarTerakhir > KABARI_SETIAP * 1000) {
          kabarTerakhir = Date.now();
          perpanjang(true);
        }
      }, { passive: true });
    });

    window.setInterval(function () {
      var sisa = (habisPada - Date.now()) / 1000;
      if (sisa <= 0) { window.location.reload(); return; }
      if (sisa <= PERINGATAN_DETIK) { peringatan.hidden = false; }
      if (pakaiIdle && Date.now() - aktifTerakhir > IDLE_DETIK * 1000) {
        var form = document.querySelector('[data-form-keluar]');
        if (form) { form.submit(); }
      }
    }, 5000);
  }
})();
