<?php

// Portal texts (SPEC.md §15). {email} is replaced by the contact address from CONTACT_EMAIL
// in the view publik/teks; the address is never written here.
return [
    // Tanggal halaman privasi dan ketentuan sesudah disunting lewat /admin/teks (KONTRAK.md §15).
    'diperbaruiPada' => 'Last updated: {0}',
    // Tautan pada kartu aplikasi, dan teks alternatif foto pembuka bawaan (docs/branding/LISENSI.md).
    'buka' => 'Open',
    'foto' => [
        'altBawaan' => 'A row of computers and keyboards on a long desk in a computer lab.',
    ],
    'nav' => [
        'tentang'   => 'About',
        'privasi'   => 'Privacy',
        'ketentuan' => 'Terms',
    ],
    'aplikasi' => [
        'lokakode'  => 'Programming practice in C, C++ and Python with automatic judging.',
        'lokaujian' => 'In preparation.',
        'lokasiber' => 'In preparation.',
    ],
    'depan' => [
        'judul'       => 'Lokandita — learn programming with one account',
        'deskripsi'   => 'Lokandita is a set of learning applications for programming courses: one account for all of them, starting with LokaKode for automatically judged practice.',
        'tajuk'       => 'One account for all your learning applications',
        'pengantar'   => 'Lokandita brings together learning applications for programming courses. Sign in once, then open the application you need without signing in again.',
        'tanpaDaftar' => 'There is no self-registration. Accounts are created by the administrator for registered students and lecturers.',
        'aplikasi'    => 'Applications',
        'caraJudul'   => 'Getting started',
        'cara'        => [
            'Press Sign in, then enter your ID number (NIM or NIP) and the password you were given.',
            'The first time, you are asked to replace the initial password with one of your own.',
            'Choose an application available to your account. You do not have to sign in again there.',
            'On a shared computer, always press Sign out in the account menu before you leave.',
        ],
        'bantuan'     => 'No account yet, forgot your password, or something is not working? See the page',
    ],
    'beranda' => [
        'aplikasi' => 'Your applications',
        'kosong'   => 'Your account has not been given access to any application yet. Contact your lecturer or the administrator:',
        'admin'    => 'User management and audit log.',
    ],
    'data' => [
        'judul'      => 'My data',
        'penjelasan' => 'You can download a copy of the data about you that Lokandita stores. Each application keeps its own data, so there is one file per application.',
        'unduhDari'  => 'My data in {0}',
        'catatan'    => 'The file is JSON and holds only your own data. Every download is recorded.',
    ],
    'tentang' => [
        'judul'     => 'About Lokandita',
        'deskripsi' => 'What Lokandita is, which applications it contains, and who it is for.',
        'pembuka'   => [
            'Lokandita is a set of learning applications for programming courses. All of them are used with one account, so students and lecturers sign in only once.',
        ],
        'bagian'    => [
            [
                'judul' => 'Applications',
                'isi'   => [],
                'butir' => [
                    'LokaAuth — sign-in and account management for every Lokandita application.',
                    'LokaKode — programming practice in C, C++ and Python. Every submission is run and judged automatically, and every student gets different numbers in a problem.',
                    'LokaUjian and LokaSiber — in preparation and not yet available.',
                ],
            ],
            [
                'judul' => 'Who it is for',
                'isi'   => [
                    'Lokandita is used by students and lecturers whose accounts are created by the administrator. There is no self-registration and no guest account.',
                ],
            ],
            [
                'judul' => 'How we treat data',
                'isi'   => [
                    'Lokandita stores only the data needed for learning, shows no advertisements, uses no trackers, and loads no files from other parties. The details are on the Privacy page.',
                ],
            ],
            [
                'judul' => 'Administrator',
                'isi'   => [
                    'Lokandita is run by the Lokandita administrator. Questions and reports can be sent to {email}.',
                ],
            ],
            // Bagian-bagian berikut dahulu halaman Kontak; sejak 2026-11-21 menjadi bagian halaman Tentang.
            [
                'judul' => 'Contact',
                'isi'   => [
                    'The Lokandita administrator can be reached by e-mail: {email}.',
                ],
            ],
            [
                'judul' => 'Forgotten password',
                'isi'   => [
                    'Students: ask the lecturer of your class to reset your password. You will receive a temporary password that must be changed at once.',
                    'Lecturers: contact the administrator at the address above.',
                ],
            ],
            [
                'judul' => 'No account yet',
                'isi'   => [
                    'Accounts are created by the administrator from the lists of students and lecturers. If you should be on the list but have not received an account, contact your lecturer.',
                ],
            ],
            [
                'judul' => 'Reporting a problem',
                'isi'   => [
                    'State your ID number, the application and page, the time, and what you saw. Never send your password — the administrator will not ask for it.',
                ],
            ],
        ],
    ],
    'privasi' => [
        'judul'      => 'Privacy policy',
        'deskripsi'  => 'Which personal data Lokandita stores, what for, who can see it, and what your rights are.',
        'diperbarui' => 'Last updated: 22 November 2026',
        'pembuka'    => [
            'This page describes the personal data processed by Lokandita (LokaAuth, LokaKode and this Portal), with reference to Indonesian Law Number 27 of 2022 on Personal Data Protection.',
            'The data is managed by the Lokandita administrator, who can be reached at {email}.',
        ],
        'bagian'     => [
            [
                'judul' => 'Data we store',
                'isi'   => [],
                'butir' => [
                    'Account data: ID number (NIM or NIP), name, role (student, lecturer or superadmin), and e-mail address if one was entered.',
                    'Your password is not stored; only its hash (Argon2id) is, which cannot be read back.',
                    'Sign-in and session records: time, IP address and browser type each time you sign in or fail to.',
                    'Learning data in LokaKode: the classes you are in, the program code you submit, automatically saved drafts, judging results and scores.',
                    'Integrity records in LokaKode, for example attempts to paste text into the editor.',
                    'Unusual typing in LokaKode: when 300 characters or more enter the editor at 150 characters per second or faster, what is recorded is the time, the problem, the number of characters, the duration and your IP address — not what you typed.',
                    'Audit log: important actions on your account, such as its creation and password resets.',
                ],
            ],
            [
                'judul' => 'What the data is used for',
                'isi'   => [],
                'butir' => [
                    'Recognising you at sign-in and keeping your account secure.',
                    'Running and judging practice work, and showing your progress.',
                    'Helping lecturers follow their classes and uphold academic honesty.',
                    'Investigating faults and misuse of the system.',
                ],
            ],
            [
                'judul' => 'Who can see your data',
                'isi'   => [],
                'butir' => [
                    'You.',
                    'The lecturer in charge of your course and the lecturers of your class: your name, ID number, submissions, scores and integrity records in that class.',
                    'Your classmates: your name and score on the class leaderboard, unless the lecturer has anonymised it.',
                    'Your classmates also see your name next to a clarification question you asked about a problem, when a lecturer publishes the answer to the whole class. A question answered privately is seen only by you and the lecturers.',
                    'Superadmin: all of the above, in order to run the system.',
                ],
            ],
            [
                'judul' => 'What we do not do',
                'isi'   => [
                    'We do not sell or hand over your data to other parties, show no advertisements, and use no third-party trackers or analytics. Every file of a page is loaded from Lokandita\'s own server.',
                ],
            ],
            [
                'judul' => 'Cookies',
                'isi'   => [
                    'Lokandita uses only the cookies needed for the system to work: a session cookie after you sign in, a form-protection cookie, and a language cookie. There are no advertising or tracking cookies.',
                ],
            ],
            [
                'judul' => 'Storage and security',
                'isi'   => [
                    'Data is stored on a server run by the Lokandita administrator. Every connection uses HTTPS. Backups are made daily and kept for 14 days.',
                    'Accounts are not permanently deleted, because scores and submissions refer to the account that owns them. An account that is no longer used is disabled so that nobody can sign in with it.',
                ],
            ],
            [
                'judul' => 'Your rights',
                'isi'   => [],
                'butir' => [
                    'Download a copy of your data: after signing in, open "My data" in the Portal.',
                    'Change your password at any time from the account menu.',
                    'Ask for wrong data (for example your name) to be corrected, or raise questions and objections about the processing of your data, at {email}.',
                ],
            ],
            [
                'judul' => 'Changes to this policy',
                'isi'   => [
                    'When this policy changes, the date at the top of the page is updated.',
                ],
            ],
        ],
    ],
    'ketentuan' => [
        'judul'      => 'Terms of use',
        'deskripsi'  => 'The rules for using a Lokandita account and its applications.',
        'diperbarui' => 'Last updated: 22 November 2026',
        'pembuka'    => [
            'By using a Lokandita account you agree to the following terms.',
        ],
        'bagian'     => [
            [
                'judul' => 'Account',
                'isi'   => [],
                'butir' => [
                    'The account is created by the administrator and is for you alone. Do not lend it and do not use anybody else\'s.',
                    'Replace the initial password the first time you sign in, and keep your password secret. The administrator and lecturers will never ask for it.',
                    'On a shared computer, sign out before you leave.',
                ],
            ],
            [
                'judul' => 'Academic honesty',
                'isi'   => [],
                'butir' => [
                    'Your submissions must be your own work, unless your lecturer says otherwise.',
                    'The system records certain events, for example attempts to paste text into the editor. These records are material for the lecturer; the decision remains the lecturer\'s.',
                    'Text that enters the editor far faster than a person can type — 300 characters or more almost at once — is also recorded as an integrity event for your lecturer.',
                ],
            ],
            [
                'judul' => 'Fair use',
                'isi'   => [],
                'butir' => [
                    'The code you submit is run automatically in an isolated environment with time and memory limits.',
                    'You may not try to break those limits, disturb the service, access other people\'s data, or look for security holes without the administrator\'s permission.',
                ],
            ],
            [
                'judul' => 'Scores',
                'isi'   => [
                    'Scores in LokaKode are practice scores per meeting. The final grade of a course is decided by the lecturer outside this system.',
                ],
            ],
            [
                'judul' => 'Availability',
                'isi'   => [
                    'We try to keep the service running, but it may stop for maintenance or because of a fault. If a fault happens during practice, report it to your lecturer.',
                ],
            ],
            [
                'judul' => 'Violations',
                'isi'   => [
                    'An account that violates these terms may be suspended, and the violation is passed on to the lecturer concerned.',
                ],
            ],
            [
                'judul' => 'Contact',
                'isi'   => [
                    'Questions about these terms can be sent to {email}.',
                ],
            ],
        ],
    ],
];
