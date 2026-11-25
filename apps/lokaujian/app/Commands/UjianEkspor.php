<?php

declare(strict_types=1);

namespace App\Commands;

use App\Libraries\Audit;
use App\Libraries\Berkas\Ekspor;
use App\Libraries\Ujian;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Lokandita\Auth\RosterDb;

/**
 * Ekspor satu ujian ke bentuk KONTRAK.md §20 (SPEC-LOKAUJIAN.md §6.3).
 *
 *   docker exec lokaujian php spark ujian:ekspor 12 --oleh <nomor induk> > ujian-12.json
 *
 * Berkasnya memuat SEMUA kunci jawaban. --oleh wajib: superadmin yang aktif atau pengampu mata
 * kuliah ujian itu; ekspornya tercatat di log audit atas namanya. JSON ditulis ke keluaran
 * standar, pesan lain ke stderr.
 */
final class UjianEkspor extends BaseCommand
{
    protected $group = 'Lokandita';

    protected $name = 'ujian:ekspor';

    protected $description = 'Mengekspor satu ujian sebagai JSON berbentuk KONTRAK.md §20 (memuat kunci jawaban).';

    protected $usage = 'ujian:ekspor <id ujian> --oleh <nomor induk>';

    protected $arguments = ['id' => 'Nomor ujian, seperti di alamat halamannya.'];

    protected $options = ['--oleh' => 'Nomor induk superadmin atau pengampu yang bertanggung jawab atas ekspor ini (wajib).'];

    public function run(array $params)
    {
        $id    = (int) ($params[0] ?? 0);
        $oleh  = CLI::getOption('oleh');
        $akses = Pelaku::pasang(is_string($oleh) ? $oleh : '');
        if (is_string($akses)) {
            fwrite(STDERR, "ujian:ekspor GAGAL: {$akses}\n");

            return EXIT_ERROR;
        }
        // Ujian dicari di antara mata kuliah yang dikelola pelaku; ujian mata kuliah lain dan ujian
        // yang tidak ada dijawab sama.
        $ujian  = null;
        $matkul = null;
        $pustaka = new Ujian($akses);
        foreach ($akses->idMatkulDiampu() as $matkulId) {
            $ujian = $pustaka->lihat($matkulId, $id);
            if ($ujian !== null) {
                $matkul = RosterDb::conn()->execute_query('SELECT id, code FROM roster_courses WHERE id = ?', [$matkulId])->fetch_assoc();
                break;
            }
        }
        if ($ujian === null || $matkul === null) {
            fwrite(STDERR, "ujian:ekspor GAGAL: ujian {$id} tidak ada, atau --oleh tidak mengelola mata kuliahnya\n");

            return EXIT_ERROR;
        }
        ini_set('memory_limit', '512M');
        $isi = Ekspor::berkas($ujian, (string) $matkul['code']);
        Audit::catat('ujian.diekspor', 'ujian', $id, ['berkas' => 'stdout', 'soal' => $ujian['ringkasan']['soal'], 'byte' => strlen($isi)], (int) $matkul['id']);
        fwrite(STDOUT, $isi);
        fwrite(STDERR, "ujian:ekspor: {$ujian['ringkasan']['soal']} soal, " . strlen($isi) . " byte\n");

        return EXIT_SUCCESS;
    }
}
