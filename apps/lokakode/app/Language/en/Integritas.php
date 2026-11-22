<?php

// Peristiwa integritas untuk dosen (SPEC.md §13.2, KONTRAK.md §17).
return [
    'judul'        => 'Integrity',
    'penjelasan'   => 'Events the system recorded for students of this class. They are material for your judgement only; the decision is yours.',
    'catatan'      => 'Unusual typing is measured by the student\'s browser: 300 characters or more entered at 150 characters per second or faster. Undoing a deletion can also trigger it and is marked separately.',
    'waktu'        => 'Time',
    'jenisJudul'   => 'Kind',
    'mahasiswa'    => 'Student',
    'pertemuan'    => 'Meeting',
    'soal'         => 'Problem',
    'rincian'      => 'Detail',
    'ip'           => 'IP address',
    'peramban'     => 'Browser',
    'semua'        => 'All',
    'kosong'       => 'No integrity events yet for students of this class.',
    'kosongSaring' => 'No event matches this filter.',
    'unduhCsv'     => 'Download CSV',
    'unduhXlsx'    => 'Download XLSX',
    'halamanKe'    => 'Page {0}',
    'jenis'        => [
        'paste_blocked'  => 'Paste blocked',
        'typing_anomaly' => 'Unusual typing',
        'seb_rejected'   => 'Refused without SEB',
        'seb_terminated' => 'SEB interrupted',
    ],
    'jalur' => [
        'paste'        => 'Paste command',
        'keyboard'     => 'Ctrl+V',
        'shift_insert' => 'Shift+Insert',
        'middle_click' => 'Middle mouse click',
        'drop'         => 'Drag and drop',
    ],
    'ketik' => [
        'ketik' => '{0} characters in {1} seconds ({2} per second)',
        'undo'  => '{0} characters in {1} seconds ({2} per second), from undo or redo',
    ],
    'seb' => [
        'tanpa_header'      => 'No SEB identification.',
        'tanpa_konfigurasi' => 'The course has no SEB configuration.',
        'kunci_konfigurasi' => 'Configuration file does not match.',
        'kunci_peramban'    => 'SEB version is not registered.',
    ],
];
