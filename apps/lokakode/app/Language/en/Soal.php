<?php

// LokaKode texts: meetings, concepts, problem bank, import (SPEC.md §8, §9).
return [
    'bahasa' => ['c' => 'C', 'cpp' => 'C++', 'python' => 'Python'],

    'tingkat' => [
        'MUDAH_SEKALI' => 'Very easy',
        'MUDAH'        => 'Easy',
        'MUDAH_SEDANG' => 'Easy-medium',
        'SEDANG'       => 'Medium',
        'SULIT'        => 'Hard',
    ],

    'status' => [
        'PENDING_REVIEW' => 'Awaiting review',
        'APPROVED'       => 'Approved',
        'REJECTED'       => 'Rejected',
    ],

    'pertemuan' => [
        'judul'        => 'Meetings',
        'semua'        => 'All meetings',
        'ke'           => 'Meeting {0}',
        'kosong'       => 'This course has no meetings yet.',
        'nomor'        => 'No.',
        'nomorIsi'     => 'Meeting number (1–16)',
        'judulKolom'   => 'Title',
        'bahasa'       => 'Programming language',
        'bahasaInfo'   => 'A meeting uses exactly one language. Imported problems must use this language.',
        'tambah'       => 'Add meeting',
        'pengaturan'   => 'Meeting settings',
        'aktif'        => 'Used for practice',
        'aktifInfo'    => 'Turn off for meetings such as mid-term and final exams, or those that cannot be graded automatically.',
        'nonaktif'     => 'not used',
        'terkunciInfo' => 'This meeting already has problems, so its number and language can no longer be changed.',
        'kelola'       => 'Meetings and problem bank',
    ],

    'konsep' => [
        'judul'       => 'Concepts first allowed at this meeting',
        'info'        => 'Concepts are cumulative: those allowed at earlier meetings are ticked and locked. A reference solution may only use allowed concepts; student code is never restricted.',
        'diwarisi'    => 'inherited',
        'dasarJudul'  => 'Base concepts of the course',
        'dasarInfo'   => 'Concepts assumed to be known before meeting 1, for example from a prerequisite course. They are allowed at every meeting.',
        'simpanDasar' => 'Save base concepts',
        'grup'        => [
            'DASAR'   => 'Basics',
            'KONTROL' => 'Control structures',
            'DATA'    => 'Data structures',
            'FUNGSI'  => 'Functions',
            'LANJUT'  => 'Advanced',
            'OOP'     => 'Object-oriented programming',
            'PUSTAKA' => 'Python libraries',
        ],
    ],

    'soal' => [
        'bank'             => 'Problem bank',
        'judul'            => 'Problem',
        'jumlah'           => 'Problems',
        'disetujui'        => 'Approved',
        'tingkat'          => 'Difficulty',
        'versi'            => 'Version',
        'status'           => 'Status',
        'kosong'           => 'This meeting has no problems yet. Problems are uploaded by a superadmin.',
        'kosongAdmin'      => 'This meeting has no problems yet. Upload a JSON file to fill it.',
        'versiLamaDipakai' => 'students still use the previous version',
    ],

    'impor' => [
        'unggah' => 'Upload problems (JSON)',
    ],

    'pesan' => [
        'disetujui' => 'The problem is approved and is now used by this meeting.',
        'ditolak'   => 'That version of the problem was rejected.',
    ],

    'galat' => [
        'nomor'          => 'The meeting number must be 1 to 16.',
        'nomorDipakai'   => 'This course already has a meeting with that number.',
        'nomorTerkunci'  => 'The number cannot be changed because this meeting already has problems.',
        'judul'          => 'The title must be 3 to 190 characters.',
        'bahasa'         => 'Choose C, C++ or Python.',
        'bahasaTerkunci' => 'The language cannot be changed because this meeting already has problems.',
    ],
];
