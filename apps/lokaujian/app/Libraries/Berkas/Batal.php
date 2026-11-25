<?php

declare(strict_types=1);

namespace App\Libraries\Berkas;

/**
 * Dilempar di dalam transaksi impor ketika berkas ternyata harus ditolak sesudah sebagian
 * ditulis (gambar yang tidak terbaca): transaksinya dibatalkan seluruhnya dan galatnya sudah
 * dicatat di App\Libraries\Aturan.
 */
final class Batal extends \RuntimeException
{
}
