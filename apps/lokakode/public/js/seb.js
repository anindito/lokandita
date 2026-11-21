// Denyut Safe Exam Browser (SPEC.md §13.1): tiap 25 detik bertanya ke server. Server memeriksa
// header SEB pada permintaan ini; bila ditolak (403), halaman dimuat ulang dan server menampilkan
// halaman "wajib SEB". Draf sudah tersimpan otomatis tiap 5 detik, jadi tidak ada yang hilang.
(function () {
  'use strict';
  var JEDA = 25000;
  var denyut = function () {
    var isi = new URLSearchParams();
    isi.set('csrf', document.body.dataset.csrf || '');
    fetch('/api/seb/denyut', {
      method: 'POST', body: isi, credentials: 'same-origin', redirect: 'manual',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (jawab) {
      if (jawab.status !== 403) { return; }
      return jawab.json().then(function (j) {
        if (j && j.error === 'seb_wajib') { window.location.reload(); }
      });
    }).catch(function () { /* jaringan putus sebentar: dicoba lagi pada denyut berikutnya */ });
  };
  window.setInterval(denyut, JEDA);
}());
