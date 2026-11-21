<?php

// Teks Portal Lokandita (SPEC.md §15). {email} diganti alamat kontak dari CONTACT_EMAIL
// oleh tampilan publik/teks; alamat itu tidak pernah ditulis di sini.
return [
    'nav' => [
        'tentang'   => 'Tentang',
        'kontak'    => 'Kontak',
        'privasi'   => 'Privasi',
        'ketentuan' => 'Ketentuan',
    ],
    'aplikasi' => [
        'lokakode'  => 'Latihan pemrograman C, C++ dan Python dengan penilaian otomatis.',
        'lokaujian' => 'Sedang disiapkan.',
        'lokasiber' => 'Sedang disiapkan.',
    ],
    'depan' => [
        'judul'       => 'Lokandita — belajar pemrograman dengan satu akun',
        'deskripsi'   => 'Lokandita adalah kumpulan aplikasi pembelajaran untuk mata kuliah pemrograman: satu akun untuk masuk ke semuanya, dimulai dengan LokaKode untuk latihan yang dinilai otomatis.',
        'tajuk'       => 'Satu akun untuk semua aplikasi belajar Anda',
        'pengantar'   => 'Lokandita menghimpun aplikasi pembelajaran untuk mata kuliah pemrograman. Masuk sekali, lalu buka aplikasi yang Anda perlukan tanpa masuk lagi.',
        'tanpaDaftar' => 'Tidak ada pendaftaran mandiri. Akun dibuatkan oleh pengelola untuk mahasiswa dan dosen yang terdaftar.',
        'aplikasi'    => 'Aplikasi',
        'caraJudul'   => 'Cara memulai',
        'cara'        => [
            'Tekan Masuk, lalu isi nomor induk (NIM atau NIP) dan kata sandi yang Anda terima.',
            'Pada kali pertama, Anda diminta mengganti kata sandi awal dengan kata sandi Anda sendiri.',
            'Pilih aplikasi yang tersedia untuk akun Anda. Anda tidak perlu masuk lagi di aplikasi itu.',
            'Di komputer bersama, selalu tekan Keluar di menu akun sebelum meninggalkan tempat.',
        ],
        'bantuan'     => 'Belum punya akun, lupa kata sandi, atau ada yang tidak berjalan? Lihat halaman',
    ],
    'beranda' => [
        'aplikasi' => 'Aplikasi Anda',
        'kosong'   => 'Akun Anda belum diberi akses ke aplikasi mana pun. Hubungi dosen Anda atau pengelola:',
        'admin'    => 'Pengelolaan pengguna dan log audit.',
    ],
    'data' => [
        'judul'      => 'Data saya',
        'penjelasan' => 'Anda dapat mengunduh salinan data tentang diri Anda yang disimpan Lokandita. Setiap aplikasi menyimpan datanya sendiri, sehingga ada satu berkas per aplikasi.',
        'unduhDari'  => 'Data saya di {0}',
        'catatan'    => 'Berkas berbentuk JSON dan hanya berisi data Anda sendiri. Setiap pengunduhan dicatat.',
    ],
    'tentang' => [
        'judul'     => 'Tentang Lokandita',
        'deskripsi' => 'Apa itu Lokandita, aplikasi apa saja yang ada di dalamnya, dan untuk siapa.',
        'pembuka'   => [
            'Lokandita adalah kumpulan aplikasi pembelajaran untuk mata kuliah pemrograman. Semua aplikasinya dipakai dengan satu akun, sehingga mahasiswa dan dosen cukup masuk satu kali.',
        ],
        'bagian'    => [
            [
                'judul' => 'Aplikasi',
                'isi'   => [],
                'butir' => [
                    'LokaAuth — tempat masuk dan pengelolaan akun untuk semua aplikasi Lokandita.',
                    'LokaKode — latihan pemrograman dalam C, C++ dan Python. Setiap kiriman dijalankan dan dinilai otomatis, dan setiap mahasiswa mendapat angka soal yang berbeda.',
                    'LokaUjian dan LokaSiber — sedang disiapkan dan belum bisa dipakai.',
                ],
            ],
            [
                'judul' => 'Untuk siapa',
                'isi'   => [
                    'Lokandita dipakai oleh mahasiswa dan dosen yang akunnya dibuatkan oleh pengelola. Tidak ada pendaftaran mandiri dan tidak ada akun tamu.',
                ],
            ],
            [
                'judul' => 'Cara kami memperlakukan data',
                'isi'   => [
                    'Lokandita hanya menyimpan data yang diperlukan untuk pembelajaran, tidak memasang iklan atau pelacak, dan tidak memuat berkas dari pihak lain. Rinciannya ada di halaman Privasi.',
                ],
            ],
            [
                'judul' => 'Pengelola',
                'isi'   => [
                    'Lokandita dikelola oleh pengelola Lokandita. Pertanyaan dan laporan dapat dikirim ke {email}.',
                ],
            ],
        ],
    ],
    'kontak' => [
        'judul'     => 'Kontak',
        'deskripsi' => 'Cara menghubungi pengelola Lokandita dan ke mana meminta bantuan soal akun.',
        'pembuka'   => [
            'Pengelola Lokandita dapat dihubungi lewat surel: {email}.',
        ],
        'bagian'    => [
            [
                'judul' => 'Lupa kata sandi',
                'isi'   => [
                    'Mahasiswa: mintalah dosen pengajar kelas Anda untuk mengatur ulang kata sandi. Anda akan menerima kata sandi sementara yang harus langsung diganti.',
                    'Dosen: hubungi pengelola lewat alamat di atas.',
                ],
            ],
            [
                'judul' => 'Belum punya akun',
                'isi'   => [
                    'Akun dibuatkan oleh pengelola berdasarkan daftar mahasiswa dan dosen. Jika nama Anda seharusnya terdaftar tetapi Anda belum menerima akun, hubungi dosen Anda.',
                ],
            ],
            [
                'judul' => 'Melaporkan masalah',
                'isi'   => [
                    'Sebutkan nomor induk Anda, aplikasi dan halaman yang bermasalah, waktu kejadian, dan apa yang Anda lihat. Jangan pernah mengirimkan kata sandi Anda — pengelola tidak akan memintanya.',
                ],
            ],
        ],
    ],
    'privasi' => [
        'judul'      => 'Kebijakan privasi',
        'deskripsi'  => 'Data pribadi apa yang disimpan Lokandita, untuk apa, siapa yang dapat melihatnya, dan apa hak Anda.',
        'diperbarui' => 'Terakhir diperbarui: 21 November 2026',
        'pembuka'    => [
            'Halaman ini menjelaskan data pribadi yang diproses Lokandita (LokaAuth, LokaKode dan Portal ini), dengan mengacu pada Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi.',
            'Data dikelola oleh pengelola Lokandita, yang dapat dihubungi di {email}.',
        ],
        'bagian'     => [
            [
                'judul' => 'Data yang kami simpan',
                'isi'   => [],
                'butir' => [
                    'Data akun: nomor induk (NIM atau NIP), nama, peran (mahasiswa, dosen atau superadmin), dan alamat surel bila diisi.',
                    'Kata sandi Anda tidak disimpan; yang disimpan hanya hasil hash-nya (Argon2id), yang tidak dapat dibaca kembali.',
                    'Catatan masuk dan sesi: waktu, alamat IP, dan jenis peramban setiap kali Anda masuk atau gagal masuk.',
                    'Data belajar di LokaKode: kelas yang Anda ikuti, kode program yang Anda kirim, draf yang tersimpan otomatis, hasil penilaian dan nilai.',
                    'Catatan integritas di LokaKode, misalnya upaya menempel teks ke editor.',
                    'Log audit: tindakan penting atas akun Anda, seperti pembuatan akun dan pengaturan ulang kata sandi.',
                ],
            ],
            [
                'judul' => 'Untuk apa data itu dipakai',
                'isi'   => [],
                'butir' => [
                    'Mengenali Anda saat masuk dan menjaga keamanan akun.',
                    'Menjalankan dan menilai latihan, serta menampilkan kemajuan Anda.',
                    'Membantu dosen memantau kelasnya dan menjaga kejujuran akademik.',
                    'Menelusuri gangguan dan penyalahgunaan sistem.',
                ],
            ],
            [
                'judul' => 'Siapa yang dapat melihat data Anda',
                'isi'   => [],
                'butir' => [
                    'Anda sendiri.',
                    'Dosen pengampu mata kuliah dan dosen pengajar kelas Anda: nama, nomor induk, kiriman, nilai dan catatan integritas Anda di kelas itu.',
                    'Teman sekelas: nama dan nilai Anda di papan peringkat kelas, kecuali bila dosen menganonimkannya.',
                    'Superadmin: semua data di atas, untuk mengelola sistem.',
                ],
            ],
            [
                'judul' => 'Yang tidak kami lakukan',
                'isi'   => [
                    'Kami tidak menjual atau menyerahkan data Anda kepada pihak lain, tidak memasang iklan, dan tidak memakai pelacak atau layanan analitik pihak ketiga. Semua berkas halaman dimuat dari server Lokandita sendiri.',
                ],
            ],
            [
                'judul' => 'Cookie',
                'isi'   => [
                    'Lokandita hanya memakai cookie yang diperlukan agar sistem berjalan: cookie sesi setelah Anda masuk, cookie pengaman formulir, dan cookie pilihan bahasa. Tidak ada cookie iklan atau pelacak.',
                ],
            ],
            [
                'judul' => 'Penyimpanan dan keamanan',
                'isi'   => [
                    'Data disimpan di server yang dikelola pengelola Lokandita. Semua hubungan memakai HTTPS. Cadangan dibuat setiap hari dan disimpan selama 14 hari.',
                    'Akun tidak dihapus permanen, karena nilai dan kiriman merujuk ke akun pemiliknya. Akun yang tidak lagi dipakai dinonaktifkan sehingga tidak bisa dipakai masuk.',
                ],
            ],
            [
                'judul' => 'Hak Anda',
                'isi'   => [],
                'butir' => [
                    'Mengunduh salinan data Anda: setelah masuk, buka "Data saya" di Portal.',
                    'Mengganti kata sandi kapan saja lewat menu akun.',
                    'Meminta perbaikan data yang keliru (misalnya nama), atau mengajukan pertanyaan dan keberatan atas pemrosesan data Anda, lewat {email}.',
                ],
            ],
            [
                'judul' => 'Perubahan kebijakan',
                'isi'   => [
                    'Bila kebijakan ini berubah, tanggal di bagian atas halaman diperbarui.',
                ],
            ],
        ],
    ],
    'ketentuan' => [
        'judul'      => 'Ketentuan penggunaan',
        'deskripsi'  => 'Aturan memakai akun dan aplikasi Lokandita.',
        'diperbarui' => 'Terakhir diperbarui: 21 November 2026',
        'pembuka'    => [
            'Dengan memakai akun Lokandita, Anda menyetujui ketentuan berikut.',
        ],
        'bagian'     => [
            [
                'judul' => 'Akun',
                'isi'   => [],
                'butir' => [
                    'Akun dibuatkan oleh pengelola dan hanya untuk Anda. Jangan meminjamkannya dan jangan memakai akun orang lain.',
                    'Ganti kata sandi awal pada kali pertama masuk, dan rahasiakan kata sandi Anda. Pengelola dan dosen tidak akan pernah memintanya.',
                    'Di komputer bersama, tekan Keluar sebelum meninggalkan tempat.',
                ],
            ],
            [
                'judul' => 'Kejujuran akademik',
                'isi'   => [],
                'butir' => [
                    'Kiriman Anda harus hasil kerja Anda sendiri, kecuali dosen menyatakan lain.',
                    'Sistem mencatat kejadian tertentu, misalnya upaya menempel teks ke editor. Catatan itu adalah bahan bagi dosen; keputusannya tetap pada dosen.',
                ],
            ],
            [
                'judul' => 'Penggunaan yang wajar',
                'isi'   => [],
                'butir' => [
                    'Kode yang Anda kirim dijalankan otomatis di lingkungan terisolasi dengan batas waktu dan memori.',
                    'Dilarang mencoba menembus batas itu, mengganggu layanan, mengakses data orang lain, atau mencari celah keamanan tanpa izin pengelola.',
                ],
            ],
            [
                'judul' => 'Nilai',
                'isi'   => [
                    'Nilai di LokaKode adalah nilai latihan per pertemuan. Nilai akhir mata kuliah ditetapkan dosen di luar sistem ini.',
                ],
            ],
            [
                'judul' => 'Ketersediaan',
                'isi'   => [
                    'Kami berusaha menjaga layanan tetap berjalan, tetapi layanan dapat terhenti untuk pemeliharaan atau karena gangguan. Bila gangguan terjadi saat latihan berlangsung, laporkan kepada dosen Anda.',
                ],
            ],
            [
                'judul' => 'Pelanggaran',
                'isi'   => [
                    'Akun yang melanggar ketentuan ini dapat ditangguhkan, dan pelanggarannya diteruskan kepada dosen yang bersangkutan.',
                ],
            ],
            [
                'judul' => 'Kontak',
                'isi'   => [
                    'Pertanyaan tentang ketentuan ini dapat dikirim ke {email}.',
                ],
            ],
        ],
    ],
];
