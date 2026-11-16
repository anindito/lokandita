<?php

declare(strict_types=1);

namespace Tests\Soal;

use App\Libraries\Soal\PosisiJson;
use App\Libraries\Soal\Skema;
use PHPUnit\Framework\TestCase;

/**
 * Pemeriksaan bentuk berkas impor dan pencarian baris JSON — tanpa basis data.
 */
final class SkemaTest extends TestCase
{
    private const KONSEP = ['IO_DASAR', 'VARIABEL_TIPE', 'INHERITANCE'];

    /**
     * @param array<string, mixed> $ganti
     *
     * @return array<string, mixed>
     */
    private function soal(array $ganti = []): array
    {
        return $ganti + [
            'slug' => 'genap-ganjil', 'difficulty' => 'mudah', 'samples_count' => 2, 'hidden_count' => 12,
            'translations' => ['id' => ['title' => 'Genap', 'statement' => 'Teks', 'input_format' => '', 'output_format' => '', 'constraints' => '']],
            'generator' => 'print(1)', 'reference_solution' => 'int main(){}',
        ];
    }

    /**
     * @param list<array{field: string, kode: string, argumen: list<string>}> $galat
     *
     * @return list<string>
     */
    private function kode(array $galat): array
    {
        return array_map(static fn (array $g): string => $g['field'] . ':' . $g['kode'], $galat);
    }

    public function testSoalYangBenarDinormalkanDenganNilaiBawaan(): void
    {
        [$normal, $galat] = Skema::periksaSoal($this->soal(), 'cpp', self::KONSEP);
        $this->assertSame([], $galat);
        $this->assertSame(['MUDAH', 'trimmed', 2000, []], [$normal['difficulty'], $normal['comparison_mode'], $normal['time_limit_ms'], $normal['required_concepts_in_submission']]);
        $this->assertSame(5000, Skema::periksaSoal($this->soal(), 'python', self::KONSEP)[0]['time_limit_ms']);
    }

    public function testPesanMeetingMenyebutAngkaYangDimaksud(): void
    {
        $galat = Skema::periksaBerkas(['course' => 'DDP101', 'meeting' => '4 — Percabangan', 'language' => 'cpp', 'problems' => [[]]]);
        $this->assertSame([['field' => 'meeting', 'kode' => 'meetingBulat', 'argumen' => ['4', '"4 — Percabangan"']]], $galat);
        $this->assertSame(['meeting:meeting'], $this->kode(Skema::periksaBerkas(['course' => 'DDP101', 'meeting' => 17, 'language' => 'cpp', 'problems' => [[]]])));
        $this->assertSame(['meeting:meetingBulat'], $this->kode(Skema::periksaBerkas(['course' => 'DDP101', 'meeting' => '4', 'language' => 'cpp', 'problems' => [[]]])));
    }

    public function testBagianAtasBerkasDiperiksaKetat(): void
    {
        $this->assertSame([':bukanObyek'], $this->kode(Skema::periksaBerkas([1, 2])));
        $galat = Skema::periksaBerkas(['course' => 'Dasar-Dasar Pemrograman', 'meeting' => 4, 'language' => 'C++', 'problems' => [], 'languge' => 'cpp']);
        $this->assertSame(['languge:kunciMirip', 'course:course', 'language:language', 'problems:problems'], $this->kode($galat));
        $this->assertSame(['languge', 'language'], $galat[0]['argumen']);
    }

    public function testKodeDanTipeSoalDiperiksaKetatDenganSaran(): void
    {
        [, $galat] = Skema::periksaSoal($this->soal([
            'slug' => 'Genap Ganjil', 'difficulty' => 'Mudah Sekali', 'comparison_mode' => 'sama', 'time_limit_ms' => '2000',
            'samples_count' => 5, 'hidden_count' => 5, 'required_concepts_in_submission' => ['INHERITANCE', 'PEWARISAN'], 'dificulty' => 'x',
        ]), 'cpp', self::KONSEP);
        $this->assertSame([
            'dificulty:kunciMirip', 'slug:slug', 'difficulty:difficultyTulis', 'comparison_mode:pilihan', 'time_limit_ms:bulat',
            'samples_count:bulat', 'hidden_count:bulat', 'required_concepts_in_submission:konsepAsing',
        ], $this->kode($galat));
        $this->assertSame(['mudah_sekali', '"Mudah Sekali"'], $galat[2]['argumen']);
        $this->assertSame(['time_limit_ms', '100', '10000', '"2000"'], $galat[4]['argumen']);
    }

    public function testTerjemahanIndonesiaWajibDanYangLainBolehKosong(): void
    {
        $this->assertSame(['translations:terjemahanId'], $this->kode(Skema::periksaSoal($this->soal(['translations' => ['en' => []]]), 'cpp', self::KONSEP)[1]));

        $t = ['id' => ['title' => 'Genap', 'statement' => ''], 'en' => [], 'ar' => ['title' => 'زوجي', 'statement' => 'نص', 'machine' => true]];
        [$normal, $galat] = Skema::periksaSoal($this->soal(['translations' => $t]), 'cpp', self::KONSEP);
        $this->assertSame(['translations:teksWajib'], $this->kode($galat));
        $this->assertSame('translations.id.statement', $galat[0]['argumen'][0]);
        $this->assertArrayNotHasKey('en', $normal['translations']);
        $this->assertTrue($normal['translations']['ar']['machine']);
        // machine tidak berlaku untuk bahasa Indonesia.
        $t['id'] = ['title' => 'Genap', 'statement' => 'Teks', 'machine' => true];
        $this->assertSame(['translations.id.machine:kunciAsing'], $this->kode(Skema::periksaSoal($this->soal(['translations' => $t]), 'cpp', self::KONSEP)[1]));
    }

    public function testParameterDiPernyataanHarusAdaDiParams(): void
    {
        $t = ['id' => ['title' => 'Batas {{n}}', 'statement' => 'Nilai paling besar {{ batas }} dan {{n}}.']];
        [, $galat] = Skema::periksaSoal($this->soal(['translations' => $t, 'params' => ['n' => 10]]), 'cpp', self::KONSEP);
        $this->assertSame([['field' => 'translations', 'kode' => 'paramHilang', 'argumen' => ['translations.id.statement', 'batas']]], $galat);
        $this->assertSame([], Skema::periksaSoal($this->soal(['translations' => $t, 'params' => ['n' => 10, 'batas' => '1000']]), 'cpp', self::KONSEP)[1]);
        $this->assertSame(['params:paramBentuk'], $this->kode(Skema::periksaSoal($this->soal(['params' => ['N besar' => 1]]), 'cpp', self::KONSEP)[1]));
    }

    public function testSidikTidakBergantungPadaUrutanKunciDanBerubahBilaIsiBerubah(): void
    {
        $a = Skema::periksaSoal($this->soal(), 'cpp', self::KONSEP)[0];
        $b = Skema::periksaSoal(array_reverse($this->soal(), true) + ['comparison_mode' => 'trimmed', 'time_limit_ms' => 2000], 'cpp', self::KONSEP)[0];
        $this->assertSame(Skema::sidik($a, 'cpp'), Skema::sidik($b, 'cpp'));
        $c = Skema::periksaSoal($this->soal(['generator' => 'print(2)']), 'cpp', self::KONSEP)[0];
        $this->assertNotSame(Skema::sidik($a, 'cpp'), Skema::sidik($c, 'cpp'));
        $this->assertNotSame(Skema::sidik($a, 'cpp'), Skema::sidik($a, 'c'));
    }

    public function testPosisiJsonMenemukanBarisTiapSoalDanTiapKuncinya(): void
    {
        $json = "{\n  \"course\": \"DDP101\",\n  \"meeting\": 4,\n  \"problems\": [\n    {\n      \"slug\": \"a\",\n      \"generator\": \"baris\\n\\\"kutip\\\" { [\",\n"
            . "      \"translations\": {\n        \"id\": {}\n      },\n      \"reference_solution\": \"x\"\n    },\n    {\"slug\": \"b\",\n     \"hidden_count\": 3}\n  ]\n}\n";
        $this->assertNotNull(json_decode($json));
        $peta = PosisiJson::petakan($json);
        $this->assertSame(3, $peta['meeting']);
        $this->assertSame([5, 6, 7, 8, 11], [$peta['problems/0'], $peta['problems/0/slug'], $peta['problems/0/generator'], $peta['problems/0/translations'], $peta['problems/0/reference_solution']]);
        $this->assertSame([13, 13, 14], [$peta['problems/1'], $peta['problems/1/slug'], $peta['problems/1/hidden_count']]);
        // Pembungkus larik satu obyek tidak mengubah jalur.
        $this->assertSame(4, PosisiJson::petakan("[\n" . $json . ']')['meeting']);
    }
}
