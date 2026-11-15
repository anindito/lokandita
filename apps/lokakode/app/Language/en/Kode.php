<?php

// LokaKode text: academic structure (SPEC.md §5).
return [
    'tidakDitemukan' => 'What you are looking for does not exist, or you are not allowed to see it.',
    'navigasi'       => 'LokaKode menu',
    'kosong'         => 'Nothing here yet.',
    'ya'             => 'Yes',
    'tidak'          => 'No',

    'nav' => [
        'beranda' => 'Home',
        'matkul'  => 'Courses',
        'tahun'   => 'Academic years',
        'audit'   => 'Audit log',
    ],

    'tahun' => [
        'aktif'         => 'Active academic year',
        'belumAda'      => 'There is no active academic year yet. Please contact the superadmin.',
        'belumAdaAdmin' => 'There is no active academic year yet. Create and activate one first.',
        'tampilkan'     => 'Academic year',
        'lihat'         => 'Show',
        'diarsipkan'    => 'archived',
        'statusNama'    => ['active' => 'Active', 'archived' => 'Archived'],
    ],

    'ringkas' => [
        'kelas'         => 'Classes',
        'mahasiswa'     => 'Active students',
        'tanpaPengajar' => 'Classes without a teacher',
    ],

    'matkul' => [
        'judul'        => 'Courses',
        'kode'         => 'Code',
        'nama'         => 'Name',
        'pengampu'     => 'Course owner',
        'kosong'       => 'You do not own or teach any course yet.',
        'andaPengampu' => 'You own this course',
        'andaPengajar' => 'You teach here',
    ],

    'kelas' => [
        'judul'         => 'Classes',
        'nama'          => 'Class',
        'kapasitas'     => 'Capacity',
        'tanpaBatas'    => 'no limit',
        'pengajar'      => 'Teachers',
        'tanpaPengajar' => 'No teacher yet',
        'mahasiswa'     => 'Active students',
        'diarsipkan'    => 'Archived',
        'kosong'        => 'No classes in this academic year yet.',
        'kosongDosen'   => 'You do not own or teach any class in this academic year.',
        'daftar'        => 'Students',
        'daftarKosong'  => 'No students in this class yet.',
        'jumlah'        => '{0} students, {1} active',
        'hanyaLihat'    => 'You teach this class. Changes to the class and its enrollment are made by the course owner.',
        'kembali'       => 'Back to the course',
    ],

    'mhs' => [
        'nomorInduk'     => 'ID number',
        'nama'           => 'Name',
        'status'         => 'Status',
        'catatan'        => 'Notes',
        'tanggalLulus'   => 'graduated {0}',
        'tanpaAkses'     => 'No LokaKode access yet',
        'akunTidakAktif' => 'Account not active',
        'akunHilang'     => 'Account not found in LokaAuth',
        'statusNama'     => [
            'AKTIF'             => 'Active',
            'LULUS'             => 'Graduated',
            'MENGUNDURKAN_DIRI' => 'Withdrawn',
            'DIPINDAHKAN'       => 'Transferred',
        ],
    ],

    'saya' => [
        'judul'  => 'My classes',
        'kosong' => 'You are not enrolled in any class yet. Please contact your course lecturer.',
        'tahun'  => 'Academic year',
        'info'   => 'Practice problems will appear here once your lecturer opens a meeting.',
    ],
];
