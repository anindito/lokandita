<?php

declare(strict_types=1);

// Dipanggil oleh entrypoint kontainer sebelum web server dijalankan.
// Pemakaian: php cek-env.php /srv/app/.env.example
// Keluar dengan kode 1 dan menyebut nama variabel yang belum diisi.

require __DIR__ . '/../src/EnvCheck.php';

use Lokandita\Auth\EnvCheck;

$example = $argv[1] ?? '';
$app     = getenv('LOKANDITA_APP') ?: '?';

try {
    $keys = EnvCheck::keysFromExample($example);
} catch (RuntimeException $e) {
    fwrite(STDERR, "[{$app}] GAGAL MULAI: {$e->getMessage()}\n");
    exit(1);
}

if ($keys === []) {
    fwrite(STDERR, "[{$app}] GAGAL MULAI: {$example} tidak memuat satu kunci pun\n");
    exit(1);
}

$missing = EnvCheck::missing($keys);
if ($missing !== []) {
    fwrite(STDERR, "[{$app}] GAGAL MULAI: variabel lingkungan wajib belum diisi: " . implode(', ', $missing) . "\n");
    exit(1);
}

// SEB (KONTRAK.md §8, §10): hanya dua nilai yang sah; keadaan "dinonaktifkan" dicatat saat mulai.
$seb = getenv('SEB_ENFORCEMENT');
if (in_array('SEB_ENFORCEMENT', $keys, true)) {
    if (! in_array($seb, ['enabled', 'disabled'], true)) {
        fwrite(STDERR, "[{$app}] GAGAL MULAI: SEB_ENFORCEMENT harus 'enabled' atau 'disabled'\n");
        exit(1);
    }
    fwrite(STDERR, $seb === 'disabled'
        ? "[{$app}] PERINGATAN: MODE UJI — penegakan SEB dinonaktifkan (SEB_ENFORCEMENT=disabled)\n"
        : "[{$app}] penegakan SEB aktif (SEB_ENFORCEMENT=enabled)\n");
}

fwrite(STDERR, "[{$app}] env lengkap (" . count($keys) . " variabel)\n");
