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

CSRF and other per-app cookies use the `__Host-` prefix and therefore carry no `Domain` attribute.

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
WHERE s.revoked_at IS NULL AND s.expires_at > UTC_TIMESTAMP() AND u.status = 'active';
```

MySQL user `identity_reader` has `SELECT` on `session_identity`, on `app_access`, and on a view `user_directory (id, nomor_induk, nama, global_role, status)`. Nothing else. It can never read `password_hash` or `mfa_secret_enc`, and has no write privilege.

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

`INSTANCE_SECRET` must never change once students have instances. It and `SEB_ENCRYPTION_KEY` are included in every backup.

Values are written without quotes.

## 11. Deploy and verification

- `infra/deploy.sh <app>` builds the image tagged with the current Git commit, starts it, and fails unless the running container reports that commit at `GET /healthz` (`{ "app": "...", "commit": "..." }`).
- `infra/smoke.sh` checks, with real requests:
  1. each public host answers over HTTPS;
  2. `/internal/anything` returns 404 from the internet on every host;
  3. from inside `lokakode` and `portal`, `POST auth-internal:8080/internal/session/touch` with the app's token and a bogus `sid` returns **422 or 404-with-JSON, not 401** — proving network, name and token are right;
  4. the same call with a wrong token returns 401;
  5. `identity_reader` can select from `session_identity` and cannot select from `users`;
  6. the judge worker is running and a trivial `RUN` job completes.
- A stage is not finished until `smoke.sh` passes after deploy.
