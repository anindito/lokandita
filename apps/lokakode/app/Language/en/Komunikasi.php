<?php

// Pemberitahuan, klarifikasi soal dan pengumuman kelas (SPEC.md §14, KONTRAK.md §17).
return [
    'notifikasi' => [
        'judul'            => 'Notifications',
        'penjelasan'       => 'Notifications appear when you open LokaKode: a meeting opened for your class, a meeting that closes within 24 hours, and a password reset by a lecturer.',
        'kosong'           => 'No notifications yet.',
        'baru'             => 'New',
        'belumDibaca'      => '{0} unread',
        'tandai'           => 'Mark all as read',
        'pertemuan_dibuka' => '{0} — Meeting {1}: {2} is now open for your class.',
        'tutup_24jam'      => '{0} — Meeting {1}: {2} closes in less than 24 hours.',
        'tutupPada'        => 'Closes at {0}.',
        'sandi_direset'    => 'Your password was reset by {0} (class {1}). If you did not ask for this, contact your lecturer at once.',
        'seorangDosen'     => 'a lecturer',
    ],
    'klarifikasi' => [
        'judul' => 'Problem clarifications',
        'penjelasanDosen' => 'Questions from students of this class about problems. A private answer is seen by the asker only; an answer published to the class is seen by every student of this class, with the question and the asker\'s name.',
        'catatanDosen' => 'The numbers in a problem differ for every student. Before publishing an answer to the class, make sure it does not depend on the asker\'s own numbers.',
        'kosongDosen' => 'No questions from students of this class yet.',
        'belum' => 'Unanswered',
        'belumJumlah' => '{0} unanswered',
        'pribadi' => 'Private answer',
        'keKelas' => 'Published to the class',
        'kirimanPenanya' => 'View the asker\'s submissions for this problem',
        'dijawabOleh' => 'Answered by {0}, {1}',
        'jawaban' => 'Answer',
        'ubahJawaban' => 'Answer (can be changed)',
        'siapaMelihat' => 'Who sees this answer',
        'pilihPribadi' => 'The asker only',
        'pilihKelas' => 'The whole class, with the question and the asker\'s name',
        'simpan' => 'Save answer',
        'tersimpan' => 'Answer saved.',
        'jawabanSalah' => 'The answer was not saved: write the answer (at most 2,000 characters) and choose who sees it.',
    ],
];
