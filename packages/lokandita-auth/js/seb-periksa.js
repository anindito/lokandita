// Halaman periksa SEB: menampilkan versi yang dilaporkan API JavaScript SEB (SafeExamBrowser.version),
// bila ada. Hanya untuk dibaca manusia; server tidak pernah mempercayai nilai ini.
(function () {
  'use strict';
  var wadah = document.querySelector('[data-seb-versi]');
  if (!wadah) { return; }
  var seb = window.SafeExamBrowser;
  if (seb && typeof seb.version === 'string' && seb.version !== '') { wadah.textContent = seb.version; }
}());
