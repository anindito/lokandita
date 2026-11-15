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
        $x = $this->sebagai($d['pengampuX']);
        $x->post('/kelas/' . $d['kelasXA'] . '/mundur', ['csrf' => $this->csrf($x), 'ids' => [$d['daftarA']]]);
        $admin = $this->sebagai($d['superadmin']);
        $admin->post('/kelas/' . $d['kelasYA'] . '/arsip', ['csrf' => $this->csrf($admin), 'arsip' => '1']);

        $r = $admin->get('/audit');
        $this->assertSame(200, $r->status);
        $this->assertStringContainsString('2 catatan', $r->body);
        $this->assertMatchesRegularExpression('/data-audit="pendaftaran\.mundur".*?Pengampu Xaverius.*?Mahasiswa Ani/s', $r->body);
        $this->assertStringContainsString('data-audit="kelas.diarsipkan"', $r->body);
        // Alamat klien diambil dari X-Forwarded-For (dikirim Caddy), bukan alamat kontainer.
        $this->assertStringContainsString('>' . $x->ip . '<', $r->body);
        $this->assertSame($x->ip, $this->row("SELECT ip FROM audit_log WHERE action = 'pendaftaran.mundur'")['ip']);

        // Saringan: awalan aksi, pelaku, pelaku tak dikenal.
        $r = $admin->get('/audit?aksi=pendaftaran.');
        $this->assertStringContainsString('1 catatan', $r->body);
        $this->assertStringNotContainsString('data-audit="kelas.diarsipkan"', $r->body);
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
        $admin->post('/kelas/' . $d['kelasYA'] . '/arsip', ['csrf' => $csrf, 'arsip' => '1']);
        $baris = $this->row('SELECT * FROM audit_log');

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
