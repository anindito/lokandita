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
        'ubah'          => 'Ubah tahun ajaran',
        'tambah'        => 'Tambah tahun ajaran',
        'nama'          => 'Nama tahun ajaran',
        'mulai'         => 'Tanggal mulai',
        'selesai'       => 'Tanggal selesai',
        'status'        => 'Status',
        'jumlahKelas'   => 'Kelas',
        'aktifkan'      => 'Aktifkan',
        'aktifkanInfo'  => 'Hanya satu tahun ajaran yang aktif. Mengaktifkan sebuah tahun ajaran mengarsipkan yang sedang aktif; data di dalamnya tetap bisa dibaca.',
        'baruArsip'     => 'Tahun ajaran baru dibuat dalam keadaan diarsipkan sampai Anda mengaktifkannya.',
        'kosong'        => 'Belum ada tahun ajaran.',
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
        'tambah'        => 'Tambah mata kuliah',
        'ubah'          => 'Ubah mata kuliah',
        'pengampuNomor' => 'Nomor induk pengampu',
        'pengampuInfo'  => 'Satu dosen per mata kuliah. Pengampu mengelola kelas, pengajar, dan pendaftaran mata kuliah ini.',
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

    'pesan' => [
        'disimpan'        => 'Perubahan disimpan.',
        'periksa'         => 'Periksa kembali isian yang ditandai.',
        'tahunDiaktifkan' => 'Tahun ajaran diaktifkan. Tahun ajaran yang sebelumnya aktif kini diarsipkan.',
    ],

    'galat' => [
        'labelPanjang'       => 'Nama tahun ajaran harus 3 sampai 40 karakter, misalnya "2026/2027 Ganjil".',
        'labelDipakai'       => 'Sudah ada tahun ajaran dengan nama ini.',
        'tanggal'            => 'Isi tanggal yang benar.',
        'tanggalUrut'        => 'Tanggal selesai harus sesudah tanggal mulai.',
        'kodeBentuk'         => 'Kode berisi 2 sampai 20 huruf besar, angka, atau tanda hubung, misalnya DDP101.',
        'kodeDipakai'        => 'Sudah ada mata kuliah dengan kode ini.',
        'namaPanjang'        => 'Nama harus 3 sampai 190 karakter.',
        'pengampuTidakAda'   => 'Nomor induk ini tidak terdaftar di LokaAuth.',
        'pengampuBukanDosen' => 'Pengampu harus akun dengan peran dosen.',
        'pengampuTidakAktif' => 'Akun dosen ini sedang tidak aktif.',
    ],

    'aksi' => [
        'simpan' => 'Simpan',
        'batal'  => 'Batal',
        'ubah'   => 'Ubah',
    ],
];
