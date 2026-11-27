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

  // Daftar bercentang: kotak "pilih semua" di kepala tabel. Tanpa skrip ini
  // setiap baris tetap bisa dicentang satu per satu.
  document.querySelectorAll('[data-pilih-semua]').forEach(function (semua) {
    var form = semua.closest('form');
    if (!form) { return; }
    semua.addEventListener('change', function () {
      form.querySelectorAll('input[type=checkbox][name="ids[]"]').forEach(function (kotak) {
        kotak.checked = semua.checked;
      });
    });
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

  // Menu kepala halaman publik di layar sempit (di bawah 768 px; lihat .kepala-menu di lokandita.css).
  // Tombol membuka dan menutup panel; panel juga tertutup oleh Escape, klik di luar, klik pada
  // tautan di dalamnya, dan saat layar melebar. Fokus masuk ke panel saat dibuka dengan tombol
  // dan kembali ke tombol saat ditutup dengan tombol atau Escape.
  document.querySelectorAll('[data-menu-kepala]').forEach(function (wadah) {
    var tombol = wadah.querySelector('[data-menu-tombol]');
    var panel = tombol ? document.getElementById(tombol.getAttribute('aria-controls')) : null;
    if (!tombol || !panel) { return; }
    var terbuka = function () { return tombol.getAttribute('aria-expanded') === 'true'; };
    var atur = function (buka, pindahFokus) {
      tombol.setAttribute('aria-expanded', buka ? 'true' : 'false');
      wadah.toggleAttribute('data-menu-buka', buka);
      if (pindahFokus) { (buka ? panel.querySelector('a') || tombol : tombol).focus(); }
    };
    tombol.addEventListener('click', function () { atur(!terbuka(), true); });
    panel.addEventListener('click', function (e) {
      if (e.target.closest('a')) { atur(false, false); }
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && terbuka()) { atur(false, true); }
    });
    document.addEventListener('click', function (e) {
      if (terbuka() && !wadah.contains(e.target)) { atur(false, false); }
    });
    window.matchMedia('(min-width: 768px)').addEventListener('change', function (e) {
      if (e.matches && terbuka()) { atur(false, false); }
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
