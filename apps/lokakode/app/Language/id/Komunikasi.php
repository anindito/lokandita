<?php

// Pemberitahuan, klarifikasi soal dan pengumuman kelas (SPEC.md §14, KONTRAK.md §17).
return [
    'notifikasi' => [
        'judul'            => 'Pemberitahuan',
        'penjelasan'       => 'Pemberitahuan muncul saat Anda membuka LokaKode: pertemuan yang dibuka untuk kelas Anda, pertemuan yang akan ditutup dalam 24 jam, dan kata sandi yang direset dosen.',
        'kosong'           => 'Belum ada pemberitahuan.',
        'baru'             => 'Baru',
        'belumDibaca'      => '{0} belum dibaca',
        'tandai'           => 'Tandai semua sudah dibaca',
        'pertemuan_dibuka' => '{0} — Pertemuan {1}: {2} sudah dibuka untuk kelas Anda.',
        'tutup_24jam'      => '{0} — Pertemuan {1}: {2} akan ditutup kurang dari 24 jam lagi.',
        'tutupPada'        => 'Ditutup pada {0}.',
        'sandi_direset'    => 'Kata sandi Anda direset oleh {0} (kelas {1}). Bila bukan Anda yang memintanya, segera hubungi dosen Anda.',
        'seorangDosen'     => 'seorang dosen',
    ],
];
