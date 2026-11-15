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

CSRF and other per-app cookies use the `__Host-` prefix and therefore carry no `Domain` attribute: `__Host-csrf` (form field `csrf`) and `__Host-bahasa` (language chosen before login).

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

Authorisation of *who may reset whom* is decided by the calling app before it calls; LokaAuth records `requested_by` and the calling app in the audit log.

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

`progress`: `{ "state": "queued|compiling|running|done", "current": n, "total": m }`

`payload` by kind:

| Kind | Payload |
|---|---|
| `RUN` | `{ language, source, stdin? , instance_id? }` |
| `SUBMIT` | `{ language, source, instance_id, submission_id }` |
| `GENERATE` | `{ problem_version_id, user_id, meeting_problem_id, seed }` |
| `VALIDATE` | `{ language, generator, reference_solution, samples_count, hidden_count, time_limit_ms, allowed_concepts[], comparison_mode }` |
| `ANALYZE` | `{ language, source, required_concepts[] }` |

`result` verdict codes: `AC`, `WA`, `CE`, `RE`, `TLE`, `MLE`, `OLE`, `CONCEPT_NOT_MET`, and for a job as a whole `FAILED`.

For `SUBMIT` the result holds per-test `{ index, verdict, duration_ms }`, `compile_log`, `passed`, `total`, and the list of concepts found. It never holds test input or expected output.

For `VALIDATE` the result holds `{ ok, reasons[] }` where a reason is `{ code, message, line?, column? }`, plus `required_concepts[]` detected in the reference solution.

The worker connects to MySQL and Redis on `127.0.0.1` (published to loopback only) with its own MySQL user limited to the tables it needs.

Judge containers carry the label `lokandita.judge=true`.

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
      "translations": {
        "id": { "title": "", "statement": "", "input_format": "", "output_format": "", "constraints": "" },
        "en": { }, "ar": { }
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
| `samples_count` | 2–3 |
| `hidden_count` | ≥ 10 |
| `translations.id` | Required; `en`, `ar` optional |
| `generator` | Python; reads env `SEED` (int) and `INDEX` (0 … samples+hidden−1); prints one input; deterministic |

A file whose top level is a one-element array wrapping this object is accepted and unwrapped.

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
| `COOKIE_DOMAIN` (`.lokandita.com`) | ✔ | | | |
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
| `DB_PASSWORD_AUTH`, `DB_PASSWORD_LOKAKODE`, `DB_PASSWORD_PORTAL`, `DB_PASSWORD_IDENTITY_READER` | Passwords of the MySQL users below; each must equal the password inside the matching DSN |
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

Rules:

- **`.env.example` is the list of required variables.** The container entrypoint reads the app's `.env.example` and refuses to start, naming the variable, when any key in it is missing or empty in the environment. Adding a key to `.env.example` therefore adds it to startup validation.
- The `.env` file reaches the container through `env_file` in Compose; it is never copied into an image.
- In `.env.example`, `{{NAME}}` marks a secret. `infra/buat-env.sh` creates all `.env` files once on a new server and refuses to run if any exists. `infra/cek-env.sh` also fails on an empty value, a leftover `{{…}}`, a quoted value, or a file mode other than 600.
- `LOKANDITA_APP` and `LOKANDITA_COMMIT` are baked into the image by `infra/deploy.sh`. They are not `.env` variables.
- `judge/.env` is defined in stage 7.

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
- `smoke.sh` additionally checks (as check 0) that every app container reports the current `HEAD` at `/healthz`.
- In check 1 a host in maintenance must answer `503`, an open host `2xx`/`3xx`, and `www` a `301` to the apex; the certificate must validate.
- Checks 3–5 become active in stage 5 and check 6 in stage 7, by a switch at the top of `smoke.sh`. Until then each prints `BELUM AKTIF`, is counted separately, and is not reported as passed.
- `infra/tes-infra.sh` tests §9, §10 and the deploy verification against the running containers.
- A stage is not finished until `smoke.sh` passes after deploy.

## 12. Tests

- `infra/tes.sh <app>` is the only way to run PHPUnit. It builds `lokandita/<app>:tes`, recreates the `*_test` schemas from nothing, runs the LokaAuth migrations on `identity_test`, and refuses any schema whose name does not end in `_test`.
- A second LokaAuth named `auth-tes` runs for the duration of the tests on `identity_test` and Redis DB `15`. Its alias on the `internal` network is `auth-internal-tes`; client apps under test use `AUTH_INTERNAL_URL=http://auth-internal-tes:8080`. When a client app is tested it is also served as `<app>-tes`.
- Tests talk to these containers over real HTTP (`Lokandita\Auth\Testing\Http`) and to MySQL directly. Nothing is mocked.
- Redis DB `15` is reserved for tests and is flushed at the start of every run.
