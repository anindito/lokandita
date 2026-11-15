<?php

// Teks LokaKode: struktur akademik (SPEC.md §5).
return [
    'tidakDitemukan' => 'Data yang Anda cari tidak ada, atau Anda tidak berhak melihatnya.',
    'navigasi'       => 'Menu LokaKode',
    'kosong'         => 'Belum ada data.',
    'ya'             => 'Ya',
    'tidak'          => 'Tidak',

    'nav' => [
        'beranda' => 'Beranda',
        'matkul'  => 'Mata kuliah',
        'tahun'   => 'Tahun ajaran',
        'audit'   => 'Log audit',
    ],

    'tahun' => [
        'aktif'         => 'Tahun ajaran aktif',
        'belumAda'      => 'Belum ada tahun ajaran yang aktif. Hubungi superadmin.',
        'belumAdaAdmin' => 'Belum ada tahun ajaran yang aktif. Buat dan aktifkan satu tahun ajaran lebih dulu.',
        'tampilkan'     => 'Tahun ajaran',
        'lihat'         => 'Tampilkan',
        'diarsipkan'    => 'diarsipkan',
        'statusNama'    => ['active' => 'Aktif', 'archived' => 'Diarsipkan'],
    ],

    'ringkas' => [
        'kelas'         => 'Kelas',
        'mahasiswa'     => 'Mahasiswa aktif',
        'tanpaPengajar' => 'Kelas tanpa pengajar',
    ],

    'matkul' => [
        'judul'        => 'Mata kuliah',
        'kode'         => 'Kode',
        'nama'         => 'Nama',
        'pengampu'     => 'Pengampu',
        'kosong'       => 'Belum ada mata kuliah yang Anda ampu atau ajar.',
        'andaPengampu' => 'Anda pengampu',
        'andaPengajar' => 'Anda pengajar',
    ],

    'kelas' => [
        'judul'         => 'Kelas',
        'nama'          => 'Kelas',
        'kapasitas'     => 'Kapasitas',
        'tanpaBatas'    => 'tidak dibatasi',
        'pengajar'      => 'Pengajar',
        'tanpaPengajar' => 'Belum ada pengajar',
        'mahasiswa'     => 'Mahasiswa aktif',
        'diarsipkan'    => 'Diarsipkan',
        'kosong'        => 'Belum ada kelas pada tahun ajaran ini.',
        'kosongDosen'   => 'Anda belum mengampu atau mengajar kelas pada tahun ajaran ini.',
        'daftar'        => 'Daftar mahasiswa',
        'daftarKosong'  => 'Belum ada mahasiswa di kelas ini.',
        'jumlah'        => '{0} mahasiswa, {1} aktif',
        'hanyaLihat'    => 'Anda pengajar kelas ini. Perubahan kelas dan pendaftaran dilakukan oleh pengampu mata kuliah.',
        'kembali'       => 'Kembali ke mata kuliah',
    ],

    'mhs' => [
        'nomorInduk'    => 'Nomor induk',
        'nama'          => 'Nama',
        'status'        => 'Status',
        'catatan'       => 'Catatan',
        'tanggalLulus'  => 'lulus {0}',
        'tanpaAkses'    => 'Belum diberi akses LokaKode',
        'akunTidakAktif' => 'Akun tidak aktif',
        'akunHilang'    => 'Akun tidak ditemukan di LokaAuth',
        'statusNama'    => [
            'AKTIF'             => 'Aktif',
            'LULUS'             => 'Lulus',
            'MENGUNDURKAN_DIRI' => 'Mengundurkan diri',
            'DIPINDAHKAN'       => 'Dipindahkan',
        ],
    ],

    'saya' => [
        'judul'  => 'Kelas saya',
        'kosong' => 'Anda belum terdaftar di kelas mana pun. Hubungi dosen pengampu mata kuliah Anda.',
        'tahun'  => 'Tahun ajaran',
        'info'   => 'Latihan soal akan tersedia di sini setelah pertemuan dibuka oleh dosen.',
    ],
];
