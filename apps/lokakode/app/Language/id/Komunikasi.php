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
    'klarifikasi' => [
        'judul' => 'Klarifikasi soal',
        'penjelasanDosen' => 'Pertanyaan mahasiswa kelas ini tentang soal. Jawaban pribadi hanya dilihat penanya; jawaban yang diterbitkan ke kelas dilihat semua mahasiswa kelas ini, bersama pertanyaan dan nama penanyanya.',
        'catatanDosen' => 'Angka pada soal berbeda untuk setiap mahasiswa. Sebelum menerbitkan jawaban ke kelas, pastikan jawaban Anda tidak bergantung pada angka milik penanya.',
        'kosongDosen' => 'Belum ada pertanyaan dari mahasiswa kelas ini.',
        'belum' => 'Belum dijawab',
        'belumJumlah' => '{0} belum dijawab',
        'pribadi' => 'Jawaban pribadi',
        'keKelas' => 'Diterbitkan ke kelas',
        'kirimanPenanya' => 'Lihat kiriman penanya untuk soal ini',
        'dijawabOleh' => 'Dijawab oleh {0}, {1}',
        'jawaban' => 'Jawaban',
        'ubahJawaban' => 'Jawaban (bisa diubah)',
        'siapaMelihat' => 'Siapa yang melihat jawaban ini',
        'pilihPribadi' => 'Hanya penanya',
        'pilihKelas' => 'Seluruh kelas, bersama pertanyaan dan nama penanya',
        'simpan' => 'Simpan jawaban',
        'tersimpan' => 'Jawaban tersimpan.',
        'jawabanSalah' => 'Jawaban tidak tersimpan: isi jawabannya (paling banyak 2.000 karakter) dan pilih siapa yang melihatnya.',
    ],
];
