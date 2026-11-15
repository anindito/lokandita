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
        'ubah'          => 'Edit academic year',
        'tambah'        => 'Add academic year',
        'nama'          => 'Academic year name',
        'mulai'         => 'Start date',
        'selesai'       => 'End date',
        'status'        => 'Status',
        'jumlahKelas'   => 'Classes',
        'aktifkan'      => 'Activate',
        'aktifkanInfo'  => 'Only one academic year is active. Activating a year archives the current one; its data stays readable.',
        'baruArsip'     => 'A new academic year starts archived until you activate it.',
        'kosong'        => 'No academic years yet.',
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
        'tambah'        => 'Add course',
        'ubah'          => 'Edit course',
        'pengampuNomor' => 'Course owner ID number',
        'pengampuInfo'  => 'One lecturer per course. The owner manages the classes, teachers and enrollment of this course.',
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

    'pesan' => [
        'disimpan'        => 'Changes saved.',
        'periksa'         => 'Please check the marked fields.',
        'tahunDiaktifkan' => 'Academic year activated. The previously active year is now archived.',
    ],

    'galat' => [
        'labelPanjang'       => 'The name must be 3 to 40 characters, for example "2026/2027 Odd".',
        'labelDipakai'       => 'An academic year with this name already exists.',
        'tanggal'            => 'Enter a valid date.',
        'tanggalUrut'        => 'The end date must be after the start date.',
        'kodeBentuk'         => 'The code is 2 to 20 capital letters, digits or hyphens, for example DDP101.',
        'kodeDipakai'        => 'A course with this code already exists.',
        'namaPanjang'        => 'The name must be 3 to 190 characters.',
        'pengampuTidakAda'   => 'This ID number is not registered in LokaAuth.',
        'pengampuBukanDosen' => 'The course owner must be an account with the lecturer role.',
        'pengampuTidakAktif' => 'This lecturer account is not active.',
    ],

    'aksi' => [
        'simpan' => 'Save',
        'batal'  => 'Cancel',
        'ubah'   => 'Edit',
    ],
];
