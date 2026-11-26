// Halaman ujian mahasiswa LokaUjian (SPEC-LOKAUJIAN.md §8, §9.2; KONTRAK.md §22).
//
// Yang diputuskan SERVER, bukan skrip ini: boleh-tidaknya sebuah jawaban disimpan, sisa waktu,
// dan siapa yang memegang pengerjaan. Skrip ini hanya menampilkan, mengirim, dan mengulang
// kiriman sampai server menerimanya. Jam peramban tidak dipakai untuk keputusan apa pun: hitung
// mundur dihitung dari "sisa detik" kiriman server dan diperbaiki oleh setiap jawaban server.
//
// Jawaban yang belum terkirim hanya ada di memori halaman ini. Sengaja tidak ditulis ke
// localStorage: di komputer laboratorium ia akan tertinggal untuk pemakai berikutnya.
(function () {
  'use strict';

  var akar = document.getElementById('ujian');
  if (!akar) { return; }
  var D = akar.dataset;
  var T = JSON.parse(D.teks);
  var csrf = document.body.dataset.csrf || '';
  var dasarApi = '/api/sesi/' + D.sesi;
  var bolehTempel = D.bolehTempel === '1';

  var q = function (pemilih, dari) { return (dari || akar).querySelector(pemilih); };
  var qa = function (pemilih, dari) { return Array.prototype.slice.call((dari || akar).querySelectorAll(pemilih)); };
  var isi = function (teks, a, b) { return teks.replace('{0}', a).replace('{1}', b); };

  // ------------------------------------------------------------------ keadaan soal
  var soal = qa('[data-halaman-soal]').map(function (el, i) {
    var kotak = q('input[type="text"], textarea', el);
    return {
      i: i, el: el, id: parseInt(el.dataset.id, 10), tipe: el.dataset.tipe, kelompok: el.dataset.kelompok,
      n: parseInt(el.dataset.n, 10) || 0,   // pencacah simpan terakhir yang DIKIRIM
      kotor: false,                        // ada perubahan yang belum diterima server
      terbang: false,                      // sebuah kiriman sedang di jalan
      kotak: kotak, ragu: q('[data-ragu]', el), tunda: null, palingLama: null
    };
  });
  var kini = 0;
  var berhenti = false;
  var gagalBeruntun = 0;
  var pernahGagal = false;

  var nilai = function (s) {
    if (s.tipe === 'isian' || s.tipe === 'uraian') { return s.kotak.value; }
    var pilih = q('input[type="radio"]:checked', s.el);
    if (!pilih) { return null; }
    return s.tipe === 'pilihan_ganda' ? parseInt(pilih.value, 10) : pilih.value;
  };
  var terjawab = function (s) { var v = nilai(s); return v !== null && v !== ''; };

  // ------------------------------------------------------------------ kepala: keadaan simpan
  var elSimpan = q('[data-simpan]');
  var elSimpanJam = q('[data-simpan-jam]');
  var jamTersimpan = D.dimuat;
  var segarkanSimpan = function () {
    var adaTerbang = soal.some(function (s) { return s.terbang; });
    var adaKotor = soal.some(function (s) { return s.kotor; });
    var keadaan = 'tersimpan';
    if (adaKotor || adaTerbang) { keadaan = pernahGagal && gagalBeruntun > 0 ? 'belum' : 'menyimpan'; }
    elSimpan.dataset.keadaan = keadaan;
    elSimpan.firstChild.nodeValue = (keadaan === 'tersimpan' ? T.tersimpan : keadaan === 'menyimpan' ? T.menyimpan : T.belumTersimpan) + ' ';
    elSimpanJam.textContent = keadaan === 'tersimpan' ? jamTersimpan : '';
    q('[data-luring]').hidden = gagalBeruntun === 0;
  };

  // ------------------------------------------------------------------ berhenti
  var hentikan = function (teks) {
    if (berhenti) { return; }
    berhenti = true;
    qa('input, textarea, button', q('[data-badan]')).forEach(function (el) { el.disabled = true; });
    q('[data-tinjau]').hidden = true;
    q('[data-badan]').hidden = true;
    q('[data-henti-teks]').textContent = teks;
    q('[data-henti]').hidden = false;
    q('[data-luring]').hidden = true;
  };
  var pesanTolak = { waktu_habis: T.waktuHabis, dikumpulkan: T.dikumpulkan, diambil_alih: T.diambilAlih, seb_wajib: T.sebBerhenti };

  // ------------------------------------------------------------------ hitung mundur
  var elJam = q('[data-jam]');
  var elUmum = q('[data-umumkan]');
  var sisaServer = parseInt(D.sisa, 10);
  var berlaluServer = parseInt(D.berlalu, 10) || 0;
  var tanpaAkhir = D.tanpaAkhir === '1';
  var saatTerima = performance.now();     // jam monoton halaman, bukan jam dinding
  var sudahUmum = {};
  var setSisa = function (sisa) { sisaServer = sisa; saatTerima = performance.now(); };
  var dua = function (n) { return (n < 10 ? '0' : '') + n; };
  var bentuk = function (detik) {
    detik = Math.max(0, Math.floor(detik));
    var j = Math.floor(detik / 3600), m = Math.floor((detik % 3600) / 60), d = detik % 60;
    return (j > 0 ? j + ':' : '') + dua(m) + ':' + dua(d);
  };
  var sisaKini = function () { return sisaServer - (performance.now() - saatTerima) / 1000; };
  var habisDitangani = false;
  var detak = function () {
    if (berhenti) { return; }
    var lewat = (performance.now() - saatTerima) / 1000;
    var sisa = sisaServer - lewat;
    var wadah = elJam.parentNode;
    if (tanpaAkhir) {
      elJam.textContent = bentuk(berlaluServer + lewat);
      wadah.setAttribute('aria-label', T.berjalan);
      wadah.dataset.tingkat = '';
    } else {
      elJam.textContent = bentuk(sisa);
      wadah.setAttribute('aria-label', T.sisa);
      wadah.dataset.tingkat = sisa <= 60 ? '1' : sisa <= 300 ? '5' : sisa <= 600 ? '10' : '';
      [[600, T.umum10], [300, T.umum5], [60, T.umum1]].forEach(function (u) {
        // Diumumkan sekali, saat melewati ambangnya (bukan ketika halaman dibuka di bawah ambang).
        if (sisa <= u[0] && sisa > u[0] - 5 && !sudahUmum[u[0]]) { sudahUmum[u[0]] = true; elUmum.textContent = u[1]; }
      });
    }
    if (sisa <= 0 && !habisDitangani) {
      // Waktu habis menurut hitungan halaman: semua yang belum terkirim dikirim sekarang (server
      // masih menerimanya dalam tenggangnya), lalu server ditanya. Yang menghentikan halaman
      // adalah jawaban server, bukan hitungan ini.
      habisDitangani = true;
      soal.forEach(function (s) { if (s.kotor) { kirim(s); } });
      window.setTimeout(tanyaKeadaan, 1500);
      window.setTimeout(function () { habisDitangani = false; }, 4000);
    }
  };

  // ------------------------------------------------------------------ simpan
  var jadwalUlang = function (s) {
    var jeda = Math.min(10000, 1500 * Math.pow(2, Math.min(gagalBeruntun, 3)));
    window.setTimeout(function () { if (s.kotor && !s.terbang) { kirim(s); } }, jeda);
  };
  var kirim = function (s) {
    if (berhenti) { return; }
    window.clearTimeout(s.tunda); s.tunda = null;
    window.clearTimeout(s.palingLama); s.palingLama = null;
    if (s.terbang) { return; }            // yang sedang di jalan selesai dulu; lalu dikirim lagi bila masih kotor
    s.terbang = true;
    s.kotor = false;
    s.n += 1;
    var n = s.n;
    segarkanSimpan();
    fetch(dasarApi + '/jawaban', {
      method: 'POST', credentials: 'same-origin', cache: 'no-store',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
      body: JSON.stringify({ halaman: D.halaman, soal: s.id, nilai: nilai(s), ragu: s.ragu.checked, n: n })
    }).then(function (r) {
      return r.json().catch(function () { return {}; }).then(function (b) { return { status: r.status, b: b }; });
    }).then(function (h) {
      s.terbang = false;
      if (h.status === 200 && h.b.ok) {
        terjangkau();
        gagalBeruntun = 0;
        jamTersimpan = h.b.disimpan;
        setSisa(h.b.sisa);
        if (s.kotor) { kirim(s); }
      } else if (h.status === 409 || h.status === 403) {
        // Tidak akan pernah diterima: waktu habis, sudah dikumpulkan, diambil alih, atau SEB.
        s.kotor = false;
        hentikan(pesanTolak[h.b.error] || T.waktuHabis);
      } else if (h.status === 422 || h.status === 404) {
        // Kiriman ini sendiri yang salah (mis. melewati batas karakter): tidak diulang apa adanya.
        gagalBeruntun = 0;
      } else {
        throw new Error('status ' + h.status);
      }
      segarkanSimpan();
    }).catch(function () {
      // Sambungan putus, server sedang dimuat ulang, atau dibatasi lajunya: jawaban tetap di
      // halaman dan dicoba lagi sampai diterima.
      s.terbang = false;
      s.kotor = true;
      gagalBeruntun += 1;
      pernahGagal = true;
      segarkanSimpan();
      jadwalUlang(s);
    });
  };
  // Pilihan disimpan seketika. Teks: 2 detik sesudah ketikan berhenti, dan paling lama 10 detik
  // sejak perubahan pertama yang belum tersimpan.
  var ubah = function (s, seketika) {
    if (berhenti) { return; }
    s.kotor = true;
    segarkanPeta();
    segarkanSimpan();
    if (seketika) { kirim(s); return; }
    window.clearTimeout(s.tunda);
    s.tunda = window.setTimeout(function () { kirim(s); }, 2000);
    if (!s.palingLama) { s.palingLama = window.setTimeout(function () { kirim(s); }, 10000); }
  };
  var kirimBilaKotor = function (s) { if (s && s.kotor) { kirim(s); } };

  // ------------------------------------------------------------------ peta dan perpindahan
  var tombolPeta = qa('[data-ke]');
  var segarkanPeta = function () {
    soal.forEach(function (s) {
      var t = tombolPeta[s.i];
      var jawab = terjawab(s);
      t.classList.toggle('terjawab', jawab);
      t.classList.toggle('ragu', s.ragu.checked);
      t.classList.toggle('kini', s.i === kini);
      if (s.i === kini) { t.setAttribute('aria-current', 'true'); } else { t.removeAttribute('aria-current'); }
      t.setAttribute('aria-label', isi(T.petaSoal, s.i + 1) + ', ' + (jawab ? T.petaTerjawab : T.petaBelum) + (s.ragu.checked ? ', ' + T.petaRagu : ''));
      var hitung = q('[data-hitung]', s.el);
      if (hitung) { hitung.textContent = isi(T.karakter, Array.from(s.kotak.value).length, hitung.dataset.maks); }
    });
  };
  var tampil = function (i) {
    if (i < 0 || i >= soal.length) { return; }
    kirimBilaKotor(soal[kini]);            // sebelum pindah, yang sedang diketik disimpan
    kini = i;
    soal.forEach(function (s) { s.el.hidden = s.i !== i; });
    qa('[data-bersama]').forEach(function (b) { b.hidden = b.dataset.bersama !== soal[i].kelompok; });
    q('[data-sebelumnya]').disabled = i === 0;
    q('[data-berikutnya]').disabled = i === soal.length - 1;
    q('[data-tinjau]').hidden = true;
    q('[data-badan]').hidden = false;
    segarkanPeta();
  };

  soal.forEach(function (s) {
    qa('input[type="radio"]', s.el).forEach(function (r) { r.addEventListener('change', function () { ubah(s, true); }); });
    var hapus = q('[data-hapus-pilihan]', s.el);
    if (hapus) {
      hapus.addEventListener('click', function () {
        qa('input[type="radio"]', s.el).forEach(function (r) { r.checked = false; });
        ubah(s, true);
      });
    }
    s.ragu.addEventListener('change', function () { ubah(s, true); });
    if (s.kotak) {
      s.kotak.addEventListener('input', function () { ubah(s, false); });
      s.kotak.addEventListener('blur', function () { kirimBilaKotor(s); });
    }
  });
  tombolPeta.forEach(function (t) { t.addEventListener('click', function () { tampil(parseInt(t.dataset.ke, 10)); }); });
  q('[data-sebelumnya]').addEventListener('click', function () { tampil(kini - 1); });
  q('[data-berikutnya]').addEventListener('click', function () { tampil(kini + 1); });

  // ------------------------------------------------------------------ tinjau dan kumpulkan
  var daftarNomor = function (wadah, pilihan) {
    wadah.textContent = '';
    if (pilihan.length === 0) { wadah.textContent = T.tinjauTidakAda; return; }
    pilihan.forEach(function (s) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'peta-tombol ltr'; b.textContent = String(s.i + 1);
      b.setAttribute('aria-label', isi(T.petaSoal, s.i + 1));
      b.addEventListener('click', function () { tampil(s.i); });
      wadah.appendChild(b); wadah.appendChild(document.createTextNode(' '));
    });
  };
  var bukaTinjau = function () {
    kirimBilaKotor(soal[kini]);
    var belum = soal.filter(function (s) { return !terjawab(s); });
    q('[data-tinjau-ringkas]').textContent = isi(T.tinjauTerjawab, soal.length - belum.length, soal.length);
    daftarNomor(q('[data-tinjau-belum]'), belum);
    daftarNomor(q('[data-tinjau-ragu]'), soal.filter(function (s) { return s.ragu.checked; }));
    q('[data-badan]').hidden = true;
    q('[data-tinjau]').hidden = false;
    q('#tinjau-judul').scrollIntoView();
  };
  q('[data-selesai]').addEventListener('click', bukaTinjau);
  q('[data-tinjau-kembali]').addEventListener('click', function () { tampil(kini); });
  var sedangKumpul = false;
  q('[data-kumpul-form]').addEventListener('submit', function (e) {
    if (sedangKumpul) { return; }
    e.preventDefault();
    var form = e.target;
    soal.forEach(kirimBilaKotor);
    // Tidak dikumpulkan selama masih ada jawaban yang belum diterima server.
    if (soal.some(function (s) { return s.kotor || s.terbang; })) {
      q('[data-tinjau-tunggu]').hidden = false;
      window.setTimeout(function () { q('[data-tinjau-tunggu]').hidden = true; }, 4000);
      return;
    }
    var belum = soal.filter(function (s) { return !terjawab(s); }).length;
    if (!window.confirm(belum === 0 ? T.yakin : isi(T.yakinKosong, belum))) { return; }
    sedangKumpul = true;
    form.submit();
  });
  window.addEventListener('beforeunload', function (e) {
    if (!berhenti && !sedangKumpul && soal.some(function (s) { return s.kotor || s.terbang; })) { e.preventDefault(); e.returnValue = T.tinggalkan; }
  });

  // ------------------------------------------------------------------ keadaan dari server (tiap 10 detik)
  var tanyaKeadaan = function () {
    if (berhenti) { return; }
    fetch(dasarApi + '/keadaan?halaman=' + encodeURIComponent(D.halaman), { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } })
      .then(function (r) {
        if (r.status === 403) { return r.json().catch(function () { return {}; }).then(function (b) { hentikan(pesanTolak[b.error] || T.sebBerhenti); return null; }); }
        if (!r.ok) { throw new Error('status ' + r.status); }
        return r.json();
      })
      .then(function (k) {
        if (!k || !k.pengerjaan) { return; }
        terjangkau();
        var p = k.pengerjaan;
        if (gagalBeruntun > 0 && !soal.some(function (s) { return s.kotor || s.terbang; })) { gagalBeruntun = 0; segarkanSimpan(); }
        if (p.diambil_alih) { hentikan(T.diambilAlih); return; }
        if (p.dikumpulkan) { hentikan(T.dikumpulkan); return; }
        tanpaAkhir = p.tanpa_akhir;
        berlaluServer = p.berlalu || 0;
        setSisa(p.sisa);
        q('[data-pita-akhiri]').hidden = !p.diakhiri;
        // Tenggat lewat menurut server. Simpan yang masih di jalan diberi kesempatan (tenggang 10
        // detik di server); sesudah itu halaman berhenti.
        if (!p.berjalan && !soal.some(function (s) { return s.terbang; })) { hentikan(T.waktuHabis); }
      })
      .catch(function () { /* dicoba lagi pada putaran berikutnya; simpan punya pengulangannya sendiri */ });
  };

  // ------------------------------------------------------------------ tempel, salin, potong (§9.2)
  var elToast = q('[data-toast-ujian]');
  var toastTunda = null;
  var toast = function (teks) {
    elToast.textContent = teks; elToast.hidden = false;
    window.clearTimeout(toastTunda);
    toastTunda = window.setTimeout(function () { elToast.hidden = true; }, 3500);
  };
  var kirimPeristiwa = function (badan) {
    fetch(dasarApi + '/peristiwa', {
      method: 'POST', credentials: 'same-origin', keepalive: true,
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
      body: JSON.stringify(badan)
    }).catch(function () {});
  };
  var catatTerakhir = {};
  var catat = function (jalur) {
    var saat = performance.now();
    if (catatTerakhir[jalur] && saat - catatTerakhir[jalur] < 1000) { return; }   // satu tindakan sering memicu beberapa peristiwa
    catatTerakhir[jalur] = saat;
    kirimPeristiwa({ jenis: 'paste_blocked', jalur: jalur });
  };
  var kotakTeks = function (el) { return !!(el && el.matches && el.matches('#ujian input[type="text"], #ujian textarea')); };
  var tolakTempel = function (e, jalur) { e.preventDefault(); e.stopPropagation(); toast(T.tempelDiblokir); catat(jalur); };
  var tolakSalin = function (e, jalur) { e.preventDefault(); e.stopPropagation(); toast(T.salinDiblokir); if (!bolehTempel) { catat(jalur); } };

  if (!bolehTempel) {
    // Setiap jalur tempel ditutup sendiri-sendiri, pada tahap tangkap, sebelum kotaknya menerima apa pun.
    akar.addEventListener('paste', function (e) { tolakTempel(e, 'paste'); }, true);
    akar.addEventListener('drop', function (e) { tolakTempel(e, 'drop'); }, true);
    akar.addEventListener('dragover', function (e) { e.preventDefault(); if (e.dataTransfer) { e.dataTransfer.dropEffect = 'none'; } }, true);
    akar.addEventListener('beforeinput', function (e) {
      if (/^insertFrom(Paste|Drop|Yank)/.test(e.inputType || '')) { tolakTempel(e, e.inputType === 'insertFromDrop' ? 'drop' : 'paste'); }
    }, true);
    // Klik tengah menempel di X11; dicegah sejak tombolnya ditekan.
    ['mousedown', 'mouseup', 'auxclick'].forEach(function (nama) {
      akar.addEventListener(nama, function (e) {
        if (e.button === 1) { e.preventDefault(); e.stopPropagation(); if (nama === 'mousedown' && kotakTeks(e.target)) { toast(T.tempelDiblokir); catat('middle_click'); } }
      }, true);
    });
    akar.addEventListener('keydown', function (e) {
      var k = (e.key || '').toLowerCase();
      if ((e.ctrlKey || e.metaKey) && k === 'v') { tolakTempel(e, 'keyboard'); }
      if (e.shiftKey && k === 'insert') { tolakTempel(e, 'shift_insert'); }
    }, true);
  }
  // Menu konteks punya butir Tempel dan Salin: ditiadakan di seluruh halaman ujian.
  akar.addEventListener('contextmenu', function (e) {
    e.preventDefault();
    if (kotakTeks(e.target) && !bolehTempel) { toast(T.tempelDiblokir); catat('context_menu'); }
  }, true);
  // Salin dan potong: di kotak jawaban dan pada teks soal.
  akar.addEventListener('copy', function (e) { tolakSalin(e, 'copy'); }, true);
  akar.addEventListener('cut', function (e) { tolakSalin(e, 'cut'); }, true);
  akar.addEventListener('keydown', function (e) {
    var k = (e.key || '').toLowerCase();
    if ((e.ctrlKey || e.metaKey) && (k === 'c' || k === 'x')) { tolakSalin(e, k === 'c' ? 'copy' : 'cut'); }
    if ((e.ctrlKey || e.metaKey) && k === 'insert') { tolakSalin(e, 'copy'); }
    if (e.shiftKey && k === 'delete' && kotakTeks(e.target)) { tolakSalin(e, 'cut'); }
  }, true);
  ['selectstart', 'dragstart'].forEach(function (nama) {
    akar.addEventListener(nama, function (e) { if (!kotakTeks(e.target)) { e.preventDefault(); } }, true);
  });
  // Cetak diblokir (juga lewat CSS @media print).
  window.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && (e.key || '').toLowerCase() === 'p') { e.preventDefault(); }
  }, true);

  // ------------------------------------------------------------------ peristiwa integritas (§9.3)
  // Skrip ini hanya MELAPORKAN; ia tidak menghukum dan tidak menghentikan apa pun.
  //
  // focus_lost: halaman kehilangan fokus atau disembunyikan; dikirim saat kembali, dengan lamanya.
  var hilangSejak = null;
  var fokusHilang = function () { if (hilangSejak === null && !berhenti) { hilangSejak = performance.now(); } };
  var fokusKembali = function () {
    if (hilangSejak === null) { return; }
    var durasi = Math.round((performance.now() - hilangSejak) / 1000);
    hilangSejak = null;
    if (!berhenti) { kirimPeristiwa({ jenis: 'focus_lost', durasi: durasi }); }
  };
  window.addEventListener('blur', fokusHilang);
  window.addEventListener('focus', fokusKembali);
  document.addEventListener('visibilitychange', function () { if (document.hidden) { fokusHilang(); } else { fokusKembali(); } });

  // display_changed: ukuran layar atau jumlah layar berubah sejak halaman dibuka.
  var layarKini = function () { return { lebar: window.screen.width, tinggi: window.screen.height, layar: typeof window.screen.isExtended === 'boolean' ? window.screen.isExtended : null }; };
  var layarLama = layarKini();
  window.setInterval(function () {
    var l = layarKini();
    if (berhenti || (l.lebar === layarLama.lebar && l.tinggi === layarLama.tinggi && l.layar === layarLama.layar)) { return; }
    layarLama = l;
    kirimPeristiwa({ jenis: 'display_changed', lebar: l.lebar, tinggi: l.tinggi, layar: l.layar });
  }, 3000);

  // reconnected: server tidak terjangkau lebih dari 60 detik, lalu terjangkau lagi. Diukur dari
  // jawaban terakhir yang berhasil (simpan atau tanya keadaan) sampai jawaban berhasil berikutnya.
  var terjangkauTerakhir = performance.now();
  var terjangkau = function () {
    var jeda = Math.round((performance.now() - terjangkauTerakhir) / 1000);
    terjangkauTerakhir = performance.now();
    if (jeda > 60 && !berhenti) { kirimPeristiwa({ jenis: 'reconnected', detik: jeda }); }
  };

  // ------------------------------------------------------------------ tanda air
  var wadahAir = q('[data-tanda-air-wadah]');
  var isiTandaAir = function () {
    var jam = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta', hour12: false });
    var teks = D.tandaAir + ' · ' + jam;
    wadahAir.textContent = '';
    for (var i = 0; i < 24; i++) { var s = document.createElement('span'); s.textContent = teks; wadahAir.appendChild(s); }
  };

  // ------------------------------------------------------------------ mulai
  q('[data-pita-akhiri]').hidden = D.diakhiri !== '1';
  isiTandaAir();
  window.setInterval(isiTandaAir, 60000);
  tampil(0);
  segarkanSimpan();
  detak();
  window.setInterval(detak, 500);
  window.setInterval(tanyaKeadaan, 10000);
  akar.dataset.siap = '1';
})();
