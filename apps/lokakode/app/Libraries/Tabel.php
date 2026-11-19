<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Menulis tabel sebagai CSV atau XLSX untuk diunduh (SPEC.md §12: ekspor nilai).
 *
 * Angka (int, float) ditulis sebagai angka, supaya bisa langsung dijumlah dan
 * dirata-rata di lembar kerja. Semua yang lain ditulis sebagai TEKS: nomor induk
 * tidak kehilangan nol di depan, dan nama yang diawali = + - @ tidak menjadi rumus
 * di komputer dosen (aturan yang sama dengan App\Libraries\Tabular di LokaAuth).
 * Sel null dibiarkan kosong.
 */
final class Tabel
{
    /**
     * @param list<string>          $judul
     * @param iterable<list<mixed>> $baris
     */
    public static function csv(array $judul, iterable $baris): string
    {
        $h = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF"); // supaya Excel membaca UTF-8
        fputcsv($h, $judul, ',', '"', '');
        foreach ($baris as $b) {
            fputcsv($h, array_map([self::class, 'selCsv'], $b), ',', '"', '');
        }
        rewind($h);
        $isi = (string) stream_get_contents($h);
        fclose($h);

        return $isi;
    }

    private static function selCsv(mixed $nilai): string
    {
        if ($nilai === null) {
            return '';
        }
        if (is_int($nilai) || is_float($nilai)) {
            return angka($nilai);
        }
        $s = (string) $nilai;

        // CSV tidak punya jenis sel: teks yang bisa dibaca sebagai rumus diberi tanda petik di depan.
        return $s !== '' && strpbrk($s[0], "=+-@\t\r") !== false ? "'" . $s : $s;
    }

    /**
     * @param array<string, array{0: list<string>, 1: list<list<mixed>>}> $lembar nama lembar => [judul, baris]
     */
    public static function xlsx(array $lembar): string
    {
        $buku = new Spreadsheet();
        $buku->removeSheetByIndex(0);
        foreach ($lembar as $nama => [$judul, $baris]) {
            $l = $buku->createSheet();
            $l->setTitle(mb_substr($nama, 0, 31));
            $r = 1;
            foreach ([$judul, ...$baris] as $b) {
                foreach (array_values($b) as $k => $nilai) {
                    if ($nilai === null) {
                        continue;
                    }
                    $angka = $r > 1 && (is_int($nilai) || is_float($nilai));
                    $l->setCellValueExplicit([$k + 1, $r], $angka ? $nilai : (string) $nilai, $angka ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING);
                }
                $r++;
            }
            $l->getStyle('1:1')->getFont()->setBold(true);
            $l->freezePane('A2');
            foreach (range(1, max(1, count($judul))) as $k) {
                $l->getColumnDimensionByColumn($k)->setAutoSize(true);
            }
        }
        $buku->setActiveSheetIndex(0);
        $berkas = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($buku))->save($berkas);
        $isi = (string) file_get_contents($berkas);
        unlink($berkas);

        return $isi;
    }

    public static function unduh(ResponseInterface $response, string $nama, string $format, string $isi): ResponseInterface
    {
        $xlsx = $format === 'xlsx';

        return $response
            ->setHeader('Content-Type', $xlsx ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]+/', '-', $nama) . '.' . ($xlsx ? 'xlsx' : 'csv') . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($isi);
    }
}
