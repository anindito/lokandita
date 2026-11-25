<?php

declare(strict_types=1);

namespace App\Libraries;

/**
 * SATU tempat aturan waktu pengerjaan (SPEC-LOKAUJIAN.md §7.4, §7.5).
 *
 * Semua keputusan memakai jam MySQL (UTC_TIMESTAMP()), tidak pernah jam peramban dan tidak
 * pernah jam kontainer: aplikasi dan penjadwal membaca jam yang sama. Tenggat tidak disimpan;
 * ia dihitung dari baris sesi, ujian dan pengerjaan setiap kali ditanyakan, sehingga
 * pengerjaan yang tenggatnya lewat SUDAH berakhir apa pun status tersimpannya.
 *
 * Potongan SQL di sini memakai alias tetap: `a` = attempts, `s` = sessions, `x` = exams.
 * Kueri yang memakainya wajib memakai alias yang sama.
 */
final class Tenggat
{
    /** Simpan yang tiba sampai sekian detik sesudah tenggat masih diterima (simpan otomatis terakhir). */
    public const TENGGANG_DETIK = 10;

    /** Sesi dikendalikan dosen berakhir sendiri sekian jam sesudah dimulai. */
    public const BATAS_JAM = 8;

    /** "Akhiri" diumumkan sekian detik sebelum berlaku. */
    public const UMUMKAN_DETIK = 30;

    /**
     * Akhir sesi dikendalikan dosen: end_at, paling lambat 8 jam sesudah mulai. NULL bila belum dimulai.
     */
    public static function akhirSesi(): string
    {
        $batas = 's.started_at + INTERVAL ' . self::BATAS_JAM . ' HOUR';

        return "LEAST(COALESCE(s.end_at, {$batas}), {$batas})";
    }

    /**
     * Tenggat satu pengerjaan:
     *   per_mahasiswa      = mulai + durasi + tambahan
     *   dikendalikan_dosen = akhir sesi + tambahan
     */
    public static function tenggat(): string
    {
        return "(CASE WHEN x.mode = 'per_mahasiswa' THEN a.started_at + INTERVAL x.duration_minutes MINUTE ELSE "
            . self::akhirSesi() . ' END + INTERVAL a.extra_minutes MINUTE)';
    }

    /**
     * ATURANNYA: mahasiswa boleh menjawab tepat ketika pengerjaannya belum dikumpulkan dan waktu
     * server masih sebelum tenggatnya.
     */
    public static function berjalan(): string
    {
        return '(a.submitted_at IS NULL AND UTC_TIMESTAMP() < ' . self::tenggat() . ')';
    }

    /**
     * Aturan yang sama untuk SIMPAN, dengan tenggang 10 detik bagi simpan otomatis yang sedang di
     * jalan. Tenggang ini tidak ditampilkan dan tidak bisa diatur.
     */
    public static function bolehSimpan(): string
    {
        return '(a.submitted_at IS NULL AND UTC_TIMESTAMP() < ' . self::tenggat() . ' + INTERVAL ' . self::TENGGANG_DETIK . ' SECOND)';
    }

    /** Detik tersisa sampai tenggat (negatif bila sudah lewat). */
    public static function sisa(): string
    {
        return 'TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), ' . self::tenggat() . ')';
    }

    /**
     * Sesi per_mahasiswa terbuka untuk MULAI.
     */
    public static function terbuka(): string
    {
        return '(s.open_at IS NOT NULL AND s.open_at <= UTC_TIMESTAMP() AND (s.close_at IS NULL OR UTC_TIMESTAMP() < s.close_at))';
    }

    /**
     * Sesi dikendalikan dosen sedang berlangsung: sudah dimulai dan belum berakhir.
     */
    public static function berlangsung(): string
    {
        return '(s.started_at IS NOT NULL AND UTC_TIMESTAMP() < ' . self::akhirSesi() . ')';
    }

    /**
     * Sesi menerima MULAI dari mahasiswa, menurut mode ujiannya.
     */
    public static function menerimaMulai(): string
    {
        return "(CASE WHEN x.mode = 'per_mahasiswa' THEN " . self::terbuka() . ' ELSE ' . self::berlangsung() . ' END)';
    }
}
