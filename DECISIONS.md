# Decisions

Technical choices made during the build, newest first. One line each: date, decision, reason.

- 2026-11-15 — `tes.sh` proves a migration ran by looking for the `migrations` table — `spark migrate` exits 0 even when it cannot connect.
- 2026-11-15 — `DB_DSN` is parsed by `Lokandita\Auth\Dsn` into plain fields — CI4's DSN parser passes the port as a string and mysqli refuses it; found by the first harness run, stage 4 never connected through CI4.
- 2026-11-15 — Tests are black-box: real HTTP to a second LokaAuth (`auth-tes`) on `identity_test`, plus direct SQL for setup and assertions — in-process feature tests would not exercise cookies, headers and container networking, which is where v1 failed.
- 2026-11-15 — The test image is the production Dockerfile with dev dependencies added (`COMPOSER_DEV=`), tag `:tes` — the code under test is built the same way as the code that is deployed.
- 2026-11-15 — PHPUnit fails on skipped and incomplete tests — a skipped test must not look like a pass.
- 2026-11-15 — Backups are not encrypted and stay on the server disk (`/srv/lokandita-data/backups`, mode 700), run by the `deploy` user's crontab at 02:00 WIB — owner accepted no off-server copy for now; a crontab needs nothing under `/etc`.
- 2026-11-15 — Example tests shipped with the CI4 starter were deleted — they pass without testing anything of ours.
- 2026-11-15 — `deploy.sh` removes older images of the app it just deployed — rollback is `git checkout` plus deploy, and old images only fill the disk.
- 2026-11-15 — `deploy.sh` refuses a dirty working tree — otherwise `/healthz` could report a commit that is not what is running.
- 2026-11-15 — App images carry a moving tag `aktif` next to the commit tag — Compose can then start any service without knowing commit hashes; the `/healthz` check is what proves the version.
- 2026-11-15 — Maintenance is a per-host flag file read by Caddy on each request, default on — apps can be opened one at a time in later stages without editing the Caddyfile; a fresh server exposes nothing.
- 2026-11-15 — The maintenance page is static HTML served by Caddy with Indonesian, English and Arabic text inline — owner-approved exception to the language-file rule, because no CI4 app is involved.
- 2026-11-15 — HSTS is sent without `includeSubDomains` and without `preload` — preload is hard to undo and future subdomains are not built yet.
- 2026-11-15 — Certificates were first requested from the Let's Encrypt staging CA, then production — a mistake in DNS or ports would otherwise burn the production rate limit.
- 2026-11-15 — Redis requires a password although it is only on the `data` network and loopback — a second barrier costs nothing.
- 2026-11-15 — `identity_reader` is created now without any right; grants arrive with the views in stage 5 — the password is fixed in every `.env` from the start and a grant on a missing view is impossible.
- 2026-11-15 — Startup validation takes its list from `.env.example` — one list instead of three that can drift.
- 2026-11-15 — Env check and `/healthz` controller live in `packages/lokandita-auth` — they are identical in all three apps.
- 2026-11-15 — Composer project names are `lokandita/app-<name>` — `lokandita/auth` is the shared package's name.
- 2026-11-15 — One `infra/docker/app.Dockerfile` for the three apps, build context = repository root — the path package must be inside the context.
- 2026-11-15 — App image is `php:8.3.36-apache` with `FallbackResource`, listening on 8080 as `www-data` — one process, no extra web server container, nothing unusual to maintain.
- 2026-11-15 — Image versions are pinned: MySQL 8.4.13, Redis 7.4.12, Caddy 2.11.9, Composer 2.11.2, phpredis 6.2.0, CodeIgniter 4.7.6 (lock file) — upgrades should be deliberate.
