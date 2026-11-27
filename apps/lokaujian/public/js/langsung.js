// Tampilan langsung halaman sesi dosen (SPEC-LOKAUJIAN.md §10): tiap 5 detik bertanya ke
// /sesi/<id>/langsung dan menyalin teks yang sudah disusun server ke sel-sel tabel. Skrip ini
// tidak memutuskan dan tidak menghitung apa pun. Baris tidak dibuat ulang, jadi centang yang
// sudah dipilih dosen tidak hilang. Bila keadaan sesi berubah atau ada peserta baru, halaman
// meminta dimuat ulang — tidak memuat ulang sendiri, supaya isian yang sedang diketik tidak hilang.
(function () {
  'use strict';
  var akar = document.querySelector('[data-langsung]');
  if (!akar) { return; }
  var q = function (p) { return document.querySelector(p); };
  var isi = function (el, teks) { if (el && el.textContent !== teks) { el.textContent = teks; } };
  var segar = function (d) {
    isi(q('[data-waktu-server]'), d.kini + ' WIB');
    isi(q('[data-ringkas]'), d.ringkas_teks);
    var pita = q('[data-penjadwal]');
    if (pita) { pita.dataset.penjadwal = d.penjadwal ? 'hidup' : 'mati'; pita.hidden = d.penjadwal; }
    var usang = d.keadaan !== akar.dataset.keadaan;
    d.baris.forEach(function (b) {
      var tr = akar.querySelector('tr[data-id="' + b.id + '"]');
      if (!tr) { usang = true; return; }
      tr.dataset.status = b.status;
      Object.keys(b.teks).forEach(function (k) { isi(tr.querySelector('[data-sel="' + k + '"]'), b.teks[k]); });
    });
    q('[data-usang]').hidden = !usang;
    q('[data-langsung-gagal]').hidden = true;
    akar.dataset.segar = String((parseInt(akar.dataset.segar || '0', 10) || 0) + 1);
  };
  var tanya = function () {
    fetch('/sesi/' + akar.dataset.langsung + '/langsung', { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } })
      .then(function (r) { if (!r.ok) { throw new Error('status ' + r.status); } return r.json(); })
      .then(segar)
      .catch(function () { q('[data-langsung-gagal]').hidden = false; })
      .then(function () { window.setTimeout(tanya, 5000); });
  };
  window.setTimeout(tanya, 5000);
  // "Centang semua" hanya mengubah kotak centang di halaman ini.
  var semua = q('[data-centang-semua]');
  if (semua) {
    semua.addEventListener('change', function () {
      akar.querySelectorAll('input[name="mahasiswa[]"]').forEach(function (c) { c.checked = semua.checked; });
    });
  }
})();
