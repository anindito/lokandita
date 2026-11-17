// Halaman pertemuan mahasiswa (SPEC.md §11): saring dan urutkan kartu soal,
// dan hitung mundur waktu tutup. Tanpa skrip ini semua kartu tetap tampil.
(function () {
  'use strict';

  var daftar = document.querySelector('[data-kartu-soal]');
  var form = document.querySelector('[data-saring-soal]');
  if (daftar && form) {
    var kosong = document.querySelector('[data-tidak-cocok]');
    var kartu = Array.prototype.slice.call(daftar.children);
    var urutanStatus = { dicoba: 0, belum: 1, dinilai: 2, selesai: 3 };
    var terapkan = function () {
      var saring = {};
      form.querySelectorAll('[data-saring]').forEach(function (s) { saring[s.dataset.saring] = s.value; });
      var urut = form.querySelector('[data-urut]').value;
      var tampak = 0;
      kartu.forEach(function (k) {
        var cocok = (!saring.tingkat || k.dataset.tingkat === saring.tingkat) && (!saring.status || k.dataset.status === saring.status);
        k.hidden = !cocok;
        if (cocok) { tampak += 1; }
      });
      var nomor = function (k) { return Number(k.dataset.nomor); };
      var poin = function (k) { return Number(k.dataset.poin); };
      kartu.slice().sort(function (a, b) {
        if (urut === 'poin-naik') { return poin(a) - poin(b) || nomor(a) - nomor(b); }
        if (urut === 'poin-turun') { return poin(b) - poin(a) || nomor(a) - nomor(b); }
        if (urut === 'status') { return urutanStatus[a.dataset.status] - urutanStatus[b.dataset.status] || nomor(a) - nomor(b); }
        return nomor(a) - nomor(b);
      }).forEach(function (k) { daftar.appendChild(k); });
      if (kosong) { kosong.hidden = tampak > 0; }
    };
    form.hidden = false;
    form.addEventListener('change', terapkan);
    form.addEventListener('submit', function (e) { e.preventDefault(); });
  }

  // Hitung mundur: waktu tutup dikirim server dalam UTC; jam peramban hanya dipakai untuk selisihnya.
  document.querySelectorAll('[data-hitung-mundur]').forEach(function (el) {
    var tutup = Date.parse(el.dataset.hitungMundur);
    var isi = function (pola, a, b) { return pola.replace('{0}', String(a)).replace('{1}', String(b)); };
    var perbarui = function () {
      var sisa = Math.floor((tutup - Date.now()) / 1000);
      if (sisa <= 0) { el.textContent = el.dataset.teksHabis; return; }
      el.textContent = sisa >= 3600
        ? isi(el.dataset.teksJam, Math.floor(sisa / 3600), Math.floor((sisa % 3600) / 60))
        : isi(el.dataset.teksMenit, Math.floor(sisa / 60), sisa % 60);
      window.setTimeout(perbarui, sisa >= 3600 ? 30000 : 1000);
    };
    perbarui();
  });
})();
