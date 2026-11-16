<?php

// Teks LokaKode: pertemuan, konsep, bank soal, impor (SPEC.md §8, §9).
return [
    'bahasa' => ['c' => 'C', 'cpp' => 'C++', 'python' => 'Python'],

    'tingkat' => [
        'MUDAH_SEKALI' => 'Mudah sekali',
        'MUDAH'        => 'Mudah',
        'MUDAH_SEDANG' => 'Mudah-sedang',
        'SEDANG'       => 'Sedang',
        'SULIT'        => 'Sulit',
    ],

    'status' => [
        'PENDING_REVIEW' => 'Menunggu tinjauan',
        'APPROVED'       => 'Disetujui',
        'REJECTED'       => 'Ditolak',
    ],

    'pertemuan' => [
        'judul'        => 'Pertemuan',
        'semua'        => 'Semua pertemuan',
        'ke'           => 'Pertemuan {0}',
        'kosong'       => 'Mata kuliah ini belum punya pertemuan.',
        'nomor'        => 'No.',
        'nomorIsi'     => 'Nomor pertemuan (1–16)',
        'judulKolom'   => 'Judul',
        'bahasa'       => 'Bahasa pemrograman',
        'bahasaInfo'   => 'Satu pertemuan memakai tepat satu bahasa. Soal yang diimpor harus memakai bahasa ini.',
        'tambah'       => 'Tambah pertemuan',
        'pengaturan'   => 'Pengaturan pertemuan',
        'aktif'        => 'Dipakai untuk latihan',
        'aktifInfo'    => 'Matikan untuk pertemuan seperti UTS, UAS, atau yang tidak bisa dinilai otomatis.',
        'nonaktif'     => 'tidak dipakai',
        'terkunciInfo' => 'Pertemuan ini sudah punya soal, jadi nomor dan bahasanya tidak bisa diubah lagi.',
        'kelola'       => 'Pertemuan dan bank soal',
    ],

    'konsep' => [
        'judul'       => 'Konsep yang mulai diizinkan di pertemuan ini',
        'info'        => 'Konsep bersifat kumulatif: yang sudah diizinkan di pertemuan sebelumnya tercentang dan terkunci. Solusi referensi sebuah soal hanya boleh memakai konsep yang diizinkan; kode mahasiswa tidak pernah dibatasi.',
        'diwarisi'    => 'diwarisi',
        'dasarJudul'  => 'Konsep dasar mata kuliah',
        'dasarInfo'   => 'Konsep yang dianggap sudah dikuasai sebelum pertemuan 1, misalnya dari mata kuliah prasyarat. Konsep ini diizinkan di semua pertemuan.',
        'simpanDasar' => 'Simpan konsep dasar',
        'grup'        => [
            'DASAR'   => 'Dasar',
            'KONTROL' => 'Struktur kontrol',
            'DATA'    => 'Struktur data',
            'FUNGSI'  => 'Fungsi',
            'LANJUT'  => 'Lanjut',
            'OOP'     => 'Pemrograman berorientasi objek',
            'PUSTAKA' => 'Pustaka Python',
        ],
    ],

    'soal' => [
        'bank'             => 'Bank soal',
        'judul'            => 'Soal',
        'jumlah'           => 'Soal',
        'disetujui'        => 'Disetujui',
        'tingkat'          => 'Tingkat',
        'versi'            => 'Versi',
        'status'           => 'Status',
        'kosong'           => 'Belum ada soal di pertemuan ini. Soal diunggah oleh superadmin.',
        'kosongAdmin'      => 'Belum ada soal di pertemuan ini. Unggah berkas JSON untuk mengisinya.',
        'versiLamaDipakai' => 'mahasiswa masih memakai versi sebelumnya',
    ],

    'impor' => [
        'unggah' => 'Unggah soal (JSON)',
    ],

    'pesan' => [
        'disetujui' => 'Soal disetujui dan sekarang dipakai pertemuan ini.',
        'ditolak'   => 'Versi soal itu ditolak.',
    ],

    'galat' => [
        'nomor'          => 'Nomor pertemuan harus angka 1 sampai 16.',
        'nomorDipakai'   => 'Mata kuliah ini sudah punya pertemuan dengan nomor itu.',
        'nomorTerkunci'  => 'Nomor tidak bisa diubah karena pertemuan ini sudah punya soal.',
        'judul'          => 'Judul harus 3 sampai 190 karakter.',
        'bahasa'         => 'Pilih C, C++, atau Python.',
        'bahasaTerkunci' => 'Bahasa tidak bisa diubah karena pertemuan ini sudah punya soal.',
    ],
];
