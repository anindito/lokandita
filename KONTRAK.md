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
├── packages/lokandita-auth/    Composer path package used by all three apps
├── judge/                      Python worker, images, seccomp profile, tests
├── infra/                      compose files, Caddyfile, scripts
├── docs/                       PETA-KONSEP.md, silabus/, branding/
├── SPEC.md  KONTRAK.md  CLAUDE.md  PROGRESS.md  DECISIONS.md

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

`GET AUTH_PUBLIC_URL/keluar-selesai` is where a client app sends the browser after it has revoked the session through §5. LokaAuth clears the cookie there only when the session is already dead, so the link cannot be used to log somebody else out.

CSRF and other per-app cookies use the `__Host-` prefix and therefore carry no `Domain` attribute: `__Host-csrf` (form field `csrf`) and `__Host-bahasa` (language chosen before login), and on LokaAuth `__Host-tunda` (a login whose password step passed and whose TOTP step has not; state in Redis for 10 minutes, key prefix `auth:tunda:`). No row exists in `sessions` until every login step has passed.

Apps set `Secure` cookies and therefore answer `500` to a cookie-setting page requested without `X-Forwarded-Proto: https` from a trusted proxy. Requests reach apps only through Caddy, which sends it. `/healthz` and `/internal/*` set no cookies and are exempt from CSRF, so they work over plain HTTP inside the Docker networks. Apps take the client address from `X-Forwarded-For` only when the request comes from a private (Docker) address.

## 3. Identity schema (`identity` database)

Owned and migrated only by LokaAuth.

```
users
  id CHAR(36) PK (UUID v4)        nomor_induk VARCHAR(40) UNIQUE NOT NULL
  email VARCHAR(190) UNIQUE NULL  nama VARCHAR(190) NOT NULL
  password_hash VARCHAR(255)      must_change_password TINYINT(1) DEFAULT 1
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
4. Check `app_access` for this app with `enabled = 1`. Missing → 403 page with a link to the Portal.
5. If `last_seen_at` is older than 5 minutes, call `POST /internal/session/touch` (§5). A failure here is logged and does **not** fail the request.

`next` is honoured by LokaAuth only when the scheme is `https` and the host is exactly one of `ALLOWED_HOSTS`.

The five steps are `Lokandita\Auth\Filters\RequireLogin` (filter alias `masuk`). Notes:

- The session is read from MySQL on every request; nothing about it is cached in the app.
- `next` and every redirect are built from `APP_URL`, never from the client's `Host` header.
- After a successful touch the filter sends the unchanged cookie again with a fresh `Max-Age` (§2). This is the only case in which code outside LokaAuth emits `lokandita_sid`, and it is the shared package doing it.

Every app, LokaAuth included, answers these three routes; in client apps they are registered by `Lokandita\Auth\Routes::akun()`. All are `POST` with the CSRF field `csrf`:

| Route | Does | Answer |
|---|---|---|
| `/keluar` | `revoke` through §5 | redirect to `AUTH_PUBLIC_URL/keluar-selesai`; if LokaAuth cannot be reached: `502` and a page saying the user is **not** logged out |
| `/sesi/perpanjang` | `touch` through §5, re-sends the cookie | `{ "sisa": <seconds> }`, or `502 {"error":"tidak_bisa_diperpanjang"}` |
| `/preferensi` | `prefs` through §5; form fields `locale`, `idle_logout` (`0`/`1`), `kembali` (local path) | redirect to `kembali` |

The CSRF token is not regenerated after use and lives 12 hours, so a page that stays open, a second tab, and the background `/sesi/perpanjang` call do not invalidate each other.

`GET /` and `GET /beranda` in the Portal are temporary pages of stage 5 (`Lokandita\Auth\Routes::sementara()`), replaced in stage 11. LokaKode has its own `/beranda` since stage 6; its `GET /` needs no session and only redirects to `/beranda` until the public landing of stage 11.

Client apps read names and nomor induk from `user_directory`, and app access from `app_access`, through `Lokandita\Auth\UserDirectory` (same `identity_reader` connection as the session check, `Lokandita\Auth\ReaderDb`). They do not copy this data into their own schema; a `user_id` stored by a client app is `users.id`.

## 5. Internal API (LokaAuth)

| Property | Value |
|---|---|
| Base URL from other containers | `http://auth-internal:8080` (`AUTH_INTERNAL_URL`) |
| Network | Docker network `internal` only |
| Authentication | Header `X-Service-Token: <token>`; LokaAuth holds `SERVICE_TOKEN_PORTAL`, `SERVICE_TOKEN_LOKAKODE`, …; each client holds its own as `AUTH_SERVICE_TOKEN` |
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

The service token identifies the app; the audit log stores it as `app`.

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

- Web inserts the row, commits, then `LPUSH` the id to `lokandita:judge:normal` or `lokandita:judge:low` (Redis DB 0).
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
                        anonymize_leaderboard, practice_after_close, published_at NULL, published_by
meeting_class_windows (id, meeting_id, class_id, course_id, open_at, close_at, override 'none'|'open'|'closed',
                       late_policy, late_factor, late_until NULL, post_close_visibility, updated_by, …)   UNIQUE(meeting_id, class_id)
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
- `problem_scores` and `meeting_scores` are derived from `submissions` and are always recomputed in full for one student and one meeting; they are never adjusted incrementally. `points` is 3 / 5 / 8 / 10 / 15 by `difficulty` of the version the student's instance was made from.
- `submissions.tests` is the worker's per-test list `{ index, verdict, duration_ms }`; nothing in these tables holds test input or expected output.
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
- Every problem that passes this check is sent to the judge as one `VALIDATE` job (§6) with the meeting's allowed concepts, and is stored only when the judge answers `ok`. It is stored as a new `problem_versions` row with status `PENDING_REVIEW`.
- Idempotent by `slug` within the meeting: a problem whose content equals the latest stored version (any status) is reported "unchanged" and creates nothing; changed content creates the next version. The meeting keeps using its approved version until the new one is approved.
- The report names, per problem: accepted, rejected or unchanged; for each reason the part (`generator`, reference solution, or the field), line and column inside that source, and the line of that key in the JSON file.
- Ways in: `php spark soal:impor [file] --oleh <nomor induk of an active superadmin>` (without a file argument it reads the file from standard input; exit code 0 only when the file is accepted and no problem is rejected), the web upload at `POST /impor` (superadmin only, field `berkas`, `.json`), and the edit form of one problem (superadmin and the pengampu), which sends that one problem through the same check and validation. `php spark soal:ekspor <course> <meeting>` prints the latest version of every problem of a meeting in this format; importing that output changes nothing.
- Approval is the web action *Setujui soal ini* (superadmin and the pengampu) or `php spark soal:setujui <course> <meeting> --oleh <nomor induk> [--slug a,b]`, which approves the latest version of every problem of that meeting that is `PENDING_REVIEW`, as that active superadmin or as the pengampu of the course. Both go through `App\Libraries\Soal\Tinjau::setujui`; the command's audit rows carry `lewat: cli`.
- An import is finished by the request that next looks at it (`GET /impor/{id}`, or the CLI's own loop); there is no background process on the web side. Until then nothing reaches students, because a new version always awaits review.
- Every import writes one row to LokaKode's `audit_log`: `soal.impor` (or `soal.impor.ditolak`, `soal.disunting`) with the actor, file name, source and the counts.

## 8. Safe Exam Browser verification

For each request from a mahasiswa, with `url` = `APP_URL` origin + request path + query (no fragment; the client's `Host` header is not trusted):

```
X-SafeExamBrowser-ConfigKeyHash  ==  hex( SHA256( url + config_key ) )
X-SafeExamBrowser-RequestHash    ==  hex( SHA256( url + browser_exam_key ) )   for any registered key of the course
```

Both must match. `config_key` is computed by the server from the generated configuration exactly as defined by the SEB developer documentation; the `.seb` file is the same configuration, so the two cannot drift. Configurations are stored encrypted with `SEB_ENCRYPTION_KEY`.

## 9. Docker networks and names

| Network | Members | Purpose |
|---|---|---|
| `edge` | caddy, auth, lokakode, portal | Public HTTP from Caddy to apps |
| `internal` | auth (alias `auth-internal`), lokakode, portal | App-to-LokaAuth calls |
| `data` | auth, lokakode, portal, mysql, redis | Database and cache |

- Container names: `caddy`, `mysql`, `redis`, `auth`, `lokakode`, `portal`.
- Every app container serves HTTP on port **8080**. No app publishes a port to the host.
- Caddy reaches apps by container name on `edge`. Apps reach LokaAuth **only** by `auth-internal` on `internal`.
- MySQL `3306` and Redis `6379` are published on `127.0.0.1` only, for the worker and for CLI use.
- LokaAuth must not decide trust from the caller's IP or subnet.
- The `internal` network is created with `internal: true`: it has no route out of the host.
- Compose project name is `lokandita`; the file is `infra/compose.yml`. App images are `lokandita/<app>:<commit>`, and the one in use also carries the tag `aktif`.

### Edge (Caddy)

- Public hosts are derived from `DOMAIN`: `DOMAIN` → `portal`, `www.DOMAIN` → 301 to `DOMAIN`, `auth.DOMAIN` → `auth`, `lokakode.DOMAIN` → `lokakode`.
- `/internal` and `/internal/*` are answered `404` by Caddy on all four hosts before anything else, whether the host is open or in maintenance.
- A host is in **maintenance** unless the file `infra/caddy/bendera/<app>.buka` exists (`infra/pemeliharaan.sh <app> on|off`, `status`). The flag is server state and is git-ignored. A fresh checkout is therefore fully in maintenance.
- Maintenance answers every path with `503`, `Retry-After: 3600` and the static page in `infra/caddy/situs/`; its assets live under `/_pemeliharaan/`.
- Caddy sets `Strict-Transport-Security` on every response. All other security headers are set by Caddy only on the maintenance page; on an open host they are the app's job (`SPEC.md` §16).

## 10. Environment variables

One `.env` per component, in the component's own directory, git-ignored, mode 600. The same file is used at build time and at run time. A committed `.env.example` lists every key. `infra/cek-env.sh` fails when `.env` lacks a key present in `.env.example`.

Each app validates its required variables at start and exits naming the missing one.

| Variable | auth | lokakode | portal | judge |
|---|---|---|---|---|
| `APP_URL` | ✔ | ✔ | ✔ | |
| `CONTACT_EMAIL` | ✔ | ✔ | ✔ | |
| `COOKIE_DOMAIN` (`.lokandita.com`) | ✔ | ✔ | ✔ | |
| `ALLOWED_HOSTS` (comma list) | ✔ | | | |
| `PORTAL_URL` | ✔ | ✔ | ✔ | |
| `DB_DSN` (own schema) | ✔ | ✔ | ✔ | ✔ |
| `IDENTITY_READER_DSN` | | ✔ | ✔ | |
| `REDIS_URL` | ✔ | ✔ | ✔ | ✔ |
| `AUTH_PUBLIC_URL`, `AUTH_LOGIN_URL` | | ✔ | ✔ | |
| `AUTH_INTERNAL_URL` (`http://auth-internal:8080`) | | ✔ | ✔ | |
| `AUTH_SERVICE_TOKEN` | | ✔ | ✔ | |
| `SERVICE_TOKEN_PORTAL`, `SERVICE_TOKEN_LOKAKODE` | ✔ | | | |
| `MFA_ENCRYPTION_KEY` | ✔ | | | |
| `INSTANCE_SECRET` | | ✔ | | |
| `SEB_ENCRYPTION_KEY`, `SEB_ENFORCEMENT` | | ✔ | | |
| `JUDGE_CONCURRENCY` | | | | ✔ |

`infra/.env` (Caddy, MySQL, Redis, backup):

| Variable | Used for |
|---|---|
| `DOMAIN` | Public host names (§9) |
| `CONTACT_EMAIL` | Shown on the maintenance page |
| `ACME_EMAIL`, `ACME_CA` | Let's Encrypt account address and directory URL |
| `MYSQL_ROOT_PASSWORD` | MySQL root; used only by `infra/` scripts through `docker exec` |
| `DB_PASSWORD_AUTH`, `DB_PASSWORD_LOKAKODE`, `DB_PASSWORD_PORTAL`, `DB_PASSWORD_IDENTITY_READER`, `DB_PASSWORD_JUDGE` | Passwords of the MySQL users below; each must equal the password inside the matching DSN |
| `REDIS_PASSWORD` | Redis `requirepass`; must equal the password inside every `REDIS_URL` |
| `BACKUP_DIR`, `BACKUP_RETENTION_DAYS` | `infra/backup.sh` |

Formats:

- `DB_DSN`, `IDENTITY_READER_DSN`: `MySQLi://<user>:<password>@mysql:3306/<schema>`. Apps parse it with `Lokandita\Auth\Dsn`, never through CI4's own DSN parser.
- `REDIS_URL`: `redis://:<password>@redis:6379/<db>`. Redis DB `0` = lokakode (and the judge queue, §6), `1` = auth, `2` = portal.
- Secrets are lowercase hex (`openssl rand -hex 32`), so they are safe inside a URL without escaping.

MySQL schemas and users, created by `infra/mysql-init.sh` (idempotent, never touches tables):

| User | Rights |
|---|---|
| `auth_app` | all on `identity`, `identity_test` |
| `lokakode_app` | all on `lokakode`, `lokakode_test` |
| `portal_app` | all on `portal`, `portal_test` |
| `identity_reader` | none from `mysql-init.sh`; `SELECT` on the three objects of §3 from `infra/mysql-hak-reader.sh` |
| `judge_worker` | none from `mysql-init.sh`; `SELECT, UPDATE` on `judge_jobs` from `infra/mysql-hak-judge.sh` (§6) |

Rules:

- **`.env.example` is the list of required variables.** The container entrypoint reads the app's `.env.example` and refuses to start, naming the variable, when any key in it is missing or empty in the environment. Adding a key to `.env.example` therefore adds it to startup validation.
- The `.env` file reaches the container through `env_file` in Compose; it is never copied into an image.
- In `.env.example`, `{{NAME}}` marks a secret. `infra/buat-env.sh` creates all `.env` files once on a new server and refuses to run if any exists. `infra/cek-env.sh` also fails on an empty value, a leftover `{{…}}`, a quoted value, or a file mode other than 600.
- `LOKANDITA_APP` and `LOKANDITA_COMMIT` are baked into the image by `infra/deploy.sh`. They are not `.env` variables.
- `judge/.env` holds `DB_DSN` (user `judge_worker`), `REDIS_URL` (DB `0`) and `JUDGE_CONCURRENCY` (1–32). The worker runs on the host, so both addresses use `127.0.0.1` instead of the container names. The worker reads `judge/.env.example` as its list of required variables and refuses to start, naming the variable, when one is missing or empty.

`INSTANCE_SECRET` must never change once students have instances. It and `SEB_ENCRYPTION_KEY` are included in every backup.

Values are written without quotes.

## 11. Deploy and verification

- `infra/deploy.sh <app>` builds the image tagged with the current Git commit, runs the app's migrations from that image (and for `auth` the reader grants of §3), starts it, and fails unless the running container reports that commit at `GET /healthz` (`{ "app": "...", "commit": "..." }`).
- `infra/deploy.sh infra` starts MySQL, Redis and Caddy, runs `infra/mysql-init.sh`, and reloads the Caddyfile.
- `deploy.sh` refuses to run when the working tree has uncommitted or untracked changes, runs `infra/cek-env.sh` first, and warns when the commit is not on `origin`.
- `/healthz` is served by every app container on port 8080 with `Cache-Control: no-store`. `deploy.sh` reads it from inside the container, so it works while the host is in maintenance.
- `infra/smoke.sh` checks, with real requests:
  1. each public host answers over HTTPS;
  2. `/internal/anything` returns 404 from the internet on every host;
  3. from inside `lokakode` and `portal`, `POST auth-internal:8080/internal/session/touch` with the app's token and a bogus `sid` returns **422 or 404-with-JSON, not 401** — proving network, name and token are right;
  4. the same call with a wrong token returns 401;
  5. `identity_reader` can select from `session_identity` and cannot select from `users`;
  6. the judge worker is running and a trivial `RUN` job completes.
  7. the judge validates a small built-in problem: one `VALIDATE` job through LokaKode (`php spark judge:coba --validasi`) answers `ok` after 240 tests; nothing is stored in the problem bank.
- `smoke.sh` additionally checks (as check 0) that every app container reports the current `HEAD` at `/healthz`.
- In check 1 a host in maintenance must answer `503`, an open host `2xx`/`3xx`, and `www` a `301` to the apex; the certificate must validate.
- Checks 3–5 become active in stage 5, check 6 in stage 7 and check 7 in stage 8, by a switch at the top of `smoke.sh`. Until then each prints `BELUM AKTIF`, is counted separately, and is not reported as passed.
- `infra/tes-infra.sh` tests §9, §10 and the deploy verification against the running containers.
- A stage is not finished until `smoke.sh` passes after deploy.

## 12. Tests

- `infra/tes.sh <app>` is the only way to run PHPUnit. It builds `lokandita/<app>:tes`, recreates the `*_test` schemas from nothing, runs the LokaAuth migrations on `identity_test`, and refuses any schema whose name does not end in `_test`.
- A second LokaAuth named `auth-tes` runs for the duration of the tests on `identity_test` and Redis DB `15`. Its alias on the `internal` network is `auth-internal-tes`; client apps under test use `AUTH_INTERNAL_URL=http://auth-internal-tes:8080`. When a client app is tested it is also served as `<app>-tes`.
- Tests talk to these containers over real HTTP (`Lokandita\Auth\Testing\Http`) and to MySQL directly. Nothing is mocked.
- Redis DB `15` is reserved for tests and is flushed at the start of every run.
- For `lokakode`, `infra/tes.sh` also starts a headless Chromium (`peramban-tes`, image pinned by digest) and a second Caddy (`caddy-tes`) that serves `https://lokakode.lokandita.com` with Caddy's internal certificate and forwards to `lokakode-tes`, as the production edge does. The browser sits only on the Docker network `tes-peramban`; `lokakode.lokandita.com` is mapped to `caddy-tes` and every other host name is made unresolvable, so a browser test cannot reach a production site. PHPUnit drives it over the Chrome DevTools Protocol (`Tests\Support\Peramban`, address in `PERAMBAN_TES_URL`); the tests in `tests/Peramban/` fail, not skip, when it is missing.
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

The formats of the teaching-assignment and enrollment files and the commands that read them are added here together with their code.
