<?php
/**
 * Statistik dan kalibrasi per soal (SPEC.md §8.4, §12). Variabel: $soal (Analitik::soalKelas
 * atau soalMatkul), $tautKiriman (awalan alamat daftar kiriman per soal, atau null).
 */
?>
<?php if ($soal === []): ?>
    <div class="kartu"><p><?= lang('Latihan.ruang.kosong') ?></p></div>
<?php else: ?>
    <div class="tabel-wadah kartu">
        <table data-statistik-soal>
            <thead>
            <tr>
                <th scope="col"><?= lang('Papan.soal.soal') ?></th>
                <th scope="col"><?= lang('Papan.soal.tingkat') ?></th>
                <th scope="col"><?= lang('Papan.soal.mencoba') ?></th>
                <th scope="col"><?= lang('Papan.soal.lulus') ?></th>
                <th scope="col"><?= lang('Papan.soal.rataTes') ?></th>
                <th scope="col"><?= lang('Papan.soal.rataCoba') ?></th>
                <th scope="col"><?= lang('Papan.soal.median') ?></th>
                <th scope="col"><?= lang('Papan.soal.petunjuk') ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($soal as $s): ?>
                <tr>
                    <td><?php if ($tautKiriman !== null): ?><a href="<?= $tautKiriman ?>?soal=<?= (int) $s['id'] ?>"><?= esc($s['judul']) ?></a><?php else: ?><?= esc($s['judul']) ?><?php endif ?></td>
                    <td><span class="lencana lencana-tingkat tingkat-<?= esc(strtolower($s['tingkat'])) ?>"><?= lang('Soal.tingkat.' . $s['tingkat']) ?></span></td>
                    <td class="ltr"><?= (int) $s['mencoba'] ?></td>
                    <td class="ltr"><?= $s['persen'] === null ? '—' : angka($s['persen']) . '%' ?></td>
                    <td class="ltr"><?= $s['rataTes'] === null ? '—' : angka($s['rataTes']) . '%' ?></td>
                    <td class="ltr"><?= angka($s['rataCoba']) ?></td>
                    <td class="ltr"><?= $s['medianMs'] === null ? '—' : angka($s['medianMs']) . ' ms' ?></td>
                    <td><?php if ($s['petunjuk'] !== null): ?><strong class="teks-peringatan" data-petunjuk="<?= $s['petunjuk'] ?>"><?= lang('Papan.soal.petunjuk_' . $s['petunjuk']) ?></strong><?php endif ?></td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="petunjuk"><?= lang('Papan.soal.info', [\App\Libraries\Analitik::KALIBRASI_MIN]) ?></p>
<?php endif ?>
