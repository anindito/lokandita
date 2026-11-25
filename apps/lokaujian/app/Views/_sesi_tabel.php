<?php
/**
 * Tabel sesi. Variabel: $sesi (Sesi::untukUjian atau Sesi::untukKelas), $namaKelas (id kelas =>
 * nama; kosong bila tabel ini milik satu kelas dan kolomnya diisi judul ujian).
 */
use Lokandita\Auth\Waktu;

$namaKelas = $namaKelas ?? [];
?>
<div class="tabel-wadah kartu">
    <table data-sesi-tabel>
        <thead><tr>
            <th scope="col"><?= $namaKelas === [] ? lang('Bank.ujian.judulIsian') : lang('Ujian.kelas.satu') ?></th>
            <th scope="col"><?= lang('Bank.ujian.mode') ?></th>
            <th scope="col"><?= lang('Sesi.keadaan') ?></th>
            <th scope="col"><?= lang('Sesi.waktu') ?></th>
            <th scope="col"><?= lang('Sesi.hadir') ?></th>
        </tr></thead>
        <tbody>
        <?php foreach ($sesi as $s): ?>
            <tr>
                <td dir="auto"><a href="/sesi/<?= (int) $s['id'] ?>"><?= esc($namaKelas === [] ? $s['title'] : ($namaKelas[(int) $s['class_id']] ?? '#' . (int) $s['class_id'])) ?></a></td>
                <td><?= lang('Bank.ujian.mode_' . $s['mode']) ?></td>
                <td><span class="lencana" data-keadaan="<?= esc($s['keadaan']) ?>"><?= lang('Sesi.keadaan_' . $s['keadaan']) ?></span></td>
                <td class="ltr">
                    <?php if ($s['mode'] === 'per_mahasiswa'): ?>
                        <?= esc(Waktu::lokal($s['open_at'])) ?> – <?= esc(Waktu::lokal($s['close_at'])) ?>
                    <?php elseif ($s['started_at'] !== null): ?>
                        <?= esc(Waktu::lokal($s['started_at'])) ?> – <?= esc(Waktu::lokal($s['akhir'])) ?>
                    <?php else: ?>—<?php endif ?>
                </td>
                <td class="ltr"><?= (int) $s['pengerjaan'] ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
