// Form staf yang akibatnya tidak bisa ditarik kembali (mis. "Akhiri") bertanya dulu.
// Teksnya ada di atribut data-konfirmasi form itu.
(function () {
  'use strict';
  document.addEventListener('submit', function (e) {
    var form = e.target;
    var teks = form && form.getAttribute ? form.getAttribute('data-konfirmasi') : null;
    if (teks && !window.confirm(teks)) { e.preventDefault(); }
  });
})();
