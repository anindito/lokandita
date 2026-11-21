<?php

// Public landing page of LokaKode (SPEC.md §15). No real data here.
return [
    'judul'       => 'LokaKode — programming practice with automatic judging',
    'deskripsi'   => 'LokaKode is where students practise programming in C, C++ and Python: write code in the browser, run it, submit it, and see the result at once.',
    'tajuk'       => 'Programming practice that is judged at once',
    'pengantar'   => 'Write code in the browser, run it on the samples, then submit. LokaKode tests your program and shows the result within seconds.',
    'tanpaDaftar' => 'Sign in with your Lokandita account. There is no self-registration.',
    'fiturJudul'  => 'What you get',
    'fitur'       => [
        ['judul' => 'Automatic judging', 'isi' => 'Every submission is run against a set of hidden tests. You see how many passed and your score, without waiting for a lecturer.'],
        ['judul' => 'Problems with your own numbers', 'isi' => 'All students read the same problem, but the numbers and tests are different for each person.'],
        ['judul' => 'Try as often as you like', 'isi' => 'There is no limit on attempts and no penalty. Your best result is what counts.'],
        ['judul' => 'A target per meeting', 'isi' => 'Each meeting has a points target. You choose which problems to solve, from very easy to hard.'],
        ['judul' => 'Editor in the browser', 'isi' => 'A code editor with syntax highlighting, automatic saving, and a Run button to try your own input.'],
        ['judul' => 'Class leaderboard', 'isi' => 'See where you stand in your class for each meeting.'],
    ],
    'caraJudul'   => 'How it works',
    'cara'        => [
        'Sign in with your ID number and password.',
        'Open a meeting that is open for your class, then choose a problem.',
        'Write your program. Press Run to try it on the samples or on your own input.',
        'Press Submit. The program is run against hidden tests and the score appears at once.',
        'Improve it and submit again as often as you need until the meeting closes.',
    ],
    'bahasaJudul'   => 'Programming languages',
    'bahasaProgram' => [
        'C (GCC 13, C17 standard)',
        'C++ (GCC 13, C++17 standard)',
        'Python 3.12',
    ],
    'sebJudul'    => 'Safe Exam Browser',
    'seb'         => [
        'Students will later be required to open LokaKode through Safe Exam Browser (SEB) version 3.10.x for 64-bit Windows. This requirement is not in force yet, and LokaKode can still be opened with an ordinary browser.',
        'The configuration file and installation instructions are already available on the SEB instructions page. You may already install SEB and try it.',
    ],
    'sebBerlaku'  => [
        'Students must open LokaKode through Safe Exam Browser (SEB) version 3.10.x for 64-bit Windows, with the configuration file of their course. An ordinary browser is refused for student accounts.',
        'The configuration file and the installation steps are on the SEB instructions page.',
    ],
    'sebPetunjuk' => 'SEB instructions and configuration files',
    'sebTautan'   => 'Download Safe Exam Browser from its official site (safeexambrowser.org)',
    'tanyaJudul'  => 'Frequently asked questions',
    'tanya'       => [
        ['tanya' => 'How do I get an account?', 'jawab' => 'Accounts are created by the administrator for registered students and lecturers. If you have not received one, contact your lecturer.'],
        ['tanya' => 'I forgot my password.', 'jawab' => 'Ask the lecturer of your class to reset it. You will receive a temporary password that must be changed at once.'],
        ['tanya' => 'Do repeated attempts lower my score?', 'jawab' => 'No. The number of attempts is recorded, but your score is the best result of all your submissions.'],
        ['tanya' => 'Why can I not paste text into the editor?', 'jawab' => 'Pasting into the editor is switched off on purpose, so that code is written by hand. Students who need assistive tools can ask the administrator for an exemption.'],
        ['tanya' => 'Why is my answer wrong although the samples pass?', 'jawab' => 'The samples are only a small part of the tests. A submission is run against other, hidden tests that cover more cases; their content is not shown.'],
        ['tanya' => 'Is the score here my final course grade?', 'jawab' => 'No. LokaKode only gives a practice score per meeting. The final grade is decided by the lecturer.'],
    ],
    'kontak'      => 'Questions and problem reports:',
    'kePortal'    => 'About Lokandita, privacy and terms of use',
    'logoAlt'     => 'LokaKode logo',
];
