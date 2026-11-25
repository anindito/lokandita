// Halaman menunggu (SPEC-LOKAUJIAN.md §7.2): bertanya ke server tiap 5 detik apakah sesi sudah
// menerima "Mulai". Dikendalikan dosen: form Mulai dikirim sendiri, sehingga halaman ini berubah
// menjadi halaman ujian tanpa disentuh. Per mahasiswa: halaman dimuat ulang supaya tombolnya muncul.
// Tidak ada keputusan yang diambil di sini; server memeriksa semuanya lagi.
(function () {
  'use strict';
  var form = document.querySelector('[data-mulai-form]');
  if (!form || form.dataset.menerima === '1' || !form.querySelector('[data-menunggu]')) { return; }
  var tanya = function () {
    fetch('/api/sesi/' + form.dataset.sesi + '/keadaan', { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } })
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (k) {
        if (k && k.menerima) {
          if (form.dataset.otomatis === '1') { form.submit(); } else { window.location.reload(); }
          return;
        }
        if (k && k.sesi_selesai) { window.location.reload(); return; }
        window.setTimeout(tanya, 5000);
      })
      .catch(function () { window.setTimeout(tanya, 5000); });
  };
  window.setTimeout(tanya, 5000);
})();
