<?php

declare(strict_types=1);

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Membaca dan menulis tabel sederhana sebagai CSV atau XLSX.
 */
final class Tabular
{
    /**
     * Membaca berkas menjadi baris-baris berkunci nama kolom (huruf kecil).
     * Kunci '_baris' berisi nomor baris di berkas (baris judul = 1).
     *
     * @return list<array<string, string|int>>
     */
    public static function read(string $path, string $ext): array
    {
        $tabel = strtolower($ext) === 'xlsx' ? self::readXlsx($path) : self::readCsv($path);
        if ($tabel === []) {
            return [];
        }
        $judul = array_map(static fn ($j) => strtolower(trim((string) $j)), array_shift($tabel));
        $baris = [];
        foreach ($tabel as $i => $sel) {
            if (trim(implode('', array_map('strval', $sel))) === '') {
                continue;
            }
            $b = ['_baris' => $i + 2];
            foreach ($judul as $k => $nama) {
                if ($nama !== '') {
                    $b[$nama] = trim((string) ($sel[$k] ?? ''));
                }
            }
            $baris[] = $b;
        }

        return $baris;
    }

    /**
     * @return list<list<string>>
     */
    private static function readCsv(string $path): array
    {
        $isi = (string) file_get_contents($path);
        $isi = preg_replace('/^\xEF\xBB\xBF/', '', $isi) ?? $isi;
        if (! mb_check_encoding($isi, 'UTF-8')) {
            $isi = mb_convert_encoding($isi, 'UTF-8', 'Windows-1252');
        }
        $pertama = strtok($isi, "\r\n") ?: '';
        $pemisah = ',';
        foreach ([';', "\t"] as $calon) {
            if (substr_count($pertama, $calon) > substr_count($pertama, $pemisah)) {
                $pemisah = $calon;
            }
        }
        $h = fopen('php://temp', 'r+');
        fwrite($h, $isi);
        rewind($h);
        $tabel = [];
        while (($sel = fgetcsv($h, null, $pemisah, '"', '')) !== false) {
            $tabel[] = array_map(static fn ($v) => (string) $v, $sel);
        }
        fclose($h);

        return $tabel;
    }

    /**
     * @return list<list<string>>
     */
    private static function readXlsx(string $path): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $lembar = $reader->load($path)->getActiveSheet();
        $tabel  = [];
        foreach ($lembar->toArray(null, false, false, false) as $baris) {
            $tabel[] = array_map(static fn ($v) => $v === null ? '' : (string) $v, $baris);
        }

        return $tabel;
    }

    /**
     * @param list<string>       $judul
     * @param iterable<list<mixed>> $baris
     */
    public static function csv(array $judul, iterable $baris): string
    {
        $h = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF"); // supaya Excel membaca UTF-8
        fputcsv($h, $judul, ',', '"', '');
        foreach ($baris as $b) {
            fputcsv($h, array_map([self::class, 'aman'], $b), ',', '"', '');
        }
        rewind($h);
        $isi = (string) stream_get_contents($h);
        fclose($h);

        return $isi;
    }

    /**
     * Nilai yang diawali = + - @ bisa dijalankan Excel sebagai rumus; diberi tanda petik di depan.
     */
    private static function aman(mixed $nilai): string
    {
        $s = (string) $nilai;

        return $s !== '' && strpbrk($s[0], "=+-@\t\r") !== false ? "'" . $s : $s;
    }

    /**
     * @param list<string>       $judul
     * @param iterable<list<mixed>> $baris
     */
    public static function xlsx(array $judul, iterable $baris): string
    {
        $buku   = new Spreadsheet();
        $lembar = $buku->getActiveSheet();
        $r      = 1;
        foreach ([$judul, ...$baris] as $b) {
            foreach (array_values($b) as $k => $nilai) {
                // Selalu teks: nomor induk tidak kehilangan nol di depan dan tidak ada rumus.
                $lembar->setCellValueExplicit([$k + 1, $r], (string) $nilai, DataType::TYPE_STRING);
            }
            $r++;
        }
        $lembar->getStyle('1:1')->getFont()->setBold(true);
        foreach (range(1, count($judul)) as $k) {
            $lembar->getColumnDimensionByColumn($k)->setAutoSize(true);
        }
        $berkas = tempnam(sys_get_temp_dir(), 'xlsx');
        (new Xlsx($buku))->save($berkas);
        $isi = (string) file_get_contents($berkas);
        unlink($berkas);

        return $isi;
    }

    /**
     * @param list<string>          $judul
     * @param iterable<list<mixed>> $baris
     */
    public static function download(\CodeIgniter\HTTP\ResponseInterface $response, string $format, string $nama, array $judul, iterable $baris): \CodeIgniter\HTTP\ResponseInterface
    {
        $xlsx = $format === 'xlsx';
        $isi  = $xlsx ? self::xlsx($judul, $baris) : self::csv($judul, $baris);

        return $response
            ->setHeader('Content-Type', $xlsx ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nama . '.' . ($xlsx ? 'xlsx' : 'csv') . '"')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody($isi);
    }
}
