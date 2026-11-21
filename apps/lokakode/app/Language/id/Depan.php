<?php

// Halaman depan publik LokaKode (SPEC.md §15). Tidak ada data nyata di sini.
return [
    'judul'       => 'LokaKode — latihan pemrograman dengan penilaian otomatis',
    'deskripsi'   => 'LokaKode adalah tempat latihan pemrograman C, C++ dan Python untuk mahasiswa: tulis kode di peramban, jalankan, kirim, dan langsung lihat hasil penilaiannya.',
    'tajuk'       => 'Latihan pemrograman yang langsung dinilai',
    'pengantar'   => 'Tulis kode di peramban, jalankan dengan contoh, lalu kirim. LokaKode menguji program Anda dan menunjukkan hasilnya dalam hitungan detik.',
    'tanpaDaftar' => 'Masuk dengan akun Lokandita Anda. Tidak ada pendaftaran mandiri.',
    'fiturJudul'  => 'Yang Anda dapatkan',
    'fitur'       => [
        ['judul' => 'Penilaian otomatis', 'isi' => 'Setiap kiriman diuji dengan serangkaian tes tersembunyi. Anda melihat berapa tes yang lolos dan nilainya, tanpa menunggu dosen.'],
        ['judul' => 'Soal dengan angka Anda sendiri', 'isi' => 'Semua mahasiswa membaca soal yang sama, tetapi angka dan tesnya berbeda untuk tiap orang.'],
        ['judul' => 'Boleh mencoba berkali-kali', 'isi' => 'Tidak ada batas percobaan dan tidak ada pengurangan nilai. Yang dihitung adalah hasil terbaik Anda.'],
        ['judul' => 'Target per pertemuan', 'isi' => 'Tiap pertemuan punya target poin. Pilih sendiri soal yang dikerjakan, dari yang mudah sekali sampai yang sulit.'],
        ['judul' => 'Editor di peramban', 'isi' => 'Editor kode dengan penyorotan sintaks, simpan otomatis, dan tombol Jalankan untuk mencoba dengan masukan Anda sendiri.'],
        ['judul' => 'Papan peringkat kelas', 'isi' => 'Lihat posisi Anda di kelas untuk tiap pertemuan.'],
    ],
    'caraJudul'   => 'Cara kerjanya',
    'cara'        => [
        'Masuk dengan nomor induk dan kata sandi Anda.',
        'Buka pertemuan yang sedang dibuka untuk kelas Anda, lalu pilih soal.',
        'Tulis program Anda. Tekan Jalankan untuk mencobanya dengan contoh atau masukan sendiri.',
        'Tekan Kirim. Program diuji dengan tes tersembunyi dan nilainya langsung tampil.',
        'Perbaiki dan kirim lagi sesering yang Anda perlukan sampai waktu pertemuan ditutup.',
    ],
    'bahasaJudul'   => 'Bahasa pemrograman',
    'bahasaProgram' => [
        'C (GCC 13, standar C17)',
        'C++ (GCC 13, standar C++17)',
        'Python 3.12',
    ],
    'sebJudul'    => 'Safe Exam Browser',
    'seb'         => [
        'Mahasiswa nantinya wajib membuka LokaKode melalui Safe Exam Browser (SEB) versi 3.10.x untuk Windows 64-bit. Saat ini kewajiban itu belum diberlakukan, dan LokaKode masih bisa dibuka dengan peramban biasa.',
        'Berkas konfigurasi dan petunjuk pemasangannya akan diumumkan di halaman ini sebelum kewajiban itu mulai berlaku. Anda sudah boleh memasang SEB dari situs resminya.',
    ],
    'sebTautan'   => 'Unduh Safe Exam Browser dari situs resminya (safeexambrowser.org)',
    'tanyaJudul'  => 'Pertanyaan yang sering diajukan',
    'tanya'       => [
        ['tanya' => 'Bagaimana cara mendapat akun?', 'jawab' => 'Akun dibuatkan oleh pengelola untuk mahasiswa dan dosen yang terdaftar. Jika Anda belum menerimanya, hubungi dosen Anda.'],
        ['tanya' => 'Saya lupa kata sandi.', 'jawab' => 'Mintalah dosen pengajar kelas Anda untuk mengatur ulang kata sandi. Anda akan menerima kata sandi sementara yang harus langsung diganti.'],
        ['tanya' => 'Apakah mencoba berkali-kali mengurangi nilai?', 'jawab' => 'Tidak. Jumlah percobaan dicatat, tetapi nilai Anda adalah hasil terbaik dari semua kiriman.'],
        ['tanya' => 'Mengapa saya tidak bisa menempel teks ke editor?', 'jawab' => 'Menempel ke editor memang dimatikan, supaya kode ditulis sendiri. Mahasiswa yang memerlukan alat bantu dapat meminta pengecualian kepada pengelola.'],
        ['tanya' => 'Mengapa jawaban saya salah padahal contohnya benar?', 'jawab' => 'Contoh hanya sebagian kecil dari tes. Kiriman diuji dengan tes tersembunyi lain yang mencakup lebih banyak kasus; isi tes itu tidak ditampilkan.'],
        ['tanya' => 'Apakah nilai di sini nilai akhir mata kuliah?', 'jawab' => 'Bukan. LokaKode hanya memberi nilai latihan per pertemuan. Nilai akhir ditetapkan dosen.'],
    ],
    'kontak'      => 'Pertanyaan dan laporan masalah:',
    'kePortal'    => 'Tentang Lokandita, privasi dan ketentuan penggunaan',
];
