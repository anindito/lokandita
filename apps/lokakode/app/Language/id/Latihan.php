<?php

// Teks LokaKode: latihan — penerbitan pertemuan, jendela per kelas, halaman
// mahasiswa, penilaian (SPEC.md §6, §7, §11).
return [
    'pesan' => [
        'terbit'          => 'Pertemuan diterbitkan. Mahasiswa melihatnya begitu jendela kelasnya dibuka.',
        'ditarik'         => 'Pertemuan ditarik. Mahasiswa tidak lagi melihatnya; kiriman dan nilai yang sudah ada tetap tersimpan.',
        'jendelaDisimpan' => 'Jadwal disimpan.',
        'statusDiubah'    => 'Keadaan pertemuan untuk kelas ini diubah.',
        'disiapkan'       => '{0} soal mahasiswa sedang disiapkan.',
    ],

    'terbit' => [
        'judul'        => 'Penerbitan',
        'sudah'        => 'Sudah terbit',
        'belum'        => 'Belum terbit',
        'sudahInfo'    => 'Mahasiswa melihat pertemuan ini selama jendela kelasnya terbuka.',
        'belumInfo'    => 'Selama belum terbit, mahasiswa tidak melihat pertemuan ini, juga bila jendela kelasnya sudah dibuka.',
        'komposisi'    => 'Soal yang sudah disetujui, dibandingkan dengan komposisi minimum',
        'poin'         => 'Poin',
        'disetujui'    => 'Disetujui',
        'minimum'      => 'Minimum',
        'keterangan'   => 'Keterangan',
        'cukup'        => 'cukup',
        'kurangSoal'   => 'kurang {0} soal',
        'poinTersedia' => 'Poin yang tersedia: {0}. Target: {1}. Untuk terbit dibutuhkan paling sedikit {2} poin (2 × target).',
        'kurangPoin'   => 'Kurang {0} poin.',
        'peringatan'   => 'Boleh terbit, tetapi pilihan soalnya sempit: sebaiknya tersedia {0} poin (3 × target).',
        'nonaktif'     => 'Pertemuan ini tidak dipakai untuk latihan, jadi tidak bisa diterbitkan.',
        'terbitkan'    => 'Terbitkan pertemuan',
        'tarik'        => 'Tarik dari mahasiswa',
        'tarikInfo'    => 'Kiriman, nilai dan soal mahasiswa yang sudah ada tidak dihapus.',
        'ditolak'      => 'Pertemuan belum bisa diterbitkan. Yang kurang ditandai di bagian Penerbitan.',
    ],

    'atur' => [
        'judul'         => 'Pengaturan latihan',
        'target'        => 'Target poin',
        'targetInfo'    => 'Mahasiswa yang mengumpulkan poin sebanyak ini mendapat nilai 100. Bawaan: 60.',
        'komposisi'     => 'Komposisi minimum',
        'komposisiInfo' => 'Jumlah soal disetujui yang harus ada di tiap tingkat sebelum pertemuan bisa terbit. Boleh diturunkan selama poin yang tersedia tetap 2 × target.',
        'poinSatuan'    => '{0} poin',
        'templat'       => 'Templat awal editor',
        'templatInfo'   => 'Kode yang sudah ada di editor ketika mahasiswa membuka soal, dan yang dikembalikan tombol Reset. Kosongkan untuk memakai templat bawaan bahasanya (terlihat samar di kotak ini).',
        'bendera'       => 'Bendera kompiler tambahan',
        'benderaInfo'   => 'Pisahkan dengan spasi. Yang boleh dipakai:',
        'lengkapi'      => 'Pelengkapan otomatis di editor',
        'lengkapiInfo'  => 'Bila dimatikan, editor tidak menawarkan saran saat mahasiswa mengetik.',
        'simpan'        => 'Simpan pengaturan latihan',
    ],

    'jendela' => [
        'judul'           => 'Jadwal pertemuan',
        'info'            => 'Tiap kelas punya jadwal buka dan tutup sendiri untuk setiap pertemuan.',
        'kosong'          => 'Mata kuliah ini belum punya pertemuan yang dipakai untuk latihan.',
        'keadaan'         => 'Keadaan',
        'buka'            => 'Dibuka',
        'tutup'           => 'Ditutup',
        'manual'          => '(manual)',
        'jumlahSoal'      => '{0} soal disetujui',
        'belumTerbit'     => 'Pertemuan ini belum diterbitkan.',
        'belumTerbitInfo' => 'Jadwal boleh diatur sekarang, tetapi mahasiswa baru melihat soalnya setelah pengampu menerbitkan pertemuan ini.',
        'manualJudul'     => 'Buka atau tutup sekarang',
        'manualInfo'      => 'Membuka atau menutup manual mengalahkan jadwal sampai Anda memilih "Ikuti jadwal" lagi.',
        'bukaSekarang'    => 'Buka sekarang',
        'tutupSekarang'   => 'Tutup sekarang',
        'ikutJadwal'      => 'Ikuti jadwal',
        'jadwal'          => 'Jadwal',
        'jadwalInfo'      => 'Waktu Indonesia Barat. Kosongkan keduanya bila pertemuan ini hanya dibuka dan ditutup manual.',
        'sesudahTutup'    => 'Sesudah ditutup, mahasiswa',
        'simpan'          => 'Simpan jadwal',
    ],

    'keadaan' => [
        'belum'     => 'Belum dijadwalkan',
        'terjadwal' => 'Terjadwal',
        'buka'      => 'Terbuka',
        'tutup'     => 'Tertutup',
    ],

    'keadaanInfo' => [
        'belum'     => 'Kelas ini belum punya jadwal untuk pertemuan ini. Mahasiswa belum bisa mengerjakannya.',
        'terjadwal' => 'Pertemuan terbuka sendiri pada waktu buka di bawah.',
        'buka'      => 'Mahasiswa kelas ini bisa mengerjakan dan mengirim jawaban sekarang.',
        'tutup'     => 'Mahasiswa kelas ini tidak bisa lagi mengirim jawaban.',
    ],

    'tampak' => [
        'hidden'      => 'hanya melihat nilainya',
        'review_only' => 'masih bisa membaca soal dan kirimannya sendiri',
    ],

    'siap' => [
        'judul'      => 'Soal mahasiswa',
        'hitung'     => '{0} dari {1} soal mahasiswa sudah siap.',
        'rincian'    => '({0} mahasiswa aktif × {1} soal)',
        'antre'      => '{0} sedang dibuat.',
        'perkiraan'  => 'Perkiraan selesai: sekitar {0} menit.',
        'workerMati' => 'Mesin penilai sedang tidak berjalan, jadi soal belum bisa dibuat. Hubungi admin.',
        'gagal'      => '{0} soal gagal dibuat. Tekan "Siapkan sekarang" untuk mencoba lagi; bila tetap gagal, soalnya perlu diperiksa pengampu.',
        'info'       => 'Setiap mahasiswa mendapat angka dan tes yang berbeda untuk soal yang sama. Soal itu dibuat begitu jadwal disimpan atau pertemuan dibuka; yang belum siap dibuat saat mahasiswanya membuka soal.',
        'tombol'     => 'Siapkan sekarang',
    ],

    'galat' => [
        'target'          => 'Target poin harus angka 1 sampai 1000.',
        'minimum'         => 'Isi dengan angka 0 sampai 50.',
        'templat'         => 'Templat paling panjang 20.000 karakter.',
        'bendera'         => 'Ada bendera yang tidak diizinkan. Pakai hanya yang ada di daftar, paling banyak 8.',
        'benderaPython'   => 'Python tidak memakai bendera kompiler. Kosongkan kolom ini.',
        'melanggarTerbit' => 'Dengan nilai ini pertemuan yang sudah terbit tidak lagi memenuhi syarat terbit (komposisi minimum dan 2 × target). Tarik dulu pertemuannya, atau pilih nilai lain.',
        'waktu'           => 'Waktu tidak dikenali. Pilih tanggal dan jam.',
        'berpasangan'     => 'Isi waktu buka dan waktu tutup dua-duanya, atau kosongkan dua-duanya.',
        'urutan'          => 'Waktu tutup harus sesudah waktu buka.',
        'tampak'          => 'Pilih salah satu dari dua pilihan.',
        'override'        => 'Pilihan tidak dikenali.',
    ],
];
