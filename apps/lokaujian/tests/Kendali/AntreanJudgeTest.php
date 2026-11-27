<?php

declare(strict_types=1);

namespace Tests\Kendali;

use Tests\Support\UjianCase;

/**
 * Peringatan antrean judge LokaKode di halaman sesi dosen (SPEC-LOKAUJIAN.md §14; KONTRAK.md §6,
 * §24), dengan Redis sungguhan: tes mengisi kedua daftar antrean di Redis DB 15 — tempat
 * JUDGE_QUEUE_REDIS_URL kontainer tes menunjuk — dan membaca halaman serta JSON tampilan langsung.
 */
final class AntreanJudgeTest extends UjianCase
{
    private const DAFTAR = ['lokandita:judge:normal', 'lokandita:judge:low'];

    protected function tearDown(): void
    {
        $this->redisTes()->del([...self::DAFTAR, 'lokaujian:judge:antre']);
    }

    public function testPeringatanMunculSelamaAdaAntreanDanHilangSesudahnya(): void
    {
        $d    = $this->duniaSesi();
        $ajar = $this->sebagai($d['pengajarA']);
        $ini  = "/sesi/{$d['sesi']}";
        $r    = $this->redisTes();
        $r->del([...self::DAFTAR, 'lokaujian:judge:antre']);

        // Antrean kosong: peringatan ada di halaman tetapi tersembunyi; JSON berkata lengang.
        $this->assertMatchesRegularExpression('/data-judge="lengang" hidden>/', $ajar->get($ini)->body);
        $this->assertSame(['antre' => 0, 'sibuk' => false], $ajar->get("{$ini}/langsung")->json()['judge']);

        // Tiga pekerjaan menunggu (dua normal, satu rendah): peringatan tampil dan menyebut angkanya.
        $r->lPush(self::DAFTAR[0], 'a', 'b');
        $r->lPush(self::DAFTAR[1], 'c');
        $j = $ajar->get("{$ini}/langsung")->json();
        $this->assertSame(['antre' => 3, 'sibuk' => true], $j['judge']);
        $this->assertStringContainsString('3 pekerjaan judge mengantre', $j['judge_teks']);
        $isi = $ajar->get($ini)->body;
        $this->assertMatchesRegularExpression('/data-judge="sibuk">[^<]*3 pekerjaan judge mengantre/', $isi);
        // LokaUjian hanya membaca: daftar antrean tidak berubah.
        $this->assertSame([2, 1], [$r->lLen(self::DAFTAR[0]), $r->lLen(self::DAFTAR[1])]);

        // Antrean habis: "sibuk" masih diingat 60 detik supaya peringatan tidak berkedip …
        $r->del(self::DAFTAR);
        $this->assertSame(['antre' => 0, 'sibuk' => true], $ajar->get("{$ini}/langsung")->json()['judge']);
        $this->assertGreaterThan(0, $r->ttl('lokaujian:judge:antre'));
        $this->assertLessThanOrEqual(60, $r->ttl('lokaujian:judge:antre'));
        // … dan sesudah ingatan itu habis, peringatan hilang.
        $r->del('lokaujian:judge:antre');
        $this->assertSame(['antre' => 0, 'sibuk' => false], $ajar->get("{$ini}/langsung")->json()['judge']);
        $this->assertMatchesRegularExpression('/data-judge="lengang" hidden>/', $ajar->get($ini)->body);
    }

    public function testMahasiswaTidakMelihatApaPunTentangAntrean(): void
    {
        $d = $this->duniaSesi();
        $this->redisTes()->lPush(self::DAFTAR[0], 'a');
        $ani = $this->sebagai($d['mhsA']);
        $this->assertSame(403, $ani->get("/sesi/{$d['sesi']}/langsung")->status);
        $this->mulaiKerja($ani, $d['sesi'], (string) $d['mhsA']['id']);
        $k = $ani->get("/api/sesi/{$d['sesi']}/keadaan")->json();
        $this->assertArrayNotHasKey('judge', $k);
        $this->assertStringNotContainsString('judge', $ani->get("/ujian-saya/{$d['sesi']}/kerjakan")->body);
    }
}
