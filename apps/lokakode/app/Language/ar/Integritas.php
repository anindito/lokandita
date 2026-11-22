<?php

// Peristiwa integritas untuk dosen (SPEC.md §13.2, KONTRAK.md §17).
return [
    'judul'        => 'النزاهة',
    'penjelasan'   => 'الأحداث التي سجّلها النظام لطلاب هذا الصف. هذه السجلات مادة للنظر فقط، والقرار لك.',
    'catatan'      => 'تُقاس الكتابة غير المعتادة في متصفح الطالب: 300 حرف أو أكثر تُدخل بسرعة 150 حرفًا في الثانية أو أكثر. التراجع عن حذف قد يسبّبها أيضًا ويُميَّز على حدة.',
    'waktu'        => 'الوقت',
    'jenisJudul'   => 'النوع',
    'mahasiswa'    => 'الطالب',
    'pertemuan'    => 'اللقاء',
    'soal'         => 'المسألة',
    'rincian'      => 'التفاصيل',
    'ip'           => 'عنوان IP',
    'peramban'     => 'المتصفح',
    'semua'        => 'الكل',
    'kosong'       => 'لا توجد أحداث نزاهة لطلاب هذا الصف بعد.',
    'kosongSaring' => 'لا يوجد حدث يطابق هذه التصفية.',
    'unduhCsv'     => 'تنزيل CSV',
    'unduhXlsx'    => 'تنزيل XLSX',
    'halamanKe'    => 'الصفحة {0}',
    'jenis'        => [
        'paste_blocked'  => 'لصق محظور',
        'typing_anomaly' => 'كتابة غير معتادة',
        'seb_rejected'   => 'رُفض بدون SEB',
        'seb_terminated' => 'انقطع SEB',
    ],
    'jalur' => [
        'paste'        => 'أمر اللصق',
        'keyboard'     => 'Ctrl+V',
        'shift_insert' => 'Shift+Insert',
        'middle_click' => 'نقرة الفأرة الوسطى',
        'drop'         => 'سحب وإفلات',
    ],
    'ketik' => [
        'ketik' => '{0} حرفًا في {1} ثانية ({2} في الثانية)',
        'undo'  => '{0} حرفًا في {1} ثانية ({2} في الثانية)، من تراجع أو إعادة',
    ],
    'seb' => [
        'tanpa_header'      => 'بدون تعريف SEB.',
        'tanpa_konfigurasi' => 'المقرر ليس له إعداد SEB.',
        'kunci_konfigurasi' => 'ملف الإعداد غير مطابق.',
        'kunci_peramban'    => 'إصدار SEB غير مسجّل.',
    ],
];
