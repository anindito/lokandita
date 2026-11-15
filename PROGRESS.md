# Progress

Current stage: **5 — LokaAuth and the shared package** — **in progress**, plan approved by the owner 2026-11-15. See "Stage 5" at the end of this file for what is done; anything not listed there is not done.

Stage 4 — Skeleton and infrastructure — finished 2026-11-15, tag `tahap-4`.

Server prepared 2026-11-15: Ubuntu 24.04.5, kernel 6.8, 4 vCPU, 8 GB RAM, 4 GB swap, Docker 29.9, Compose 5.7, Python 3.12, Claude Code 2.1.

## Stage 4 — 2026-11-15

Built:

- `apps/auth`, `apps/lokakode`, `apps/portal`: CodeIgniter 4.7.6 on PHP 8.3, each with only `GET /healthz`, locales `id`/`en`/`ar`, UTC.
- `packages/lokandita-auth`: startup env check and the `/healthz` controller.
- `infra/compose.yml`: `caddy`, `mysql` 8.4, `redis` 7, `auth`, `lokakode`, `portal` on networks `edge`, `internal`, `data`.
- Caddy with production Let's Encrypt certificates for `lokandita.com`, `www`, `auth`, `lokakode`; every host in maintenance (503 page); `/internal/*` → 404.
- MySQL schemas `identity`, `lokakode`, `portal` and their `_test` twins, one user per app. No tables yet.
- Scripts in `infra/`: `buat-env.sh`, `cek-env.sh`, `mysql-init.sh`, `deploy.sh`, `pemeliharaan.sh`, `smoke.sh`, `backup.sh`, `uji-restore.sh`, `tes-infra.sh`.
- Daily backup at 02:00 WIB in the crontab of user `deploy`; `docs/RESTORE.md`.

Verified by the agent (commands and output shown in the session):

- `infra/tes-infra.sh`: 55 passed, 0 failed.
- `infra/smoke.sh`: checks 0–2 passed (11 assertions), certificate validated by curl.
- `infra/backup.sh` and `infra/uji-restore.sh` passed.

Not verified, or weaker than it looks:

- **Smoke checks 3, 4, 5 and 6 are not active** (`BELUM AKTIF`). The code for 3–5 is written but has never run; check 6 is not written. They prove nothing until stages 5 and 7.
- The restore test compared three **empty** schemas. It proves the mechanism, not that real data survives; rerun it after stage 5.
- How the maintenance page looks in a browser, and access from a network outside this server, were not checked by the agent.
- Backups are unencrypted and on the same disk as the database (owner's decision for now).

Owner's gate for stage 4:

1. Open `https://lokandita.com`, `https://www.lokandita.com`, `https://auth.lokandita.com`, `https://lokakode.lokandita.com` in a browser: valid certificate, maintenance page (`www` redirects to the apex).
2. Run `infra/smoke.sh`.

Gate result, 2026-11-15:

- The owner ran `infra/smoke.sh` and reported `11 lulus, 0 gagal, 4 belum aktif`. Smoke check 1 covers the gate of `SPEC.md` §18 (maintenance page over HTTPS with a valid certificate on every host).
- The owner did not explicitly report the browser check (item 1 above). How the page looks in a browser remains unconfirmed in this file.
- The previous session was cut off by the usage limit after the last stage-4 commit. In the closing session the agent re-ran only `infra/smoke.sh` and `infra/cek-env.sh` (both passed). `infra/tes-infra.sh`, `infra/backup.sh` and `infra/uji-restore.sh` were **not** re-run, by the owner's decision; their results above are from the earlier session.

## Stage 5 — in progress

Plan (9 steps, one commit each): 1 test harness · 2 identity schema · 3 login core · 4 MFA and first superadmin · 5 internal API · 6 shared package · 7 superadmin pages · 8 placeholder page in LokaKode and Portal · 9 close (smoke checks 3–5, contract, deploy, restore test).

Owner's decisions for this stage (2026-11-15): lockout 1 / 5 / 15 / 60 minutes after each round of 5 failures; per-IP limit 100 failures per 15 minutes; `suspended` and `disabled` both block login and revoke sessions, `disabled` is hidden from the default list; bulk import refuses `superadmin`; the last active superadmin cannot be disabled or demoted; idle logout is enforced on the server; "export of own data" moved to stage 11; `auth.lokandita.com` is opened only after asking again.

Done:

- Step 1 — `infra/tes.sh`: `infra/tes.sh auth` → 5 tests pass against `identity_test` and a real `auth-tes` container.
- Step 2 — identity schema: migrations for the seven tables and two views, `infra/mysql-hak-reader.sh`; `infra/tes.sh auth` → 15 tests pass (view hides revoked, expired, inactive and idle sessions; `identity_reader` reads three objects and writes nothing). Exists only in `identity_test` so far.
- Step 3 — login core: login, lockout, per-IP limit, forced password change, password policy and history, session cookie, sliding session, Keluar, `next` validation, security headers, three languages; `infra/tes.sh auth` → 43 tests, 256 assertions pass. Not deployed.
- Step 4 — MFA: TOTP enrolment at a superadmin's first login, 8 single-use backup codes, encrypted secret, code replay refused, `spark akun:superadmin`; `infra/tes.sh auth` → 52 tests, 391 assertions pass. Not deployed.
- Step 5 — internal API: six endpoints, service token, JSON errors 401 / 403 / 404 / 422, denials audited; `infra/tes.sh auth` → 66 tests, 487 assertions pass.
- Steps 6 and 8 (done together, because the package can only be tested inside a client app) — shared package: LokaAuth client, session filter (`KONTRAK.md` §4), full account menu, "Tetap masuk", idle logout, `/keluar` · `/sesi/perpanjang` · `/preferensi` in all three apps, temporary `/beranda` in LokaKode and Portal. `infra/tes.sh auth` → 69 tests; `infra/tes.sh lokakode` → 19 tests; `infra/tes.sh portal` → 19 tests. Keluar from a client app revokes the session in LokaAuth over real HTTP between containers.

Open for the owner:

- **Tailwind is not used.** Downloading the Tailwind binary was refused by this session's permission system; the pages use hand-written CSS instead. Decide: allow the download, or accept plain CSS and change `SPEC.md` §2.

Not done: step 7 (superadmin pages) and step 9 (close). The session-expiry banner and idle logout in the browser are JavaScript and have **not** been exercised by any test; only their server side is tested. Nothing of stage 5 is deployed.
