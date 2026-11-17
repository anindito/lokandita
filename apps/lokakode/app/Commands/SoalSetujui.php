<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Soal\Tinjau;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Lokandita\Auth\UserDirectory;

/**
 * Menyetujui soal sebuah pertemuan yang masih menunggu tinjauan (SPEC.md §8.1).
 *
 *   docker exec lokakode php spark soal:setujui DDP101 2 --oleh <nomor induk>
 *   docker exec lokakode php spark soal:setujui DDP101 2 --oleh <nomor induk> --slug jam-mundur,nilai-tengah
 *
 * Sama dengan menekan "Setujui soal ini" pada versi TERAKHIR tiap soal, satu per
 * satu, atas nama --oleh: superadmin yang aktif, atau pengampu mata kuliah itu.
 * Setiap persetujuan tercatat di log audit dengan `lewat: cli`.
 *
 * Keluar 0 bila semua yang diminta sudah disetujui (termasuk bila tidak ada yang menunggu).
 */
final class SoalSetujui extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'soal:setujui';

    protected $description = 'Menyetujui soal sebuah pertemuan yang menunggu tinjauan, atas nama superadmin atau pengampu.';

    protected $usage = 'soal:setujui <kode mata kuliah> <nomor pertemuan> --oleh <nomor induk> [--slug a,b,…]';

    protected $arguments = ['kode' => 'Kode mata kuliah, mis. DDP101.', 'pertemuan' => 'Nomor pertemuan, 1–16.'];

    protected $options = [
        '--oleh' => 'Nomor induk superadmin atau pengampu yang menyetujui (wajib).',
        '--slug' => 'Hanya soal dengan slug ini (dipisah koma). Tanpa ini: semua soal pertemuan yang menunggu tinjauan.',
    ];

    public function run(array $params)
    {
        $kode  = strtoupper((string) ($params[0] ?? ''));
        $nomor = (string) ($params[1] ?? '');
        $oleh  = CLI::getOption('oleh');
        $oleh  = is_string($oleh) ? $oleh : '';
        if ($kode === '' || preg_match('/^\d{1,2}$/', $nomor) !== 1 || $oleh === '') {
            CLI::write('soal:setujui GAGAL: pemakaian: ' . $this->usage);

            return EXIT_ERROR;
        }
        $db        = db_connect();
        $pertemuan = $db->query('SELECT m.id, m.course_id, c.owner_id FROM meetings m JOIN courses c ON c.id = m.course_id WHERE c.code = ? AND m.number = ?',
            [$kode, (int) $nomor])->getRowArray();
        if ($pertemuan === null) {
            CLI::write("soal:setujui GAGAL: {$kode} pertemuan {$nomor} tidak ada");

            return EXIT_ERROR;
        }
        // Hak yang sama dengan halaman web: superadmin, atau pengampu mata kuliah ini.
        $pelaku = UserDirectory::byNomorInduk([$oleh])[$oleh] ?? null;
        $berhak = $pelaku !== null && $pelaku['status'] === 'active'
            && ($pelaku['global_role'] === 'superadmin' || ($pelaku['global_role'] === 'dosen' && $pelaku['id'] === $pertemuan['owner_id']));
        if (! $berhak) {
            CLI::write("soal:setujui GAGAL: --oleh {$oleh} bukan superadmin yang aktif dan bukan pengampu {$kode}");

            return EXIT_ERROR;
        }

        // Versi terakhir tiap soal pertemuan ini.
        $soal = $db->query('
            SELECT p.id, p.slug, p.meeting_id, ? AS course_id, v.id AS versi_id, v.version, v.status
            FROM problems p
            JOIN problem_versions v ON v.problem_id = p.id
                AND v.version = (SELECT MAX(v2.version) FROM problem_versions v2 WHERE v2.problem_id = p.id)
            WHERE p.meeting_id = ? ORDER BY p.id', [(int) $pertemuan['course_id'], (int) $pertemuan['id']])->getResultArray();

        $pilih = CLI::getOption('slug');
        if (is_string($pilih) && $pilih !== '') {
            $slug   = array_values(array_filter(array_map('trim', explode(',', $pilih))));
            $tiada  = array_diff($slug, array_column($soal, 'slug'));
            if ($tiada !== []) {
                CLI::write('soal:setujui GAGAL: slug tidak ada di pertemuan ini: ' . implode(', ', $tiada) . '. Tidak ada yang disetujui.');

                return EXIT_ERROR;
            }
            $soal = array_values(array_filter($soal, static fn (array $s): bool => in_array($s['slug'], $slug, true)));
        }

        $disetujui = 0;
        foreach ($soal as $s) {
            if ($s['status'] !== 'PENDING_REVIEW') {
                CLI::write(sprintf('DILEWATI   %s (versi %d: %s)', $s['slug'], $s['version'], $s['status']));

                continue;
            }
            $ya = Tinjau::setujui($s, ['id' => $s['versi_id'], 'version' => $s['version']], (string) $pelaku['id'], 'cli');
            $disetujui += $ya ? 1 : 0;
            CLI::write(sprintf('%s  %s (versi %d)', $ya ? 'DISETUJUI' : 'DILEWATI ', $s['slug'], $s['version']));
        }
        CLI::write(sprintf('ringkasan: %d disetujui, %d dilewati, dari %d soal', $disetujui, count($soal) - $disetujui, count($soal)));

        return EXIT_SUCCESS;
    }
}
