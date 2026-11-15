<?php

namespace Config;

use App\Filters\RequireSession;
use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
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
        'sesi'        => RequireSession::class,
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
     * API internal memakai X-Service-Token, bukan cookie, jadi dikecualikan.
     * /healthz dipanggil lewat HTTP biasa di dalam kontainer; cookie CSRF yang
     * bertanda Secure tidak boleh dikirim ke sana.
     *
     * @var array<string, array<string, array<string, string>|string>>
     */
    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['healthz', 'internal', 'internal/*']],
        ],
        'after' => [
            'keamanan',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}
