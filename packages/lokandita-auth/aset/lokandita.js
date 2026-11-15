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
})();
