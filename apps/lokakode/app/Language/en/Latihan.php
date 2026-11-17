<?php

// LokaKode texts: practice — publishing a meeting, class windows, student
// pages, scoring (SPEC.md §6, §7, §11).
return [
    'pesan' => [
        'terbit'          => 'Meeting published. Students see it once their class window opens.',
        'ditarik'         => 'Meeting withdrawn. Students no longer see it; existing submissions and scores are kept.',
        'jendelaDisimpan' => 'Schedule saved.',
        'statusDiubah'    => 'The state of this meeting for this class was changed.',
        'disiapkan'       => '{0} student problems are being prepared.',
    ],

    'terbit' => [
        'judul'        => 'Publishing',
        'sudah'        => 'Published',
        'belum'        => 'Not published',
        'sudahInfo'    => 'Students see this meeting while their class window is open.',
        'belumInfo'    => 'Until it is published, students do not see this meeting, even when their class window is open.',
        'komposisi'    => 'Approved problems compared with the minimum composition',
        'poin'         => 'Points',
        'disetujui'    => 'Approved',
        'minimum'      => 'Minimum',
        'keterangan'   => 'Note',
        'cukup'        => 'enough',
        'kurangSoal'   => '{0} more needed',
        'poinTersedia' => 'Points available: {0}. Target: {1}. Publishing needs at least {2} points (2 × target).',
        'kurangPoin'   => '{0} points short.',
        'peringatan'   => 'It can be published, but the choice of problems is narrow: {0} points (3 × target) is recommended.',
        'nonaktif'     => 'This meeting is not used for practice, so it cannot be published.',
        'terbitkan'    => 'Publish meeting',
        'tarik'        => 'Withdraw from students',
        'tarikInfo'    => 'Existing submissions, scores and student problems are not deleted.',
        'ditolak'      => 'The meeting cannot be published yet. What is missing is marked under Publishing.',
    ],

    'atur' => [
        'judul'         => 'Practice settings',
        'target'        => 'Target points',
        'targetInfo'    => 'A student who collects this many points scores 100. Default: 60.',
        'komposisi'     => 'Minimum composition',
        'komposisiInfo' => 'How many approved problems each level needs before the meeting can be published. It may be lowered as long as the points available stay at 2 × target.',
        'poinSatuan'    => '{0} points',
        'templat'       => 'Starter template',
        'templatInfo'   => 'The code already in the editor when a student opens a problem, and what Reset restores. Leave empty to use the default for the language (shown faintly in this box).',
        'bendera'       => 'Extra compiler flags',
        'benderaInfo'   => 'Separate with spaces. Allowed:',
        'lengkapi'      => 'Autocomplete in the editor',
        'lengkapiInfo'  => 'When off, the editor offers no suggestions while the student types.',
        'simpan'        => 'Save practice settings',
    ],

    'jendela' => [
        'judul'           => 'Meeting schedule',
        'info'            => 'Each class has its own opening and closing time for every meeting.',
        'kosong'          => 'This course has no meeting used for practice yet.',
        'keadaan'         => 'State',
        'buka'            => 'Opens',
        'tutup'           => 'Closes',
        'manual'          => '(manual)',
        'jumlahSoal'      => '{0} approved problems',
        'belumTerbit'     => 'This meeting is not published yet.',
        'belumTerbitInfo' => 'You can set the schedule now, but students only see the problems after the course owner publishes this meeting.',
        'manualJudul'     => 'Open or close now',
        'manualInfo'      => 'Opening or closing by hand overrides the schedule until you choose "Follow schedule" again.',
        'bukaSekarang'    => 'Open now',
        'tutupSekarang'   => 'Close now',
        'ikutJadwal'      => 'Follow schedule',
        'jadwal'          => 'Schedule',
        'jadwalInfo'      => 'Western Indonesia Time (WIB). Leave both empty if this meeting is only opened and closed by hand.',
        'sesudahTutup'    => 'After closing, students',
        'simpan'          => 'Save schedule',
    ],

    'keadaan' => [
        'belum'     => 'Not scheduled',
        'terjadwal' => 'Scheduled',
        'buka'      => 'Open',
        'tutup'     => 'Closed',
    ],

    'keadaanInfo' => [
        'belum'     => 'This class has no schedule for this meeting yet. Students cannot work on it.',
        'terjadwal' => 'The meeting opens by itself at the opening time below.',
        'buka'      => 'Students of this class can work and submit now.',
        'tutup'     => 'Students of this class can no longer submit.',
    ],

    'tampak' => [
        'hidden'      => 'see only their score',
        'review_only' => 'can still read the problems and their own submissions',
    ],

    'siap' => [
        'judul'      => 'Student problems',
        'hitung'     => '{0} of {1} student problems are ready.',
        'rincian'    => '({0} active students × {1} problems)',
        'antre'      => '{0} being made.',
        'perkiraan'  => 'Estimated to finish in about {0} minutes.',
        'workerMati' => 'The judge is not running, so problems cannot be made yet. Contact the admin.',
        'gagal'      => '{0} problems could not be made. Press "Prepare now" to try again; if it keeps failing, the course owner needs to check the problem.',
        'info'       => 'Every student gets different numbers and tests for the same problem. They are made when the schedule is saved or the meeting is opened; any that are not ready are made when the student opens the problem.',
        'tombol'     => 'Prepare now',
    ],

    'galat' => [
        'target'          => 'Target points must be a number from 1 to 1000.',
        'minimum'         => 'Enter a number from 0 to 50.',
        'templat'         => 'The template can be at most 20,000 characters.',
        'bendera'         => 'A flag is not allowed. Use only those in the list, at most 8.',
        'benderaPython'   => 'Python takes no compiler flags. Leave this empty.',
        'melanggarTerbit' => 'With these values the published meeting would no longer meet the publishing rules (minimum composition and 2 × target). Withdraw the meeting first, or choose other values.',
        'waktu'           => 'The time was not recognised. Choose a date and a time.',
        'berpasangan'     => 'Fill in both the opening and the closing time, or leave both empty.',
        'urutan'          => 'The closing time must be after the opening time.',
        'tampak'          => 'Choose one of the two options.',
        'override'        => 'Unknown choice.',
    ],
];
