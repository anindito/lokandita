// Grafik analitik (SPEC.md §12), digambar Chart.js dari TABEL yang sudah ada di halaman:
// angka hanya ada di satu tempat, dan tanpa skrip tabelnya tetap terbaca. Tidak ada
// data yang diambil dari server oleh berkas ini.
(function () {
  'use strict';
  if (!window.Chart) { return; }
  var gaya = window.getComputedStyle(document.documentElement);
  var warna = function (nama, cadangan) { return (gaya.getPropertyValue(nama) || '').trim() || cadangan; };
  var teks = warna('--teks', '#151a33');
  var garis = warna('--garis', '#d5d8e3');
  var palet = [warna('--utama', '#2b3a8c'), warna('--emas', '#b26a00'), warna('--hijau', '#1b7a4b'), warna('--bahaya', '#b3261e'), '#6a3fb5', '#1769aa'];
  window.Chart.defaults.color = teks;
  window.Chart.defaults.borderColor = garis;
  window.Chart.defaults.font.family = gaya.fontFamily || 'sans-serif';
  // Angka dan sumbu selalu kiri-ke-kanan, juga di halaman berbahasa Arab (SPEC.md §11).
  var sumbu = { y: { beginAtZero: true, ticks: { precision: 0 } } };

  var sebaran = document.querySelector('canvas[data-grafik="sebaran"]');
  var tabelSebaran = document.querySelector('table[data-sebaran]');
  if (sebaran && tabelSebaran) {
    sebaran.dir = 'ltr';
    new window.Chart(sebaran, {
      type: 'bar',
      data: {
        labels: Array.prototype.map.call(tabelSebaran.querySelectorAll('thead th'), function (s) { return s.textContent.trim(); }),
        datasets: [{
          label: sebaran.dataset.label,
          data: Array.prototype.map.call(tabelSebaran.querySelectorAll('tbody td'), function (s) { return Number(s.textContent); }),
          backgroundColor: palet[0]
        }]
      },
      options: { responsive: true, maintainAspectRatio: false, animation: false, scales: sumbu, plugins: { legend: { display: false } } }
    });
  }

  var banding = document.querySelector('canvas[data-grafik="banding"]');
  var tabelBanding = document.querySelector('table[data-banding]');
  if (banding && tabelBanding) {
    banding.dir = 'ltr';
    var baris = tabelBanding.querySelectorAll('tbody tr');
    var kelas = Array.prototype.map.call(tabelBanding.querySelectorAll('thead th[data-kelas]'), function (s) { return s.dataset.kelas; });
    new window.Chart(banding, {
      type: 'bar',
      data: {
        labels: Array.prototype.map.call(baris, function (b) { return b.querySelector('[data-pertemuan]').textContent.trim(); }),
        datasets: kelas.map(function (nama, i) {
          return {
            label: nama,
            data: Array.prototype.map.call(baris, function (b) {
              var sel = b.querySelectorAll('td')[i];
              return sel && sel.dataset.rata !== '' ? Number(sel.dataset.rata) : null;
            }),
            backgroundColor: palet[i % palet.length]
          };
        })
      },
      options: { responsive: true, maintainAspectRatio: false, animation: false, scales: { y: { beginAtZero: true, max: 100 } } }
    });
  }
}());
