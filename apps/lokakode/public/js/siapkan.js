// Layar "menyiapkan soal Anda" (SPEC.md §8.2): bertanya tiap dua detik apakah
// instans sudah ada, menampilkan perkiraan yang jujur, lalu memuat ulang halaman.
(function () {
  'use strict';
  var wadah = document.querySelector('[data-siapkan]');
  if (!wadah) { return; }
  var pesan = wadah.querySelector('[data-siapkan-pesan]');
  var isi = function (pola, angka) { return pola.replace('{0}', String(angka)); };

  var tanya = function () {
    fetch(wadah.dataset.siapkan, { credentials: 'same-origin', redirect: 'manual', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (jawab) {
        if (!jawab.ok) { throw new Error('status ' + jawab.status); }
        return jawab.json();
      })
      .then(function (k) {
        if (k.siap) { window.location.reload(); return; }
        if (k.gagal) { pesan.textContent = wadah.dataset.teksGagal; pesan.className = 'teks-bahaya'; return; }
        if (!k.worker || k.perkiraan === null) {
          pesan.textContent = wadah.dataset.teksMati;
          pesan.className = 'teks-bahaya';
        } else {
          pesan.className = '';
          pesan.textContent = k.perkiraan >= 120
            ? isi(wadah.dataset.teksMenit, Math.ceil(k.perkiraan / 60))
            : isi(wadah.dataset.teksDetik, Math.max(1, k.perkiraan));
        }
        window.setTimeout(tanya, 2000);
      })
      .catch(function () { window.setTimeout(tanya, 5000); });
  };
  window.setTimeout(tanya, 2000);
})();
