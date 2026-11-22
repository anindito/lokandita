<?php

// Peristiwa integritas untuk dosen (SPEC.md §13.2, KONTRAK.md §17).
return [
    'judul'        => 'Integritas',
    'penjelasan'   => 'Kejadian yang dicatat sistem untuk mahasiswa kelas ini. Catatan ini hanya bahan pertimbangan; keputusannya ada pada Anda.',
    'catatan'      => 'Ketikan tak wajar diukur oleh peramban mahasiswa: 300 karakter atau lebih yang masuk dengan laju 150 karakter per detik atau lebih. Mengurungkan penghapusan (undo) juga bisa memicunya dan ditandai tersendiri.',
    'waktu'        => 'Waktu',
    'jenisJudul'   => 'Jenis',
    'mahasiswa'    => 'Mahasiswa',
    'pertemuan'    => 'Pertemuan',
    'soal'         => 'Soal',
    'rincian'      => 'Rincian',
    'ip'           => 'Alamat IP',
    'peramban'     => 'Peramban',
    'semua'        => 'Semua',
    'kosong'       => 'Belum ada peristiwa integritas untuk mahasiswa kelas ini.',
    'kosongSaring' => 'Tidak ada peristiwa yang cocok dengan saringan ini.',
    'unduhCsv'     => 'Unduh CSV',
    'unduhXlsx'    => 'Unduh XLSX',
    'halamanKe'    => 'Halaman {0}',
    'jenis'        => [
        'paste_blocked'  => 'Tempel diblokir',
        'typing_anomaly' => 'Ketikan tak wajar',
        'seb_rejected'   => 'Ditolak tanpa SEB',
        'seb_terminated' => 'SEB terputus',
    ],
    'jalur' => [
        'paste'        => 'Perintah tempel',
        'keyboard'     => 'Ctrl+V',
        'shift_insert' => 'Shift+Insert',
        'middle_click' => 'Klik tengah tetikus',
        'drop'         => 'Seret dan lepas',
    ],
    'ketik' => [
        'ketik' => '{0} karakter dalam {1} detik ({2} per detik)',
        'undo'  => '{0} karakter dalam {1} detik ({2} per detik), dari undo atau redo',
    ],
    'seb' => [
        'tanpa_header'      => 'Tanpa tanda pengenal SEB.',
        'tanpa_konfigurasi' => 'Mata kuliah belum punya konfigurasi SEB.',
        'kunci_konfigurasi' => 'Berkas konfigurasi tidak cocok.',
        'kunci_peramban'    => 'Versi SEB tidak terdaftar.',
    ],
];
