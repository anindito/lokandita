// Halaman soal mahasiswa (SPEC.md §11): editor Monaco, templat dan Reset, blokir
// tempel dan salin, tanda air, simpan draf otomatis, Run dan Submit dengan
// polling 1 detik, tab hasil, panel yang bisa diubah ukurannya.
//
// Tanpa pustaka selain Monaco dan tanpa eval: aman untuk CSP. Teks yang berasal
// dari pengguna atau dari program selalu ditulis dengan textContent, tidak
// pernah dengan innerHTML.
(function () {
  'use strict';

  var blok = document.getElementById('data-soal');
  var tata = document.querySelector('[data-soal]');
  if (!blok || !tata) { return; }
  var D = JSON.parse(blok.textContent);
  var T = D.teks;
  var kerja = D.mode === 'kerja';
  var csrf = document.body.dataset.csrf || '';
  var api = '/api/soal/' + D.mp;

  // ---- alat kecil -----------------------------------------------------------------

  var isi = function (pola) {
    var argumen = Array.prototype.slice.call(arguments, 1);
    return String(pola).replace(/\{(\d)\}/g, function (_, i) { return String(argumen[Number(i)]); });
  };
  var angka = function (n) { return String(Math.round(Number(n) * 100) / 100); };
  var el = function (tag, kelas, teks) {
    var e = document.createElement(tag);
    if (kelas) { e.className = kelas; }
    if (teks !== undefined && teks !== null) { e.textContent = teks; }
    return e;
  };
  var kosongkan = function (wadah) { while (wadah.firstChild) { wadah.removeChild(wadah.firstChild); } };
  var verdict = function (kode) { return D.verdict[kode] || kode; };

  var toastWadah = document.querySelector('[data-toast-soal]');
  var toast = function (teks, jenis) {
    var t = el('div', 'toast toast-' + (jenis || 'galat'));
    t.setAttribute('role', jenis === 'sukses' ? 'status' : 'alert');
    t.appendChild(el('span', '', teks));
    var tutup = el('button', 'toast-tutup', '×');
    tutup.type = 'button';
    tutup.addEventListener('click', function () { t.remove(); });
    t.appendChild(tutup);
    toastWadah.appendChild(t);
    window.setTimeout(function () { t.remove(); }, 6000);
  };

  var bentukIsian = function (data) {
    var bagian = ['csrf=' + encodeURIComponent(csrf)];
    Object.keys(data).forEach(function (k) { bagian.push(encodeURIComponent(k) + '=' + encodeURIComponent(data[k])); });
    return bagian.join('&');
  };

  // Semua permintaan ke server. Sesi yang habis dijawab server dengan pengalihan
  // ke halaman login di host lain; itu tidak diikuti, tetapi dilaporkan dengan jelas.
  var minta = function (metode, url, data) {
    var opsi = { method: metode, credentials: 'same-origin', redirect: 'manual', headers: { 'X-Requested-With': 'XMLHttpRequest' } };
    if (metode === 'POST') {
      opsi.headers['Content-Type'] = 'application/x-www-form-urlencoded';
      opsi.body = bentukIsian(data || {});
    }
    return fetch(url, opsi).then(function (jawab) {
      if (jawab.type === 'opaqueredirect' || jawab.status === 0) {
        return { status: 0, data: { error: 'sesi_habis', message: T.sesiHabis } };
      }
      return jawab.json().then(
        function (json) { return { status: jawab.status, data: json }; },
        function () { return { status: jawab.status, data: { error: 'bukan_json', message: T.jaringan } }; }
      );
    }, function () {
      return { status: -1, data: { error: 'jaringan', message: T.jaringan } };
    });
  };

  // ---- editor -----------------------------------------------------------------------

  var wadahEditor = document.querySelector('[data-editor]');
  var cadangan = document.querySelector('[data-editor-cadangan]');
  var awal = D.draf !== null && D.draf !== undefined ? D.draf : D.templat;
  cadangan.value = awal;
  var monacoEditor = null;

  var ambil = function () { return monacoEditor ? monacoEditor.getValue() : cadangan.value; };
  var pasang = function (teks) {
    if (monacoEditor) { monacoEditor.setValue(teks); } else { cadangan.value = teks; panjangCadangan = teks.length; }
  };

  // ---- blokir tempel (SPEC.md §11) --------------------------------------------------
  // Setiap jalur ditutup sendiri-sendiri; onDidPaste di bawah adalah jaring terakhir.

  var laporTerakhir = 0;
  var tempelDiblokir = function (jalur) {
    var kini = Date.now();
    if (kini - laporTerakhir < 1000) { return; }
    laporTerakhir = kini;
    toast(T.tempelDiblokir);
    minta('POST', '/api/integritas', { mp: D.mp, jalur: jalur });
  };

  if (!D.bolehTempel) {
    var tolak = function (e, jalur) {
      e.preventDefault();
      e.stopPropagation();
      tempelDiblokir(jalur);
    };
    // Fase tangkap pada wadah: dijalankan sebelum Monaco melihat peristiwanya.
    wadahEditor.addEventListener('paste', function (e) { tolak(e, 'paste'); }, true);
    wadahEditor.addEventListener('drop', function (e) { tolak(e, 'drop'); }, true);
    wadahEditor.addEventListener('dragover', function (e) { e.preventDefault(); e.dataTransfer.dropEffect = 'none'; }, true);
    wadahEditor.addEventListener('beforeinput', function (e) {
      if (/^insertFrom(Paste|Drop|Yank)/.test(e.inputType || '')) { tolak(e, e.inputType === 'insertFromDrop' ? 'drop' : 'paste'); }
    }, true);
    wadahEditor.addEventListener('keydown', function (e) {
      var v = e.key === 'v' || e.key === 'V' || e.code === 'KeyV';
      if ((e.ctrlKey || e.metaKey) && v) { tolak(e, 'keyboard'); }
      if (e.shiftKey && (e.key === 'Insert' || e.code === 'Insert')) { tolak(e, 'shift_insert'); }
    }, true);
    // Klik tengah menempelkan pilihan utama di Linux.
    ['mousedown', 'mouseup', 'auxclick'].forEach(function (nama) {
      wadahEditor.addEventListener(nama, function (e) {
        if (e.button === 1) { tolak(e, 'middle_click'); }
      }, true);
    });
    // Menu konteks peramban punya butir "Tempel"; menu Monaco dimatikan di opsinya.
    wadahEditor.addEventListener('contextmenu', function (e) { e.preventDefault(); e.stopPropagation(); }, true);
  }

  // ---- ketikan tak wajar (SPEC.md §13.2, KONTRAK.md §17) -------------------------------
  // Tambahan karakter dalam dua detik terakhir. Bila jumlahnya ≥ 300 dan lajunya ≥ 150
  // karakter per detik, satu peristiwa dikirim; server memeriksa ulang kedua ambang itu.
  // Reset ke templat dan pemulihan draf tidak lewat sini (teks itu kami sendiri yang memasang).

  var KETIK_KARAKTER = 300;
  var KETIK_PER_DETIK = 150;
  var KETIK_JENDELA_MS = 2000;
  var KETIK_TUNDA_MS = 400;
  var tambahan = [];
  var laporKetik = null;
  var jumlahTambahan = function () {
    var total = 0;
    var undo = false;
    tambahan.forEach(function (x) { total += x.n; undo = undo || x.sebab === 'undo'; });
    return { total: total, ms: tambahan[tambahan.length - 1].t - tambahan[0].t, undo: undo };
  };
  var catatTambahan = function (jumlah, sebab) {
    if (!kerja || D.bolehTempel || jumlah <= 0) { return; }
    var kini = Date.now();
    tambahan.push({ t: kini, n: jumlah, sebab: sebab });
    if (laporKetik !== null) { return; }   // sedang menunggu sisa semburan yang sama
    while (kini - tambahan[0].t > KETIK_JENDELA_MS) { tambahan.shift(); }
    var j = jumlahTambahan();
    if (j.total >= KETIK_KARAKTER && j.total * 1000 >= KETIK_PER_DETIK * j.ms) {
      // Ambang terlewati. Sisa semburan yang sama ditunggu sebentar, supaya jumlah yang
      // dilaporkan adalah seluruh teks yang masuk, bukan hanya 300 karakter pertamanya.
      laporKetik = window.setTimeout(function () {
        var akhir = jumlahTambahan();
        tambahan = [];
        laporKetik = null;
        minta('POST', '/api/integritas', { mp: D.mp, jenis: 'ketik', karakter: akhir.total, ms: akhir.ms, sebab: akhir.undo ? 'undo' : 'ketik' });
      }, KETIK_TUNDA_MS);
    }
  };
  // Editor cadangan (tanpa Monaco): selisih panjang teks pada setiap masukan.
  var panjangCadangan = cadangan.value.length;
  cadangan.addEventListener('input', function () {
    catatTambahan(cadangan.value.length - panjangCadangan, 'ketik');
    panjangCadangan = cadangan.value.length;
  });

  // ---- simpan draf otomatis tiap 5 detik --------------------------------------------

  var statusDraf = document.querySelector('[data-status-draf]');
  var tersimpan = awal;
  var sedangSimpan = false;
  var jam = function () {
    var d = new Date();
    var dua = function (n) { return (n < 10 ? '0' : '') + n; };
    return dua(d.getHours()) + ':' + dua(d.getMinutes()) + ':' + dua(d.getSeconds());
  };
  var simpanDraf = function () {
    if (!kerja || sedangSimpan) { return Promise.resolve(); }
    var sumber = ambil();
    if (sumber === tersimpan) { return Promise.resolve(); }
    sedangSimpan = true;
    statusDraf.textContent = T.menyimpan;
    return minta('POST', api + '/draf', { source: sumber }).then(function (j) {
      sedangSimpan = false;
      if (j.status === 200) {
        tersimpan = sumber;
        statusDraf.textContent = isi(T.tersimpan, jam());
        statusDraf.classList.remove('teks-bahaya');
      } else {
        statusDraf.textContent = j.status === 0 ? T.sesiHabis : T.gagalSimpan;
        statusDraf.classList.add('teks-bahaya');
      }
    });
  };
  if (kerja) {
    window.setInterval(simpanDraf, 5000);
    // Tab ditutup atau disembunyikan: kirim draf terakhir tanpa menunggu jawaban.
    var kirimSaatPergi = function () {
      var sumber = ambil();
      if (sumber === tersimpan || !navigator.sendBeacon) { return; }
      var badan = new Blob([bentukIsian({ source: sumber })], { type: 'application/x-www-form-urlencoded' });
      if (navigator.sendBeacon(api + '/draf', badan)) { tersimpan = sumber; }
    };
    window.addEventListener('pagehide', kirimSaatPergi);
    document.addEventListener('visibilitychange', function () { if (document.visibilityState === 'hidden') { kirimSaatPergi(); } });
  }

  // ---- tab hasil ----------------------------------------------------------------------

  var tabBaris = document.querySelector('[data-tab-hasil]');
  var tab = Array.prototype.slice.call(tabBaris.querySelectorAll('[role=tab]'));
  var bukaTab = function (id, fokus) {
    tab.forEach(function (t) {
      var aktif = t.id === id;
      t.setAttribute('aria-selected', aktif ? 'true' : 'false');
      t.tabIndex = aktif ? 0 : -1;
      document.getElementById(t.getAttribute('aria-controls')).hidden = !aktif;
      if (aktif && fokus) { t.focus(); }
    });
    if (id === 'tab-riwayat') { muatRiwayat(); }
    if (id === 'tab-klarifikasi') { muatKlarifikasi(); }
  };
  tab.forEach(function (t, i) {
    t.addEventListener('click', function () { bukaTab(t.id, false); });
    t.addEventListener('keydown', function (e) {
      var arah = e.key === 'ArrowRight' ? 1 : (e.key === 'ArrowLeft' ? -1 : 0);
      if (document.documentElement.dir === 'rtl') { arah = -arah; }
      if (e.key === 'Home') { e.preventDefault(); bukaTab(tab[0].id, true); }
      if (e.key === 'End') { e.preventDefault(); bukaTab(tab[tab.length - 1].id, true); }
      if (arah !== 0) { e.preventDefault(); bukaTab(tab[(i + arah + tab.length) % tab.length].id, true); }
    });
  });

  // Di bawah 1024 px ketiga panel menjadi tab bertumpuk.
  var tabSempit = document.querySelector('[data-tab-sempit]');
  var bukaPanel = function (nama) {
    tata.dataset.panelAktif = nama;
    tabSempit.querySelectorAll('[role=tab]').forEach(function (t) {
      t.setAttribute('aria-selected', t.dataset.panel === nama ? 'true' : 'false');
    });
    if (monacoEditor) { monacoEditor.layout(); }
  };
  tabSempit.querySelectorAll('[role=tab]').forEach(function (t) {
    t.addEventListener('click', function () { bukaPanel(t.dataset.panel); });
  });

  // ---- menampilkan hasil ----------------------------------------------------------------

  var keluaran = document.querySelector('[data-keluaran]');
  var statusJalan = document.querySelector('[data-status-jalan]');

  var blokTeks = function (judul, teks) {
    var wadah = el('div', 'hasil-blok');
    wadah.appendChild(el('p', 'redup', judul));
    var pre = el('pre', 'kode-sumber', teks === '' ? T.kosong : teks);
    pre.dir = 'ltr';
    wadah.appendChild(pre);
    return wadah;
  };
  var lencanaVerdict = function (kode) {
    return el('span', 'lencana verdict verdict-' + String(kode).toLowerCase(), verdict(kode));
  };
  var kepalaHasil = function (judul, kode) {
    var h = el('h3', 'hasil-judul', judul + ' ');
    h.appendChild(lencanaVerdict(kode));
    return h;
  };

  var tampilCE = function (hasil, judul) {
    keluaran.appendChild(kepalaHasil(judul, 'CE'));
    keluaran.appendChild(blokTeks(T.logKompilasi, hasil.compile_log || ''));
  };

  var tampilContoh = function (hasil) {
    kosongkan(keluaran);
    if (hasil.verdict === 'CE') { tampilCE(hasil, T.hasilContoh); return; }
    keluaran.appendChild(kepalaHasil(T.hasilContoh, hasil.verdict));
    if (hasil.compile_log) { keluaran.appendChild(blokTeks(T.logKompilasi, hasil.compile_log)); }
    (hasil.samples || []).forEach(function (c) {
      var h = el('h4', '', isi(T.contoh, c.index + 1) + ' ');
      h.appendChild(lencanaVerdict(c.verdict));
      h.appendChild(el('span', 'redup ltr', ' ' + isi(T.waktu, c.duration_ms)));
      keluaran.appendChild(h);
      keluaran.appendChild(blokTeks(T.stdout, c.stdout));
      if (c.stderr) { keluaran.appendChild(blokTeks(T.stderr, c.stderr)); }
      // Tab Test Cases: keluaran program di samping keluaran yang diharapkan.
      var kartu = document.querySelector('[data-tes-contoh="' + c.index + '"]');
      if (kartu) {
        var tanda = kartu.querySelector('[data-tes-verdict]');
        tanda.hidden = false;
        tanda.className = 'lencana verdict verdict-' + String(c.verdict).toLowerCase();
        tanda.textContent = verdict(c.verdict);
        kartu.querySelector('[data-tes-nyata]').hidden = false;
        kartu.querySelector('[data-tes-keluaran]').textContent = c.stdout === '' ? T.kosong : c.stdout;
      }
    });
  };

  var tampilKustom = function (hasil) {
    kosongkan(keluaran);
    if (hasil.verdict === 'CE') { tampilCE(hasil, T.hasilKustom); return; }
    var h = kepalaHasil(T.hasilKustom, hasil.verdict);
    h.appendChild(el('span', 'redup ltr', ' ' + isi(T.waktu, hasil.duration_ms) + ' · ' + isi(T.kodeKeluar, hasil.exit_code)));
    keluaran.appendChild(h);
    if (hasil.compile_log) { keluaran.appendChild(blokTeks(T.logKompilasi, hasil.compile_log)); }
    keluaran.appendChild(blokTeks(T.stdout, hasil.stdout || ''));
    if (hasil.stderr) { keluaran.appendChild(blokTeks(T.stderr, hasil.stderr)); }
  };

  // Hasil Submit: verdict, nilai, dan STATUS tiap tes tersembunyi. Masukan dan
  // keluaran tes tersembunyi tidak pernah dikirim server (SPEC.md §10).
  var tampilKiriman = function (k, tambahan) {
    kosongkan(keluaran);
    if (k.status === 'GAGAL') {
      keluaran.appendChild(kepalaHasil(T.hasilKirim, 'FAILED'));
      keluaran.appendChild(el('p', 'teks-bahaya', T.judgeGagal));
      return;
    }
    keluaran.appendChild(kepalaHasil(T.hasilKirim, k.verdict));
    keluaran.appendChild(el('p', 'hasil-nilai', isi(T.nilaiKirim, angka(k.score), k.points)));
    if (k.practice) { keluaran.appendChild(el('p', 'teks-peringatan', T.kirimanLatihan)); }
    if (k.late) { keluaran.appendChild(el('p', 'teks-peringatan', isi(T.kirimanTelat, angka(k.late_factor)))); }
    if (k.verdict === 'CE') {
      keluaran.appendChild(blokTeks(T.logKompilasi, k.compile_log || ''));
    } else {
      keluaran.appendChild(el('p', '', isi(T.lulusTes, k.passed, k.total)));
      if (k.missing && k.missing.length) { keluaran.appendChild(el('p', 'teks-peringatan', isi(T.konsepKurang, k.missing.join(', ')))); }
      keluaran.appendChild(el('p', 'redup', T.tesTersembunyi));
      var daftar = el('ol', 'tes-daftar');
      (k.tests || []).forEach(function (t) {
        var li = el('li', '', isi(T.tes, t.index + 1) + ' ');
        li.appendChild(lencanaVerdict(t.verdict));
        li.appendChild(el('span', 'redup ltr', ' ' + isi(T.waktu, t.duration_ms)));
        daftar.appendChild(li);
      });
      keluaran.appendChild(daftar);
      if (k.compile_log) { keluaran.appendChild(blokTeks(T.logKompilasi, k.compile_log)); }
    }
    if (tambahan) { keluaran.appendChild(tambahan); }
  };

  // Ringkasan nilai pertemuan dan nilai terbaik soal, diperbarui sesudah Submit.
  var perbaruiRingkasan = function (n) {
    if (!n) { return; }
    document.querySelectorAll('[data-ringkasan]').forEach(function (r) {
      r.querySelector('[data-nilai]').textContent = angka(n.nilai);
      var batang = r.querySelector('[data-batang]');
      batang.max = n.target;
      batang.value = Math.min(n.terhitung, n.target);
      r.querySelector('[data-poin]').textContent = isi(T.poinDari, angka(n.terhitung), n.target);
      r.querySelector('[data-mudah]').textContent = isi(T.mudahSekali, n.mudahSekali);
      r.querySelector('[data-kurang]').textContent = n.kurang > 0 ? isi(T.kurang, angka(n.kurang)) : T.tercapai;
    });
  };
  var terbaik = null;
  var perbaruiTerbaik = function (nilai, poin) {
    if (nilai === null || nilai === undefined) { return; }
    if (terbaik === null || nilai > terbaik) { terbaik = nilai; }
    document.querySelector('[data-skor-soal]').textContent = isi(T.terbaik, angka(terbaik), poin);
  };

  // ---- Run dan Submit -------------------------------------------------------------------

  var tombol = {};
  document.querySelectorAll('[data-aksi]').forEach(function (b) { tombol[b.dataset.aksi] = b; });
  var sibuk = false;
  var aturSibuk = function (ya) {
    sibuk = ya;
    ['jalankan', 'kirim', 'jalankan-kustom'].forEach(function (n) { if (tombol[n]) { tombol[n].disabled = ya; } });
  };

  var teksKemajuan = function (job, satu) {
    var p = job.progress || {};
    if (job.status === 'QUEUED' || p.state === 'queued') { return T.antre; }
    if (p.state === 'compiling') { return T.kompilasi; }
    if (p.state === 'running') {
      return satu ? T.menjalankanSatu : isi(T.menjalankan, Math.min((p.current || 0) + 1, p.total || 1), p.total || 1);
    }
    return T.memeriksa;
  };

  // Polling GET /api/executions/{id} tiap detik (SPEC.md §2) sampai pekerjaan berakhir.
  var pantau = function (id, satu) {
    return new Promise(function (selesai) {
      var gagalBeruntun = 0;
      var tanya = function () {
        minta('GET', '/api/executions/' + id).then(function (j) {
          if (j.status === 200) {
            gagalBeruntun = 0;
            if (j.data.status === 'DONE' || j.data.status === 'FAILED') { selesai(j); return; }
            statusJalan.textContent = teksKemajuan(j.data, satu);
          } else if (j.status === 0 || j.status === 404 || (gagalBeruntun += 1) >= 10) {
            selesai(j);
            return;
          }
          window.setTimeout(tanya, 1000);
        });
      };
      tanya();
    });
  };

  var gagalMinta = function (j) {
    statusJalan.textContent = '';
    toast((j.data && j.data.message) || T.jaringan);
  };

  var jalankan = function (kustom) {
    if (!kerja || sibuk) { return; }
    aturSibuk(true);
    bukaTab('tab-keluaran', false);
    bukaPanel('hasil');
    statusJalan.textContent = T.antre;
    var data = { source: ambil() };
    if (kustom) {
      data.kustom = '1';
      data.stdin = document.querySelector('[data-masukan-kustom]').value;
    }
    minta('POST', api + '/jalankan', data).then(function (j) {
      if (j.status !== 202) { gagalMinta(j); aturSibuk(false); return; }
      tersimpan = data.source;
      pantau(j.data.id, kustom).then(function (akhir) {
        statusJalan.textContent = '';
        aturSibuk(false);
        if (akhir.status !== 200) { gagalMinta(akhir); return; }
        var hasil = akhir.data.result || {};
        if (akhir.data.status === 'FAILED') {
          kosongkan(keluaran);
          keluaran.appendChild(el('p', 'teks-bahaya', T.judgeGagal));
          return;
        }
        if (kustom) { tampilKustom(hasil); } else { tampilContoh(hasil); }
      });
    });
  };

  var selesaiKirim = function (akhir) {
    statusJalan.textContent = '';
    aturSibuk(false);
    if (akhir.status !== 200) { gagalMinta(akhir); return; }
    var k = akhir.data.submission;
    if (!k) {
      kosongkan(keluaran);
      keluaran.appendChild(el('p', 'teks-bahaya', T.judgeGagal));
      return;
    }
    tampilKiriman(k);
    // Kiriman latihan sesudah tutup tidak pernah mengubah nilai terbaik.
    if (!k.practice) { perbaruiTerbaik(k.score, k.points); }
    perbaruiRingkasan(akhir.data.meeting);
    riwayatDimuat = false;
  };

  var kirim = function () {
    if (!kerja || sibuk) { return; }
    aturSibuk(true);
    bukaTab('tab-keluaran', false);
    bukaPanel('hasil');
    statusJalan.textContent = T.antre;
    var sumber = ambil();
    minta('POST', api + '/kirim', { source: sumber }).then(function (j) {
      // 409 dengan id: kiriman sebelumnya masih dinilai; hasilnya ditunggu, bukan dikirim ulang.
      if (j.status !== 202 && !(j.status === 409 && j.data.id)) { gagalMinta(j); aturSibuk(false); return; }
      if (j.status === 202) { tersimpan = sumber; } else { toast(j.data.message, 'sukses'); }
      pantau(j.data.id, false).then(selesaiKirim);
    });
  };

  if (tombol.jalankan) { tombol.jalankan.addEventListener('click', function () { jalankan(false); }); }
  if (tombol['jalankan-kustom']) { tombol['jalankan-kustom'].addEventListener('click', function () { jalankan(true); }); }
  if (tombol.kirim) { tombol.kirim.addEventListener('click', kirim); }
  if (tombol.reset) {
    tombol.reset.addEventListener('click', function () {
      if (window.confirm(T.resetYakin)) { pasang(D.templat); simpanDraf(); }
    });
  }

  // Pintasan: Ctrl/Cmd+Enter = Run, Ctrl/Cmd+Shift+Enter = Submit.
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      e.stopPropagation();
      if (e.shiftKey) { kirim(); } else { jalankan(false); }
    }
  }, true);

  // ---- riwayat ----------------------------------------------------------------------------

  var riwayat = document.querySelector('[data-riwayat]');
  var riwayatDimuat = false;
  var lihatKiriman = function (id) {
    minta('GET', api + '/riwayat/' + id).then(function (j) {
      if (j.status !== 200) { gagalMinta(j); return; }
      var tambahan = el('div', 'hasil-blok');
      tambahan.appendChild(blokTeks(isi(T.kirimanPada, j.data.at), j.data.source));
      if (kerja) {
        var muat = el('button', 'tombol tombol-sekunder tombol-kecil', T.muatKeEditor);
        muat.type = 'button';
        muat.addEventListener('click', function () {
          if (window.confirm(T.muatYakin)) { pasang(j.data.source); bukaPanel('editor'); }
        });
        tambahan.appendChild(muat);
      }
      tampilKiriman(j.data, tambahan);
      bukaTab('tab-keluaran', true);
    });
  };
  var muatRiwayat = function () {
    if (riwayatDimuat) { return; }
    riwayatDimuat = true;
    minta('GET', api + '/riwayat').then(function (j) {
      if (j.status !== 200) { riwayatDimuat = false; gagalMinta(j); return; }
      var daftar = j.data.submissions;
      if (!daftar.length) { return; }
      kosongkan(riwayat);
      var tabel = el('table');
      var badan = el('tbody');
      daftar.forEach(function (k) {
        var baris = el('tr');
        baris.appendChild(el('td', 'ltr', k.at));
        var sel = el('td');
        sel.appendChild(k.status === 'MENUNGGU' ? el('span', 'lencana', T.sedangDinilai) : lencanaVerdict(k.status === 'GAGAL' ? 'FAILED' : k.verdict));
        if (k.practice) { sel.appendChild(el('span', 'lencana', T.lencanaLatihan)); }
        if (k.late) { sel.appendChild(el('span', 'lencana', T.lencanaTelat)); }
        baris.appendChild(sel);
        baris.appendChild(el('td', 'ltr', k.score === null ? '—' : angka(k.score) + ' / ' + j.data.points));
        baris.appendChild(el('td', 'ltr', k.total ? k.passed + ' / ' + k.total : '—'));
        var aksi = el('td');
        if (k.status !== 'MENUNGGU') {
          var lihat = el('button', 'tombol tombol-sekunder tombol-kecil', T.lihat);
          lihat.type = 'button';
          lihat.addEventListener('click', function () { lihatKiriman(k.id); });
          aksi.appendChild(lihat);
        }
        baris.appendChild(aksi);
        badan.appendChild(baris);
      });
      tabel.appendChild(badan);
      riwayat.appendChild(tabel);
    });
  };

  // ---- klarifikasi soal (SPEC.md §14) ------------------------------------------------------
  // Pertanyaan sendiri dan jawaban yang diterbitkan dosen ke kelas. Semua teks ditulis dengan textContent.

  var klarDaftar = document.querySelector('[data-klarifikasi-daftar]');
  var klarForm = document.querySelector('[data-klarifikasi-form]');
  var klarIsian = document.querySelector('[data-klarifikasi-isian]');
  var muatKlarifikasi = function () {
    minta('GET', api + '/klarifikasi').then(function (j) {
      if (j.status !== 200) { gagalMinta(j); return; }
      kosongkan(klarDaftar);
      if (!j.data.daftar.length) { klarDaftar.appendChild(el('p', 'redup', T.klarKosong)); return; }
      j.data.daftar.forEach(function (q) {
        var kartu = el('div', 'hasil-blok klarifikasi');
        kartu.dataset.klarifikasi = q.id;
        var kepala = el('p', 'redup', (q.milikSaya ? T.klarSaya : isi(T.klarDari, q.penanya)) + ' · ' + q.waktu);
        if (q.kelas) { kepala.appendChild(document.createTextNode(' ')); kepala.appendChild(el('span', 'lencana', T.klarKelas)); }
        kartu.appendChild(kepala);
        kartu.appendChild(el('p', 'kabar-isi', q.pertanyaan));
        if (q.jawaban === null) {
          kartu.appendChild(el('p', 'redup', T.klarBelum));
        } else {
          kartu.appendChild(el('p', 'redup', T.klarJawaban + ' · ' + q.dijawab));
          kartu.appendChild(el('p', 'kabar-isi klarifikasi-jawaban', q.jawaban));
        }
        klarDaftar.appendChild(kartu);
      });
    });
  };
  if (klarForm) {
    klarForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var teks = klarIsian.value.trim();
      if (!teks) { return; }
      minta('POST', api + '/klarifikasi', { pertanyaan: teks }).then(function (j) {
        if (j.status !== 200) { gagalMinta(j); return; }
        klarIsian.value = '';
        toast(T.klarTerkirim, 'sukses');
        muatKlarifikasi();
      });
    });
  }

  // ---- panel yang bisa diubah ukurannya; ukurannya disimpan per pengguna ------------------

  var ukuran = { kiri: 40, atas: 60 };
  if (D.panel && D.panel.kiri && D.panel.atas) { ukuran = { kiri: D.panel.kiri, atas: D.panel.atas }; }
  var batas = { kiri: [15, 75], atas: [15, 85] };
  var terapkanUkuran = function () {
    tata.style.setProperty('--kiri', ukuran.kiri + '%');
    tata.style.setProperty('--atas', ukuran.atas + '%');
    if (monacoEditor) { monacoEditor.layout(); }
  };
  var simpanUkuranNanti = null;
  var simpanUkuran = function () {
    window.clearTimeout(simpanUkuranNanti);
    simpanUkuranNanti = window.setTimeout(function () {
      minta('POST', '/api/panel', { kiri: Math.round(ukuran.kiri), atas: Math.round(ukuran.atas) });
    }, 800);
  };
  var atur = function (nama, nilai) {
    ukuran[nama] = Math.max(batas[nama][0], Math.min(batas[nama][1], nilai));
    terapkanUkuran();
    simpanUkuran();
  };
  document.querySelectorAll('[data-pembagi]').forEach(function (p) {
    var nama = p.dataset.pembagi;
    var kanan = document.querySelector('.soal-kanan');
    var gerak = function (e) {
      if (nama === 'kiri') {
        var kotak = tata.getBoundingClientRect();
        var x = document.documentElement.dir === 'rtl' ? kotak.right - e.clientX : e.clientX - kotak.left;
        atur('kiri', x / kotak.width * 100);
      } else {
        var k = kanan.getBoundingClientRect();
        atur('atas', (e.clientY - k.top) / k.height * 100);
      }
    };
    p.addEventListener('pointerdown', function (e) {
      e.preventDefault();
      p.setPointerCapture(e.pointerId);
      p.addEventListener('pointermove', gerak);
    });
    var lepas = function (e) {
      p.removeEventListener('pointermove', gerak);
      if (p.hasPointerCapture(e.pointerId)) { p.releasePointerCapture(e.pointerId); }
    };
    p.addEventListener('pointerup', lepas);
    p.addEventListener('pointercancel', lepas);
    p.addEventListener('keydown', function (e) {
      var langkah = { ArrowLeft: -2, ArrowRight: 2, ArrowUp: -2, ArrowDown: 2 }[e.key];
      if (langkah === undefined || (nama === 'kiri') !== (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) { return; }
      e.preventDefault();
      if (nama === 'kiri' && document.documentElement.dir === 'rtl') { langkah = -langkah; }
      atur(nama, ukuran[nama] + langkah);
    });
  });
  terapkanUkuran();

  // ---- soal: pilihan bahasa, blokir salin, tanda air -------------------------------------

  document.querySelectorAll('[data-bahasa-soal]').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('[data-pernyataan]').forEach(function (a) { a.hidden = a.dataset.pernyataan !== b.dataset.bahasaSoal; });
      document.querySelectorAll('[data-bahasa-soal]').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
    });
  });

  var diDalamSoal = function (simpul) {
    var e = simpul && simpul.nodeType === 1 ? simpul : (simpul ? simpul.parentElement : null);
    return !!(e && e.closest('.tanpa-salin'));
  };
  var bisaDiketik = function (simpul) {
    return !!(simpul && simpul.closest && simpul.closest('textarea, input, [data-editor]'));
  };
  var pilihanDiSoal = function () {
    var p = window.getSelection ? window.getSelection() : null;
    return !!(p && p.rangeCount && !p.isCollapsed && (diDalamSoal(p.anchorNode) || diDalamSoal(p.focusNode)));
  };
  ['copy', 'cut'].forEach(function (nama) {
    document.addEventListener(nama, function (e) {
      if (diDalamSoal(e.target) || pilihanDiSoal()) {
        e.preventDefault();
        if (e.clipboardData) { e.clipboardData.setData('text/plain', ''); }
        toast(T.salinDiblokir);
      }
    }, true);
  });
  ['selectstart', 'dragstart', 'contextmenu'].forEach(function (nama) {
    document.addEventListener(nama, function (e) { if (diDalamSoal(e.target)) { e.preventDefault(); } }, true);
  });
  document.addEventListener('keydown', function (e) {
    if (!(e.ctrlKey || e.metaKey)) { return; }
    var k = (e.key || '').toLowerCase();
    // Ctrl/Cmd+P: cetak. Ctrl/Cmd+C/X/A di luar editor dan kotak isian: salin soal.
    if (k === 'p' || ((k === 'c' || k === 'x' || k === 'a') && !bisaDiketik(e.target))) { e.preventDefault(); }
  }, true);

  // Tanda air: nomor induk, nama, dan waktu yang diperbarui tiap 30 detik.
  var tandaAir = document.querySelector('[data-tanda-air]');
  if (tandaAir) {
    var perbaruiTanda = function () {
      var d = new Date();
      var dua = function (n) { return (n < 10 ? '0' : '') + n; };
      var waktu = dua(d.getDate()) + '-' + dua(d.getMonth() + 1) + '-' + d.getFullYear() + ' ' + dua(d.getHours()) + ':' + dua(d.getMinutes());
      var teks = D.tanda.nomor + ' · ' + D.tanda.nama + ' · ' + waktu;
      tandaAir.querySelectorAll('span').forEach(function (s) { s.textContent = teks; });
    };
    perbaruiTanda();
    window.setInterval(perbaruiTanda, 30000);
  }

  // ---- Monaco -----------------------------------------------------------------------------

  var pakaiCadangan = function () {
    wadahEditor.classList.add('editor-sederhana');
    if (kerja) { toast(T.editorGagal); }
  };
  if (typeof window.require !== 'function' || !window.require.config) { pakaiCadangan(); return; }

  // Pekerja latar Monaco dimuat dari host sendiri, bukan dari URL blob: (lihat monaco-pekerja.js).
  window.MonacoEnvironment = { getWorkerUrl: function () { return '/js/monaco-pekerja.js'; } };
  window.require.config({ paths: { vs: '/monaco/vs' } });
  window.require(['vs/editor/editor.main'], function () {
    var monaco = window.monaco;
    var gelap = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    var sekarang = cadangan.value;
    cadangan.hidden = true;
    var tempat = el('div', 'editor-monaco');
    wadahEditor.appendChild(tempat);
    monacoEditor = monaco.editor.create(tempat, {
      value: sekarang,
      language: D.bahasa,
      theme: gelap ? 'vs-dark' : 'vs',
      readOnly: !kerja,
      automaticLayout: true,
      minimap: { enabled: false },
      fontSize: 14,
      tabSize: 4,
      insertSpaces: true,
      scrollBeyondLastLine: false,
      ariaLabel: cadangan.getAttribute('aria-label'),
      // Tanpa menu konteks (ada butir Tempel), tanpa seret-dan-lepas.
      contextmenu: false,
      dragAndDrop: false,
      dropIntoEditor: { enabled: false },
      pasteAs: { enabled: false },
      // Pelengkapan otomatis bisa dimatikan per pertemuan (SPEC.md §6).
      quickSuggestions: D.lengkapi,
      suggestOnTriggerCharacters: D.lengkapi,
      wordBasedSuggestions: D.lengkapi ? 'currentDocument' : 'off',
      parameterHints: { enabled: D.lengkapi },
      snippetSuggestions: D.lengkapi ? 'inline' : 'none',
      inlineSuggest: { enabled: false }
    });

    monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.Enter, function () { jalankan(false); });
    monacoEditor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyMod.Shift | monaco.KeyCode.Enter, kirim);

    if (!D.bolehTempel) {
      // Palet perintah (F1) punya perintah Tempel: dimatikan.
      monacoEditor.addCommand(monaco.KeyCode.F1, function () {});
      // Jaring terakhir: bila tempelan tetap lolos lewat jalur yang tidak terduga, ia dibatalkan.
      monacoEditor.onDidPaste(function () {
        monacoEditor.trigger('blokir-tempel', 'undo', null);
        tempelDiblokir('paste');
      });
    }
    // Ketikan tak wajar: karakter yang bertambah pada setiap perubahan. isFlush = setValue
    // kami sendiri (Reset), tidak dihitung; undo dan redo dihitung dan ditandai.
    monacoEditor.onDidChangeModelContent(function (e) {
      if (e.isFlush) { return; }
      var bertambah = 0;
      e.changes.forEach(function (c) { bertambah += c.text.length - c.rangeLength; });
      catatTambahan(bertambah, e.isUndoing || e.isRedoing ? 'undo' : 'ketik');
    });
    window.lokakodeEditor = monacoEditor; // dipakai tes peramban
    document.documentElement.dataset.editorSiap = '1';
  }, function () {
    pakaiCadangan();
  });
})();
