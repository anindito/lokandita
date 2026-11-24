# Additions to KONTRAK.md for LokaUjian — draft

**This file is not the contract.** It lists what `KONTRAK.md` gains for LokaUjian (`SPEC-LOKAUJIAN.md`), written 2026-11-24 for the owner to read first. `KONTRAK.md` itself is changed only by the stage that builds each item, in the same commit as the code, so that contract and code never disagree. The stage is named with every item. When the last item has been merged, this file is deleted.

Section numbers are those of `KONTRAK.md`. §18–§20 are new sections.

The plan of a stage may change a name below when the code shows a better one; it then changes it here and in `KONTRAK.md` in the same commit. It may not change the shape of something two components share without the owner's approval.

---

## §1 Repository layout — U0

```
├── apps/lokaujian/             CodeIgniter 4 — LokaUjian
├── packages/lokandita-auth/    Composer path package used by all four apps (now also SEB, §8)
├── SPEC-LOKAUJIAN.md
```

## §3 Identity schema — nothing changes

`app_access.app` already has `lokaujian`. LokaUjian reads `session_identity`, `app_access` and `user_directory` through `identity_reader`, as LokaKode does. `paste_allowed` comes from `session_identity`.

## §4 Client authentication and public routes — U0, U1

LokaUjian uses `Lokandita\Auth\Filters\RequireLogin` (filter `masuk`) with app name `lokaujian`, and registers `Routes::akun()` and `Routes::publik()`.

Public routes table gains:

| App | Routes | Stage |
|---|---|---|
| LokaUjian | `/healthz`, `GET /bahasa/{id\|en\|ar}`, `/robots.txt` | U0 |
| LokaUjian | `/` — with a `lokandita_sid` cookie present it redirects to `/beranda`; without one it shows a short page with a "Masuk" link to `/beranda`. Every page of LokaUjian is `noindex, nofollow`; `/robots.txt` disallows everything | U1 |
| LokaUjian | `/seb`, `/seb/periksa`, `/seb/keluar`, `/seb/unduh/<course code>.seb` (§8) | U4 |

`GET /beranda` in the Portal: `lokaujian` becomes an app that is built (address from `LOKAUJIAN_URL`) — **U6**, when the owner opens the host. Until then it stays "segera".

## §5 Internal API — U0

- LokaAuth holds `SERVICE_TOKEN_LOKAUJIAN`; LokaUjian holds the same value as `AUTH_SERVICE_TOKEN`. The audit log stores `lokaujian` as `app`.
- LokaUjian calls only `session/touch`, `session/revoke` and `session/prefs`. It does **not** call `/internal/password-reset`; a call with LokaUjian's token is answered `403 {"error":"app_not_allowed"}` and logged.

## §8 Safe Exam Browser — one implementation in the shared package — U0 (move), U4 (LokaUjian uses it)

The verification of §8 is unchanged: the same two headers, the same formula, the same `url`, both matching for the same configuration, SEB-JSON as for 3.10.2, the same `.seb` container format, lease 30 s and heartbeat 25 s, the same refusal answers and reason codes. What changes is where the code lives and what each app supplies.

**Moved to `packages/lokandita-auth/src/Seb/`, namespace `Lokandita\Auth\Seb`, behaviour unchanged:**

| Class | Was | Does |
|---|---|---|
| `Seb` | `App\Libraries\Seb\Seb` | `diberlakukan()`, `asal()`, `url()`, `periksa($request, $kandidat)`, `perpanjangSewa()`, `akhiriSewa()`; configuration store `aktif()`, `buat()`, `daftarkanKunci()`, `kunciTerdaftar()`, `berkas()`; constants for headers, lease, heartbeat, minimum quit password |
| `Setelan` | `App\Libraries\Seb\Setelan` | the settings array |
| `Plist` | `App\Libraries\Seb\Plist` | XML plist, SEB-JSON, Config Key |
| `Brankas` | `App\Libraries\Seb\Brankas` | encryption with `SEB_ENCRYPTION_KEY` |
| `Filters\WajibSeb` (alias `seb`) | `App\Filters\WajibSeb` | the filter |
| `Controllers\Seb`, `Routes::seb()` | LokaKode's SEB controllers and routes | the routes of §8, the same paths in both apps |
| views `seb_*`, language file `Seb`, `/js/seb.js` | LokaKode | pages, texts in `id`, `en`, `ar`, heartbeat script |

**Supplied by each app** — one class implementing `Lokandita\Auth\Seb\Sumber`, registered in the app's configuration:

| Method | Returns |
|---|---|
| `matkulMahasiswa(string $userId): list<int>` | course ids whose configuration counts for this student: `AKTIF` enrollment in the active academic year |
| `matkul(list<int> $ids): array<int, array{code, name}>` | code and name of courses, for pages and file names |
| `matkulDariKode(string $kode): ?int` | course id of a code, for the download |
| `bolehKelola(Identity $siapa, int $matkulId): bool` | whether this user may open the panel of that course (superadmin, pengampu) |
| `catatPeristiwa(string $userId, string $jenis, array $detail): void` | writes `seb_rejected` / `seb_terminated` to the app's own `integrity_events` |
| `catatAudit(string $aksi, int $matkulId, array $detail): void` | writes `seb.dibuat`, `seb.dirotasi`, `seb.kunci` to the app's own `audit_log` |

LokaKode's implementation reads its own tables, as today. LokaUjian's reads the roster views (§18).

**Per app, not shared:**

- Tables `seb_configs` and `seb_exam_keys`, identical in the `lokakode` and `lokaujian` schemas, each created by its own app's migration. The package reads and writes them through the app's default connection. A configuration belongs to one app: `startURL`, `quitURL` and the URL filter are built from that app's `APP_URL`, so the Config Key, the `.seb` file and the registered Browser Exam Keys of LokaKode and LokaUjian are different and independent.
- `SEB_ENCRYPTION_KEY` and `SEB_ENFORCEMENT`, one value per app in its own `.env`.
- Redis keys, prefixed with the app name (`LOKANDITA_APP`): `lokakode:seb:sewa:…` (unchanged), `lokaujian:seb:sewa:…`; likewise the rate-limit key of `seb_rejected`. The package gets the Redis connection from the app.
- Which routes carry the filter.

**LokaUjian's use of it (U4):**

- Filter `seb` on every route a mahasiswa can open except `GET /beranda` (the read-only list), the public SEB routes, `/data-saya`, `/data-saya/unduh` and the account routes of §4.
- Starting an exam, the waiting page, the exam page, every answer endpoint and the released review call `Seb::periksa` a second time with **only the configuration of that exam's course** as candidate; a request that matches another course's configuration is refused with reason `kunci_konfigurasi`.
- Rotation (`POST /mata-kuliah/{id}/seb`) answers `409` while a session of that course is running.
- `startURL` = `APP_URL/beranda` of LokaUjian.

**Proof required in U0:** LokaKode's `tests/Seb/` pass unchanged against `lokakode-seb-tes`; the Config Key of an existing production configuration, recomputed by the moved code, equals the stored one; and the same real request to production LokaKode (`GET /seb`, `GET /seb/periksa`, a student route without headers) gives the same status and body before and after the deploy.

## §9 Docker networks and names — U0; scheduler U3

| Network | Members |
|---|---|
| `edge` | caddy, auth, lokakode, portal, **lokaujian** |
| `internal` | auth (alias `auth-internal`), lokakode, portal, **lokaujian** |
| `data` | auth, lokakode, portal, **lokaujian**, **lokaujian-jadwal**, mysql, redis |

- Container names gain `lokaujian` (HTTP on 8080, no published port) and, from U3, `lokaujian-jadwal` (the same image `lokandita/lokaujian:<commit>`, runs the scheduler of §20, serves no HTTP, is on `data` only).
- Named volume `lokandita_lokaujian_media` (Compose name `lokaujian_media`), mounted at `/srv/media` in `lokaujian` only — from U2.
- Edge: `lokaujian.DOMAIN` → `lokaujian`. `/internal` and `/internal/*` answer `404` on all five hosts. Maintenance flag `infra/caddy/bendera/lokaujian.buka`; `infra/pemeliharaan.sh` knows `lokaujian`.

## §10 Environment variables — U0 unless marked

| Variable | auth | lokakode | portal | **lokaujian** | judge |
|---|---|---|---|---|---|
| `APP_URL`, `CONTACT_EMAIL`, `COOKIE_DOMAIN`, `PORTAL_URL` | ✔ | ✔ | ✔ | ✔ | |
| `DB_DSN` (own schema) | ✔ | ✔ | ✔ | ✔ | ✔ |
| `IDENTITY_READER_DSN` | | ✔ | ✔ | ✔ | |
| **`ROSTER_READER_DSN`** (§18) | | | | ✔ | |
| `REDIS_URL` | ✔ | ✔ | ✔ | ✔ | ✔ |
| `AUTH_PUBLIC_URL`, `AUTH_LOGIN_URL`, `AUTH_INTERNAL_URL`, `AUTH_SERVICE_TOKEN` | | ✔ | ✔ | ✔ | |
| `SERVICE_TOKEN_PORTAL`, `SERVICE_TOKEN_LOKAKODE`, **`SERVICE_TOKEN_LOKAUJIAN`** | ✔ | | | | |
| `SEB_ENCRYPTION_KEY`, `SEB_ENFORCEMENT` | | ✔ | | ✔ | |
| **`LOKAUJIAN_URL`** (address of LokaUjian for links from the Portal) — U6 | | | ✔ | | |

- `ALLOWED_HOSTS` of LokaAuth gains `lokaujian.lokandita.com` (a value, not a new variable).
- `lokaujian-jadwal` uses `apps/lokaujian/.env`, the same file as `lokaujian`.
- `infra/.env` gains `DB_PASSWORD_LOKAUJIAN` and `DB_PASSWORD_ROSTER_READER`.
- `ROSTER_READER_DSN`: `MySQLi://roster_reader:<password>@mysql:3306/<schema that holds the views>`; today that schema is `lokakode`. The schema name appears nowhere in LokaUjian's code.
- Redis DB **`3`** = lokaujian (`0` lokakode and judge, `1` auth, `2` portal, `15` tests).
- `SEB_ENCRYPTION_KEY` of LokaUjian is its own secret, must not change while configurations exist, and is included in every backup.
- Every new key goes into `.env.example` (and so into startup validation), into `infra/buat-env.sh` and `infra/cek-env.sh`, in the same commit.

MySQL schemas and users:

| User | Rights |
|---|---|
| `lokaujian_app` | all on `lokaujian`, `lokaujian_test` (from `infra/mysql-init.sh`) |
| `roster_reader` | none from `mysql-init.sh`; `SELECT` on the five views of §18 from `infra/mysql-hak-roster.sh` |

## §11 Deploy and verification — U0 unless marked

- `infra/deploy.sh lokaujian` builds, migrates, starts `lokaujian` (and from U3 `lokaujian-jadwal`) and verifies the commit at `/healthz` (`{ "app": "lokaujian", "commit": "…" }`); from U3 it also fails unless the scheduler's heartbeat (§20) carries that commit.
- `infra/deploy.sh lokakode` runs `infra/mysql-hak-roster.sh` after LokaKode's migrations, because the views live in LokaKode's schema.
- `infra/backup.sh` dumps `lokaujian` with the other schemas and, from U2, writes the picture volume as `media-lokaujian.tar.gz`; `infra/uji-restore.sh` restores both and checks every picture row against a file. `docs/RESTORE.md` is updated.

`infra/smoke.sh` — existing checks extended:

- 0: `lokaujian` reports the current `HEAD`.
- 1: `lokaujian.DOMAIN` answers over HTTPS (`503` in maintenance, `2xx`/`3xx` when open).
- 2: `/internal/anything` on `lokaujian.DOMAIN` is `404`.
- 3, 4: the `session/touch` calls also from inside `lokaujian`, with its token and with a wrong one.
- 5: also through `IDENTITY_READER_DSN` of `lokaujian`.
- 10: unchanged for `lokakode` — it is the standing proof that the move of §8 changed nothing.

New checks:

| # | Check | Stage |
|---|---|---|
| 13 | **Roster views.** Through `ROSTER_READER_DSN` from inside `lokaujian`: `SELECT COUNT(*)` works on each of the five views; `SELECT` on `enrollments` and on `submissions` of the same schema is denied; `INSERT` into `roster_classes` is denied; the count of each view equals the count of its table, asked through `lokakode` | U0 |
| 14 | **Way in.** Asked at the container as in check 8: `GET /beranda` of `lokaujian` without a cookie redirects to `AUTH_LOGIN_URL` with `next` equal to its own `/beranda` | U1 |
| 15 | **Pictures.** `GET /media/soal/smoke.png` and `/media/soal/smoke.php` of `lokaujian` answer `403`; `GET /gambar/<32 zeros>` without a cookie redirects to login | U2 |
| 16 | **Scheduler.** The heartbeat of §20 exists, is younger than 60 s and carries the current commit | U3 |
| 17 | **SEB of LokaUjian.** As check 10, for `lokaujian`: `/seb` names the state of enforcement, `/seb/periksa` reports no header, `SEB_ENFORCEMENT` in the container equals the `.env`; `disabled` passes with the warning line | U4 |
| 18 | **Routes exist.** Named staff and student routes of LokaUjian without a cookie each redirect to login with the right `next` | U3, extended in U5 |

## §12 Tests — U0, extended by each stage

- `infra/tes.sh lokaujian` recreates `lokaujian_test`, `lokakode_test` and `identity_test`, runs the LokaAuth and LokaKode migrations (the views of §18 must exist in `lokakode_test`) and `infra/mysql-hak-roster.sh` for the test schema, and serves `auth-tes`, `lokaujian-tes` (`SEB_ENFORCEMENT=disabled`) and `lokaujian-seb-tes` (`enabled`).
- Roster rows for tests are written into `lokakode_test` by the test code through a test-only connection as `lokakode_app`. The app under test has only `roster_reader`. A test proves that `lokaujian_app` and `roster_reader` cannot write to, or read a table of, `lokakode_test`.
- From U3 the headless Chromium of §12 is started for `lokaujian` too (`lokaujian.lokandita.com` mapped to a test edge, every other name unresolvable). `tests/Peramban/` covers autosave, countdown, resume, working without a connection (network switched off through the DevTools Protocol), and the paste block; they fail, not skip, when the browser is missing.
- SEB tests of `lokaujian` compute the headers by the formula of §8. No test can show that a real SEB client is accepted; that is the owner's gate in U4.
- From U3 `infra/tes.sh lokaujian` also runs the scheduler once against the test schema.
- `infra/tes.sh lokakode` is unchanged and must stay green in U0.

## §14 Export of a user's own data — U5

LokaUjian answers `GET /data-saya` and `POST /data-saya/unduh` under the rules of §14; neither is behind filter `seb`. File name `lokandita-lokaujian-data-saya.json`, `"aplikasi": "lokaujian"`. The Portal's `/data-saya` links to it when the user has `lokaujian` access (U6).

| App | Keys |
|---|---|
| LokaUjian | `pengerjaan[]` (`ujian` = title, `mata_kuliah`, `kelas`, `mulai`, `kumpul`, `cara_kumpul`, `tambahan_menit`, and `nilai_objektif`, `poin_objektif` only when results of that class are released), `jawaban[]` (`ujian`, `nomor` as the user saw it, `tipe`, `jawaban` = the user's own text or the letter chosen, `disimpan`), `peristiwa_integritas[]` (`created_at`, `kind`), `sesi_dijalankan[]` and `ujian_dibuat[]` for staff (titles and times only) |

Never in the export: question text, options, keys, pictures, points per question while results are not released, and any row of another person.

## §16 App list — U6

`apps/portal/app/Config/Aplikasi.php`: `lokaujian` becomes `status = aktif`, `alamat = LOKAUJIAN_URL`. Smoke check 12 keeps the six cards and their order. Brand files of LokaUjian (`infra/buat-merek.sh`) are added to `apps/lokaujian/public/` in U0 and to check 12.

## §18 Academic roster views (new) — U0

The academic structure of `SPEC.md` §5 as other apps read it. **The views are the contract; the tables behind them are not.** A reader app queries these five views and nothing else of the roster.

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
- Capacity, creation times, who created or assigned, and the history of class moves are **not** part of the contract.

Access:

- MySQL user **`roster_reader`**: `SELECT` on these five views, nothing else, no write privilege. Granted by `infra/mysql-hak-roster.sh` as MySQL root after LokaKode's migrations (`infra/deploy.sh lokakode`, `infra/deploy.sh lokaujian` and `infra/tes.sh` run it). It cannot read `enrollments`, `submissions` or any other table.
- A reader app connects with `ROSTER_READER_DSN`, a connection separate from its own schema's (`Lokandita\Auth\Roster`, in the shared package, is the only code that queries the views). There are no joins between a reader's schema and the roster: the reader first reads the ids it may see, then binds its own queries to them.
- A reader stores `class_id`, `course_id`, `academic_year_id` as plain BIGINT UNSIGNED and `user_id` as CHAR(36), without foreign keys.
- LokaKode itself keeps reading and writing its tables; the views change nothing for it.

**When the tables move (stage A1):** the five views are redefined, with the same names, columns, types and guarantees, wherever the tables then live; `ROSTER_READER_DSN` and the grant script follow. No reader app changes.

## §19 LokaUjian exam file format (new) — U2

One JSON file per exam, UTF-8. Used by the web import and export and by `php spark ujian:impor` / `ujian:ekspor` (`SPEC-LOKAUJIAN.md` §6.3).

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
    { "type": "pilihan_ganda", "text": "…", "points": 2, "image": "g1",
      "options": [ { "text": "…" }, { "text": "…", "correct": true }, { "text": "…" } ] },
    { "type": "isian", "text": "…", "points": 2 },
    { "type": "uraian", "text": "…", "points": 10 },
    { "type": "kelompok", "text": "…bacaan…", "image": "g2",
      "questions": [
        { "type": "pilihan_ganda", "text": "…", "points": 1,
          "options": [ { "text": "…", "correct": true }, { "text": "…" } ] },
        { "type": "isian", "text": "…", "points": 1 }
      ] }
  ],
  "images": {
    "g1": { "type": "png",  "alt": "Diagram alir", "sha256": "…", "data": "<base64>" },
    "g2": { "type": "jpeg", "alt": "Denah",        "sha256": "…", "data": "<base64>" }
  }
}
```

| Field | Rule |
|---|---|
| `format` | Exactly `lokaujian-ujian/1` |
| `course` | Exact course code; the importing user must be superadmin or its pengampu. On the web it must equal the course of the page |
| `title` | 1–190 characters |
| `kind` | `ujian` or `kuis`; default `ujian` |
| `instructions` | Markdown, at most 5,000 characters; may be empty |
| `settings.mode` | `per_mahasiswa` or `dikendalikan_dosen` |
| `settings.duration_minutes` | Integer 5–600; required for `per_mahasiswa`, optional or `null` for `dikendalikan_dosen` |
| `settings.shuffle_questions`, `settings.shuffle_options` | Boolean; default `false` |
| `items` | 1–200 questions in all, counting those inside groups. Order in the file is the exam order |
| `type` | `benar_salah`, `pilihan_ganda`, `isian`, `uraian`, or `kelompok` (top level only; a group cannot hold a group) |
| `text` | Markdown, 1–10,000 characters for a question; for a group at most 20,000, and it may be empty when the group has an `image`. Raw HTML is shown as text |
| `points` | Number 0.25–100 in steps of 0.25; default 1. Not allowed on a `kelompok` |
| `answer` | `benar_salah` only, required: `true` or `false` |
| `options` | `pilihan_ganda` only, required: 2–6 objects `{ "text", "correct"? }`, `text` 1–1,000 characters; **exactly one** has `"correct": true` |
| `questions` | `kelompok` only: 1–20 questions of the four types |
| `image` | Optional on a question and on a group: a key of `images` |
| `images.<key>` | Key `[a-z0-9_-]{1,40}`; `type` `png` or `jpeg`; `alt` 1–300 characters; `data` Base64 of the file; `sha256` lower-case hex of the decoded bytes. At most 40 pictures. Every picture must be used by an item |

Rules:

- At most 24 MB. Unknown keys are refused at every level; a key that does not belong to the type (`options` on an `isian`, `answer` on a `pilihan_ganda`) is refused. Any mistake refuses the whole file and nothing is stored. Messages name the JSON path and line and say what to change (`items[3].options: exactly one option must have "correct": true, found 2`).
- An import creates one new exam in `draf`, as the importing user. A question or group whose content (type, text, points, options with their order and key, answer, picture) equals one already in that course's bank is reused; otherwise it is added to the bank.
- A picture whose `sha256` equals a stored picture of that course is reused as it is. Any other picture goes through the upload checks (type decided from the content, at most 5 MB and 30,000,000 pixels, decodable) and is written anew from its pixels; its `sha256` in later exports is that of the stored file.
- The export writes the stored files, so exporting, importing and exporting again gives a byte-identical file. Key order and formatting of the export are fixed (two-space indentation, keys in the order of the table above).
- The file never holds classes, sessions, schedules, students, answers or ids of the database.
- The export contains the keys: staff only (superadmin, the pengampu), `Cache-Control: no-store`, `Content-Disposition: attachment; filename="lokaujian-<course code>-<exam id>.json"`.
- `php spark ujian:impor [file] --oleh <nomor induk of an active superadmin or of the pengampu>` reads standard input without a file argument and exits 0 only when the file is accepted. `php spark ujian:ekspor <exam id> --oleh <nomor induk>` prints the file.
- Audit in LokaUjian's `audit_log`: `ujian.diimpor` (`{ berkas, soal, soal_baru, gambar, gambar_baru }`), `ujian.impor.ditolak` (`{ berkas, alasan }`), `ujian.diekspor`; from the command line with `lewat: cli`.

## §20 LokaUjian scheduler (new) — U3

`lokaujian-jadwal` runs `php spark ujian:jadwal` from the app image: one loop, one pass at least every 20 seconds, each pass idempotent. It has no state of its own; MySQL is the source of truth, and nothing a student may or may not do depends on it having run (`SPEC-LOKAUJIAN.md` §7.5).

- **Heartbeat:** Redis key `lokaujian:jadwal` (DB `3`), JSON `{ commit, pid, mulai, waktu, lintasan }`, written after every pass with a lifetime of 60 s and deleted on a clean stop. A missing key means no scheduler is running. `infra/deploy.sh lokaujian`, `infra/smoke.sh` (check 16) and the lecturer's session page read it.
- There is exactly one scheduler per schema; a second one that finds a live heartbeat of another process exits with one log line.
- It connects as `lokaujian_app` and through `ROSTER_READER_DSN`; it makes no HTTP call and no call to LokaAuth.
- Exit code 78 means a missing environment variable, as for the judge worker.

---

## Not in the contract

LokaUjian's own tables, routes and the format of its autosave requests are read by no other component. Each stage adds them to `KONTRAK.md` as it builds them, in the way §6 and §17 record LokaKode's, so that the tests against real containers have something to hold on to.
