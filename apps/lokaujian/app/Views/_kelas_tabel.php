<?php
/**
 * Tabel kelas. Variabel: $kelas (baris Roster::daftarKelas), $pengajar (id per kelas),
 * $orang (direktori), $denganMatkul (bool: kolom mata kuliah ditampilkan).
 */
?>
<div class="tabel-wadah kartu">
    <table>
        <thead>
        <tr>
            <?php if ($denganMatkul): ?><th scope="col"><?= lang('Ujian.matkul.satu') ?></th><?php endif ?>
            <th scope="col"><?= lang('Ujian.kelas.satu') ?></th>
            <th scope="col"><?= lang('Ujian.kelas.mahasiswa') ?></th>
            <th scope="col"><?= lang('Ujian.kelas.pengajar') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($kelas as $k): ?>
            <tr>
                <?php if ($denganMatkul): ?>
                    <td><a href="/mata-kuliah/<?= (int) $k['course_id'] ?>"><span class="ltr"><?= esc($k['matkul_kode']) ?></span> — <?= esc($k['matkul_nama']) ?></a></td>
                <?php endif ?>
                <td>
                    <a href="/kelas/<?= (int) $k['id'] ?>"><?= esc($k['name']) ?></a>
                    <?php if ($k['archived_at'] !== null): ?><span class="lencana"><?= lang('Ujian.kelas.diarsipkan') ?></span><?php endif ?>
                </td>
                <td><?= (int) $k['jumlah_aktif'] ?></td>
                <td><?= view('_pengajar', ['daftar' => $pengajar[(int) $k['id']] ?? [], 'orang' => $orang]) ?></td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>
