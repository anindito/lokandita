# KONTRAK — shared contract between Lokandita components

The single source of truth for anything two components must agree on. If code needs something that is not here, add it here first, in the same commit.

Every item in this file must be covered by a test that runs against **real containers**.

---

## 1. Repository layout

```
/srv/lokandita/                 Git repository
├── apps/auth/                  CodeIgniter 4 — LokaAuth
├── apps/lokakode/              CodeIgniter 4 — LokaKode
├── apps/portal/                CodeIgniter 4 — Portal
├── apps/lokaujian/             CodeIgniter 4 — LokaUjian (`SPEC-LOKAUJIAN.md`)
├── packages/lokandita-auth/    Composer path package used by all four apps (session check, account menu, SEB §8, roster connection §18)
├── judge/                      Python worker, images, seccomp profile, tests
├── infra/                      compose files, Caddyfile, scripts
├── docs/                       PETA-KONSEP.md, silabus/, branding/
├── SPEC.md  SPEC-LOKAUJIAN.md  KONTRAK.md  CLAUDE.md  PROGRESS.md  DECISIONS.md

/srv/lokandita-data/            NOT in Git: CSV of accounts, enrollments, backups
```

## 2. Session cookie

| Property | Value |
|---|---|
| Name | `lokandita_sid` |
| Value | 32 random bytes, base64url (43 characters) |
| Domain | `.lokandita.com` |
| Path | `/` |
| Flags | `HttpOnly`, `Secure`, `SameSite=Lax` |
| Lifetime | 12 h, sliding |

Only LokaAuth sets or clears this cookie. The database stores `SHA-256(value)` as lowercase hex, never the value.

The cookie is sent with `Max-Age=43200`. LokaAuth sends it again, unchanged, whenever it extends a session, so the cookie slides together with the row in `sessions`.

`GET AUTH_PUBLIC_URL/keluar-selesai` is where a client app sends the browser after it has revoked the session through §5. LokaAuth clears the cookie there only when the session is already dead, so the link cannot be used to log somebody else out. It then redirects to `PORTAL_URL/`, the public landing of the Portal; with a session that is still alive it redirects to LokaAuth's own `/` and clears nothing. LokaAuth's own **Keluar** (`POST /keluar`) ends at `PORTAL_URL/` too: since 2026-11-21 every logout, from whichever app, ends on the Portal's landing (the owner's decision). The login page still shows "you have logged out" for `/login?keluar=1`, but nothing links there any more.

CSRF and other per-app cookies use the `__Host-` prefix and therefore carry no `Domain` attribute: `__Host-csrf` (form field `csrf`) and `__Host-bahasa` (language chosen before login), and on LokaAuth `__Host-tunda` (a login whose password step passed and whose TOTP step has not; state in Redis for 10 minutes, key prefix `auth:tunda:`). No row exists in `sessions` until every login step has passed.

Apps set `Secure` cookies and therefore answer `500` to a cookie-setting page requested without `X-Forwarded-Proto: https` from a trusted proxy. Requests reach apps only through Caddy, which sends it. `/healthz` and `/internal/*` set no cookies and are exempt from CSRF, so they work over plain HTTP inside the Docker networks. Apps take the client address from `X-Forwarded-For` only when the request comes from a private (Docker) address.

## 3. Identity schema (`identity` database)

Owned and migrated only by LokaAuth.

```
users
  id CHAR(36) PK (UUID v4)        nomor_induk VARCHAR(40) UNIQUE NOT NULL
  email VARCHAR(190) UNIQUE NULL  nama VARCHAR(190) NOT NULL
  password_hash VARCHAR(255)      must_change_password TINYINT(1) DEFAULT 1
  initial_password_expires_at DATETIME NULL   -- §13, deadline for the initial password
  global_role ENUM('superadmin','dosen','mahasiswa')
  status ENUM('active','suspended','disabled') DEFAULT 'active'
  mfa_enabled TINYINT(1) DEFAULT 0   mfa_secret_enc VARBINARY(255) NULL
  failed_attempts INT DEFAULT 0      locked_until DATETIME NULL
  idle_logout TINYINT(1) DEFAULT 0   locale ENUM('id','en','ar') DEFAULT 'id'
  paste_allowed TINYINT(1) DEFAULT 0
  last_login_at DATETIME NULL        created_by CHAR(36) NULL
  created_at, updated_at

app_access      (user_id, app ENUM('portal','lokakode','lokaujian','lokasiber'), enabled, granted_by, granted_at)  PK(user_id, app)
sessions        (id CHAR(36) PK, token_hash CHAR(64) UNIQUE, user_id, created_at, expires_at, last_seen_at, ip, user_agent, revoked_at NULL)
password_history(user_id, password_hash, created_at)
mfa_backup_codes(user_id, code_hash, used_at NULL)
login_attempts  (nomor_induk, ip, success, created_at)
audit_log       (id BIGINT PK, actor_id, action, target_id, app, detail JSON, ip, user_agent, created_at)
```

All times are stored in UTC. Display uses Asia/Jakarta.

### Read-only view for client apps

```sql
CREATE VIEW session_identity AS
SELECT s.token_hash, s.expires_at, s.last_seen_at,
       u.id AS user_id, u.nomor_induk, u.nama, u.global_role,
       u.locale, u.idle_logout, u.paste_allowed, u.must_change_password
FROM sessions s JOIN users u ON u.id = s.user_id
WHERE s.revoked_at IS NULL AND s.expires_at > UTC_TIMESTAMP() AND u.status = 'active'
  AND (u.idle_logout = 0 OR s.last_seen_at > UTC_TIMESTAMP() - INTERVAL 30 MINUTE);
```

The last condition enforces idle logout on the server: a session of a user with `idle_logout = 1` that has not been touched for 30 minutes no longer exists for any client app, even when the browser was simply closed.

MySQL user `identity_reader` has `SELECT` on `session_identity`, on `app_access`, and on a view `user_directory (id, nomor_induk, nama, global_role, status)`. Nothing else. It can never read `password_hash` or `mfa_secret_enc`, and has no write privilege.

These three grants are given by `infra/mysql-hak-reader.sh` as MySQL root, after the LokaAuth migrations; `auth_app` cannot grant. `infra/deploy.sh auth` and `infra/tes.sh` both run it.

Tables beyond the columns listed above carry a surrogate `id` where needed (`password_history`, `mfa_backup_codes`, `login_attempts`). The migration that creates the tables cannot be rolled back.

## 4. How a client app authenticates a request

Implemented once in `packages/lokandita-auth`; apps do not reimplement it.

1. Read cookie `lokandita_sid`. Absent → redirect to `AUTH_LOGIN_URL?next=<current absolute URL>`.
2. `SELECT * FROM session_identity WHERE token_hash = SHA2(cookie, 256)` through `IDENTITY_READER_DSN`. No row → redirect as above.
3. `must_change_password = 1` → redirect to `AUTH_PUBLIC_URL/ganti-password?next=…`.
4. Check `app_access` for this app with `enabled = 1`. Missing → 403 page with a link to the Portal. **The Portal itself skips this step** (since stage 11): every live session may open the Portal, which then shows only the apps that `app_access` allows. The value `portal` stays in the schema and blocks nothing.
5. If `last_seen_at` is older than 5 minutes, call `POST /internal/session/touch` (§5). A failure here is logged and does **not** fail the request.

`next` is honoured by LokaAuth only when the scheme is `https` and the host is exactly one of `ALLOWED_HOSTS`.

The five steps are `Lokandita\Auth\Filters\RequireLogin` (filter alias `masuk`). Notes:

- The session is read from MySQL on every request; nothing about it is cached in the app.
- `next` and every redirect are built from `APP_URL`, never from the client's `Host` header.
- After a successful touch the filter sends the unchanged cookie again with a fresh `Max-Age` (§2). This is the only case in which code outside LokaAuth emits `lokandita_sid`, and it is the shared package doing it.

Every app, LokaAuth included, answers these three routes; in client apps they are registered by `Lokandita\Auth\Routes::akun()`. All are `POST` with the CSRF field `csrf`:

| Route | Does | Answer |
|---|---|---|
| `/keluar` | `revoke` through §5 | redirect to `AUTH_PUBLIC_URL/keluar-selesai`, which clears the cookie and redirects to `PORTAL_URL/` (§2); if LokaAuth cannot be reached: `502` and a page saying the user is **not** logged out |
| `/sesi/perpanjang` | `touch` through §5, re-sends the cookie | `{ "sisa": <seconds> }`, or `502 {"error":"tidak_bisa_diperpanjang"}` |
| `/preferensi` | `prefs` through §5; form fields `locale`, `idle_logout` (`0`/`1`), `kembali` (local path) | redirect to `kembali` |

The CSRF token is not regenerated after use and lives 12 hours, so a page that stays open, a second tab, and the background `/sesi/perpanjang` call do not invalidate each other.

### Public routes (since stage 11, `SPEC.md` §15)

Routes that need no session. They never read the session or the identity database, and their content is the same for everybody. The only data a public page reads is the Portal's own text overrides (§15) and, on LokaKode's SEB pages, the SEB configurations with the code and name of their course (§8); the landings of LokaKode and LokaUjian read no database at all:

| App | Routes |
|---|---|
| Portal | `/`, `/tentang`, `/privasi`, `/ketentuan`, `/sitemap.xml`, `/robots.txt`; `/kontak` answers `301` to `/tentang` (the contact page became part of Tentang) |
| LokaKode | `/` (public landing), `/robots.txt`, `/seb`, `/seb/periksa`, `/seb/keluar`, `/seb/unduh/<course code>.seb` (§8) |
| LokaUjian | `/` (a short page with the way in, since stage U1), `/robots.txt` (static: disallows everything); since stage U4 `/seb`, `/seb/periksa`, `/seb/keluar` (§23) — instructions and a header check that read no database and name no exam; there is **no** public download |
| every app | `/healthz`, `GET /bahasa/{id\|en\|ar}` |

- `GET /` in the Portal, in LokaKode and in LokaUjian looks only at whether a `lokandita_sid` cookie **is present**: with one it redirects to `/beranda` (where §4 decides whether the session is alive), without one it shows the landing page. The value of the cookie is not read.
- `GET /bahasa/{locale}?kembali=<local path>` (`Lokandita\Auth\Controllers\Bahasa`, registered by `Lokandita\Auth\Routes::publik()`; LokaAuth uses the same controller) sets `__Host-bahasa` (§2) for one year and redirects to `kembali`, which is honoured only as a path on the same host (`Akun::kembali`); otherwise `/`. An unknown locale sets nothing. For a user with a session the language of the account wins (§3 `locale`).
- Public pages are indexable, **except in LokaUjian, where every page is `noindex, nofollow`**, its `/` included (`Publik::data(…, indeks: false)`: the page keeps the "Masuk" button and the language choice, and carries no canonical link and no Open Graph tags); every page behind a session carries `noindex, nofollow` and `Cache-Control: no-store`. The "Masuk" button of a public page is a link to `/beranda` of the same app.
- `GET /beranda` in the Portal (session, any role) shows name, role and one card per app in `app_access` with `enabled = 1`: an app that is built (`lokakode`, address from `LOKAKODE_URL`; since LokaUjian stage U6 `lokaujian`, address from `LOKAUJIAN_URL`) as a link to its `/beranda`, one that is not built yet (`lokasiber`) as "segera" without a link. `portal` itself is not a card.

Client apps read names and nomor induk from `user_directory`, and app access from `app_access`, through `Lokandita\Auth\UserDirectory` (same `identity_reader` connection as the session check, `Lokandita\Auth\ReaderDb`). They do not copy this data into their own schema; a `user_id` stored by a client app is `users.id`.

## 5. Internal API (LokaAuth)

| Property | Value |
|---|---|
| Base URL from other containers | `http://auth-internal:8080` (`AUTH_INTERNAL_URL`) |
| Network | Docker network `internal` only |
| Authentication | Header `X-Service-Token: <token>`; LokaAuth holds `SERVICE_TOKEN_PORTAL`, `SERVICE_TOKEN_LOKAKODE`, `SERVICE_TOKEN_LOKAUJIAN`; each client holds its own as `AUTH_SERVICE_TOKEN` |
| Content type | `application/json` both ways |
| From the internet | Edge Caddy answers `404` for `/internal/*` on every host |

Failures are **never silent**. Missing or wrong token → `401 {"error":"invalid_service_token"}` and a log line `internal.denied` with the reason and caller IP. Unknown path → `404 {"error":"unknown_internal_route"}`. Validation error → `422` with field names.

| Method and path | Body | Result |
|---|---|---|
| `POST /internal/session/touch` | `{ "sid": "<cookie value>" }` | `{ "expires_at": "…" }` |
| `POST /internal/session/revoke` | `{ "sid": "…" }` | `{ "ok": true }` — logout everywhere |
| `POST /internal/session/prefs` | `{ "sid": "…", "locale"?, "idle_logout"? }` | updated prefs |
| `GET  /internal/users/{id}` | — | `{ id, nomor_induk, nama, global_role, status }` |
| `POST /internal/users/lookup` | `{ "nomor_induk": ["…"] }` | `{ "found": [{id, nomor_induk, nama, global_role}], "missing": ["…"] }` |
| `POST /internal/password-reset` | `{ "requested_by": "<uuid>", "user_id": "<uuid>" }` | `{ "temp_password": "…" }`; sets `must_change_password`, revokes the user's sessions |

LokaKode calls `/internal/password-reset` for a student who is `AKTIF` in a class the requester may see (superadmin, the pengampu of the course, a pengajar of that class). Authorisation of *who may reset whom* is decided by the calling app before it calls; LokaAuth records `requested_by` and the calling app in the audit log. As a second barrier, `/internal/password-reset` only resets accounts whose `global_role` is `mahasiswa`; any other target gets `403 {"error":"target_not_allowed"}`. Other accounts are reset by a superadmin inside LokaAuth.

Details that callers rely on:

- `sid` must look like a cookie value (43 base64url characters), otherwise `422`. A well-formed `sid` of a session that is revoked, expired, idle-expired or unknown gives `404 {"error":"session_not_found"}` for `touch` and `prefs`. `touch` never revives a dead session.
- `revoke` answers `{ "ok": true }` also for a session that is already dead or unknown.
- `expires_at` is ISO 8601 in UTC, for example `2026-11-15T22:30:00Z`.
- `prefs` answers `{ "locale": "id|en|ar", "idle_logout": true|false }`. `idle_logout: false` for a mahasiswa gives `422` with `fields.idle_logout = "forced_on_for_mahasiswa"`.
- `GET /internal/users/{id}`: unknown id gives `404 {"error":"user_not_found"}`; `lookup` accepts at most 1000 values.
- A `422` body is `{ "error": "validation_failed", "fields": { "<name>": "<what is wrong>" } }`.
- A wrong HTTP method on a known path is answered like an unknown path.
- Written to `audit_log`: every denial (`internal.denied`), `revoke` (`logout`), `prefs` (`prefs.changed`) and `password-reset` (`password.reset`). `touch`, `users/{id}` and `lookup` are read-like and only appear in the container log.

The service token identifies the app; the audit log stores it as `app` (`portal`, `lokakode`, `lokaujian`).

LokaUjian uses only `session/touch`, `session/revoke` and `session/prefs`. It does not reset passwords: `POST /internal/password-reset` with LokaUjian's token is answered `403 {"error":"app_not_allowed"}` before the body is read, and written to `audit_log` as `internal.denied` with reason `app_not_allowed`.

## 6. Web ↔ judge worker

The database is the source of truth; Redis only carries job IDs.

```
lokakode.judge_jobs
  id CHAR(36) PK            kind ENUM('RUN','SUBMIT','GENERATE','VALIDATE','ANALYZE')
  priority ENUM('normal','low')
  status ENUM('QUEUED','RUNNING','DONE','FAILED')
  payload JSON              progress JSON NULL      result JSON NULL
  attempts INT DEFAULT 0    idempotency_key VARCHAR(100) UNIQUE NULL
  created_by CHAR(36)       created_at, started_at, finished_at
```

- Web inserts the row, commits, then `LPUSH` the id to `lokandita:judge:normal` or `lokandita:judge:low` (Redis DB 0). One exception: the `VALIDATE` jobs of a problem import are `RPUSH`ed to `lokandita:judge:low`, so the worker takes them after every `normal` job (students' `RUN` and `SUBMIT`) and before the pre-generation jobs already waiting on `low`.
- Worker `BRPOP`s normal before low, claims the row with a conditional update `QUEUED → RUNNING`, writes `progress` while working, then `result` and `DONE`/`FAILED`.
- Every 5 s the worker also scans for `QUEUED` rows older than 10 s that are not in Redis and re-queues them.
- `attempts` > 2 on infrastructure failure → `FAILED` with `result.error = "judge_unavailable"`.
- Web reads `judge_jobs` to answer `GET /api/executions/{id}` (polled every second by the browser). A user can read only jobs they created.

Details both sides rely on:

- Web side is `App\Libraries\Antrean::kirim()` in LokaKode. It refuses to run inside an open transaction, because the row must be committed before the id reaches Redis. If the `LPUSH` fails the row stays `QUEUED` and the worker's scan picks it up; the failure is logged as `judge.lpush_failed`. With an `idempotency_key` that already exists it returns the existing job's id and queues nothing.
- `GET /api/executions/{id}` answers `{ "id", "kind", "status", "progress", "result" }` with `Cache-Control: no-store`, never the payload. For a `SUBMIT` job that has ended it also carries `submission` (`{ id, status, verdict, passed, total, score, points, duration_ms, compile_log, tests, missing, late, late_factor, practice }`, copied from `submissions` after the web app has scored it) and `meeting` (the student's meeting score: `{ nilai, terhitung, total, target, kurang, surplus, mudahSekali, kiriman }`); the request that reads an ended `SUBMIT` is what scores it. A job of another user, a system job, an unknown id and a malformed id all get the same `404 {"error":"execution_not_found"}`; this holds for dosen and superadmin too.
- The claim also increments `attempts` and sets `started_at`. An id in Redis that has no `QUEUED` row (unknown, already claimed, pushed twice) is dropped without effect, so a job is never run twice at the same time.
- Infrastructure failure (Docker cannot run the container, or an error inside the worker): the row goes back to `QUEUED` and is re-queued by the scan; at the third failed attempt it becomes `FAILED` with `result.error = "judge_unavailable"`. A verdict (`CE`, `TLE`, …) is never an infrastructure failure and is never retried.
- **A compile that was starved of CPU is an infrastructure failure, not a `CE`.** When a compile (of a student's `RUN` or `SUBMIT`, or of a reference solution in `VALIDATE` or `GENERATE`) reaches the 10 s limit although its container used less than 8 s of CPU — measured by the container's cgroup — or was stopped from outside before it could report, the server was busy: the job is retried as above and ends, at worst, as `judge_unavailable`. `compile_error = "timeout"` is given only when the compiler itself used the time. The web app does not count a `judge_unavailable` submission as an attempt.
- Since stage 9a the worker does all five kinds. A kind it does not know answers `FAILED` with `result.error = "unsupported_kind"`.
- Worker start: rows left `RUNNING` by a previous worker go back to `QUEUED` (or to `FAILED` / `judge_unavailable` when `attempts` is already above 2) and are pushed to Redis at once. While running, the scan does the same for a row that has been `RUNNING` for more than 15 minutes and is not being worked on.
- There is exactly one worker per schema. It refuses to start — exit code 69, one log line `tidak_mulai` with the reason — when Docker, a required kernel feature (cgroup v2, memory, swap, pids and CPU limits, seccomp), the seccomp profile or a judge image with the current tag is missing. The judge images are `judge-c`, `judge-cpp`, `judge-python` and `judge-analisis` (concept analysis; it parses source and never runs it). Their tag is a hash over `judge/images/`, computed once when the worker starts. Exit code 78 means a missing environment variable.
- **Heartbeat**: Redis key `lokandita:judge:worker` (same DB as the queues), a JSON object `{ commit, pid, mulai, waktu, konkurensi, image, skema }`, written every 5 s with a lifetime of 15 s and deleted on a clean stop. `commit` is the Git commit of the working tree at worker start. A missing key means no worker is running. `infra/deploy.sh judge` and `infra/smoke.sh` read it; LokaKode reads it through `Antrean::denyut()`.
- `php spark judge:coba [--bahasa c|cpp|python]` in the `lokakode` container sends one real `RUN` as a system job through `Antrean::kirim()`, waits for the result and exits 0 only when the heartbeat exists and the output is right. With `--validasi` it sends one `VALIDATE` for a small built-in problem instead and exits 0 only when the answer is `ok` with 240 tests.

- **One other reader of the queues (since LokaUjian stage U6):** LokaUjian reads the length of the two lists `lokandita:judge:normal` and `lokandita:judge:low` (`LLEN`, through its `JUDGE_QUEUE_REDIS_URL`, §10) to warn a lecturer that the server is busy judging (§24 "Judge queue warning"). It reads nothing else of this section, never writes to this Redis DB, and decides nothing from the number. The names of the two lists are therefore part of this contract.

`progress`: `{ "state": "queued|compiling|running|done", "current": n, "total": m }`

`payload` by kind:

| Kind | Payload |
|---|---|
| `RUN` | `{ language, source, stdin?, instance_id?, flags? }` — with `instance_id` the sample tests of that instance are run; `stdin` and `instance_id` together are refused |
| `SUBMIT` | `{ language, source, instance_id, submission_id, flags? }` — the two ids are integers |
| `GENERATE` | `{ problem_version_id, user_id, meeting_problem_id, seed }` — the two ids and `seed` are integers, `user_id` a UUID |
| `VALIDATE` | `{ language, generator, reference_solution, samples_count, hidden_count, time_limit_ms, allowed_concepts[], comparison_mode }` |
| `ANALYZE` | `{ language, source, required_concepts[] }` |

`result` verdict codes: `AC`, `WA`, `CE`, `RE`, `TLE`, `MLE`, `OLE`, `CONCEPT_NOT_MET`, `OK` (a `RUN` that finished normally; nothing was compared), and for a job as a whole `FAILED`.

For `RUN` the result holds `{ verdict, compile_log, stdout, stderr, exit_code, signal, duration_ms, memory_kb }` with verdict `OK`, `RE`, `TLE`, `MLE` or `OLE`; `stdout` and `stderr` are UTF-8 text (invalid bytes replaced), each cut at 1 MB. On a compile failure it holds only `{ verdict: "CE", compile_log, compile_error }`, where `compile_error` is `compiler` (the compiler refused the source), `timeout` (the compile reached the 10 s limit while it had the CPU), `memory` or `output_too_large`.

Limits the worker applies to a payload: `language` is `c`, `cpp` or `python`; `source` at most 256 KB; `stdin` at most 1 MB. A payload outside them gives `FAILED` with `result.error = "invalid_payload"` and a `message`; it is not retried.

Time: `duration_ms` is CPU time of the program, measured inside the container; starting the container is not counted. `TLE` means the CPU time passed the limit (2 s for C and C++, 5 s for Python), or the wall-clock time passed 2 × limit + 1 s (a program that sleeps or waits).

`flags` (optional, C and C++ only) is the meeting's list of extra compiler flags. Web and worker both accept only these: `-Wall`, `-Wextra`, `-Wshadow`, `-Wpedantic`, `-O0`, `-O1`, `-O2`, `-lm`, and `-std=` followed by `c11`, `c17`, `gnu17` (C) or `c++14`, `c++17`, `c++20`, `gnu++17` (C++); at most 8 flags. Anything else gives `invalid_payload`.

Instance jobs (`RUN` with `instance_id`, and `SUBMIT`): the worker reads `problem_instances` itself and refuses, with `invalid_payload`, an instance whose `user_id` is not the job's `created_by`, or whose version's language is not `language`. The time limit and comparison mode come from the instance's `problem_versions` row, never from the payload.

For `RUN` with `instance_id` the result holds `{ verdict, compile_log, samples: [{ index, verdict, stdout, stderr, exit_code, signal, duration_ms }] }`: one entry per sample test (the first `samples_count` tests of the instance), each `AC`, `WA`, `RE`, `TLE`, `MLE` or `OLE`; the job's `verdict` is `AC` when every sample is, otherwise the first other verdict. Sample input and expected output are not in the result; the web app reads them from the instance to show them, because samples are public to their student. Hidden tests are never run by `RUN`.

For `SUBMIT` the result holds `{ verdict, compile_log, tests: [{ index, verdict, duration_ms }], passed, total, concepts, missing }` over the **hidden** tests only (`index` counts them from 0; samples are never scored). It never holds test input, expected output or program output. `verdict` is `CE` (with `compile_error`, `tests: []`, `passed: 0`), `AC`, the first failing test's verdict, or `CONCEPT_NOT_MET`. Concept analysis runs only when every hidden test passed and the version has `required_in_submission`: `concepts` is then the list of codes found and `missing` the required ones not found; a non-empty `missing` makes the verdict `CONCEPT_NOT_MET` with `passed = total`. Otherwise `concepts` is `null` and `missing` is `[]`. If the student's source cannot be analysed the verdict stays `AC`, `concepts` is `null` and `analysis_failed` is `true`: a failure of our parser is never charged to the student.

The worker does not touch `submissions`: `submission_id` is only echoed in its log. The web app copies a finished `SUBMIT` result into `submissions` and computes scores (`App\Libraries\Penilaian`), in the request that polls the job and again whenever scores are read, so a result is never lost because a browser tab was closed.

For `VALIDATE` the result holds `{ ok, reasons[], warnings[], concepts[], tests, max_cpu_ms }`. `concepts` are the codes detected in the reference solution; `ok` is true exactly when `reasons` is empty. A problem that is refused is a result (`DONE`, `ok: false`), not a failure of the job. A reason or warning is `{ code, where, message, line?, column?, concept?, construct?, verdict?, seed?, index? }`:

| `code` | `where` | Meaning |
|---|---|---|
| `reference_compile_error` | `reference_solution` | does not compile; `line`, `column` and the compiler's message |
| `concept_not_allowed` | `reference_solution` | uses `concept` outside `allowed_concepts`; `construct`, `line`, `column` |
| `analysis_failed` | `reference_solution` | the source could not be analysed |
| `generator_failed` | `generator` | exit code, signal or time limit at `seed` / `index`; `verdict`, `line` when Python names one |
| `generator_output_too_large` | `generator` | one test input above 256 KB, or all of them above 64 MB |
| `generator_output_not_utf8` | `generator` | a test input is not UTF-8 |
| `generator_not_deterministic` | `generator` | the same `seed` and `index` gave different output |
| `reference_failed` | `reference_solution` | `verdict` `RE`, `TLE`, `MLE` or `OLE` at `seed` / `index` |
| warning `generator_ignores_seed` | `generator` | all seeds gave the same inputs |
| warning `reference_empty_output` | `reference_solution` | a test has empty expected output |
| warning `reference_slow` | `reference_solution` | the slowest test used more than half of the time limit |

`line` and `column` count from 1 inside the named source. `message` is technical detail (a compiler line, the last line of stderr); the sentence shown to the user comes from the web app's language files, by `code`.

What `VALIDATE` does: compiles the reference solution; analyses it against `allowed_concepts`; runs the generator for 20 random seeds and every `INDEX` 0 … `samples_count + hidden_count − 1`; runs it twice more for the first 3 of those seeds and compares (same `SEED` and `INDEX` three times); runs the reference solution over all tests of the 20 seeds with `time_limit_ms` as the CPU limit. Everything that can be checked is checked, so one import reports all reasons at once. Payload limits: `samples_count` 2–3, `hidden_count` 10–50, `time_limit_ms` 100–10000, sources at most 256 KB.

For `GENERATE` the result holds `{ ok: true, existed, tests? }` and **never a test**: the worker writes the row in `problem_instances` itself, because a job's result can be read by whoever created the job. An instance that already exists is left untouched (`existed: true`), whatever the seed. A version that is not `APPROVED` gives `invalid_payload`. A generator or reference solution that fails for this seed gives `FAILED` with `result.error = "generation_failed"` and `result.reason = { code, where, verdict?, index? }`, without any program output.

For `ANALYZE` the result holds `{ concepts[], missing[] }`: every concept code found in `source`, and those of `required_concepts` that were not. Source that cannot be parsed gives `FAILED` with `result.error = "analysis_failed"`.

Seed (`SPEC.md` §8.2): the web app computes `HMAC_SHA256(INSTANCE_SECRET, user_id + "|" + problem_version_id)`, takes the first 8 bytes as a big-endian integer and clears the top bit, so `seed` is an integer 0 … 2^63 − 1. The generator receives it in the environment variable `SEED` as decimal text.

Generator and reference solution are the lecturer's code. All their runs for one job happen in **one** container (`SPEC.md` §8.2) under the same limits as any judge container; `/box` is emptied and every left-over process killed between two runs, and each run has its own CPU, wall-clock and output limit. Student code is never run this way: it keeps one new container per test.

The worker connects to MySQL and Redis on `127.0.0.1` (published to loopback only). Its MySQL user is `judge_worker`, whose rights are exactly these three, given by `infra/mysql-hak-judge.sh` as MySQL root after the LokaKode migrations (`infra/deploy.sh lokakode`, `infra/deploy.sh judge` and `infra/tes.sh judge` all run it):

| Table | Rights | Why |
|---|---|---|
| `judge_jobs` | `SELECT`, `UPDATE` | claim a job, write progress and result; it cannot insert or delete jobs |
| `problem_versions` | `SELECT` | generator and reference solution for `GENERATE` |
| `problem_instances` | `SELECT`, `INSERT` | store an instance; it cannot change or delete one |

It cannot read any other table or schema. A later stage that needs the worker to touch another table adds it to that script and to this section.

`created_by` is `users.id` of §3, or `NULL` for a job made by the system itself (the smoke check); such a job can be read by no user.

Judge containers carry the label `lokandita.judge=true`, plus `lokandita.judge.instans=<schema name>` (which worker made it) and `lokandita.judge.berakhir=<unix time>` (after which the reaper may remove it). The reaper removes only containers that have the first label and an expiry in the past. A starting worker removes all containers of its own instance.

### LokaKode tables next to `judge_jobs`

The `lokakode` schema is owned and migrated only by LokaKode. Since stage 6 it holds the academic structure (`SPEC.md` §5): `academic_years`, `courses`, `classes`, `class_teachers`, `enrollments`, `enrollment_moves`, and LokaKode's own append-only `audit_log`. The judge worker has no right on any of these; it gets rights only on the tables named in this section.

Since stage 8 it also holds concepts, meetings and the problem bank (`SPEC.md` §8, §9):

```
concepts             (code PK, grup, name, name_en, name_ar, languages SET('c','cpp','python'), urutan)
meetings             (id, course_id, number 1–16, title, language, enabled)            UNIQUE(course_id, number)
meeting_concepts     (meeting_id, concept_code)       concepts ADDED at that meeting
course_base_concepts (course_id, concept_code)        concepts known before meeting 1
problems             (id, meeting_id, slug)                                            UNIQUE(meeting_id, slug)
problem_versions     (id, problem_id, version, status, language, difficulty, comparison_mode, time_limit_ms,
                      samples_count 2–3, hidden_count 10–50, params JSON, translations JSON, generator,
                      reference_solution, concepts JSON, required_in_submission JSON, content_hash,
                      import_id, created_by, created_at, reviewed_by, reviewed_at, review_note)
meeting_problems     (id, meeting_id, problem_id UNIQUE, problem_version_id)   the APPROVED version in use
problem_instances    (id, user_id, meeting_problem_id, problem_version_id, seed BIGINT UNSIGNED,
                      tests LONGTEXT, created_at)                              UNIQUE(user_id, meeting_problem_id)
imports, import_items                                                          one row per file, one per problem
```

- The concepts allowed at a meeting are the course's base concepts plus the concepts of every meeting of that course with a number up to and including its own.
- `meetings` gained the rest of `SPEC.md` §6 in stage 9a (below).
- `problem_versions.status` is `PENDING_REVIEW`, `APPROVED` or `REJECTED`. `meeting_problems` gets its row at the first approval and then always points at the last approved version.
- `problem_instances.tests` is a JSON array of `{ "in": "<stdin>", "out": "<expected stdout>" }`, the samples first. It is written only by the judge worker and never leaves the server (`SPEC.md` §10).

Since stage 9a it also holds practice and scores (`SPEC.md` §6, §7, §11):

```
meetings              + target_points (default 60), min_mudah_sekali / min_mudah / min_mudah_sedang / min_sedang / min_sulit
                        (default 8 / 8 / 6 / 6 / 4), starter_template NULL, compiler_flags, autocomplete,
                        anonymize_leaderboard, practice_after_close, published_at NULL, published_by, kind 'latihan'|'ujian'
meeting_class_windows (id, meeting_id, class_id, course_id, open_at, close_at, override 'none'|'open'|'closed',
                       late_policy, late_factor, late_until NULL, post_close_visibility, scores_released_at NULL, scores_released_by NULL, updated_by, …)   UNIQUE(meeting_id, class_id)
submissions           (id, user_id, meeting_problem_id, problem_version_id, instance_id, language, source, job_id UNIQUE,
                       status 'MENUNGGU'|'DINILAI'|'GAGAL', verdict, passed, total, score, late, late_factor NULL, practice, duration_ms,
                       compile_log, tests JSON, missing_concepts JSON, created_at, judged_at)
problem_scores        (user_id, meeting_problem_id, meeting_id, difficulty, points, best_score, best_submission_id,
                       attempts, reached_at, updated_at)                                PK(user_id, meeting_problem_id)
meeting_scores        (user_id, meeting_id, total_points, counted_points, target_points, score, surplus,
                       very_easy_counted, submissions, reached_at, updated_at)          PK(user_id, meeting_id)
drafts                (user_id, meeting_problem_id, source, updated_at)                 PK(user_id, meeting_problem_id)
integrity_events      (id, user_id, kind, meeting_problem_id NULL, detail JSON, ip, user_agent, created_at)
ui_prefs              (user_id PK, panels JSON, updated_at)
```

- A meeting's problems reach students only when `published_at` is set and `enabled = 1`. Since stage 9b a student's home page and `GET /latihan/{meeting}` also show the number and title of the other practice meetings (`enabled = 1`) of their own course, with the reason they cannot be worked on yet — not published, or not opened for their class — and nothing else of them; a meeting of another course stays a `404` indistinguishable from a missing one. A window is **open** when `override = 'open'`, or `override = 'none'` and `open_at <= now < close_at`; `override = 'closed'` closes it whatever the times say. A class without a window row is closed. The class must belong to the meeting's course (composite foreign keys).
- Since stage 9b a window also has `late_until` (nullable) and a submission has `late_factor` (nullable) and `practice` (0/1). What a student may submit is decided in one place, `App\Libraries\Ruang::jenis`, when the submission is created:
  - **normal** — the window is open (`late = 0`, `practice = 0`);
  - **late** — the *scheduled* `close_at` has passed, `override = 'none'`, `late_policy = 'multiplier'`, and `late_until` is empty or still ahead: `late = 1` and `late_factor` is copied from the window at that moment; the submission's score is `points × passed / total × late_factor` (after the `CONCEPT_NOT_MET` halving). Changing the window later never changes an existing submission. `override = 'closed'` ends the late period; `override = 'open'` is an open window, not late;
  - **practice** — closed, not late, and `meetings.practice_after_close = 1`: `practice = 1`. The submission is judged and its result is shown to its student, but it is excluded from `problem_scores`, `meeting_scores`, attempt counts, the leaderboard and every analytic. Practice makes the problems visible again even when `post_close_visibility = 'hidden'`;
  - otherwise the submission is refused. A student who is not `AKTIF`, an archived academic year and an unpublished meeting never submit anything.
- `late_policy = 'reject'` stores `late_until = NULL`. A submission has `late = 1` exactly when `late_factor` is set, and is never both late and practice (check constraints).
- **Exam meetings** (`SPEC.md` §6; migration `2026-11-23-000008`): `meetings.kind` is `'latihan'` (default) or `'ujian'`; `meeting_class_windows.scores_released_at` / `scores_released_by` are `NULL` until the scores of that class are released. For `kind = 'ujian'`:
  - `App\Libraries\Ruang::jenis` gives only **normal** while the window is open — never late, never practice — and `Ruang::mode` gives `nilai` (problems hidden) whenever the window is not open; `late_policy`, `late_until`, `post_close_visibility` and `practice_after_close` are not consulted.
  - `meetings.target_points` always equals the points of the approved problems attached (`App\Libraries\Latihan::selaraskanTargetUjian`, called when the type changes, when a problem is approved, and on publishing); `App\Libraries\Penilaian` counts every problem and stores `surplus = 0`, `very_easy_counted = 0`.
  - A student may see scores of a meeting when it is a practice meeting, or when `scores_released_at` is set on the window of a class they are enrolled in. That rule is written once in SQL (`Latihan::sqlNilaiTampak`) and once in PHP (`Ruang::nilaiTampak`). While it is false: `GET /api/executions/{id}` of a `SUBMIT` answers `result: null`, no `meeting`, and `submission` reduced to `{ id, status, verdict, points, compile_log, rahasia: true }` with `passed`, `total`, `score`, `duration_ms` `null`, `tests` and `missing` empty, where `verdict` is `CE` (with the compiler log), `TERKIRIM` (judged, anything else), `FAILED` (judge failure) or `null` (still waiting); the history endpoints answer the same; no page shows a score; `GET /latihan/{meeting}/papan` is `404` for an exam always.
  - Staff routes: `POST /kelas/{class}/pertemuan/{meeting}/buka-selama` (field `menit`, 10–600; whoever may see the class) sets `open_at = now`, `close_at = now + menit`, `override = 'none'`, `late_policy = 'reject'`, `post_close_visibility = 'hidden'`, audit `jendela.dibukaSelama`; `POST /kelas/{class}/pertemuan/{meeting}/rilis` (field `rilis` `1` or `0`; superadmin and the pengampu only, a pengajar gets `403`) sets or clears the release, audit `ujian.nilaiDirilis` / `ujian.rilisDitarik`. Both answer `409` for a practice meeting. The schedule form `POST /kelas/{class}/pertemuan/{meeting}` answers `409` for an exam.
  - `POST /api/integritas` answers `404` unless the problem page itself can be opened now (window open), for every meeting type — it must not reveal that a problem id exists.
- `problem_scores` and `meeting_scores` are derived from `submissions` and are always recomputed in full for one student and one meeting; they are never adjusted incrementally. `points` is 3 / 5 / 8 / 10 / 15 by `difficulty` of the version the student's instance was made from.
- `submissions.tests` is the worker's per-test list `{ index, verdict, duration_ms }`; nothing in these tables holds test input or expected output.
- `integrity_events.kind` and what writes each kind: §17.
- The judge worker has no right on any of these tables.
- **Leaderboard** (`App\Libraries\Papan`): per meeting per class, and per meeting per course within one academic year; only `AKTIF` enrollments that have a `meeting_scores` row; order `score` desc, `surplus` desc, `reached_at` asc, `submissions` asc, equal on all four → equal rank. The ordered rows (user ids and numbers, never names) are cached in Redis DB 0 under `lokakode:papan:<meeting>:kelas:<class>` and `lokakode:papan:<meeting>:tahun:<year>` for at most 300 s; the keys of a meeting are deleted whenever a score of that meeting is recomputed, and all of them whenever an enrollment changes (enrol, withdraw, graduate, class move). Without Redis the board is computed from MySQL.
- Analytics and exports store nothing: they are computed from `enrollments`, `meeting_scores`, `problem_scores` and `submissions` as they are now, so a class move needs no recompute.
- An instance for a student is requested with one `GENERATE` job whose `idempotency_key` is `gen:<meeting_problem_id>:<user_id>`; pre-generation uses the `low` queue, a student who opens a problem without an instance the `normal` queue.
- Two things the web app does to an existing `GENERATE` row (`App\Libraries\Antrean`): a `FAILED` one is put back to `QUEUED` in place, with `attempts = 0` and the current payload (`ulangi`); a `QUEUED` one on `low` whose student is now waiting gets `priority = 'normal'` and its id is pushed to the normal queue as well (`dahulukan`). The id may then sit in both queues; the conditional claim runs it once.

Every `user_id`, `owner_id`, `actor_id` and similar column in these tables is `users.id` of §3. An enrollment is one row per student per course per academic year; a class move changes `class_id` on that row, so anything that later points at an enrollment follows the student.

## 7. Problem import format

One JSON file per meeting.

```json
{
  "course": "DDP101",
  "meeting": 4,
  "language": "cpp",
  "problems": [
    {
      "slug": "genap-ganjil",
      "difficulty": "mudah",
      "comparison_mode": "trimmed",
      "time_limit_ms": 2000,
      "samples_count": 3,
      "hidden_count": 12,
      "required_concepts_in_submission": [],
      "params": { "batas": 1000 },
      "translations": {
        "id": { "title": "", "statement": "… sampai {{batas}} …", "input_format": "", "output_format": "", "constraints": "" },
        "en": { "title": "", "statement": "", "machine": true }, "ar": { }
      },
      "generator": "…python source…",
      "reference_solution": "…source in the meeting language…"
    }
  ]
}
```

| Field | Rule |
|---|---|
| `course` | Exact course code. Not the course name |
| `meeting` | Integer 1–16 |
| `language` | `c`, `cpp`, `python`; must equal the meeting's language |
| `slug` | `[a-z0-9-]{3,80}`, unique within the meeting |
| `difficulty` | `mudah_sekali`, `mudah`, `mudah_sedang`, `sedang`, `sulit` |
| `comparison_mode` | `exact`, `trimmed`, `token`, `float`; default `trimmed` |
| `time_limit_ms` | Integer 100–10000; default 2000 for `c` and `cpp`, 5000 for `python` |
| `samples_count` | Integer 2–3 |
| `hidden_count` | Integer 10–50 |
| `required_concepts_in_submission` | Array of concept codes valid for the meeting's language; default `[]` |
| `params` | Optional object, name `[a-z_][a-z0-9_]*` → text or number. `{{name}}` in any translated text is replaced by the value, the same for every student; a `{{name}}` without a value in `params` is refused |
| `translations.id` | Required: `title` (at most 190 characters) and `statement` non-empty; `input_format`, `output_format`, `constraints` text, may be empty. Texts are Markdown; raw HTML is shown as text, never executed |
| `translations.en`, `.ar` | Optional. `{}` or absent means not translated. `"machine": true` marks a machine translation (badge until the pengampu clears it) |
| `reference_solution` | Source in the meeting's language, at most 256 KB |
| `generator` | Python; reads env `SEED` (int) and `INDEX` (0 … samples+hidden−1); prints one input; deterministic |

A file whose top level is a one-element array wrapping this object is accepted and unwrapped.

Rules of an import (`SPEC.md` §8.3):

- At most 8 MB and 200 problems per file. Unknown keys are refused, at the top level and inside a problem: a misspelt key must not be silently ignored.
- A mistake above the problems (not JSON, wrong `course`, `meeting` or `language`, unknown key) refuses the whole file; nothing is validated or stored. A mistake inside one problem refuses that problem only.
- Every problem that passes this check is sent to the judge as one `VALIDATE` job (§6) with the meeting's allowed concepts, and is stored only when the judge answers `ok`. It is stored as a new `problem_versions` row with status `PENDING_REVIEW`. These jobs have priority `low` and enter at the front of that queue (§6): an import waits while students are being judged.
- Idempotent by `slug` within the meeting: a problem whose content equals the latest stored version (any status) is reported "unchanged" and creates nothing; changed content creates the next version. The meeting keeps using its approved version until the new one is approved.
- The report names, per problem: accepted, rejected or unchanged; for each reason the part (`generator`, reference solution, or the field), line and column inside that source, and the line of that key in the JSON file.
- Ways in: `php spark soal:impor [file] --oleh <nomor induk of an active superadmin>` (without a file argument it reads the file from standard input; exit code 0 only when the file is accepted and no problem is rejected), the web upload at `POST /impor` (superadmin only, field `berkas`, `.json`), and the edit form of one problem (superadmin and the pengampu), which sends that one problem through the same check and validation. `php spark soal:ekspor <course> <meeting>` prints the latest version of every problem of a meeting in this format; importing that output changes nothing.
- Approval is the web action *Setujui soal ini* (superadmin and the pengampu) or `php spark soal:setujui <course> <meeting> --oleh <nomor induk> [--slug a,b]`, which approves the latest version of every problem of that meeting that is `PENDING_REVIEW`, as that active superadmin or as the pengampu of the course. Both go through `App\Libraries\Soal\Tinjau::setujui`; the command's audit rows carry `lewat: cli`.
- An import is finished by the request that next looks at it (`GET /impor/{id}`, or the CLI's own loop); there is no background process on the web side. Until then nothing reaches students, because a new version always awaits review.
- Every import writes one row to LokaKode's `audit_log`: `soal.impor` (or `soal.impor.ditolak`, `soal.disunting`) with the actor, file name, source and the counts.

## 8. Safe Exam Browser verification

There is **one implementation**, in `packages/lokandita-auth` (namespace `Lokandita\Auth\Seb`), used by every app that requires SEB. What an app supplies is listed under "Shared package and what each app supplies" below. Everything else in this section is the same for every such app.

**LokaKode uses all of it.** **LokaUjian (since stage U4) uses the verification only** — the two header formulas, the settings and `.seb` file builder, the Config Key, the encryption, the lease and the heartbeat — because its configurations belong to an exam, not to a course (the owner's decision of 2026-11-27). Which configuration counts, the tables, the panel, the download and the routes of LokaUjian are §23; the paragraphs below that speak of courses ("Configuration and keys", "Routes", the `Sumber` table) describe LokaKode.

Applies to requests of a user whose global role is `mahasiswa`, after §4 has identified the user: filter `seb` (`Lokandita\Auth\Filters\WajibSeb`) always follows filter `masuk`. Staff are never checked. The User-Agent is never read.

For each such request, with `url` = `APP_URL` origin + the raw path and query of the request line (no fragment; the client's `Host` header is not trusted):

```
X-SafeExamBrowser-ConfigKeyHash  ==  hex( SHA256( url + config_key ) )
X-SafeExamBrowser-RequestHash    ==  hex( SHA256( url + browser_exam_key ) )   for any registered key of the course
```

Both must match **for the same configuration**. The configurations that count for a student are the current ones of the courses in which the student has an `AKTIF` enrollment in the active academic year; a student none of whose courses has a configuration is refused.

`config_key` is computed by the server from the generated configuration exactly as defined by the SEB developer documentation; the `.seb` file is the same configuration, so the two cannot drift. Configurations are stored encrypted with `SEB_ENCRYPTION_KEY`.

### Configuration and keys

```
seb_configs    (id, course_id, version, config_enc MEDIUMBLOB, config_key CHAR(64), created_by, created_at, retired_at NULL)
               UNIQUE(course_id, version); at most one row per course with retired_at IS NULL — the current one
seb_exam_keys  (id, seb_config_id, exam_key CHAR(64), created_by, created_at)      UNIQUE(seb_config_id, exam_key)
```

- One settings array (`Lokandita\Auth\Seb\Setelan::bangun`) is written twice by `Lokandita\Auth\Seb\Plist`: as the XML plist of the file, and as the "SEB-JSON" whose SHA-256 is the Config Key. `config_enc` is that XML, encrypted (libsodium secretbox, key = SHA-256 of `SEB_ENCRYPTION_KEY`, stored as nonce + ciphertext).
- SEB-JSON follows SEB for Windows 3.10.2 (`Json.cs`): keys sorted without regard to case (lower case first on a tie), no whitespace, no escaping, binary data as quoted Base64, `originatorVersion` left out. The generated settings hold only booleans, integers, ASCII strings, one data value, arrays and dictionaries.
- The **Browser Exam Key cannot be computed by the server**: since SEB 3.4 it comes from the client's own integrity module. The pengampu reads it from the SEB Configuration Tool of the client version in use and registers it (lower-case hex, 64 characters, at most 20 per configuration). Keys belong to one configuration row.
- **Rotation** retires the current row and inserts the next `version` with a new `examKeySalt`; the old file and every key registered for it stop matching at once.
- The quit password (12–200 characters) is never stored; the configuration holds its SHA-256 as `hashedQuitPassword`.
- The App Signature Key of SEB 3.5+ is **not** checked: it is exchanged only with SEB Server.

Settings written (key names of SEB for Windows 3.10.2): `startURL` = `APP_URL/beranda`; `quitURL` = `APP_URL/seb/keluar` with `quitURLConfirm`; `allowQuit`, `hashedQuitPassword`; `sendBrowserExamKey` (switches on both headers), `examKeySalt`; `createNewDesktop` = true, `killExplorerShell` = false; `monitorProcesses` = true, `permittedProcesses` = empty, `prohibitedProcesses` (other browsers, remote-control tools, screen recorders, screen-sharing and chat applications; each `os` = 1, `active`, `strongKill`; no system process); `allowVirtualMachine`, `enablePrintScreen`, `allowScreenCapture`, `allowScreenSharing` = false; `allowedDisplaysMaxNumber` = 1; `URLFilterEnable` with one allow rule per origin of `APP_URL`, `AUTH_PUBLIC_URL` and `PORTAL_URL` (exact host, `https://.<host>`); `allowDeveloperConsole`, `allowDownloads`, `allowUploads`, `allowSpellCheck`, `allowPDFReaderToolbar`, `allowApplicationLog` = false; `clipboardPolicy` = 2 (isolated); cookies cleared at start and end. The allowed-URL list is never taken from a form.

The `.seb` file is `gzip( "plnd" + gzip( plist XML ) )`: not password-protected, so a double click opens it. Its download name is `<app name>-<course code>.seb` (`LokaKode-DDP101.seb`).

### Lease, heartbeat and refusal

- A request that passes sets the Redis key `<app>:seb:sewa:<user id>:<SHA-256 of the session cookie>` for 30 s, in the app's own Redis DB (`<app>` is `LOKANDITA_APP`; for LokaKode `lokakode:seb:sewa:…`, DB 0), and the page loads `/js/seb.js`, which sends `POST /api/seb/denyut` every 25 s. The heartbeat is an ordinary checked request; it answers `{ "ok": true, "sewa": 30 }`.
- A request that fails is answered `403`: for a path under `/api/` as `{ "error": "seb_wajib", "message": … }`, otherwise as a page that names the reason (`tanpa_header`, `tanpa_konfigurasi`, `kunci_konfigurasi`, `kunci_peramban`) and links to `/seb`. On a refused heartbeat the page reloads itself and so shows that page: a lost or invalid header ends the session in that app within 30 s.
- Nothing is revoked in LokaAuth: the student's session stays alive, and the app simply answers `403` until the headers match again. In LokaKode, drafts and accepted submissions are untouched.
- Integrity events (`integrity_events.kind`): `seb_terminated` when the refused request still held a lease (it was inside a valid SEB a moment ago; always recorded, lease deleted), otherwise `seb_rejected` (at most one per user per minute; Redis key `<app>:laju:seb:<user id>`). `detail` is `{ alasan, jalur }`. The row is written by the app (`Sumber::catatPeristiwa`) into its own `integrity_events`.
- Without Redis the check itself still works; only the lease, and with it the difference between the two kinds, is lost (`seb.sewa_gagal` in the log).

### `SEB_ENFORCEMENT`

`enabled` or `disabled`; any other value stops the container at start. With `disabled` the filter checks nothing and every page of a mahasiswa carries the red banner "MODE UJI: penegakan SEB dinonaktifkan" (`Lokandita\Auth\Sisipan`, drawn by the shared layout); the container logs that state at start. Configurations, keys, the download and `/seb/periksa` work in both modes.

### Routes

| Route | Who | Does |
|---|---|---|
| `GET /seb` | public | instructions; lists the courses that have a configuration; states the supported client (SEB 3.10.x, Windows x64) |
| `GET /seb/unduh/<course code>.seb` | public | the current file of that course; `404` when there is none |
| `GET /seb/periksa` | public, `noindex` | whether each header arrived and, per configured course, whether it matches; never shows a key |
| `GET /seb/keluar` | public | the `quitURL`: SEB closes when it is opened; an ordinary browser gets a short explanation |
| `GET /mata-kuliah/{id}/seb` | superadmin, pengampu of the course | the panel |
| `POST /mata-kuliah/{id}/seb` | same | field `sandi_keluar`: create, or rotate |
| `POST /mata-kuliah/{id}/seb/kunci` | same | field `kunci`: Browser Exam Keys, one per line, replacing the list |
| `POST /api/seb/denyut` | mahasiswa | heartbeat |

Filter `seb` is on every route a mahasiswa can open **except** `GET /data-saya` and `POST /data-saya/unduh` (§14): the right to one's own data does not depend on SEB, and the SEB configuration forbids downloads. The account routes of §4 (`/keluar`, `/sesi/perpanjang`, `/preferensi`) are not checked either, so logging out always works.

Audit (the app's own `audit_log`, through `Sumber::catatAudit`, in the transaction of the change): `seb.dibuat`, `seb.dirotasi` (`{ versi }`), `seb.kunci` (`{ versi, jumlah }`) — never a key or a password.

### Shared package and what each app supplies

In `packages/lokandita-auth` (moved out of LokaKode in LokaUjian stage U0, behaviour unchanged):

| Part | Does |
|---|---|
| `Lokandita\Auth\Seb\Seb` | `diberlakukan()`, `asal()`, `url()`, `kandidat($userId)`, `semuaAktif()`, `periksa($request, $kandidat)`, `perpanjangSewa()`, `akhiriSewa()`; the configuration store `aktif()`, `buat()`, `daftarkanKunci()`, `kunciTerdaftar()`, `berkas()`; constants for headers, lease, heartbeat, minimum quit password |
| `Seb\Setelan`, `Seb\Plist`, `Seb\Brankas` | the settings array; XML plist, SEB-JSON and Config Key; encryption with `SEB_ENCRYPTION_KEY` |
| `Filters\WajibSeb` | the filter; the app gives it the alias `seb` |
| `Controllers\Seb`, `Controllers\SebKelola`, `Routes::seb()` | every route of the table above, with the same paths and filters in every app |
| views `seb_*`, language file `Seb` (`id`, `en`, `ar`; `{0}` is the app's name), `js/seb.js`, `js/seb-periksa.js` | pages, texts, heartbeat script. The image build copies `js/` to `public/js/` of every app |

Supplied by the app:

- `app/Config/Seb.php` with `public string $sumber`: the class that implements `Lokandita\Auth\Seb\Sumber`. The package never reads a course, class or enrollment itself.

| Method of `Sumber` | Returns or does |
|---|---|
| `matkulMahasiswa(string $userId): list<int>` | course ids whose configuration counts for this student: `AKTIF` enrollment in the active academic year |
| `matkul(list<int> $ids): list<{id, code, name}>` | code and name of courses, **ordered by code by the database** |
| `matkulDariKode(string $kode): ?{id, code, name}` | the course of a code, for the download |
| `matkulUntukPanel(Identity $siapa, int $id): ?{id, code, name, boleh_kelola}` | `null` = does not exist or outside what this user may see (`404`); `boleh_kelola` false = visible but not its manager (`403`); true = superadmin or the pengampu |
| `navigasi(Identity $siapa): string` | the app's navigation HTML for the panel page |
| `redis(): \Redis` | the app's Redis connection, for the lease and the rate limit |
| `catatPeristiwa(string $userId, string $jenis, array $detail, RequestInterface $request): void` | writes `seb_rejected` / `seb_terminated` with IP and user agent |
| `catatAudit(string $aksi, int $matkulId, array $detail): void` | writes the audit rows above |

- Tables `seb_configs` and `seb_exam_keys` in the app's own schema, created by the app's own migration and read by the package through the app's default connection. A configuration belongs to one app: `startURL`, `quitURL` and the URL filter come from that app's `APP_URL`.
- `SEB_ENCRYPTION_KEY` and `SEB_ENFORCEMENT`, one value per app in its own `.env`.
- Language file `SebApl` with the three sentences that differ per app: `tersimpan` (refusal page: what stays saved), `selesai` (last step of the instructions), `putus` (what happens when SEB closes or the connection drops).
- Which routes carry filter `seb`.

LokaKode's implementation is `App\Libraries\SumberSeb`, which reads LokaKode's own tables.

## 9. Docker networks and names

| Network | Members | Purpose |
|---|---|---|
| `edge` | caddy, auth, lokakode, portal, lokaujian | Public HTTP from Caddy to apps |
| `internal` | auth (alias `auth-internal`), lokakode, portal, lokaujian | App-to-LokaAuth calls |
| `data` | auth, lokakode, portal, lokaujian, lokaujian-jadwal, mysql, redis | Database and cache |

- Container names: `caddy`, `mysql`, `redis`, `auth`, `lokakode`, `portal`, `lokaujian`, `lokaujian-jadwal`.
- Every app container serves HTTP on port **8080**. No app publishes a port to the host.
- `lokaujian-jadwal` (since stage U3) is the scheduler of §21: the image `lokandita/lokaujian:aktif`, the same `.env` as `lokaujian`, on `data` only, no HTTP, no volume.
- Caddy reaches apps by container name on `edge`. Apps reach LokaAuth **only** by `auth-internal` on `internal`.
- MySQL `3306` and Redis `6379` are published on `127.0.0.1` only, for the worker and for CLI use.
- MySQL runs with `max_connections = 500` (`infra/compose.yml`; since LokaUjian stage U6). The default of 151 is not enough: every app container has up to 150 Apache workers and one request of a client app holds up to three connections (its own schema, `identity_reader`, and for LokaUjian `roster_reader`). `infra/smoke.sh` fails when the running server allows fewer than 500.
- Named volumes (Compose project `lokandita`): `lokandita_mysql_data`, `lokandita_redis_data`, `lokandita_caddy_data`, `lokandita_caddy_config`, `lokandita_portal_media` (the Portal's uploaded hero photos, §16), and `lokandita_lokaujian_media` (Compose name `lokaujian_media`; LokaUjian's question pictures, §20, mounted at `/srv/media` in `lokaujian` only).
- LokaAuth must not decide trust from the caller's IP or subnet.
- The `internal` network is created with `internal: true`: it has no route out of the host.
- Compose project name is `lokandita`; the file is `infra/compose.yml`. App images are `lokandita/<app>:<commit>`, and the one in use also carries the tag `aktif`.

### Edge (Caddy)

- Public hosts are derived from `DOMAIN`: `DOMAIN` → `portal`, `www.DOMAIN` → 301 to `DOMAIN`, `auth.DOMAIN` → `auth`, `lokakode.DOMAIN` → `lokakode`, `lokaujian.DOMAIN` → `lokaujian`.
- `/internal` and `/internal/*` are answered `404` by Caddy on all five hosts before anything else, whether the host is open or in maintenance.
- A host is in **maintenance** unless the file `infra/caddy/bendera/<app>.buka` exists (`infra/pemeliharaan.sh <auth|lokakode|portal|lokaujian> on|off`, `status`). The flag is server state and is git-ignored. A fresh checkout is therefore fully in maintenance.
- Maintenance answers every path with `503`, `Retry-After: 3600` and the static page in `infra/caddy/situs/`; its assets live under `/_pemeliharaan/`.
- Caddy sets `Strict-Transport-Security` on every response. All other security headers are set by Caddy only on the maintenance page; on an open host they are the app's job (`SPEC.md` §16).

## 10. Environment variables

One `.env` per component, in the component's own directory, git-ignored, mode 600. The same file is used at build time and at run time. A committed `.env.example` lists every key. `infra/cek-env.sh` fails when `.env` lacks a key present in `.env.example`.

Each app validates its required variables at start and exits naming the missing one.

| Variable | auth | lokakode | portal | lokaujian | judge |
|---|---|---|---|---|---|
| `APP_URL` | ✔ | ✔ | ✔ | ✔ | |
| `CONTACT_EMAIL` | ✔ | ✔ | ✔ | ✔ | |
| `COOKIE_DOMAIN` (`.lokandita.com`) | ✔ | ✔ | ✔ | ✔ | |
| `ALLOWED_HOSTS` (comma list; holds the host of every app) | ✔ | | | | |
| `PORTAL_URL` | ✔ | ✔ | ✔ | ✔ | |
| `LOKAKODE_URL` (address of LokaKode for links from the Portal) | | | ✔ | | |
| `LOKAUJIAN_URL` (address of LokaUjian for links from the Portal; since LokaUjian stage U6) | | | ✔ | | |
| `DB_DSN` (own schema) | ✔ | ✔ | ✔ | ✔ | ✔ |
| `IDENTITY_READER_DSN` | | ✔ | ✔ | ✔ | |
| `ROSTER_READER_DSN` (§18) | | | | ✔ | |
| `REDIS_URL` | ✔ | ✔ | ✔ | ✔ | ✔ |
| `JUDGE_QUEUE_REDIS_URL` (the Redis DB that holds the judge queues, §6; read-only use) | | | | ✔ (since U6) | |
| `AUTH_PUBLIC_URL`, `AUTH_LOGIN_URL` | | ✔ | ✔ | ✔ | |
| `AUTH_INTERNAL_URL` (`http://auth-internal:8080`) | | ✔ | ✔ | ✔ | |
| `AUTH_SERVICE_TOKEN` | | ✔ | ✔ | ✔ | |
| `SERVICE_TOKEN_PORTAL`, `SERVICE_TOKEN_LOKAKODE`, `SERVICE_TOKEN_LOKAUJIAN` | ✔ | | | | |
| `MFA_ENCRYPTION_KEY` | ✔ | | | | |
| `INSTANCE_SECRET` | | ✔ | | | |
| `SEB_ENCRYPTION_KEY` | | ✔ | | ✔ (since U4) | |
| `SEB_ENFORCEMENT` | | ✔ | | ✔ (since U3) | |
| `JUDGE_CONCURRENCY` | | | | | ✔ |

LokaUjian has `SEB_ENFORCEMENT` since stage U3 and its own `SEB_ENCRYPTION_KEY` since U4 (§23): a secret of LokaUjian alone, different from LokaKode's, which must not change while configurations exist and is included in every backup. `lokaujian-jadwal` uses `apps/lokaujian/.env`, the same file as `lokaujian`; its entrypoint runs the same check of required variables and exits with code 78 when one is missing.

`infra/.env` (Caddy, MySQL, Redis, backup):

| Variable | Used for |
|---|---|
| `DOMAIN` | Public host names (§9) |
| `CONTACT_EMAIL` | Shown on the maintenance page |
| `ACME_EMAIL`, `ACME_CA` | Let's Encrypt account address and directory URL |
| `MYSQL_ROOT_PASSWORD` | MySQL root; used only by `infra/` scripts through `docker exec` |
| `DB_PASSWORD_AUTH`, `DB_PASSWORD_LOKAKODE`, `DB_PASSWORD_PORTAL`, `DB_PASSWORD_LOKAUJIAN`, `DB_PASSWORD_IDENTITY_READER`, `DB_PASSWORD_JUDGE`, `DB_PASSWORD_ROSTER_READER` | Passwords of the MySQL users below; each must equal the password inside the matching DSN |
| `REDIS_PASSWORD` | Redis `requirepass`; must equal the password inside every `REDIS_URL` |
| `BACKUP_DIR`, `BACKUP_RETENTION_DAYS` | `infra/backup.sh` |

Formats:

- `DB_DSN`, `IDENTITY_READER_DSN`, `ROSTER_READER_DSN`: `MySQLi://<user>:<password>@mysql:3306/<schema>`. Apps parse it with `Lokandita\Auth\Dsn`, never through CI4's own DSN parser.
- `REDIS_URL`: `redis://:<password>@redis:6379/<db>`. Redis DB `0` = lokakode (and the judge queue, §6), `1` = auth, `2` = portal, `3` = lokaujian, `15` = tests (§12).
- `JUDGE_QUEUE_REDIS_URL` (LokaUjian only): the same form, with the DB of the judge queues — `0` in production. `infra/tes.sh` rewrites it to DB `15` like `REDIS_URL` and refuses anything else, so a test never reads the production queue.
- Secrets are lowercase hex (`openssl rand -hex 32`), so they are safe inside a URL without escaping.

MySQL schemas and users, created by `infra/mysql-init.sh` (idempotent, never touches tables):

| User | Rights |
|---|---|
| `auth_app` | all on `identity`, `identity_test` |
| `lokakode_app` | all on `lokakode`, `lokakode_test` |
| `portal_app` | all on `portal`, `portal_test` |
| `lokaujian_app` | all on `lokaujian`, `lokaujian_test`; nothing on any other schema |
| `identity_reader` | none from `mysql-init.sh`; `SELECT` on the three objects of §3 from `infra/mysql-hak-reader.sh` |
| `judge_worker` | none from `mysql-init.sh`; `SELECT, UPDATE` on `judge_jobs` from `infra/mysql-hak-judge.sh` (§6) |
| `roster_reader` | none from `mysql-init.sh`; `SELECT` on the five views of §18 from `infra/mysql-hak-roster.sh` |

Rules:

- **`.env.example` is the list of required variables.** The container entrypoint reads the app's `.env.example` and refuses to start, naming the variable, when any key in it is missing or empty in the environment. Adding a key to `.env.example` therefore adds it to startup validation.
- The `.env` file reaches the container through `env_file` in Compose; it is never copied into an image.
- In `.env.example`, `{{NAME}}` marks a secret. `infra/buat-env.sh` creates all `.env` files once on a new server and refuses to run if any exists. `infra/cek-env.sh` also fails on an empty value, a leftover `{{…}}`, a quoted value, or a file mode other than 600.
- `LOKANDITA_APP` and `LOKANDITA_COMMIT` are baked into the image by `infra/deploy.sh`. They are not `.env` variables.
- `judge/.env` holds `DB_DSN` (user `judge_worker`), `REDIS_URL` (DB `0`) and `JUDGE_CONCURRENCY` (1–32). The worker runs on the host, so both addresses use `127.0.0.1` instead of the container names. The worker reads `judge/.env.example` as its list of required variables and refuses to start, naming the variable, when one is missing or empty.

`SEB_ENFORCEMENT` is `enabled` or `disabled` (§8); `packages/lokandita-auth/bin/cek-env.php` refuses any other value at container start and logs which of the two is in force. `SEB_ENCRYPTION_KEY` must not change while configurations exist: a stored configuration could no longer be opened and every course would need a rotation.

`INSTANCE_SECRET` must never change once students have instances. It and `SEB_ENCRYPTION_KEY` are included in every backup.

`ROSTER_READER_DSN` is `MySQLi://roster_reader:<password>@mysql:3306/<schema that holds the roster views>`; today that schema is `lokakode`. The schema name appears nowhere in a reader's code, which connects through `Lokandita\Auth\RosterDb` and names only the views.

Values are written without quotes.

## 11. Deploy and verification

- `infra/deploy.sh <auth|lokakode|portal|lokaujian>` builds the image tagged with the current Git commit, runs the app's migrations from that image (and for `auth` the reader grants of §3), starts it, and fails unless the running container reports that commit at `GET /healthz` (`{ "app": "...", "commit": "..." }`).
- `infra/deploy.sh lokaujian` also recreates `lokaujian-jadwal` from the new image and fails unless the scheduler's heartbeat (§21) carries the deployed commit **and was written by the new container** (`nama` begins with its host name) — a heartbeat outlives its writer by up to 60 seconds, so on a redeploy of the same commit the commit alone proves nothing. It waits up to 90 seconds, because a new scheduler watches an old heartbeat for up to 25 seconds before taking over.
- `infra/deploy.sh infra` starts MySQL, Redis and Caddy, runs `infra/mysql-init.sh`, and reloads the Caddyfile.
- `infra/deploy.sh lokakode` runs `infra/mysql-hak-judge.sh` and `infra/mysql-hak-roster.sh` after LokaKode's migrations, because `judge_jobs` and the roster views (§18) live in LokaKode's schema. `roster_reader` must exist first (`infra/deploy.sh infra`).
- `deploy.sh` refuses to run when the working tree has uncommitted or untracked changes, runs `infra/cek-env.sh` first, and warns when the commit is not on `origin`.
- `/healthz` is served by every app container on port 8080 with `Cache-Control: no-store`. `deploy.sh` reads it from inside the container, so it works while the host is in maintenance.
- `infra/smoke.sh` checks, with real requests:
  1. each public host answers over HTTPS;
  2. `/internal/anything` returns 404 from the internet on every host;
  3. from inside `lokakode`, `portal` and `lokaujian`, `POST auth-internal:8080/internal/session/touch` with the app's token and a bogus `sid` returns **422 or 404-with-JSON, not 401** — proving network, name and token are right;
  4. the same call with a wrong token returns 401;
  5. `identity_reader` can select from `session_identity` and cannot select from `users`, asked through `IDENTITY_READER_DSN` of `lokakode` and of `lokaujian`;
  6. the judge worker is running and a trivial `RUN` job completes.
  7. the judge validates a small built-in problem: one `VALIDATE` job through LokaKode (`php spark judge:coba --validasi`) answers `ok` after 240 tests; nothing is stored in the problem bank.
  8. public pages and the way in (stage 11), asked directly at the app container from another app container, with the `X-Forwarded-Proto: https` header Caddy sends, so that it also works while a host is in maintenance: `GET /` of `portal` and of `lokakode` without a cookie answers `200` with an indexable page; `GET /beranda` of both without a cookie redirects to `AUTH_LOGIN_URL` with `next` equal to that app's own `/beranda`; `GET /privasi` of `portal` answers `200`.
  9. the Portal's hero photo (stage 11c, §16), asked the same way: the first AVIF file named by the landing page answers `200 image/avif`, and `/media/hero/smoke.php` answers `403`.
  10. Safe Exam Browser (stage 10a, §8), asked the same way: `GET /seb` of `lokakode` answers `200` and names the state of enforcement that the running container has; `GET /seb/periksa` answers `200` and reports no SEB header; `SEB_ENFORCEMENT` in the running container equals the value in `apps/lokakode/.env`. `disabled` passes, with a warning line that says students are not required to use SEB.
  11. integrity and communication (stage 10b, §17): the `lokakode` schema has the tables `notifications`, `clarifications` and `announcements`; asked as in check 8, `GET /notifikasi`, `/pengumuman`, `/kelas/1/integritas`, `/kelas/1/klarifikasi` and `/kelas/1/pengumuman` of `lokakode` without a cookie each redirect to `AUTH_LOGIN_URL` with `next` equal to that address (the route exists in the running code); `GET /ketentuan` of `portal` in Indonesian holds the sentence on unusual typing — it fails when a text override (§15) of that page lacks the sentence. Since LokaUjian stage U6 also: `GET /privasi` of `portal` in Indonesian names LokaUjian and says that exam answers are not deleted automatically (`SPEC-LOKAUJIAN.md` §13).
  12. brand (§16 "Brand files"), asked as in check 8: `auth`, `portal`, `lokakode` and `lokaujian` each answer `200` for `/favicon.ico`, `/merek/icon-{16,32,180,192,512}.png`, `/merek/logo-{terang,gelap}.webp` and a `/merek/site.webmanifest` that is JSON with a name and two icons; `GET /` of `portal` carries the six app cards in the order of §16, and — since LokaUjian stage U6 — the card of `lokaujian` is a link to the host of LokaUjian; no public page of `portal` and not the landing of `lokakode` contains "LokaAuth" — it fails when a text override (§15) brings the name back.
  13. roster views (§18), through `ROSTER_READER_DSN` from inside `lokaujian`: the user is `roster_reader`; `SELECT COUNT(*)` works on each of the five views; `SELECT` on `enrollments` and on `submissions` and `INSERT` into `roster_classes` are each refused with error 1142; the count of each view equals the count of its table, asked through `lokakode`. Only counts are printed.
  14. the way into LokaUjian (stage U1, §19), asked as in check 8 from inside `portal`: `GET /` of `lokaujian` without a cookie answers `200` with `noindex, nofollow` and a link to `/beranda`; `GET /beranda`, `/mata-kuliah`, `/mata-kuliah/1` and `/kelas/1` without a cookie each redirect to `AUTH_LOGIN_URL` with `next` equal to that address.
  15. question pictures of LokaUjian (stage U2, §20), asked as in check 8 from inside `portal`: `GET /media/soal/smoke.png` and `/media/soal/smoke.php` of `lokaujian` answer `403`; `GET /gambar/<32 zeros>`, `/mata-kuliah/1/bank` and `/mata-kuliah/1/ujian` without a cookie each redirect to `AUTH_LOGIN_URL` with `next` equal to that address; `/srv/media` of the running `lokaujian` container is the volume `lokandita_lokaujian_media`.
  16. the scheduler of LokaUjian (stage U3, §21): the heartbeat `lokaujian:jadwal` exists, carries the current `HEAD`, was written by the running `lokaujian-jadwal` container, and its `waktu` is younger than 60 seconds; the container is running.
  18. routes of LokaUjian (stages U3 and U5, §22, §24), asked as in check 8 from inside `portal`: `GET /mata-kuliah/1/ujian/1/sunting`, `/sesi/1`, `/sesi/1/pratinjau`, `/ujian-saya/1`, `/ujian-saya/1/kerjakan` and `/api/sesi/1/keadaan` without a cookie each redirect to `AUTH_LOGIN_URL` with `next` equal to that address; the `lokaujian` schema has the seven tables of §22. Since stage U5 also `GET /sesi/1/langsung`, `/mata-kuliah/1/ujian/1/koreksi`, `/ujian-saya/1/tinjau`, `/audit` and `/data-saya`, and the schema has `time_grants`, `attempts.score`, `attempts.reopen_until` and `sessions.release_level` (§24). 
  17. Safe Exam Browser of LokaUjian (stage U4, §23), asked as in check 8: `GET /seb` answers `200`, names the state of enforcement that the running container has and holds no link to a `.seb` file; `GET /seb/periksa` answers `200` and reports no SEB header; `GET /seb/unduh/UJI.seb` answers `404` (no public download); `GET /mata-kuliah/1/ujian/1/seb`, `/sesi/1/peristiwa`, `/ujian-saya/1/seb` and `/ujian/1/masuk` without a cookie each redirect to `AUTH_LOGIN_URL` with `next` equal to that address; the schema has `seb_configs` and `seb_exam_keys`; `SEB_ENCRYPTION_KEY` in the running container is 64 hexadecimal characters (never printed); `SEB_ENFORCEMENT` in the container equals `apps/lokaujian/.env`, and `disabled` passes with a warning line that says students are not required to use SEB.
  19. MySQL allows at least 500 connections (§9; since LokaUjian stage U6).
- `smoke.sh` additionally checks (as check 0) that every app container reports the current `HEAD` at `/healthz`.
- In check 1 a host in maintenance must answer `503`, an open host `2xx`/`3xx`, and `www` a `301` to the apex; the certificate must validate.
- Checks 3–5 become active in stage 5, check 6 in stage 7 and check 7 in stage 8, by a switch at the top of `smoke.sh`. Until then each prints `BELUM AKTIF`, is counted separately, and is not reported as passed.
- `infra/tes-infra.sh` tests §9, §10 and the deploy verification against the running containers.
- `infra/backup.sh` dumps the schemas `identity`, `lokakode`, `portal` and `lokaujian` and every `.env`; `infra/uji-restore.sh` compares the tables and views of the four schemas with the live database (`docs/RESTORE.md`). The two picture volumes are written next to the dump: `media.tar.gz` (§16) and `media-lokaujian.tar.gz` (§20). Since LokaUjian stage U6 `uji-restore.sh` also prints the number of rows of LokaUjian's tables in the restored database next to the live one, fails when a table whose rows are never deleted (`attempts`, `answers`, `integrity_events`, `images`, `audit_log`) holds more rows in the backup than live, and — when no attempt is in progress and the numbers of attempts and answers are equal — fails unless `CHECKSUM TABLE` of `answers` and `attempts` is the same in both: every answer comes back exactly.
- A stage is not finished until `smoke.sh` passes after deploy.

## 12. Tests

- `infra/tes.sh <auth|lokakode|portal|lokaujian>` is the only way to run PHPUnit. It builds `lokandita/<app>:tes`, recreates the `*_test` schemas from nothing, runs the LokaAuth migrations on `identity_test`, and refuses any schema whose name does not end in `_test`.
- A second LokaAuth named `auth-tes` runs for the duration of the tests on `identity_test` and Redis DB `15`. Its alias on the `internal` network is `auth-internal-tes`; client apps under test use `AUTH_INTERNAL_URL=http://auth-internal-tes:8080`. When a client app is tested it is also served as `<app>-tes`.
- Tests talk to these containers over real HTTP (`Lokandita\Auth\Testing\Http`) and to MySQL directly. Nothing is mocked.
- Redis DB `15` is reserved for tests and is flushed at the start of every run.
- For `lokakode`, `infra/tes.sh` serves the same image twice: `lokakode-tes` with `SEB_ENFORCEMENT=disabled` (`APP_TES_URL`; the test client is not SEB) and `lokakode-seb-tes` with `enabled` (`APP_SEB_TES_URL`), where `tests/Seb/SebTest.php` sends headers it computes itself by the formula of §8. The value in the production `.env` plays no part. No test can show that a real SEB client sends the same headers; that is the owner's gate.
- For `lokakode`, `infra/tes.sh` also starts a headless Chromium (`peramban-tes`, image pinned by digest) and a second Caddy (`caddy-tes`) that serves `https://lokakode.lokandita.com` with Caddy's internal certificate and forwards to `lokakode-tes`, as the production edge does. The browser sits only on the Docker network `tes-peramban`; `lokakode.lokandita.com` is mapped to `caddy-tes` and every other host name is made unresolvable, so a browser test cannot reach a production site. PHPUnit drives it over the Chrome DevTools Protocol (`Lokandita\Auth\Testing\Peramban` in the shared package, address in `PERAMBAN_TES_URL`); the tests in `tests/Peramban/` fail, not skip, when it is missing.
- For `portal`, `infra/tes.sh` also serves `lokakode-tes` on `lokakode_test` (the stage 11 gate needs three real apps; `LOKAKODE_TES_URL`) and starts the same browser with three host names mapped — `lokandita.com` → `portal-tes`, `lokakode.lokandita.com` → `lokakode-tes`, `auth.lokandita.com` → `auth-tes` — and every other name unresolvable. `apps/portal/tests/Peramban/ArabPublikTest.php` measures there that every public page of the three apps in Arabic is the mirror of its Indonesian version. `PembukaTest.php` measures the two landing pages in Indonesian and Arabic, light and dark, at 1280 and 390 pixels: the hero image loads as AVIF or WebP from the same origin, text never lies over it, no layout shift, WCAG AA contrast, and one size and stroke width for every icon.
- The header of the shared layout is one row at every width (owner's rule of 2026-11-27). `Lokandita\Auth\Testing\Kepala` measures it in the browser — every visible item crosses one horizontal line, no two items touch, no text wraps, nothing leaves the screen — at 360, 390, 412, 768, 1024 and 1440 pixels. `apps/portal/tests/Peramban/KepalaTest.php` applies it to the Portal's landing and `/tentang` and to LokaKode's landing in Indonesian, English and Arabic, light and dark, and to the Portal after login; it also drives the menu button that holds the links and languages below 768 pixels (open, Escape, second click, click outside, widening, a link; focus; `aria-expanded`; the panel on the end side, left in Arabic). `apps/lokaujian/tests/Peramban/KepalaDepanTest.php` does the same for LokaUjian's landing. Fonts of a real phone are not the test browser's; the owner's gate in DevTools and on a phone stays.
- With `TANGKAPAN=<host directory>` set, `infra/tes.sh` mounts that directory into the test container (`TANGKAPAN_DIR`) and the browser tests that take screenshots write them there; without it nothing is written.
- For `lokaujian`, `infra/tes.sh` also builds LokaKode's image, recreates `lokakode_test` through the LokaKode migrations (so the views of §18 exist) and runs `infra/mysql-hak-roster.sh lokakode_test`; LokaKode itself is not served. `ROSTER_READER_DSN` of the app under test points at `lokakode_test`. Roster rows are written by the test code through `TES_ROSTER_TULIS_DSN` (user `lokakode_app`), which the app under test does not use. `apps/lokaujian/tests/RosterTest.php` proves that `roster_reader` reads the five views and nothing else, cannot write, and that `lokaujian_app` cannot touch the roster schema. Since stage U1 the shared client test (`packages/lokandita-auth/tests`) runs in LokaUjian's suite as it does in LokaKode's and the Portal's; its users get access to the app under test in addition to `portal` and `lokakode`.
- Since stage U3, for `lokaujian`, `infra/tes.sh` also serves the same image a second time as `lokaujian-seb-tes` with `SEB_ENFORCEMENT=enabled` (`APP_SEB_TES_URL`; `lokaujian-tes` has `disabled`), starts the headless Chromium of the paragraph above with `lokaujian.lokandita.com` mapped to `lokaujian-tes` and every other name unresolvable, and runs `php spark ujian:jadwal --sekali` once against the test schema before PHPUnit. `apps/lokaujian/tests/Peramban/HalamanUjianTest.php` covers autosave, the countdown, resume after the browser is closed, working without a connection (network switched off through the DevTools Protocol), every paste path, copy and cut, takeover by a second page, the stop at the deadline, review and submit, and the waiting page; these tests fail, not skip, when the browser is missing. Tests that depend on time move the stored timestamps of the test schema (when an attempt started, when a session ends); the application under test keeps reading MySQL's clock. `tests/Jadwal/JadwalTest.php` runs the scheduler as a real process against Redis DB `15`. Since stage U4 `tests/Seb/SebUjianTest.php` runs against `lokaujian-seb-tes` and sends SEB headers it computes itself by the formula of §8, for the address of every request; no test can show that a real SEB client sends the same headers — that is the owner's gate. Since stage U6 `tests/Kendali/AntreanJudgeTest.php` fills the two judge queues in Redis DB `15` and reads the warning from the session page and the live view. Since stage U5 `tests/Kendali/KendaliTest.php` (live view, extra time and reopening, proven through the real save endpoint), `tests/Hasil/NilaiRilisTest.php` (scoring, correction with full rescore, release per class, the review inside SEB with computed headers), `tests/Hasil/CsvTest.php` (both files and the event file read back byte by byte and through a CSV parser: BOM, CRLF, both separators, Arabic text, line breaks inside an answer, formula cells, every answer present) and `tests/Hasil/AuditDataSayaTest.php` join, and `tests/Peramban/TampilanLangsungTest.php` drives the live view in the headless browser. No test can show that Excel displays the files correctly — that is the owner's gate.
- **Load test of LokaUjian (stage U6, `SPEC-LOKAUJIAN.md` §14):** `infra/uji-beban.sh <students> [minutes] [--restart]` is the only way to run it; `infra/tes.sh lokaujian` does not (PHPUnit suite `Beban`, outside the default suite `App`). It shares `infra/lib-tes.sh` with `infra/tes.sh`: the same test image, the same refusal of any schema not ending in `_test` and any Redis DB other than `15`, invented accounts only.
  - **What is loaded:** `auth-tes` and `lokaujian-seb-tes` (enforcement on), behind a test Caddy (`caddy-beban`, plain HTTP), with a real scheduler on the test schema (`jadwal-beban`). `apps/lokaujian/tests/Beban/BebanTest.php` builds one exam of 40 questions (10 true/false, 20 multiple choice, 6 short, 4 long, one picture, both shuffles on) for one class of N students.
  - **What a simulated student does** (`Tests\Support\Beban\Murid`, a port of `/js/ujian.js` and `/js/seb.js`): logs in through LokaAuth's real form within the first minute, opens the exam's start address, waits on the waiting page, starts when the lecturer presses *Mulai*, then asks the state every 10 seconds, sends the SEB heartbeat every 25 seconds, saves a choice at once and text 2 seconds after typing stops and at least every 10 seconds, one save per question at a time with a rising counter, and repeats a save that got no answer, a `5xx` or a `429`. One in ten submits by themselves, one in twenty is silent for 75 seconds, one in twenty reopens the page. The SEB headers are computed by the formula of §8. A simulated lecturer asks the live view every 5 seconds, gives three students extra time and downloads a CSV while the session runs.
  - **The gate — no lost answer:** every save the server answered `200 ok` is compared with `answers` (`answers.counter` not below the confirmed counter, and the stored value exactly the value sent with that counter), a reopened page must show counters not below the confirmed ones, the long CSV must hold N × 40 rows with the stored text answers, and the scheduler must mark and score every attempt within 60 seconds of the end. Response times, status codes and machine figures (MySQL connections, Apache workers, CPU, memory) are reported, not asserted.
  - **Production is on the same machine.** The script refuses to start when the last 15 minutes hold a LokaKode submission, an unfinished judge job or a production LokaUjian attempt in progress; it checks again every 30 seconds and stops the run at the first LokaKode submission or judge job; the load generator is limited to one CPU core. It must not run together with `infra/tes.sh`.
  - Not proven by it: a real browser, a real SEB client, TLS, and the campus network.
- `infra/tes.sh judge` is the only way to run the worker's tests (pytest, on the host, in `judge/.venv`). It recreates `lokakode_test` through the LokaKode migrations, gives `judge_worker` its grant there, and runs the tests against real MySQL, Redis DB `15` and real Docker. The tests refuse any schema not ending in `_test` and any Redis DB other than `15`, fail when Docker is unavailable, and fail when a test is skipped.

## 13. Production data (`SPEC.md` §17)

Everything in this section is a `spark` command that previews by default and writes only with `--jalankan`, acts as `--oleh <nomor induk of an active superadmin>`, can be repeated without changing anything the second time, and writes the same audit rows as the web action it replaces, marked `lewat`.

### Curriculum seed

`docker exec lokakode php spark benih:kurikulum --oleh <nomor induk> [--jalankan]` applies `apps/lokakode/app/Data/benih.json` (`App\Libraries\BenihKurikulum`): the three courses with their pengampu, 16 meetings each (title, language, used-for-practice switch, concepts added), base concepts, and the classes in the active academic year. The file holds no personal data.

- What does not exist is created. What exists and differs is reported as `BEDA` and left alone: pengampu or name of a course, language or practice switch of a meeting, a non-empty concept list, an archived class of the same name.
- Two things are changed on existing rows: a meeting's title is set to the seed's, and an **empty** concept list (of a meeting, or the base concepts of a course) is filled from the seed.
- It fails, writing nothing, when the pengampu is not an active dosen, when there is no active academic year, or when the seed names a concept that is not in the catalogue or not valid for the course's language.
- Nothing is published and no class window is created. Audit rows carry `lewat: benih`.

### Student accounts

`docker exec -i [-e SANDI_AWAL] auth php spark akun:impor --oleh <nomor induk> [--jalankan] [--format csv|xlsx] [--apps a,b] [--nama <file name>] < file`

- The file comes in on standard input, so `/srv/lokandita-data/` is never mounted into a container. Columns as on `/admin/impor` (`SPEC.md` §4.1): `nomor_induk`, `nama`, `global_role` required; `email`, `apps` optional; comma or semicolon; at most 2000 rows. Classification and creation are `Importer::analyse` and `Importer::run`, the same code as the web page.
- Only `mahasiswa` rows are created. `dosen` rows are skipped and counted; a `superadmin` row is a bad row. A row with an empty `apps` gets `--apps` (default: all four apps).
- The shared initial password is read from the environment variable `SANDI_AWAL` (passed by name with `docker exec -e SANDI_AWAL`, never as an argument), must pass the password policy, and is never printed or audited. Every account is created with `must_change_password = 1`.
- Any bad row refuses the whole write, so that the number of accounts can equal the file. An account that already exists is left untouched, password included.
- Output holds counts, line numbers and reason codes only — no name and no nomor induk. Audit: `user.created` per account (`via: import`) and one `user.imported` with the counts and `lewat: cli`.

### Deadline for the initial password

`docker exec auth php spark akun:batas-sandi-awal --oleh <nomor induk> --sampai <YYYY-MM-DD> [--jalankan]`

- Sets `users.initial_password_expires_at` (UTC, nullable; not part of the views for client apps) to the end of that date in Asia/Jakarta, for every active mahasiswa who must still change the password and has had no other password since the account was created (exactly one row in `password_history`). It can be repeated with another date. A date already past is refused.
- At login (`LoginGuard`), a correct password of an account with `must_change_password = 1` whose deadline has passed is refused with reason `initial_password_expired`: the same screen text as a wrong password, audit `login.failed` with that reason, no increase of `failed_attempts`. A session that already exists is not ended.
- Any new password — the user's own change, a reset from LokaAuth or from a client app (`POST /internal/password-reset`) — sets the column back to `NULL`. A reset is the only way back in after the deadline.
- Output holds counts and the deadline only. Audit: one `password.initial_deadline` with `until`, `accounts`, `changed` and `lewat: cli`.

### Source files in `/srv/lokandita-data/`

CSV with a header line, comma or semicolon, UTF-8 (a BOM and CRLF line ends are accepted). They are piped into a command on standard input and never mounted into a container, copied into the repository or printed.

| File | Columns | Read by |
|---|---|---|
| `import-mahasiswa.csv` | `nomor_induk, nama, global_role, mata_kuliah, kelas` — one row per student, which is both the account and the enrollment | `akun:impor` (LokaAuth), `data:pendaftaran`, `data:cocokkan mahasiswa` (LokaKode) |
| `import-dosen.csv` | `nomor_induk, nama, global_role` | `data:cocokkan dosen`; the accounts themselves are created on the LokaAuth page |
| `penugasan-pengajar.csv` | `mata_kuliah, kelas, nip_pengajar` | `data:pengajar`, `data:cocokkan pengajar` |

`mata_kuliah` is the course code, `kelas` the class name; together they name a class of the **active** academic year that is not archived.

### Teaching assignments and enrollments (LokaKode, `App\Libraries\DataAwal`)

- `docker exec -i lokakode php spark data:pengajar --oleh <nomor induk> [--jalankan] < penugasan-pengajar.csv` assigns each `nip_pengajar` (an active dosen) to its class; an assignment that exists is left alone. Audit: `pengajar.ditugaskan` with `lewat: cli`.
- `docker exec -i lokakode php spark data:pendaftaran --oleh <nomor induk> [--jalankan] < import-mahasiswa.csv` enrolls through `Pendaftaran::golongkan` and `Pendaftaran::jalankan`, the code behind the class page: a new student is enrolled, one who withdrew from that course is re-activated in the class of the file (the same enrollment row, with a row in `enrollment_moves`), one already `AKTIF` in that class is left alone. Audit: `pendaftaran.dibuat` / `pendaftaran.diaktifkan` with `lewat: cli`.
- For both, **one bad row refuses the whole write**: an unknown class, an unknown account, an account of the wrong role, a student `AKTIF` in another class of the same course or `LULUS` there, the same person twice. Bad rows are named by line number and reason code.
- A student whose account has no `lokakode` access or is not `active` is enrolled and counted under `PERHATIAN`, as on the web page.

### Count check — the gate of stage 12

`docker exec -i lokakode php spark data:cocokkan <mahasiswa|dosen|pengajar> < file` only reads, and exits 0 only when everything is `COCOK`:

- `mahasiswa`: every row is an active mahasiswa account with `lokakode` access; the number of distinct nomor induk equals the number of active mahasiswa in LokaAuth; for each class the file's students are exactly its `AKTIF` enrollments (same number, same people); the number of rows equals all `AKTIF` enrollments of the active year.
- `dosen`: every row is an active dosen account; the number of rows equals the number of active dosen in LokaAuth.
- `pengajar`: for each class the pengajar in the file are exactly its `class_teachers`; the number of rows equals all assignments in non-archived classes of the active year; the classes named equal the non-archived classes of the active year.

So a leftover test class, account or enrollment makes the check fail until it is archived, disabled or withdrawn.

Output of all these commands holds counts, class names, line numbers and reason codes only — no name and no nomor induk.

The same holds for the terminal's stderr: an audit row written from a `spark` command is not mirrored as a structured log line (`Audit::log` in LokaAuth, `Audit::catat` in LokaKode), because there stderr is the operator's terminal, not the container log. From the web the mirror line is still written to the container log, and in LokaAuth it never carries `nomor_induk`, `nama`, `name` or `email` from the audit detail (`Audit::untukLog`); a person appears in a log line by id only. The audit row in the database keeps the full detail.

## 14. Export of a user's own data (`SPEC.md` §16)

Each app exports only what it stores itself; there is no internal API for it and no app reads another app's tables for it. LokaAuth, LokaKode and — since stage U5 — LokaUjian each answer:

| Route | Does |
|---|---|
| `GET /data-saya` | session; a page that says what the file holds, with the download button |
| `POST /data-saya/unduh` | session, CSRF field `csrf`; answers the file |

The Portal's `GET /data-saya` (session) only links to those pages: LokaAuth's always, LokaKode's when the user has `lokakode` access, and — since LokaUjian stage U6 — LokaUjian's when the user has `lokaujian` access.

- The file is JSON, `Content-Disposition: attachment; filename="lokandita-<app>-data-saya.json"`, `Cache-Control: no-store`.
- Every query is bound to `user_id` of the session. No route takes an id, so nobody — a superadmin included — can export somebody else's data this way.
- At most one download per user per 60 seconds per app (Redis key `ekspor:<user id>` in the app's own Redis DB); sooner → `429` and a page that says so. When Redis cannot be reached the download is allowed and the failure is logged.
- Every download writes `data.diekspor` to that app's `audit_log` (actor and target are the user).
- Lists are newest first and hold at most 5000 rows each; a list that was cut is named in `terpotong`.

Common top level: `{ "aplikasi": "auth|lokakode|lokaujian", "dibuat": "<ISO 8601 UTC>", "pengguna": "<users.id>", "terpotong": ["<list name>"], … }`. Times are UTC.

| App | Keys |
|---|---|
| LokaAuth | `akun` (`id, nomor_induk, nama, email, global_role, status, locale, idle_logout, paste_allowed, mfa_enabled, must_change_password, created_at, last_login_at`), `akses_aplikasi[]` (`app, enabled, granted_at`), `sesi[]` (`created_at, last_seen_at, expires_at, revoked_at, ip, user_agent`), `percobaan_masuk[]` (`created_at, ip, success`, by the user's nomor induk), `riwayat[]` (`created_at, action, app, sebagai` = `pelaku` or `sasaran`; rows of `audit_log` where the user is actor or target) |
| LokaKode | `pendaftaran[]` (`mata_kuliah, kelas, tahun_ajaran, status`), `mengajar[]` (`mata_kuliah, kelas, tahun_ajaran`), `mengampu[]` (`mata_kuliah`), `nilai_pertemuan[]`, `nilai_soal[]`, `kiriman[]` (with the user's own `source`, verdict and score), `draf[]`, `peristiwa_integritas[]` (`created_at, kind`), `preferensi_tampilan`; since stage 10b (§17) `notifikasi[]` (`created_at, kind, read_at`), `klarifikasi[]` (the user's own questions: `created_at, soal, pertanyaan, jawaban, answered_at, terbit_ke_kelas`), `jawaban_klarifikasi[]` (answers the user wrote as staff: `answered_at, soal, jawaban, terbit_ke_kelas` — never the question, which is another person's) and `pengumuman[]` (announcements the user wrote: `created_at, kelas, judul, isi, withdrawn_at`) |
| LokaUjian | `pengerjaan[]` (`ujian` = title, `mata_kuliah` = course code, `kelas`, `mulai`, `kumpul`, `cara_kumpul`, `tambahan_menit`, `buka_ulang_menit`, and `nilai_objektif`, `poin_objektif` **only when the results of that class are released** and the attempt is scored), `jawaban[]` (`ujian`, `nomor` as the user saw it, `tipe`, `jawaban` = the user's own text, `benar`/`salah`, or the letter of the chosen option in the order the user saw, `disimpan`), `peristiwa_integritas[]` (`created_at`, `kind`), and for staff `sesi_dijalankan[]` (`waktu`, `tindakan` = the audit action, `ujian` = title; the user's own audit rows on sessions) and `ujian_dibuat[]` (`judul`, `created_at`) |

LokaUjian's routes are behind filter `masuk` only, never behind `seb`; its rate-limit key lives in Redis DB `3`. Never in LokaUjian's export: question text, options, keys, pictures, points per question, the minutes or reasons a lecturer wrote, and any row of another person. The Portal's `/data-saya` links to it since stage U6.

For an exam meeting (§6) LokaKode names a problem `soal-<meeting problem id>` instead of its slug in every list; while the scores of the user's class are not released, `nilai_pertemuan[]` and `nilai_soal[]` leave that meeting out and its `kiriman[]` rows carry `verdict`, `passed`, `total`, `score` and `duration_ms` as `null` (the user's own `source` stays).

Never in any export: `password_hash`, `password_history`, `mfa_secret_enc`, backup codes, `token_hash` or any session value, the `detail` of an audit row, `problem_instances.tests` or anything else of hidden tests, generators and reference solutions, and any row that belongs to another person.

## 15. Portal text overrides (stage 11b)

The wording of the Portal's public pages can be changed by a superadmin without a deploy. The language files (`apps/portal/app/Language/{id,en,ar}/Portal.php`) stay the defaults and stay in Git; an override replaces the text of **one page in one language**.

```
portal.teks       (halaman VARCHAR(20), bahasa ENUM('id','en','ar'), isi MEDIUMTEXT, updated_by CHAR(36), updated_at DATETIME)   PK(halaman, bahasa)
portal.audit_log  (id BIGINT PK, actor_id CHAR(36), action VARCHAR(60), halaman, bahasa, sebelum MEDIUMTEXT NULL, sesudah MEDIUMTEXT NULL, ip, user_agent, created_at)
```

- `halaman` is one of `depan`, `aplikasi` (one description per app of `Config\Aplikasi`, six since 2026-11-22 — see §16 "App list"; also shown on `/beranda`), `tentang`, `privasi`, `ketentuan`. Until 2026-11-21 there was a sixth, `kontak`; its contents are now the last sections of `tentang`, and `/kontak` answers `301` to `/tentang`.
- `isi`, `sebelum` and `sesudah` hold JSON as text (a MySQL `JSON` column would reorder the keys, and their order is the order on the page).
- `isi` has exactly the shape of that page's array in the language file: the same keys, the same number of sections (`bagian`); only the texts and the number of paragraphs (`pembuka`, `isi`, `cara`) and bullets (`butir`) may differ. `App\Libraries\Teks::dariForm` builds it from the form and refuses anything else, so a stored row can always be rendered by the same view as the default.
- Reading (`App\Libraries\Teks::ambil`): the row when there is one **and it still has the shape of the default** (same keys, same number of sections), else the language file; a row of another shape is left in the table, not shown, and logged as `teks.bentuk_usang`. When the `portal` database cannot be reached the language file is used and `teks.db_unavailable` is logged — a public page never fails because of this table.
- Every text is escaped when shown. HTML typed into the editor appears as text. `{email}` is replaced by `CONTACT_EMAIL` on the text pages, as in the defaults.
- "Terakhir diperbarui" on `privasi` and `ketentuan` is the day (Asia/Jakarta) the override of that language was saved; without an override it is the date in the language file.
- Routes, all behind `masuk:superadmin` (any other role 403, no session → login), `POST` with the CSRF field `csrf`:

| Route | Does |
|---|---|
| `GET /admin/teks` | the five pages × three languages: default or changed, by whom and when, and whether a translation is older than the Indonesian text; and the way to the hero photo (§16) |
| `GET /admin/teks/{halaman}/{bahasa}` | the form |
| `POST /admin/teks/{halaman}/{bahasa}` | field `aksi` = `pratinjau` (shows the page as it would look, saves nothing) or `simpan` (live at once) |
| `POST /admin/teks/{halaman}/{bahasa}/bawaan` | deletes the override: back to the language file |
| `GET /admin/teks/riwayat` | the last 200 rows of the Portal's `audit_log` with the text before and after; the photo actions of §16 are in the same list |

- `simpan` and `bawaan` write `teks.diubah` / `teks.dikembalikan` to `portal.audit_log` in the same transaction, with the whole text before and after. The table is append-only: no route changes or deletes a row. An unchanged save writes nothing.

## 16. Portal hero photo (stage 11c)

The photo next to the heading of the Portal's landing page. The default is part of the image; a superadmin can upload another one, preview it and publish it, inside the editor of §15. Nothing here adds an environment variable.

### What is served

A photo is always served as **twelve generated files**: AVIF, WebP and JPEG at 480, 768, 1200 and 1600 pixels wide, made by `App\Libraries\Gambar` (GD) from decoded pixels, so they carry no metadata (EXIF, GPS, comments, colour profile) and nothing that was appended to the upload. The uploaded file itself is never stored and never served. The landing page uses them in one `<picture>` with `width` and `height` written out; the box is always 4:3 (`object-fit: cover`).

| Photo | Files | Address |
|---|---|---|
| Default (`docs/branding/foto/hero-portal.jpg`, the only file of `docs/` in the Docker build context) | made **at image build** by `infra/docker/foto-bawaan.php` with the same `Gambar` code, into `public/merek/hero/hero-<width>.<ext>` plus `hero.json` (`{ lebar, tinggi }`) and `public/merek/og-image.jpg` (1200 × 630 crop, the Portal's Open Graph image) | `/merek/hero/hero-<width>.<ext>?v=<commit>` |
| Uploaded | `/srv/media/hero/<kunci>-<width>.<ext>` in the volume; `kunci` is 32 hex characters from `random_bytes(16)`, never derived from the upload | `/media/hero/<kunci>-<width>.<ext>` |

### Volume and static serving

- Docker volume **`lokandita_portal_media`** (Compose name `portal_media`), mounted at **`/srv/media`** in the `portal` container only. It survives deploys. `auth` and `lokakode` have the directory in their image but no volume and no use for it.
- Apache in the app container serves `/media/` from `/srv/media` (`infra/docker/apache-app.conf`): PHP engine off, `SetHandler default-handler`, no `.htaccess`, no index, and **only** names matching `<32 hex>-<digits>.(avif|webp|jpg)` are answered; everything else is `403`. Responses carry `X-Content-Type-Options: nosniff` and `Cache-Control: public, max-age=31536000, immutable` (a name is never reused). The directory is outside the document root, so CodeIgniter never sees these requests and they carry no cookie and no CSP header.
- The CSP is unchanged: images are same-origin (`img-src 'self' data:`).
- `infra/backup.sh` writes the volume as `media.tar.gz` (read through `docker exec portal tar`) next to `db.sql.gz`, after the dump; `infra/uji-restore.sh` unpacks it into a temporary directory and checks it against the restored `portal.foto` and against the live volume (`docs/RESTORE.md`).
- Test containers (`portal-tes`) have no volume: their `/srv/media` is the image's own directory and disappears with the container.

### Tables (`portal` schema)

```
portal.foto         (id INT PK, kunci CHAR(32) UNIQUE, nama_asli VARCHAR(255), jenis 'jpeg'|'png'|'webp', lebar, tinggi,
                     alt_id, alt_en, alt_ar VARCHAR(300), sumber VARCHAR(200), lisensi VARCHAR(200), berhak TINYINT(1),
                     uploaded_by CHAR(36), uploaded_at DATETIME, published_at DATETIME NULL)
portal.foto_tayang  (slot VARCHAR(20) PK, foto_id INT NULL → foto.id, updated_by CHAR(36), updated_at DATETIME)
```

- `foto` is append-only: no route changes or deletes a row or a file, so every earlier photo can be published again. `nama_asli` is the browser's file name kept as text only (control characters and slashes removed); it never names a file. `lebar` × `tinggi` is the size of the largest generated file. `published_at` is the first time the photo went live.
- `foto_tayang` has at most the row `slot = 'hero'`. No row, or `foto_id` NULL, means the default photo.
- Reading (`App\Libraries\Foto::tayang`): the published photo; the **default** when nothing is published, when the `portal` database cannot be reached (`foto.db_unavailable` is logged) or when a file of the published photo is missing from the volume (`foto.berkas_hilang`). The landing page never fails because of the photo.
- Alt text comes from `alt_<language>` of the published photo, for the default from `Portal.foto.altBawaan` in the language files. The footer of the landing page shows `Foto: <sumber>` (`Lokandita.kreditFoto`), escaped; for the default `Josh Sorenson / Pexels`.

### Upload rules (`Gambar::periksa`, all checked before the image is decoded)

| Rule | Value | Error code |
|---|---|---|
| Size | at most 10 MB (PHP itself accepts 24 MB, so that a slightly larger file gets this message instead of being dropped) | `terlalu_besar` |
| Type | JPEG, PNG or WebP, decided from the content: `finfo` and the image header must agree. Extension and the browser's MIME type are ignored. GIF, SVG and anything else are refused | `bukan_gambar` |
| Pixels | width × height from the header at most 30,000,000 | `terlalu_banyak_piksel` |
| Smallest | at least 1600 × 900 after EXIF orientation is applied | `terlalu_kecil` |
| Decodable | GD must open it; a truncated JPEG is refused (`gd.jpeg_ignore_warning = 0`) | `tidak_terbaca` |

Required with every upload: `alt_id`, `alt_en`, `alt_ar` (at most 300 characters each), `sumber` and `lisensi` (at most 200), and `berhak = 1` (the tick box "Saya berhak memakai foto ini"). The request raises its own memory limit to 512 MB.

### Routes

In the route group of §15: behind `masuk:superadmin` (any other role `403`, no session → login), every `POST` with the CSRF field `csrf`.

| Route | Does |
|---|---|
| `GET /admin/teks/foto` | the upload form and every photo ever uploaded, with the one that is live |
| `POST /admin/teks/foto` | multipart, file field `foto` plus the required fields: checks, generates the twelve files, stores the photo as **not published**, redirects to its preview. An error answers `422` with the form and the reason |
| `GET /admin/teks/foto/{id}` | preview: the real landing partial (`publik/_depan`) with this photo, in Indonesian and in Arabic, and the publish button |
| `POST /admin/teks/foto/{id}/tayang` | publishes that photo — a new one, or an earlier one again |
| `POST /admin/teks/foto/bawaan` | back to the default photo |

- Written to `portal.audit_log` (§15) in the same transaction, with `halaman = 'foto'`, `bahasa` NULL and the photo's description (id, original file name, size, source, licence, alt texts) in `sebelum` / `sesudah`: `foto.diunggah`, `foto.ditayangkan` (first time live), `foto.dipulihkan` (an earlier photo live again), `foto.bawaan`. Publishing the photo that is already live writes nothing.
- A photo that is uploaded but not published is not referenced by any public page, but its files answer to anyone who knows the random name.

### Brand files (2026-11-22)

The brand family and its names: `docs/branding/LOKANDITA-BRAND.md`. **"LokaAuth" is not a name users see any more**: interface text says "Akun Lokandita" (`en` "Lokandita account", `ar` "حساب Lokandita"), the identity site's title and manifest say "Akun Lokandita", and new TOTP enrolments carry the issuer "Lokandita". Internal identifiers keep their names: directories, hosts, environment variables, service tokens, `"aplikasi": "auth"` and the file name in §14, log lines, and the operator's `spark` output.

Originals are `docs/branding/baru/Lokandita-Brand-Family-Variation-1/brands/<Brand>/` and are never served. What is served is derived from them by **`infra/buat-merek.sh`** (PHP/GD in the app image, no network) and committed under `apps/<app>/public/`; `infra/buat-merek.sh --periksa` fails when the committed files are not what the originals produce. `auth` and `portal` carry the Lokandita brand, `lokakode` its own.

| File (every app) | What |
|---|---|
| `/favicon.ico` | ICO holding the original 16, 32 and 48 px PNGs, light variant |
| `/merek/icon-16.png`, `icon-32.png`, `icon-192.png`, `icon-512.png` | originals, byte for byte (512 is the "logo only" file) |
| `/merek/icon-16-gelap.png`, `icon-32-gelap.png` | dark variant, linked with `media="(prefers-color-scheme: dark)"` |
| `/merek/icon-180.png` | the 180 px original on opaque `#f4f7fb` (iOS paints transparency black) |
| `/merek/site.webmanifest` | `application/manifest+json`: `name`, `short_name`, icons 192 and 512 |
| `/merek/logo-{terang,gelap}.{webp,png}`, `logo.json` | header logo: "name right" original with the transparent margin trimmed, 80 px high (shown at 40); `logo.json` = `{ lebar, tinggi }` as shown |

| File (one app) | What |
|---|---|
| `auth`, `lokakode`: `/merek/og-image.png` (not `lokaujian`: none of its pages is indexable or meant to be shared) | 1200 × 630, the light logo centred on `#f4f7fb`. **The Portal's Open Graph image stays the photo crop `/merek/og-image.jpg` above** (the owner's decision) |
| `lokakode`: `/merek/pembuka-{terang,gelap}.{webp,png}` | hero of the landing: "name below" original at 704 px |
| `portal`: `/merek/aplikasi/<kode>-{terang,gelap}.{webp,png}` | app-card icons, 112 px (shown at 56), one set per brand folder that has a "logo only" file; `<kode>` is the brand name in lower case |

The shared layout (`tata_letak.php`) writes the icon links, the manifest link, `theme-color` and the header logo; a `<picture>` chooses the dark files under `prefers-color-scheme: dark`. No original is recoloured. The CSP is unchanged (`default-src 'self'` covers the manifest, `img-src 'self'` the images).

### App list

The apps the Portal shows are data: `apps/portal/app/Config/Aplikasi.php`, one entry per app in display order — `nama`, `status` (`aktif` = a link to the address in the environment variable named by `alamat`; `segera` = not a link, badge "Segera"), `ikon` (file name under `/merek/aplikasi/`, or `null`). The one-line description is `Portal.aplikasi.<kode>` in the three language files and can be overridden as page `aplikasi` (§15). Adding an app is one entry plus one line per language; no view changes. An entry without an icon, or whose icon file is missing, is drawn with a neutral ring of the same size.

Order since 2026-11-22: `lokakode`, `lokaujian`, `lokakelas`, `lokasiber`, `lokaretas`, `lokadata`. `lokakode` is `aktif`; `lokaujian` is `aktif` since LokaUjian stage U6 (`alamat = LOKAUJIAN_URL`); the other four are `segera`. `/beranda` (§4) still shows only the apps `app_access` allows; `lokakelas`, `lokaretas` and `lokadata` are not values of `app_access.app`, and nothing in §3 changed.

### Icons

Icons on the public pages (other than the app cards, which use the brand icons above) are Lucide SVG files in `packages/lokandita-auth/ikon/` (`docs/branding/LISENSI.md`), written inline by `Lokandita\Auth\Ikon::svg()`: `aria-hidden="true"` unless a label is given, class `ikon-arah` for icons that are mirrored under `dir="rtl"`. The footer of every page of the three apps uses them.

## 17. Integrity events and communication (stage 10b, `SPEC.md` §13.2, §14)

All of this is LokaKode's own: no other component reads these tables, and the judge worker has no right on them. Nothing here adds an environment variable.

**Code similarity (`SPEC.md` §13.2) is not built** — the owner's decision of 2026-11-22. There is no table, job kind or route for it.

Staff routes below are open to whoever may see the class (`App\Policy\Akses::lihatKelas`, in the query): superadmin, the pengampu of the course, a pengajar of that class. Another pengajar or pengampu gets `404`, a mahasiswa `403`. Student routes are behind `masuk:mahasiswa` and `seb` (§8). Every `POST` carries the CSRF field `csrf`.

### Integrity events

`integrity_events.kind` is one of:

| `kind` | Written by | `detail` |
|---|---|---|
| `paste_blocked` | `POST /api/integritas` (stage 9a) | `{ jalur }` — `paste`, `keyboard`, `shift_insert`, `middle_click`, `drop` |
| `typing_anomaly` | `POST /api/integritas` with `jenis=ketik` | `{ karakter, ms, cps, sebab }` |
| `seb_rejected`, `seb_terminated` | filter `seb` (§8) | `{ alasan, jalur }` |

`POST /api/integritas` (mahasiswa): fields `mp` (a problem the student may open) and either `jalur` (as before; `jenis` absent or `tempel`) or `jenis=ketik` with `karakter` (integer, characters added in one burst), `ms` (integer ≥ 0, length of the burst) and `sebab` (`ketik` or `undo`). The server stores a `typing_anomaly` only when **`karakter` ≥ 300 and `karakter` × 1000 ≥ 150 × `ms`** (`SPEC.md` §13.2: ≥ 300 characters at ≥ 150 characters per second), otherwise `422`; `cps` is computed by the server (for `ms` = 0 it equals `karakter`). A user with `paste_allowed` gets `{ "ok": true }` and nothing is stored. At most one event per user per second, as before. The measurement itself is the browser's (`public/js/soal.js`): a burst is what was added during the last two seconds; when it crosses both thresholds the page waits 0.4 s for the rest of the same burst and reports it once, with `ms` the time between its first and last insertion; Reset to the starter template and restoring the draft are not counted; undo and redo are counted with `sebab = undo`.

| Route | Does |
|---|---|
| `GET /kelas/{id}/integritas` | events of the students enrolled in that class (`AKTIF` or `LULUS`) on problems of that course, and their events without a problem (SEB), from the moment of the enrollment on; newest first, 100 per page (`halaman`). Filters: `jenis`, `mahasiswa` (user id), `pertemuan` (meeting id), `dari`, `sampai` (dates, Asia/Jakarta) |
| `GET /kelas/{id}/integritas/ekspor/{csv\|xlsx}` | the same rows under the same filters, at most 20,000; columns time (Asia/Jakarta), nomor induk, name, kind, meeting, problem, detail, IP address, user agent. Writes `integritas.diekspor` (`{ format, baris }`) to `audit_log` |

An event follows its student: after a class move it is listed in the new class.

### Notifications

```
notifications (id, user_id, kind, ref VARCHAR(80), data JSON, created_at, read_at NULL)      UNIQUE(user_id, kind, ref)
```

| `kind` | For | Created | `ref` | `data` |
|---|---|---|---|---|
| `pertemuan_dibuka` | a student `AKTIF` in the class | lazily, see below, while the class window of a published practice meeting is open (§6) | `<window id>:<open_at>`, or `<window id>:manual` when it is open by `override = 'open'` | `{ pertemuan, nomor, judul, matkul, tutup }` |
| `tutup_24jam` | same | lazily, while that window is open with `override = 'none'` and `close_at` is at most 24 hours ahead — but only for a window that is longer than 24 hours from `open_at` to `close_at`; a shorter one has its close time in `pertemuan_dibuka` already | `<window id>:<close_at>` | same |
| `sandi_direset` | the student whose password was reset | by `POST /kelas/{id}/reset-sandi` after LokaAuth answered `200` (§5) | id of the `sandi.direset` row in `audit_log` | `{ oleh: <user id>, kelas }` |

- There is no background process on the web side. `App\Libraries\Notifikasi::segarkan` runs whenever LokaKode renders a page for a mahasiswa (`BaseController::halaman`), at most once per 60 s per user (Redis key `lokakode:laju:notif:<user id>`; without Redis it runs every time), and always on `GET /notifikasi`; it inserts the missing rows with `INSERT IGNORE`. So a notification appears at the first page load after its moment, never without a page load, and never twice. A window whose times change notifies again, because the time is part of `ref`.
- A reset done inside LokaAuth by a superadmin is **not** notified: LokaKode does not learn of it.
- `created_at` is the moment the row was written, not the moment the window opened.

| Route | Does |
|---|---|
| `GET /notifikasi` | mahasiswa: own notifications, newest first, at most 100 |
| `POST /notifikasi/baca` | mahasiswa: sets `read_at` on all own unread rows; redirects to `/notifikasi` |

The number of unread rows is shown in LokaKode's own navigation (`app/Views/_nav.php`) on the student's home, meeting, leaderboard, notification and announcement pages, read when the page is rendered; the problem page has no navigation bar. The shared account menu (§4) is unchanged.

### Clarifications

```
clarifications (id, meeting_problem_id, class_id, user_id, question, created_at,
                answer NULL, answered_by NULL, answered_at NULL, visibility 'private'|'class', published_at NULL)
```

- `class_id` is the class in which the asker was `AKTIF` when asking. `visibility = 'class'` exactly when `published_at` is set, and only with an answer (check constraints).
- A student sees: own questions with their answers; and questions with `visibility = 'class'` of the class in which the student is `AKTIF` **now**, with the asker's name (the owner's decision). A private answer is seen by the asker and by staff only.
- Texts are plain text, shown escaped; question and answer 1–2,000 characters each. No notification is sent.

| Route | Does |
|---|---|
| `GET /api/soal/{mp}/klarifikasi` | mahasiswa, a problem the student may open: `{ "daftar": [{ id, pertanyaan, waktu, milikSaya, penanya, jawaban, dijawab, kelas }] }` — `penanya` is a name, `kelas` true when published to the class, `jawaban` `null` while unanswered |
| `POST /api/soal/{mp}/klarifikasi` | field `pertanyaan`; at most one per user per 60 s (`429`); answers `{ "ok": true, "id" }` |
| `GET /kelas/{id}/klarifikasi` | staff: the questions of that class, unanswered first; filter `pertemuan` |
| `POST /kelas/{id}/klarifikasi/{kid}/jawab` | staff: fields `jawaban` and `tampil` (`pribadi` or `kelas`); answers, or changes the answer or its visibility. Audit `klarifikasi.dijawab` (first answer or changed text) and `klarifikasi.diterbitkan` / `klarifikasi.ditarik` (visibility changed) |

### Announcements

```
announcements (id, class_id, author_id, title VARCHAR(150), body, created_at, withdrawn_at NULL, withdrawn_by NULL)
```

- For one class. Plain text, shown escaped with line breaks kept; title 1–150 characters, body 1–5,000. No edit and no delete: *Tarik* sets `withdrawn_at`, after which students no longer see it. A class that has an announcement or a clarification cannot be deleted.
- A student sees the announcements that are not withdrawn of the classes in which the student is `AKTIF` or `LULUS`.

| Route | Does |
|---|---|
| `GET /kelas/{id}/pengumuman` | staff: all announcements of the class, withdrawn ones marked, and the form |
| `POST /kelas/{id}/pengumuman` | staff: fields `judul`, `isi`. Audit `pengumuman.dibuat` |
| `POST /kelas/{id}/pengumuman/{aid}/tarik` | staff: withdraws it. Audit `pengumuman.ditarik`. Withdrawing one that is already withdrawn changes nothing and writes no audit row; an id that is not an announcement of that class gets `404` |
| `GET /pengumuman` | mahasiswa: own classes' announcements, newest first, at most 100; the newest three are also on `/beranda` |

In an archived academic year a dosen can read these pages but not answer, post or withdraw (`403`), like every other change to a class.

## 18. Academic roster views

The academic structure of `SPEC.md` §5 as other apps read it (`SPEC-LOKAUJIAN.md` §4). **The views are the contract; the tables behind them are not.** A reader app queries these five views and nothing else of the roster.

Created by a LokaKode migration in the `lokakode` schema (`SQL SECURITY DEFINER`, definer `lokakode_app`), because that schema is migrated only by LokaKode:

```sql
CREATE VIEW roster_academic_years AS
SELECT id, label, starts_on, ends_on, status FROM academic_years;

CREATE VIEW roster_courses AS
SELECT id, code, name, owner_id FROM courses;

CREATE VIEW roster_classes AS
SELECT id, course_id, academic_year_id, name, archived_at FROM classes;

CREATE VIEW roster_class_teachers AS
SELECT class_id, user_id FROM class_teachers;

CREATE VIEW roster_enrollments AS
SELECT id, user_id, course_id, academic_year_id, class_id, status, graduated_on FROM enrollments;
```

| View | Column | Type | Meaning |
|---|---|---|---|
| `roster_academic_years` | `id` | BIGINT UNSIGNED | |
| | `label` | VARCHAR(40) | unique |
| | `starts_on`, `ends_on` | DATE | |
| | `status` | `'active'` \| `'archived'` | at most one row is `active` |
| `roster_courses` | `id` | BIGINT UNSIGNED | |
| | `code` | VARCHAR(20) | unique |
| | `name` | VARCHAR(190) | |
| | `owner_id` | CHAR(36) | the pengampu; `users.id` of §3 |
| `roster_classes` | `id` | BIGINT UNSIGNED | |
| | `course_id`, `academic_year_id` | BIGINT UNSIGNED | |
| | `name` | VARCHAR(60) | unique within course and year |
| | `archived_at` | DATETIME NULL, UTC | `NULL` = in use |
| `roster_class_teachers` | `class_id` | BIGINT UNSIGNED | |
| | `user_id` | CHAR(36) | a pengajar of that class; `users.id` |
| `roster_enrollments` | `id` | BIGINT UNSIGNED | |
| | `user_id` | CHAR(36) | `users.id` |
| | `course_id`, `academic_year_id`, `class_id` | BIGINT UNSIGNED | the class always belongs to that course and year |
| | `status` | `'AKTIF'` \| `'LULUS'` \| `'MENGUNDURKAN_DIRI'` \| `'DIPINDAHKAN'` | |
| | `graduated_on` | DATE NULL | |

What a reader may rely on:

- Ids are never reused and never change — also not when the tables move (stage A1).
- One enrollment per student per course per academic year; a class move changes `class_id` on that row.
- A row can disappear only where LokaKode allows a delete: a class without enrollments, a teacher assignment. A reader keeps working when an id it stored no longer appears.
- Capacity, creation times, who created or assigned, and the history of class moves are **not** part of the contract and cannot be read through the views.

Access:

- MySQL user **`roster_reader`**: `SELECT` on these five views, nothing else, no write privilege. Granted by `infra/mysql-hak-roster.sh [lokakode|lokakode_test]` as MySQL root after LokaKode's migrations. It cannot read `enrollments`, `submissions` or any other table.
- A reader app connects with `ROSTER_READER_DSN` (§10), a connection separate from its own schema's: `Lokandita\Auth\RosterDb` in the shared package. Today the only reader is LokaUjian.
- A reader stores `class_id`, `course_id`, `academic_year_id` as plain BIGINT UNSIGNED and `user_id` as CHAR(36), without foreign keys, and never joins its own schema with the roster: it first reads the ids it may see, then binds its own queries to them.
- LokaKode itself keeps reading and writing its tables; the views change nothing for it.
- `apps/lokakode/tests/Schema/ViewRosterTest.php` holds the columns and types above against real MySQL.

**When the tables move (stage A1):** the five views are redefined, with the same names, columns, types and guarantees, wherever the tables then live; `ROSTER_READER_DSN` and the grant script follow. No reader app changes.

## 19. LokaUjian — access and scope (stage U1, `SPEC-LOKAUJIAN.md` §3, §4)

LokaUjian's own; no other component calls these routes. They are recorded here so that the tests against real containers have something to hold on to. Everything below is read from the roster views (§18) and from `user_directory` (§3); LokaUjian's own tables come with stage U2 (§20). Nothing here adds an environment variable.

Authentication is §4 with app name `lokaujian` (filter `masuk`, account routes from `Routes::akun()`), so a user without `app_access` for `lokaujian` gets `403` on every route below, whatever their classes.

Scoped roles are derived on **every request** from the views and are cached nowhere:

| Scoped role | Derived from |
|---|---|
| Pengampu of a course | `roster_courses.owner_id` |
| Pengajar of a class | `roster_class_teachers` |
| Mahasiswa of a class | `roster_enrollments.status = 'AKTIF'`, and the class has `archived_at IS NULL`, and its academic year is `active` |

The rules live once, in `App\Policy\Akses`, as SQL conditions that `App\Libraries\Roster` puts into every roster query; no function fetches a course or a class by id alone.

- **Sees a class:** superadmin; the pengampu of its course; a pengajar of that class.
- **Sees a course:** superadmin; its pengampu; a dosen who is pengajar of at least one of its classes — who then sees its code, name, pengampu and only their own classes.
- A dosen outside that scope gets `404` with the page *Halaman tidak ditemukan*; the answer for an id that does not exist is the same page, and the container logs `access.denied` with reason `outside_scope_or_missing`. A mahasiswa on a staff route gets `403` from the filter.

| Route | Who | Does |
|---|---|---|
| `GET /` | public (§4) | short page with "Masuk"; with a `lokandita_sid` cookie it redirects to `/beranda` |
| `GET /beranda` | any session; **not** behind filter `seb` (`SPEC-LOKAUJIAN.md` §9.1) | staff: the classes they see in the active academic year, with the number of `AKTIF` students and the pengajar. Mahasiswa: the list of their exams — empty until U3 — and the classes in which they are mahasiswa by the rule above |
| `GET /mata-kuliah` | superadmin, dosen | the courses they see |
| `GET /mata-kuliah/{id}` | same, in scope | the course and those of its classes in the active academic year that they see; for whoever manages the course (§20) also the way to its question bank and exams |
| `GET /kelas/{id}` | same, in scope | course, academic year, pengampu, pengajar, and the `AKTIF` students (nomor induk, name) ordered by nomor induk |

- Lists hold the active academic year only. `GET /kelas/{id}` also opens a class of an archived year that is in scope, marked as archived; an archived class is listed for its staff with a mark and is never a class of a mahasiswa.
- Names and nomor induk come from `user_directory` at the moment of the request; an id without a row there is shown as "Akun Lokandita tidak ditemukan".
- Every page is `noindex, nofollow`; pages behind a session carry `Cache-Control: no-store`.
- `apps/lokaujian/tests/Policy/MatriksAksesTest.php` fails when a route registered in `Config/Routes.php` is missing from its matrix; `LingkupKelasTest.php` holds the cross-class cases.

## 20. LokaUjian — question bank, pictures and exam file (stage U2, `SPEC-LOKAUJIAN.md` §5, §5.4, §6.3)

LokaUjian's own; no other component reads these tables or calls these routes. Recorded here so that the tests against real containers have something to hold on to. Nothing here adds an environment variable.

### Who manages a course

**Superadmin, and the pengampu of the course** (`roster_courses.owner_id`). Nobody else reads or writes its bank, pictures or exams.

LokaUjian never joins its schema with the roster (§18). `App\Policy\Akses::idMatkulDiampu()` reads the ids of the courses the user manages from the roster views, once per request and never kept beyond it; `Akses::diKelola()` turns them into the condition `course_id IN (…)`, and **every** query of `App\Libraries\Bank`, `Ujian` and `Media` carries that condition together with the course id of the route. No function fetches a question, group, picture or exam by its id alone.

- Every route below except `/gambar/…` has the course id in its path: `/mata-kuliah/{m}/…`, behind `masuk:superadmin,dosen`. A course outside what the user sees → `404` (*Halaman tidak ditemukan*, as §19). A dosen who sees the course as pengajar but does not manage it → `403`. A mahasiswa → `403` from the filter.
- A question, group or exam id that belongs to another course, in whichever course's path it is requested, → `404`, the same answer as for an id that does not exist.
- In an archived academic year nothing changes here: the bank belongs to the course, not to a year.

### Tables (`lokaujian` schema)

```
audit_log        (id BIGINT PK, actor_id, action, target_tipe, target_id, course_id, class_id, detail JSON, ip, user_agent, created_at)
images           (id, course_id, kunci CHAR(32) UNIQUE, jenis 'png'|'jpeg', lebar, tinggi, byte, sha256 CHAR(64),
                  uploaded_by, uploaded_at)                                                   UNIQUE(course_id, sha256)
question_groups  (id, course_id, text, image_id NULL → images, image_alt NULL, content_hash CHAR(64), archived_at NULL,
                  created_by, created_at, updated_by, updated_at)
questions        (id, course_id, group_id NULL → question_groups, position NULL, type, text, points DECIMAL(5,2),
                  answer TINYINT NULL, image_id NULL → images, image_alt NULL, content_hash CHAR(64), archived_at NULL,
                  created_by, created_at, updated_by, updated_at)                              UNIQUE(group_id, position)
question_options (id, question_id → questions, position, text VARCHAR(1000), correct, image_id NULL → images, image_alt NULL)
                                                                                              UNIQUE(question_id, position)
exams            (id, course_id, title, kind 'ujian'|'kuis', instructions, mode 'per_mahasiswa'|'dikendalikan_dosen',
                  duration_minutes NULL, shuffle_questions, shuffle_options, status 'draf'|'terbit'|'arsip',
                  created_by, created_at, updated_at)
exam_items       (id, exam_id → exams, position, question_id NULL → questions, group_id NULL → question_groups)
                                                                                              UNIQUE(exam_id, position)
```

- `course_id` is the roster's id and every `*_by` / `actor_id` is `users.id` of §3: no foreign key leaves the schema.
- `questions.type` is `benar_salah`, `pilihan_ganda`, `isian` or `uraian` and never changes after creation. `answer` is set exactly for `benar_salah`. `points` is 0.25–100 in steps of 0.25. A question with `group_id` is a member of that group at `position` (1–20, no gaps); it is never listed, archived or put into an exam on its own.
- A `pilihan_ganda` has 2–6 options and exactly one with `correct = 1`. **An option may carry a picture, and its text may then be empty** (the owner's decision of 2026-11-25, overruling `SPEC-LOKAUJIAN.md` §17 point 10). A question and a group carry at most one picture each; a group needs a text or a picture.
- **The alternative text belongs to the use, not to the file**: `image_alt` sits next to `image_id` and is required whenever `image_id` is set (1–300 characters). `images` holds one row per stored file per course; the same file may be used with different alternative texts.
- `content_hash` is the SHA-256 of the content in a fixed form — for a question: type, text, points, answer, picture (SHA-256 of the file and its alternative text), and the options with their order, key and pictures; for a group: text, picture, and the hashes of its questions in order. It is recomputed on every save and is how an import recognises content that is already in the bank.
- `exam_items` holds exactly one of `question_id` (a question outside a group) or `group_id` per row. An exam item refers to the bank: a `draf` follows later edits in the bank. Publishing copies the content into the frozen tables of §22; the `exam_items` rows stay, so a question that a published exam was made from still counts as used.
- A question or group that any exam uses — a draft included — cannot be deleted, only archived (`archived_at`): hidden from the bank list, kept for its exams. One that no exam uses can be deleted for good; its picture file stays.
- `audit_log` is append-only: `App\Libraries\Audit` only inserts, in the transaction of the change, and no route changes or deletes a row. The actor of a row written from a `spark` command is the person named by `--oleh`, and its `detail` carries `lewat: cli`.

| `action` | When | `detail` |
|---|---|---|
| `ujian.dibuat` | a draft made from ticked bank items | `{ judul, butir, soal }` |
| `ujian.diimpor` | a file accepted | `{ judul, butir, soal, berkas, soal_baru, gambar, gambar_baru }` |
| `ujian.impor.ditolak` | a file refused (`target_tipe = berkas`) | `{ berkas, alasan, di, kesalahan }` — code and JSON path of the first mistake, and how many there were; never content of the file |
| `ujian.diekspor` | a file handed out | `{ berkas, soal, byte }` |
| `ujian.dihapus` | a draft deleted | `{ judul, soal }` |
| `soal.dihapus`, `kelompok.dihapus` | a permanent delete from the bank | `{ tipe, kelompok }` / `{ soal }` |

Creating, editing and archiving in the bank are not audited (`SPEC-LOKAUJIAN.md` §13 does not list them); the rows carry `created_by` / `updated_by`.

### Routes

All under `/mata-kuliah/{m}`, for whoever manages course `m`. Every `POST` carries the CSRF field `csrf`; forms with a file are `multipart/form-data`.

| Route | Does |
|---|---|
| `GET /bank` | the bank: questions outside a group and groups, oldest first, with type, points, first words and the exams that use each. Query: `q` (words in a question's or group's text), `tipe` (one of the four types, or `kelompok`), `arsip=1` (the archived ones instead) |
| `GET /bank/soal/baru?tipe=<type>[&kelompok=<id>]`, `POST /bank/soal` | form and creation of one question; with `kelompok` it becomes the last question of that group (at most 20, else `422`) |
| `GET /bank/soal/{id}` | preview of a question as a student sees it, marked *Pratinjau*, then — outside that partial — its key and the actions. A group member redirects to its group |
| `GET /bank/soal/{id}/sunting`, `POST /bank/soal/{id}` | form and save. The type cannot be changed |
| `POST /bank/soal/{id}/arsip` | field `arsip` `1` or `0`; `404` for a group member |
| `POST /bank/soal/{id}/hapus` | permanent delete; refused (redirect with `pesan=dipakai`, nothing changed) when an exam uses it or its group |
| `POST /bank/soal/{id}/geser` | group members only: field `arah` `naik` or `turun`, one step |
| `GET /bank/kelompok/baru`, `POST /bank/kelompok`, `GET /bank/kelompok/{id}`, `GET /bank/kelompok/{id}/sunting`, `POST /bank/kelompok/{id}`, `POST /bank/kelompok/{id}/arsip`, `POST /bank/kelompok/{id}/hapus` | the same for a group's shared part; the group page previews the whole group and lists its questions with keys. Deleting a group deletes its questions |
| `GET /ujian` | the exams of the course that are not archived (`?arsip=1`: the archived ones instead) with number of questions, total points and the split between automatically scored and lecturer-marked points; and the import form |
| `POST /ujian` | fields `judul`, `jenis` (`ujian`/`kuis`), `butir[]` (`s<question id>` or `k<group id>`): a new `draf` holding those bank items in the order sent, with mode `dikendalikan_dosen`, no duration and no shuffling. Refused, with nothing stored, for no item, an item that is not a question outside a group or a group of this course, an empty group, or more than 200 questions |
| `GET /ujian/{id}` | preview of the whole exam with the student's partial; no key on the page. Since U3 also its state, its sessions and the actions of §22 |
| `GET /ujian/{id}/ekspor`, `POST /ujian/{id}/ekspor` | the page that says the file contains every key; the file (below) |
| `POST /ujian/impor` | file field `berkas`: import (below). Accepted → redirect to the new exam; refused → `422` with every mistake |
| `POST /ujian/{id}/hapus` | deletes an exam of any status that has **no attempt**, with its sessions; its questions stay in the bank. With an attempt: redirect with `pesan=ada_pengerjaan`, nothing changed |

Form fields of a question: `type`, `text`, `points` (a comma is accepted as decimal sign), `answer` (`benar`/`salah`), `gambar` (file), `alt`, `hapus_gambar`; for `pilihan_ganda` six rows `opsi_teks[0…5]`, `opsi_gambar_0…5` (files), `opsi_alt[…]`, `opsi_hapus_gambar[…]` and `benar` (the row that is correct). A row with neither text nor picture is not an option. A mistake answers `422` with the form, everything typed, and one sentence per mistake; nothing is stored. Forms and import go through the same rules (`App\Libraries\Aturan`).

Preview uses the view partials `_soal` and `_kelompok`, which the student's exam page of stage U3 will use; they hold no key and no hint of correctness. A preview creates and records nothing. Text is Markdown (`league/commonmark`, raw HTML escaped, unsafe links removed) inside `dir="auto"`.

### Pictures

- **Volume** `lokandita_lokaujian_media` at `/srv/media` in `lokaujian` only (§9); files are `/srv/media/soal/<kunci>.png` or `.jpg`, `kunci` = 32 hex characters from `random_bytes(16)`. A file is never changed or deleted.
- **Upload checks** (`App\Libraries\Gambar::periksa`, before the image is decoded): at most 5 MB (`terlalu_besar`); JPEG, PNG or WebP decided from the content, `finfo` and the image header agreeing — extension and browser MIME type are ignored (`bukan_gambar`); at most 30,000,000 pixels (`terlalu_banyak_piksel`). There is no minimum size.
- **The upload is never stored.** `Gambar::tulis` decodes it (a truncated file is refused, `tidak_terbaca`) and writes one new file from the pixels: PNG for a PNG upload (transparency kept), JPEG quality 85 otherwise (transparency on white), EXIF orientation applied, longest side at most 2000 px. A result above 5 MB is refused (`hasil_terlalu_besar`). The new file carries no metadata and nothing that was appended to the upload.
- A result whose SHA-256 equals a stored picture of the same course is not stored twice; the existing row is used.
- **Pictures are not static files.** Apache's `/media/` (§16) answers only names of the form `<32 hex>-<digits>.(avif|webp|jpg)`; a question picture is `<32 hex>.png|jpg`, so `/media/soal/…` is `403` for every name, with or without a session.
- **`GET /gambar/<kunci>`** (filter `masuk`, any role) is the only way out: the app reads the row with `Akses::diKelola()` in the query and answers the file with its `Content-Type`, `Cache-Control: private, no-store` and `X-Content-Type-Options: nosniff`. Besides whoever manages the course, since stage U3 the picture is answered to a staff member who may run a session (§22) of a published or archived exam whose frozen copy contains it, and to a mahasiswa **only while that student has an attempt in progress** on such an exam, or — since stage U5 — an attempt in a class whose results are released at level `nilai_dan_kunci` (the review, §24). A `kunci` of another course, an unknown `kunci` and every other request get the same `404`. A row whose file is missing is `404` with the log line `gambar.berkas_hilang`.
- `infra/backup.sh` writes the volume as `media-lokaujian.tar.gz` (through `docker exec lokaujian tar`) after the dump; `infra/uji-restore.sh` unpacks it into a temporary directory, refuses any file not named `soal/<32 hex>.png|jpg`, checks every row of the restored `lokaujian.images` against its file (size and SHA-256), and compares the archive with the live volume (`docs/RESTORE.md`).
- Test containers have no volume: their `/srv/media` is the image's own directory. A `spark` command run by a test writes pictures inside the test-runner container, not inside `lokaujian-tes`.

### Exam file format

One JSON file per exam, UTF-8 (a BOM is accepted). Used by `POST /ujian/impor`, `POST /ujian/{id}/ekspor` and by `php spark ujian:impor` / `ujian:ekspor`. `apps/lokaujian/contoh/ujian-contoh.json` is a file with every type of content.

```json
{
  "format": "lokaujian-ujian/1",
  "course": "DDP101",
  "title": "Kuis 1 — Percabangan",
  "kind": "kuis",
  "instructions": "Kerjakan semua soal.",
  "settings": {
    "mode": "per_mahasiswa",
    "duration_minutes": 30,
    "shuffle_questions": true,
    "shuffle_options": true
  },
  "items": [
    { "type": "benar_salah", "text": "…", "points": 1, "answer": true },
    { "type": "pilihan_ganda", "text": "…", "points": 2,
      "options": [ { "text": "…" }, { "text": "…", "correct": true }, { "text": "", "image": "g2" } ],
      "image": "g1" },
    { "type": "isian", "text": "…", "points": 2 },
    { "type": "uraian", "text": "…", "points": 10 },
    { "type": "kelompok", "text": "…bacaan…",
      "questions": [
        { "type": "pilihan_ganda", "text": "…", "points": 1,
          "options": [ { "text": "…", "correct": true }, { "text": "…" } ] },
        { "type": "isian", "text": "…", "points": 1 }
      ],
      "image": "g3" }
  ],
  "images": {
    "g1": { "type": "png",  "alt": "Diagram alir", "sha256": "…", "data": "<base64>" },
    "g2": { "type": "png",  "alt": "Belah ketupat", "sha256": "…", "data": "<base64>" },
    "g3": { "type": "jpeg", "alt": "Denah",        "sha256": "…", "data": "<base64>" }
  }
}
```

| Field | Rule |
|---|---|
| `format` | Exactly `lokaujian-ujian/1` |
| `course` | Exact course code, case included; the importing user must be superadmin or its pengampu. On the web it must be the course of the page |
| `title` | 1–190 characters, one line |
| `kind` | `ujian` or `kuis`; default `ujian` |
| `instructions` | Markdown, at most 5,000 characters; may be empty or absent |
| `settings.mode` | `per_mahasiswa` or `dikendalikan_dosen` |
| `settings.duration_minutes` | Integer 5–600; required for `per_mahasiswa`, optional or `null` for `dikendalikan_dosen` |
| `settings.shuffle_questions`, `settings.shuffle_options` | Boolean; default `false` |
| `items` | 1–200 questions in all, counting those inside groups. Order in the file is the exam order |
| `type` | `benar_salah`, `pilihan_ganda`, `isian`, `uraian`, or `kelompok` (top level only; a group cannot hold a group) |
| `text` | Markdown, 1–10,000 characters for a question; for a group at most 20,000, and it may be empty or absent when the group has an `image`. Raw HTML is shown as text |
| `points` | Number 0.25–100 in steps of 0.25; default 1. Not allowed on a `kelompok` |
| `answer` | `benar_salah` only, required: `true` or `false` |
| `options` | `pilihan_ganda` only, required: 2–6 objects `{ "text"?, "correct"?, "image"? }`; `text` at most 1,000 characters, and it may be empty or absent only when the option has an `image`; **exactly one** has `"correct": true` |
| `questions` | `kelompok` only: 1–20 questions of the four types |
| `image` | Optional on a question, on a group and on an option: a key of `images` |
| `images.<key>` | Key `[a-z0-9_-]{1,40}`; `type` `png` or `jpeg`, which must be what the content is; `alt` 1–300 characters — the alternative text of the uses that name this key; `data` Base64 of the file; `sha256` lower-case hex of the decoded bytes. At most 40 entries. Every entry must be used by an item. The same file may appear under two keys with different `alt` |

Rules:

- At most 24 MB. Types are checked strictly (a number in quotes is not a number). Unknown keys are refused at every level; a key that does not belong to the type (`options` on an `isian`, `answer` on a `pilihan_ganda`, `points` on a `kelompok`) is refused; a key written twice in one object is refused. **Any mistake refuses the whole file and nothing is stored** — no question, picture row, picture file or exam. All mistakes found are reported together (at most 50), each with its JSON path, its line in the file, and a sentence that says what to change (`items[2].options (baris 14): tepat satu opsi harus ditandai sebagai jawaban benar; sekarang 2`), in the language of the user.
- Text is stored with `\n` line ends and without leading or trailing white space; forms store it the same way.
- An import creates one new exam in `draf`, as the importing user. A question outside a group whose content hash equals one already in that course's bank — archived ones included, which stay archived — is reused; so is a group whose shared part and questions all equal a stored group. Anything else is added to the bank.
- A picture whose `sha256` equals a stored picture of that course is reused as it is. Any other picture goes through the upload checks above and is written anew from its pixels; its `sha256` in later exports is that of the stored file.
- The export of a `terbit` or `arsip` exam writes its frozen copy (§22), not the bank.
- The export writes the stored files and is a pure function of the exam's content: two-space indentation, one newline at the end, `/` and non-ASCII characters unescaped, keys in this order — top level as in the table; `settings`: `mode`, `duration_minutes` (always written, `null` when there is none), `shuffle_questions`, `shuffle_options`; an item: `type`, `text`, `points`, `answer`, `options`, `questions`, `image`; an option: `text` (always written), `correct` (only when `true`), `image`; a picture: `type`, `alt`, `sha256`, `data`. Pictures are named `g1`, `g2`, … in the order of first use (a group's picture before those of its questions, a question's before those of its options). **Exporting, importing that file into the same course and exporting again gives a byte-identical file.**
- The file never holds classes, sessions, schedules, students, answers or ids of the database.
- The export contains the keys: `Content-Type: application/json; charset=utf-8`, `Cache-Control: no-store`, `Content-Disposition: attachment; filename="lokaujian-<course code>-<exam id>.json"`.
- `docker exec -i lokaujian php spark ujian:impor [file] --oleh <nomor induk> [--nama <file name>]` reads standard input without a file argument and exits 0 only when the file is accepted; a refusal prints every mistake with path and line. `php spark ujian:ekspor <exam id> --oleh <nomor induk>` prints the file to standard output and everything else to standard error. `--oleh` must be an active superadmin, or an active dosen with `lokaujian` access who is the pengampu of the course; an exam of a course that person does not manage is answered like one that does not exist.

## 21. LokaUjian scheduler (stage U3, `SPEC-LOKAUJIAN.md` §7.5)

`lokaujian-jadwal` (§9) runs `php spark ujian:jadwal` from the app image: one loop, one pass every 10 seconds, each pass idempotent. It has no state of its own; MySQL is the source of truth, and **nothing a student may or may not do depends on it having run**: whether an attempt may be answered is derived from the time on every read and every write (§22 "The time rule").

- **What a pass does:** one statement that marks every attempt whose deadline passed more than 10 seconds ago (the grace of §22) as submitted — `submitted_at` = the deadline itself, `submit_how` = `diakhiri_dosen` when the session was ended by the lecturer and the attempt has no extra time, else `waktu_habis`. The state of a session (open, closed, running, finished) is not stored anywhere and is derived from its times, so a pass has nothing to do for it. Since stage U5 the same pass then **scores** every submitted attempt that has no score yet (`App\Libraries\Nilai`, §24); an attempt reopened for a student (§24) is marked `waktu_habis` when its reopening ends.
- The lecturer's session page and its live view, the CSV downloads, the release of results and the student's review run the same pass before they read, so their numbers are right while the scheduler is down.
- **Heartbeat:** Redis key `lokaujian:jadwal` (DB `3`), JSON `{ commit, pid, nama, mulai, waktu, lintasan }` — `nama` = `<host name>:<pid>`, `mulai` and `waktu` in UTC as `YYYY-MM-DDTHH:MM:SSZ`, `lintasan` = passes since the start — written after every pass with a lifetime of 60 s. A missing key means no scheduler is running. `infra/deploy.sh lokaujian`, `infra/smoke.sh` (check 16), `infra/tes-infra.sh` and the lecturer's session page read it.
- **Exactly one scheduler per schema.** A scheduler that finds the heartbeat of another process watches it for up to 25 seconds: when it advances, the newcomer exits with one log line; when it stands still (the container that wrote it is gone), the newcomer takes over. The process handles no signal: stopped, it dies at once, and its key expires by itself. (The draft said "deleted on a clean stop"; the image has no `pcntl`, and a key that only ever expires needs none.)
- It connects as `lokaujian_app` through `DB_DSN`; it makes no HTTP call and no call to LokaAuth.
- `php spark ujian:jadwal --sekali` runs one pass and exits (tests; no heartbeat). `--periksa` runs nothing and exits 0 when the heartbeat of this container exists (the container's health check).
- Exit code 78 means a missing environment variable, as for the judge worker.

## 22. LokaUjian — publishing, sessions and attempts (stage U3, `SPEC-LOKAUJIAN.md` §6, §7, §8, §9.2)

LokaUjian's own; no other component reads these tables or calls these routes. Recorded here so that the tests against real containers have something to hold on to.

### Who may do what

- **Manage** (edit a draft, publish, withdraw, archive, delete, give an exam to a class, change a schedule, delete a session): whoever manages the course (§20).
- **Run a session** (see its page, open, close, start, set the end, end, preview the exam): whoever **sees the class** (§19) — superadmin, the pengampu of its course, a pengajar of that class. `App\Policy\Akses::idKelasDilihat()` reads those class ids from the roster once per request and `Akses::diJalankan()` turns them into `class_id IN (…)`; every query of `App\Libraries\Sesi` carries it. A session outside that scope is `404`, the same answer as for an id that does not exist. Schedule and delete answer `404` to a pengajar.
- **Mahasiswa:** a student finds a session only when its class is one of theirs by the rule of §19 (`Akses::diKelasSaya()`), or when their own attempt already belongs to it — a student who leaves the class after starting keeps the attempt. **No address carries an attempt id**: student routes carry the session id, and the attempt is looked up from (exam of that session, logged-in user). A session of another class is `404`, identical to one that does not exist. Staff on a student route get `403` from the filter.

### Tables (`lokaujian` schema, migration `2026-11-25-000002`)

```
exams            + published_at NULL, published_by NULL
exam_groups      (id, exam_id → exams, text, image_id NULL → images, image_alt NULL, source_group_id NULL)
exam_questions   (id, exam_id → exams, position, exam_group_id NULL → exam_groups, type, text, points, answer NULL,
                  image_id NULL → images, image_alt NULL, source_question_id NULL)            UNIQUE(exam_id, position)
exam_options     (id, exam_question_id → exam_questions, position, text, correct, image_id NULL → images, image_alt NULL)
                                                                                              UNIQUE(exam_question_id, position)
sessions         (id, exam_id → exams, course_id, class_id, open_at NULL, close_at NULL, started_at NULL, end_at NULL,
                  ended_by NULL, created_by, created_at, updated_at)                           UNIQUE(exam_id, class_id)
attempts         (id, session_id → sessions, exam_id → exams, class_id, user_id, started_at, extra_minutes DEFAULT 0,
                  submitted_at NULL, submit_how 'mahasiswa'|'waktu_habis'|'diakhiri_dosen' NULL, urutan JSON,
                  page_token CHAR(32), last_saved_at NULL)                                     UNIQUE(exam_id, user_id)
answers          (id, attempt_id → attempts, exam_question_id → exam_questions, option_id NULL → exam_options,
                  benar NULL, text NULL, ragu, counter, saved_at)                              UNIQUE(attempt_id, exam_question_id)
integrity_events (id, user_id, attempt_id NULL, session_id NULL, kind, detail JSON, ip, user_agent, created_at)
```

- **The frozen copy.** `exam_groups`, `exam_questions` and `exam_options` are written by publishing and by nothing else: every question, option, key, points value and picture reference of the exam, with `position` 1…n in exam order (the questions of a group are consecutive). Later edits in the bank never reach them. Withdrawing an exam to `draf` deletes them. Ids in every student page and request are ids of the frozen copy, never of the bank.
- `sessions` holds times, not a state. The state is derived: for `per_mahasiswa` the session is **open for starting** while `open_at <= now` and (`close_at` is null or `now < close_at`); for `dikendalikan_dosen` it **runs** from `started_at` until its end, which is `end_at`, and never later than 8 hours after `started_at`. `ended_by` is set by "Akhiri".
- **One attempt per student per exam** is enforced by `UNIQUE(exam_id, user_id)`. `urutan` is `{ "soal": [frozen question ids in this student's order], "opsi": { "<question id>": [option ids in this student's order] } }`, fixed when the attempt is created and never rewritten. With question shuffling the units of the shuffle are groups and questions outside a group; inside a group the order never changes. *Benar* / *Salah* are not shuffled.
- `extra_minutes` is part of the deadline; it is granted through §24. The columns stage U5 added to `attempts` and `sessions` are listed there.
- An answer row with `option_id`, `benar` and `text` all empty is "not answered" (a cleared choice keeps its row and its counter).
- All times are UTC. No attempt, answer or integrity event is ever deleted; a session or an exam can be deleted only while it has no attempt.

### The time rule

Written once, in `App\Libraries\Tenggat`, as SQL over MySQL's clock (`UTC_TIMESTAMP()`); the browser's clock and the container's clock decide nothing. The deadline is not stored:

```
deadline = per_mahasiswa:       attempts.started_at + exams.duration_minutes + extra_minutes
           dikendalikan_dosen:  end of the session (above)                   + extra_minutes
           reopened (§24):      attempts.reopen_until, in place of both
```

- **A student may answer exactly when the attempt is not submitted and the server's time is before its deadline.** An attempt whose deadline has passed is ended whatever its stored status says.
- A save is accepted up to **10 seconds** after the deadline; the grace is not shown and not configurable. Anything later is refused.
- A student who starts just before a `per_mahasiswa` session closes gets the full duration. A student who joins a running `dikendalikan_dosen` session gets the time that is left.

### Exam routes (in addition to §20), for whoever manages course `m`

All under `/mata-kuliah/{m}/ujian/{id}`; a draft route on an exam that is not a `draf` answers `404`.

| Route | Does |
|---|---|
| `GET /sunting` | form of a `draf`: settings, the order of its items, and the bank items not yet in it |
| `POST ` (the exam itself) | fields `judul`, `jenis`, `petunjuk`, `mode`, `durasi`, `acak_soal`, `acak_opsi`; checked by the rules of the exam file (§20). A mistake → `422` with the form |
| `POST /butir` | `butir[]` as in `POST /ujian`: appends bank items |
| `POST /butir/{position}/geser`, `POST /butir/{position}/hapus` | field `arah` `naik` or `turun`: one step; or removes the item (the last one cannot be removed) |
| `POST /terbit` | publishes: writes the frozen copy, `status = terbit`. Refused with `422` and a list of **exactly what is missing** — no question; a `pilihan_ganda` without exactly one correct option; a `benar_salah` without a key; `per_mahasiswa` without a duration — and nothing stored |
| `POST /tarik` | back to `draf`, only while the exam has no attempt (`pesan=ada_pengerjaan` otherwise). Its sessions stay and are shown to students again when it is published again |
| `POST /arsip` | field `arsip` `1` or `0`: `terbit` ↔ `arsip`. Archiving is refused while an attempt is running (`pesan=sedang_berjalan`). An archived exam is in no student's list and its sessions take no action |
| `POST /sesi` | fields `kelas`, and for `per_mahasiswa` `buka`, `tutup` (`YYYY-MM-DDTHH:MM`, Asia/Jakarta): gives a `terbit` exam to a class of this course that is not archived and belongs to the active academic year. At most one session per exam per class |

### Session routes, for whoever may run the session

| Route | Does |
|---|---|
| `GET /sesi/{id}` | state, the server's time, the times of the session, whether the scheduler is alive, and since stage U5 the live view per student with its actions (§24). No answer and no score |
| `GET /sesi/{id}/pratinjau` | the published exam with the student's partial, no key |
| `POST /sesi/{id}/buka`, `/tutup` | `per_mahasiswa`: open from now (a closing time still ahead is kept, one that has passed is cleared), or close now. Closing stops nobody who is working |
| `POST /sesi/{id}/mulai` | `dikendalikan_dosen`: the clock starts for the whole class. Optional `menit` (1–480) or `jam` (`HH:MM`, Asia/Jakarta) sets the end with it |
| `POST /sesi/{id}/akhir` | sets or changes the end of a running session: `menit` from now, or `jam` |
| `POST /sesi/{id}/akhiri` | ends the session **30 seconds from now** for everyone; the end cannot be changed afterwards |
| `POST /sesi/{id}/jadwal`, `/hapus` | managers only: new `buka` / `tutup`; delete a session that has no attempt |

A refused action redirects to the session page with `pesan=g_<reason>` and changes nothing.

### Student routes

All behind `masuk:mahasiswa` and `seb` (below); `{s}` is a session id.

| Route | Does |
|---|---|
| `GET /beranda` (§19, not behind `seb`) | the student's exams: in progress, not taken yet, finished. Title, kind, mode, duration and schedule — nothing of the content |
| `GET /ujian-saya/{s}` | before the start: number of questions, time, instructions, rules, and *Mulai* — or the waiting page, which asks `…/keadaan` every 5 seconds and turns into the exam by itself when the lecturer starts. While running: *Lanjutkan*. Afterwards: the receipt — received, when, how, how many answered; the objective score only when the results of the class are released (§24) |
| `POST /ujian-saya/{s}/mulai` | creates the attempt, or returns the existing one; redirects to `/kerjakan`. Not accepted yet → back with `pesan=belum` |
| `GET /ujian-saya/{s}/kerjakan` | the exam page, loaded once with the whole exam and no key. **Opening it takes the attempt over**: a new `page_token` is written. An attempt that has ended redirects to the receipt |
| `POST /ujian-saya/{s}/kumpulkan` | submits (`submit_how = mahasiswa`); an attempt that has already ended is left as it is |
| `GET /api/sesi/{s}/keadaan[?halaman=<token>]` | JSON `{ menerima, sesi_selesai, pengerjaan: null \| { berjalan, sisa, tanpa_akhir, berlalu, diakhiri, tambahan, dikumpulkan, diambil_alih } }` — `menerima` is also true for a student with a reopening in force (§24); for a reopened attempt `diakhiri` and `tanpa_akhir` are false; `dikumpulkan` is true only when the student submitted (`submit_how = mahasiswa`), not when the time ended the attempt (since stage U6; the page then says "time is up"); `sisa` = seconds to the deadline; `tanpa_akhir` = lecturer-controlled without an end time (the page then shows `berlalu`, the seconds since the start) |
| `POST /api/sesi/{s}/jawaban` | one save (below) |
| `POST /api/sesi/{s}/peristiwa` | one integrity event reported by the exam page, as JSON (§23 "Integrity events") |

**Autosave request.** `POST /api/sesi/{s}/jawaban`, `Content-Type: application/json`, header `X-CSRF-TOKEN`:

```json
{ "halaman": "<page_token>", "soal": 123, "nilai": …, "ragu": false, "n": 7 }
```

`nilai` is `"benar"`, `"salah"` or `null` for `benar_salah`; an option id or `null` for `pilihan_ganda`; a string for `isian` (at most 200 characters) and `uraian` (at most 20,000). `n` is the save counter of that question, counted by the page from the stored value upwards.

| Answer | Meaning |
|---|---|
| `200 { ok, n, disimpan, sisa }` | committed to MySQL. `n` = the counter now stored, `disimpan` = `HH:MM:SS` Asia/Jakarta, `sisa` = seconds left. **A save with a counter not above the stored one changes nothing** and is answered the same way |
| `409 { error }` | never to be accepted, the page stops: `waktu_habis`, `dikumpulkan`, or `diambil_alih` (the page's token is no longer the attempt's) |
| `422 { error }` | this request is wrong (`bentuk`, `soal`, `nilai`); nothing stored |
| `404` | no attempt of this user in that session |
| `429` | more than 240 saves in a minute for one student and session; the page retries. The counter lives in Redis (`lokaujian:laju:simpan:…`); with Redis empty or unreachable every save is accepted |
| `403 { error: "seb_wajib" }` | from filter `seb` |

The order of the checks is: the attempt is this user's; the page still holds it; the time rule; the question belongs to this exam and the value is valid; write. The page shows *Tersimpan* only for a `200`. On a network error, a `5xx` or a `429` it keeps the answer and sends it again until it is accepted; unsent answers live in the page's memory only, never in `localStorage`.

### Safe Exam Browser

Since stage U4 filter `seb` is `App\Filters\SebUjian` and the rules are §23. (In stage U3 it was a stand-in that showed the banner with `disabled` and refused every student with `enabled`.)

### Audit

`App\Libraries\Audit` writes, in the transaction of the change, with `course_id` and for sessions `class_id`:

| `action` | When |
|---|---|
| `ujian.diterbitkan`, `ujian.ditarik`, `ujian.diarsipkan`, `ujian.dipulihkan`, `ujian.dihapus` | the exam's status changes |
| `sesi.dibuat`, `sesi.dijadwalkan`, `sesi.dibuka`, `sesi.ditutup`, `sesi.dimulai`, `sesi.akhir_ditetapkan`, `sesi.diakhiri`, `sesi.dihapus` | a person acts on a session |

A session that opens or closes by its schedule, and an attempt that the scheduler marks, write no audit row: nobody acted. Saving settings and reordering a draft are not audited.

## 23. LokaUjian — Safe Exam Browser per exam, and integrity events (stage U4, `SPEC-LOKAUJIAN.md` §9.1, §9.3)

LokaUjian's own. **A SEB configuration of LokaUjian belongs to one ujian/kuis** (the owner's decision of 2026-11-27), not to a course. From §8 LokaUjian takes, unchanged: the two header formulas and `Seb::periksa`, the settings (`Seb\Setelan`), the plist, SEB-JSON and Config Key (`Seb\Plist`), the encryption (`Seb\Brankas`), the `.seb` file form, the 30-second lease, the heartbeat `POST /api/seb/denyut` with `/js/seb.js`, `GET /seb/keluar`, and the meaning of `SEB_ENFORCEMENT`. The course-based store, panel, list and download of §8 have no route in LokaUjian; `App\Libraries\SumberSeb` gives the package LokaUjian's Redis connection and answers "none" to every question about courses.

### Tables (`lokaujian` schema, migration `2026-11-27-000003`)

```
seb_configs    (id, exam_id → exams ON DELETE CASCADE, version, config_enc MEDIUMBLOB, config_key CHAR(64),
                created_by, created_at, retired_at NULL)            UNIQUE(exam_id, version)
seb_exam_keys  (id, seb_config_id → seb_configs, exam_key CHAR(64), created_by, created_at)
                                                                    UNIQUE(seb_config_id, exam_key)
```

As §8 with `exam_id` in place of `course_id`: at most one row per exam with `retired_at IS NULL`, the current one; rotation retires it and inserts the next `version` with a new `examKeySalt`, and every key registered for the old row stops matching at once. `seb_exam_keys` has the name and shape of §8 on purpose: `Seb::periksa` reads the registered keys from it.

- Settings are those of §8 with `startURL` = `APP_URL/ujian/<exam id>/masuk` and `quitURL` = `APP_URL/seb/keluar`. The file is `LokaUjian-<course code>-<exam id>.seb`.
- A configuration survives withdrawing the exam to `draf` and publishing it again; it is deleted with the exam (only an exam without attempts can be deleted).
- At most 20 Browser Exam Keys per configuration. A key is valid for the file of that exam only.

### Which configuration counts

Filter `seb` (`App\Filters\SebUjian`, always after `masuk`) applies to a `mahasiswa` only; staff are never checked and the User-Agent is never read.

| Request | Candidates given to `Seb::periksa` |
|---|---|
| a path that carries a session, `/ujian-saya/{s}/…` and `/api/sesi/{s}/…` | **only the current configuration of that session's exam** |
| any other path behind the filter: `POST /api/seb/denyut`, `GET /gambar/<kunci>` | the current configurations of every exam among the student's sessions (§22: sessions of their classes, and sessions that hold their own attempt) |

- SEB opened with the file of exam A is refused on exam B with reason `kunci_konfigurasi`, even when both are valid exams of the same class.
- An exam without a configuration → `tanpa_konfigurasi`; with a configuration but no matching registered key → `kunci_peramban`. Nobody can start such an exam while enforcement is on.
- A refusal is `403`: JSON `{ "error": "seb_wajib" }` under `/api/`, otherwise the page `seb_wajib` of the app (it speaks of the file of the exam) with `data-seb-wajib="<reason>"`.
- **Behind the filter:** every student route of §22, the heartbeat, and — for a mahasiswa — `GET /gambar/<kunci>`. **Not behind it:** `GET /beranda`, `GET /ujian-saya/{s}/seb`, `GET /ujian/{id}/masuk`, the account routes of §4 and the public SEB pages.
- Lease keys are `lokaujian:seb:sewa:…` and the rate limit of `seb_rejected` is `lokaujian:laju:seb:<user id>`, both in Redis DB `3`.

### Routes

| Route | Who | Does |
|---|---|---|
| `GET /mata-kuliah/{m}/ujian/{id}/seb` | whoever manages course `m` (§20) | the panel: state, the start address, the registered keys, how to read a Browser Exam Key. Never shows the quit password |
| `POST /mata-kuliah/{m}/ujian/{id}/seb` | same | field `sandi_keluar` (12–200 characters): create, or rotate. **`409` while the exam is running** — a session of it accepts starts, or an attempt is in progress — and nothing changes |
| `POST /mata-kuliah/{m}/ujian/{id}/seb/kunci` | same | field `kunci`: Browser Exam Keys, one per line, replacing the list; allowed while the exam runs |
| `GET /mata-kuliah/{m}/ujian/{id}/seb/unduh` | same | the current `.seb` file; `404` when there is none |
| `GET /ujian-saya/{s}/seb` | mahasiswa who sees session `s` (§22); `masuk:mahasiswa`, **not** behind `seb` | the same file, for download in an ordinary browser from the home list. A session of another class is `404` |
| `GET /ujian/{id}/masuk` | any session; not behind `seb` | the `startURL`: redirects a student to `/ujian-saya/<their session of that exam>`, everybody else and every unknown id to `/beranda`. Shows nothing |
| `GET /seb` | public, `noindex` | instructions; names the state of enforcement; **lists no exam and links to no file** |
| `GET /seb/periksa` | public, `noindex` | whether each of the two headers arrived on this request (`data-seb-periksa` = `ada_header` or `tanpa_header`); matches them against nothing |
| `GET /seb/keluar` | public | the `quitURL` (the shared controller of §8) |
| `POST /api/seb/denyut` | mahasiswa, behind `seb` | heartbeat, `{ "ok": true, "sewa": 30 }` |
| `GET /sesi/{id}/peristiwa` | whoever may run the session (§22) | the integrity events of that session, newest first, at most 500: time, student, kind, detail. Its CSV export is `POST /sesi/{id}/csv` with `bentuk=peristiwa` (§24) |

- The student's home lists, next to each exam not yet finished, the link to its file — or "not available yet" when the exam has no configuration and enforcement is on.
- The exam page and the session page show one of three states: no configuration; a configuration without a registered key; ready (`data-seb-ringkas` = `tanpa`, `tanpa_kunci`, `siap`).

### Integrity events

`integrity_events.kind`, each with `user_id`, `session_id`, `attempt_id` (when the student has one), IP address and user agent:

| `kind` | Written by | `detail` |
|---|---|---|
| `seb_terminated` | filter `seb`: the refused request still held a lease (always recorded; the lease is deleted) | `{ alasan, jalur }` |
| `seb_rejected` | filter `seb`: any other refusal, at most one per user per minute | `{ alasan, jalur }` |
| `paste_blocked` | the exam page (since U3) | `{ jalur }`, one of `paste`, `keyboard`, `shift_insert`, `drop`, `middle_click`, `context_menu`, `copy`, `cut` |
| `focus_lost` | the exam page, when focus or visibility returns | `{ durasi }` seconds, 0–86,400 |
| `display_changed` | the exam page, when the screen size or the number of displays differs from before | `{ lebar, tinggi, layar }` — `layar` is `screen.isExtended` or `null` |
| `reconnected` | the exam page, when a request succeeds more than 60 seconds after the last one that did | `{ detik }`, 60–86,400 |
| `page_taken_over` | the save endpoint (since U3) | `{ halaman }`, once per page that was taken over |

The page reports through `POST /api/sesi/{s}/peristiwa` with JSON `{ "jenis": <kind>, …the fields of detail }`. Only the four kinds the page owns are accepted, every value is checked, and nothing else from the request is stored; any other `jenis` → `422`. At most 60 events a minute per student and session (`429`). A user with `paste_allowed` (§3) may paste, and no `paste_blocked` is recorded for them. Events report; they stop nothing.

### Audit

`seb.dibuat`, `seb.dirotasi` (`{ versi }`), `seb.kunci` (`{ versi, jumlah }`), with `target_tipe = ujian`, the exam id and the course id — never a key or a password.

## 24. LokaUjian — lecturer control, scoring, release and export (stage U5, `SPEC-LOKAUJIAN.md` §7.3, §10, §11, §12, §13)

LokaUjian's own; no other component reads these tables or calls these routes. Stage U5 added no environment variable; stage U6 added `JUDGE_QUEUE_REDIS_URL` (§10) for the judge queue warning below.

### Tables (`lokaujian` schema, migration `2026-11-27-000004`)

```
attempts     + score DECIMAL(8,2) NULL, scored_at NULL, reopen_until NULL, reopened_minutes DEFAULT 0
sessions     + release_level 'nilai'|'nilai_dan_kunci' NULL, released_at NULL, released_by NULL
time_grants  (id, session_id → sessions ON DELETE CASCADE, user_id, kind 'tambahan'|'buka_ulang', minutes,
              reason NULL, until_at NULL, created_by, created_at)
```

- `time_grants` is the record of every grant; it is only inserted. Its rows go with a session that is deleted, which is possible only while the session has no attempt (§22).
- Presence for the live view is **not** in MySQL: Redis key `lokaujian:hadir:<session id>:<user id>` = `tunggu|<unix time>` or `kerja|<unix time>`, written by `GET /api/sesi/{s}/keadaan` and by every accepted save. Nothing decides anything from it; with Redis empty the live view says "unknown" until the next request of the student's page.

### Scoring (`App\Libraries\Nilai`)

- `benar_salah` and `pilihan_ganda`: full points for the correct answer, 0 otherwise, 0 without an answer. `isian` and `uraian` have no score in the app.
- `attempts.score` is the **objective score**. It is written when the attempt ends — in the transaction of *Kumpulkan*, or by the pass of §21 — and is always computed in full from the answers and the frozen copy, by one statement. The points available (`SUM(points)` of the automatically scored questions) are never stored.
- A running attempt has `score = NULL`; reopening an attempt clears its score until it ends again.
- There is no total, no percentage and no pass mark.

### Extra time and reopening (`App\Libraries\Kendali`), for whoever may run the session (§22)

The participants of a session are the `AKTIF` students of its class and every owner of an attempt in it; a user id outside that set is refused (`asing`).

| Route | Fields | Does |
|---|---|---|
| `POST /sesi/{id}/tambah-waktu` | `mahasiswa[]` (user ids), `menit` 1–240, `yakin` | adds the minutes for every listed participant. With an attempt in progress: `extra_minutes += menit` (and `reopen_until += menit` while a reopening is in force). Without an attempt: the grant waits in `time_grants` and becomes the attempt's `extra_minutes` when the student starts. **One participant who does not qualify refuses the whole request**: an attempt that has ended, or no attempt in a session that no longer accepts starts (`sudah_berakhir`) |
| `POST /sesi/{id}/buka-ulang` | `mahasiswa` (one user id), `menit` 1–600, `alasan` 1–500 characters, `yakin` | for an attempt that has ended: the **same** attempt runs again with every answer and the same order — `submitted_at`, `submit_how` and `score` are cleared, `reopen_until = now + menit`, `reopened_minutes += menit`. For a participant without an attempt in a session that no longer accepts starts: the grant is stored with `until_at = now + menit`; the student may then start, and the new attempt gets `reopen_until` = that time. Refused: an attempt still in progress (`masih_berjalan`); no attempt while the session still accepts starts (`masih_bisa_mulai`); **the class is released at `nilai_dan_kunci`** (`dirilis`) |

- **Two steps.** Without `yakin=1` the request is only checked and answered `200` with a page that names the students and the minutes (and the reason), with nothing changed; the same fields with `yakin=1` apply it. A refusal redirects to the session page with `pesan=g_<reason>` and changes nothing.
- Grants only add; no route takes one back. The session itself does not change for anybody else.
- Both work only while the exam is `terbit`.

### Live view

`GET /sesi/{id}/langsung`, for whoever may run the session; the session page asks it every 5 seconds (`/js/langsung.js`) and copies the texts into its table without reloading. JSON:

```
{ kini: "HH:MM:SS", keadaan, akhir: "HH:MM:SS"|null, penjadwal: bool, usai: bool, judge: { antre, sibuk }, judge_teks,
  ringkas: { peserta, hadir, mengerjakan, terputus, selesai }, ringkas_teks, hadir_diketahui: bool,
  baris: [ { id, nomor_induk, nama, aktif, status, tersambung, sejak, cara, terjawab, total, simpan, sisa,
             tambahan, buka_ulang, peristiwa, teks: { status, terjawab, simpan, sisa, tambahan, peristiwa } } ] }
```

- One row per participant, ordered by nomor induk. `status` is `belum_hadir`, `menunggu` (no attempt, seen within 30 seconds), `mengerjakan` or `selesai` (`cara` = `mahasiswa`, `waktu_habis`, `diakhiri_dosen`). For `mengerjakan`: `tersambung` is true when the student's page was seen within 30 seconds, false otherwise (`sejak` = `HH:MM` last seen, or null), and null when Redis cannot be read; `sisa` = seconds to the deadline, null without an end time. `aktif` is false for the owner of an attempt who is no longer `AKTIF` in the class. `teks` holds the same values as sentences in the requester's language.
- Times are Asia/Jakarta. **No answer and no score is in this answer or on the session page.**
- When the state of the session or the list of participants differs from the page, the page shows a notice with a reload link; it never reloads by itself.

### Judge queue warning (stage U6, `SPEC-LOKAUJIAN.md` §14)

The session page and the live view carry one more thing: whether LokaKode's judge has jobs waiting (`App\Libraries\AntreanJudge`, reading §6 through `JUDGE_QUEUE_REDIS_URL`).

- `GET /sesi/{id}/langsung` gains `judge: { antre: <jobs waiting now in both queues, or null when that Redis cannot be read>, sibuk: <bool> }` and `judge_teks` (the sentence in the requester's language). `sibuk` is true while `antre > 0` and for 60 seconds afterwards — remembered in LokaUjian's own Redis as `lokaujian:judge:antre` — so the notice does not flicker between two polls.
- The session page shows the sentence in an element `data-judge="sibuk|lengang"`, hidden while `lengang`; the live script shows and hides it.
- It is a notice only: nothing is refused or delayed because of it, no student page carries it, and when Redis cannot be read it stays hidden.

### Correction (`App\Libraries\Koreksi`), for whoever manages the course (§20)

| Route | Does |
|---|---|
| `GET /mata-kuliah/{m}/ujian/{id}/koreksi` | every question of the frozen copy of a `terbit` or `arsip` exam **with its key**, and one form per question. A `draf` is `404` |
| `POST /mata-kuliah/{m}/ujian/{id}/koreksi/{question id}` | fields `poin` (0.25–100 in steps of 0.25; a comma is accepted) and `kunci` (`benar`/`salah` for `benar_salah`; an option id of that question for `pilihan_ganda`; must be empty for `isian` and `uraian`). An empty field means "unchanged". Redirects with `pesan=dikoreksi&p=<attempts rescored>&b=<scores that changed>`, or `pesan=g_poin`, `g_kunci`, `g_sama` (nothing differs) with nothing changed |

- Only key and points change; text, options and order never do. The bank's own question is not touched. Allowed from publication on, also while attempts are running.
- In one transaction: the question is changed, **every submitted attempt of the exam is rescored in full**, and `soal.dikoreksi` is written. A student whose class is released sees the new score at once.

### Release of results, for whoever manages the course

| Route | Does |
|---|---|
| `POST /sesi/{id}/rilis` | field `tingkat` = `nilai` or `nilai_dan_kunci`: releases the results of **that class**. Only when the session is **over** — it accepts no start by its times (a `per_mahasiswa` session opened "until closed" is not over) and no attempt of it is in progress — else `g_belum_usai`. `nilai_dan_kunci` while another class of the same exam is not over answers `200` with a warning that names those classes, nothing changed; the same request with `yakin=1` releases |
| `POST /sesi/{id}/rilis/tarik` | withdraws the release |

Both answer `404` to a pengajar, as `/jadwal` does.

| Level | The student sees |
|---|---|
| none (default) | the receipt only |
| `nilai` | on `/beranda` and on the receipt: the objective score out of the objective points, and the sentence that short and long answers are marked by the lecturer outside the app |
| `nilai_dan_kunci` | that, and `GET /ujian-saya/{s}/tinjau` — **behind `seb`, with the configuration of that exam** (§23): each question in the student's own order, the student's own answer, and for automatically scored questions the correct answer. `/beranda` then keeps the link to the exam's `.seb` file. Without that release, or while the attempt runs, the route redirects to the receipt |

### CSV

`POST /sesi/{id}/csv` (one session; `bentuk` = `panjang`, `lebar` or `peristiwa`) for whoever may run it, and `POST /ujian/{exam id}/csv` (`panjang` or `lebar`) for **all sessions of that exam the requester may run** — a pengajar gets their own classes only; an exam with no such session is `404`. Field `pemisah`: `titik_koma` (default) or `koma`.

- `Content-Type: text/csv; charset=utf-8`, `Cache-Control: no-store`, `Content-Disposition: attachment; filename="lokaujian-<course code>-ujian<exam id>-<class name | semua-kelas>-<YYYYMMDD>-<bentuk>.csv"` (date in Asia/Jakarta; names reduced to letters, digits and `-`).
- **Form** (`App\Libraries\Csv`): UTF-8 with BOM; every record ends with CRLF; a cell is quoted when it holds the separator, a quote, a line break or outer white space, quotes doubled; a line break inside an answer stays LF inside its quoted cell. **A text cell that begins with `=`, `+`, `-`, `@`, tab or CR gets a leading apostrophe.** Numbers written by the app are plain, with the decimal sign that belongs to the separator: a comma with `;`, a point with `,`. Times are `YYYY-MM-DD HH:MM:SS` in Asia/Jakarta. Headers and fixed words are in the requester's language.
- **Long** — one row per question for every participant **with an attempt**: class, nomor induk, name, question number (exam order), question number as that student saw it, frozen question id, group number, type, question text (plain, first 200 characters), answer, key, correct (`1`/`0`; empty for `isian` and `uraian`), maximum points, points (empty for `isian` and `uraian`), time of the last save. **It contains every key.**
- **Wide** — one row per participant, those who never started included: class, nomor induk, name, status (not started / working / finished; with "no longer in this class" for the owner of an attempt who left it), started, submitted, how it was submitted, extra minutes, reopening minutes, questions answered, objective score (empty while the attempt runs), objective points available, points to be marked by hand, number of integrity events, then one column per question in exam order holding the answer.
- A `pilihan_ganda` answer is the option's letter **in exam order** and its plain text, or `[gambar: <alternative text>]` for an option that is only a picture; `benar_salah` is *Benar* / *Salah*; text answers are complete.
- **Events** — time, class, nomor induk, name, kind, detail (`key: value; …`), oldest first.
- An export may be taken while the session runs; the wide file's status column then says who is still working.

### Audit page and audit actions

`GET /audit` (`masuk:superadmin`): `audit_log`, newest first, 50 rows a page (`hal`), filtered by `aksi` (exact action), `matkul` (course id), `pelaku` (nomor induk), `dari` / `sampai` (dates in Asia/Jakarta). It only reads; no route of any method changes or deletes a row.

| `action` | When | `detail` |
|---|---|---|
| `waktu.ditambah` | extra time, one row per student (`target_tipe = sesi`) | `{ ujian, mahasiswa, menit, sudah_mulai }` |
| `pengerjaan.dibuka_ulang` | reopening (`target_tipe = sesi`) | `{ ujian, mahasiswa, menit, alasan, sudah_mulai }` |
| `soal.dikoreksi` | key or points corrected (`target_tipe = ujian`) | `{ soal (number), tipe, poin_lama, poin_baru, kunci_lama, kunci_baru, pengerjaan, nilai_berubah }` — the key as `benar`/`salah` or the option's letter; this is the only audit row that holds a key |
| `hasil.dirilis`, `hasil.ditarik` | release or withdrawal (`target_tipe = sesi`) | `{ tingkat, sebelumnya, kelas_belum_usai }` / `{ tingkat }` |
| `csv.diekspor` | a CSV handed out (`target_tipe = ujian`; `class_id` only for one session) | `{ bentuk, sesi[], baris, pemisah, berkas }` |
| `data.diekspor` | own-data export (§14) | `{ terpotong }` |
