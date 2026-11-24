<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use Lokandita\Auth\Filters\RequireLogin;
use Lokandita\Auth\Filters\SecurityHeaders;

class Filters extends BaseFilters
{
    /**
     * @var array<string, class-string|list<class-string>>
     */
    public array $aliases = [
        'csrf'        => CSRF::class,
        'forcehttps'  => ForceHTTPS::class,
        'pagecache'   => PageCache::class,
        'performance' => PerformanceMetrics::class,
        'keamanan'    => SecurityHeaders::class,
        // Sesi LokaAuth: satu-satunya cara aplikasi ini mengenali pengguna (KONTRAK.md §4).
        'masuk'       => RequireLogin::class,
        // Safe Exam Browser bagi mahasiswa; selalu dipasang sesudah `masuk` (KONTRAK.md §8).
        'seb'         => \Lokandita\Auth\Filters\WajibSeb::class,
    ];

    /**
     * @var array{before: list<string>, after: list<string>}
     */
    public array $required = [
        'before' => ['forcehttps', 'pagecache'],
        'after'  => ['pagecache', 'performance'],
    ];

    /**
     * CSRF berlaku untuk setiap form yang mengubah keadaan (SPEC.md §16).
     * /healthz dipanggil lewat HTTP biasa di dalam kontainer; cookie CSRF yang
     * bertanda Secure tidak boleh dikirim ke sana.
     *
     * @var array<string, array<string, array<string, string>|string>>
     */
    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['healthz']],
        ],
        'after' => [
            'keamanan',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}
