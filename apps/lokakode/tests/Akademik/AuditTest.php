<?php

declare(strict_types=1);

namespace Tests\Akademik;

use Tests\Support\KodeCase;

/**
 * Halaman log audit LokaKode: superadmin saja, hanya membaca.
 */
final class AuditTest extends KodeCase
{
    public function testHalamanAuditMemuatPerubahanDenganNamaPelakuDanSasaran(): void
    {
        $d = $this->dunia();
        // Dua tindakan LokaKode yang diaudit: pengajar mereset kata sandi mahasiswanya, superadmin
        // mengubah konsep dasar mata kuliah. (Perubahan roster diaudit Akun Lokandita, KONTRAK.md §18.)
        $x = $this->sebagai($d['pengajarA']);
        $x->post('/kelas/' . $d['kelasXA'] . '/reset-sandi', ['csrf' => $this->csrf($x), 'user_id' => $d['mhsA']['id'], 'yakin' => '1']);
        $admin = $this->sebagai($d['superadmin']);
        $admin->post('/mata-kuliah/' . $d['matkulY'] . '/konsep-dasar', ['csrf' => $this->csrf($admin), 'konsep' => ['PERCABANGAN']]);

        $r = $admin->get('/audit');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('2 catatan', $r->body);
        $this->assertMatchesRegularExpression('/data-audit="sandi\.direset".*?Pengajar Aminah.*?Mahasiswa Ani/s', $r->body);
        $this->assertStringContainsString('data-audit="matkul.konsepDasar"', $r->body);
        // Alamat klien diambil dari X-Forwarded-For (dikirim Caddy), bukan alamat kontainer.
        $this->assertStringContainsString('>' . $x->ip . '<', $r->body);
        $this->assertSame($x->ip, $this->row("SELECT ip FROM audit_log WHERE action = 'sandi.direset'")['ip']);

        // Saringan: awalan aksi, pelaku, pelaku tak dikenal.
        $r = $admin->get('/audit?aksi=sandi.');
        $this->assertStringContainsString('1 catatan', $r->body);
        $this->assertStringNotContainsString('data-audit="matkul.konsepDasar"', $r->body);
        $this->assertStringContainsString('1 catatan', $admin->get('/audit?aktor=' . $d['superadmin']['nomor_induk'])->body);
        $this->assertStringContainsString('0 catatan', $admin->get('/audit?aktor=tidak-ada')->body);
        $this->assertStringContainsString('0 catatan', $admin->get('/audit?sampai=2020-01-01')->body);
        $this->assertStringContainsString('2 catatan', $admin->get('/audit?dari=2020-01-01')->body);
    }

    public function testTidakAdaRuteYangMengubahAtauMenghapusAudit(): void
    {
        $d     = $this->dunia();
        $admin = $this->sebagai($d['superadmin']);
        $csrf  = $this->csrf($admin);
        $admin->post('/mata-kuliah/' . $d['matkulY'] . '/konsep-dasar', ['csrf' => $csrf, 'konsep' => ['PERCABANGAN']]);
        $baris = $this->row('SELECT * FROM audit_log');
        $this->assertNotNull($baris);

        foreach (['/audit', '/audit/' . $baris['id'], '/audit/' . $baris['id'] . '/hapus', '/audit/hapus'] as $jalur) {
            $this->assertSame(404, $admin->post($jalur, ['csrf' => $csrf])->status, $jalur);
        }
        $this->assertSame($baris, $this->row('SELECT * FROM audit_log'));

        // Dalam kode pun tidak ada penulisan ke audit_log selain INSERT di App\Libraries\Audit.
        $sumber = '';
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(APPPATH, \FilesystemIterator::SKIP_DOTS)) as $berkas) {
            if ($berkas->getExtension() === 'php' && ! str_contains($berkas->getPathname(), '/Database/Migrations/')) {
                $sumber .= file_get_contents($berkas->getPathname());
            }
        }
        $this->assertSame(1, preg_match_all("/table\\('audit_log'\\)->(insert|update|delete|truncate|replace)/", $sumber, $m));
        $this->assertSame(['insert'], $m[1]);
        $this->assertDoesNotMatchRegularExpression('/(UPDATE|DELETE FROM|TRUNCATE)\s+audit_log/i', $sumber);
    }
}
