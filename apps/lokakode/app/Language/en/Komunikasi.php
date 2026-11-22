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
];
