# Additions to KONTRAK.md for LokaUjian — draft

**This file is not the contract.** It lists what `KONTRAK.md` gains for LokaUjian (`SPEC-LOKAUJIAN.md`), written 2026-11-24 for the owner to read first. `KONTRAK.md` itself is changed only by the stage that builds each item, in the same commit as the code, so that contract and code never disagree. The stage is named with every item. When the last item has been merged, this file is deleted.

Section numbers are those of `KONTRAK.md`. §20 and §21 are new sections; §18 (roster views) was merged in U0 and §19 (access and scope) was written in U1.

The plan of a stage may change a name below when the code shows a better one; it then changes it here and in `KONTRAK.md` in the same commit. It may not change the shape of something two components share without the owner's approval.

---

## §3 Identity schema — nothing changes

`app_access.app` already has `lokaujian`. LokaUjian reads `session_identity`, `app_access` and `user_directory` through `identity_reader`, as LokaKode does. `paste_allowed` comes from `session_identity`.

## §4 Client authentication and public routes — U4, U6

Filter `masuk`, `Routes::akun()` and the landing `/` were merged into `KONTRAK.md` §4 in U1.

Public routes table gains:

| App | Routes | Stage |
|---|---|---|
| LokaUjian | `/seb`, `/seb/periksa`, `/seb/keluar`, `/seb/unduh/<course code>.seb` (§8) | U4 |

`GET /beranda` in the Portal: `lokaujian` becomes an app that is built (address from `LOKAUJIAN_URL`) — **U6**, when the owner opens the host. Until then it stays "segera".

## §8 Safe Exam Browser — LokaUjian uses the shared package — U4

The move into `packages/lokandita-auth` was done in U0 and is in `KONTRAK.md` §8 ("Shared package and what each app supplies"), with the `Sumber` interface as built. LokaUjian's `Sumber` reads the roster views (§18); its Redis keys are `lokaujian:seb:sewa:…` and `lokaujian:laju:seb:…`; its tables `seb_configs` and `seb_exam_keys` are created by its own migration in U4.

**LokaUjian's use of it (U4):**

- Filter `seb` on every route a mahasiswa can open except `GET /beranda` (the read-only list), the public SEB routes, `/data-saya`, `/data-saya/unduh` and the account routes of §4.
- Starting an exam, the waiting page, the exam page, every answer endpoint and the released review call `Seb::periksa` a second time with **only the configuration of that exam's course** as candidate; a request that matches another course's configuration is refused with reason `kunci_konfigurasi`.
- Rotation (`POST /mata-kuliah/{id}/seb`) answers `409` while a session of that course is running.
- `startURL` = `APP_URL/beranda` of LokaUjian.

## §9 Docker networks and names — scheduler U3, pictures U2

- From U3 the container `lokaujian-jadwal` (the same image `lokandita/lokaujian:<commit>`, runs the scheduler of §21, serves no HTTP, is on `data` only).
- Named volume `lokandita_lokaujian_media` (Compose name `lokaujian_media`), mounted at `/srv/media` in `lokaujian` only — from U2.

## §10 Environment variables — after U0

- `SEB_ENCRYPTION_KEY`, `SEB_ENFORCEMENT` for `lokaujian` — in the stage whose code first reads them (U3 for the test banner, U4 for configurations). `SEB_ENCRYPTION_KEY` of LokaUjian is its own secret, must not change while configurations exist, and is included in every backup.
- `LOKAUJIAN_URL` in the Portal (address of LokaUjian for links) — U6.
- `lokaujian-jadwal` uses `apps/lokaujian/.env`, the same file as `lokaujian` — U3.

## §11 Deploy and verification — after U0

- From U3 `infra/deploy.sh lokaujian` also starts `lokaujian-jadwal` and fails unless the scheduler's heartbeat (§21) carries the deployed commit.
- From U2 `infra/backup.sh` writes the picture volume as `media-lokaujian.tar.gz`; `infra/uji-restore.sh` restores it and checks every picture row against a file.

New smoke checks (13, roster views, exists since U0):

| # | Check | Stage |
|---|---|---|
| 14 | **Way in.** Asked at the container as in check 8: `GET /beranda` of `lokaujian` without a cookie redirects to `AUTH_LOGIN_URL` with `next` equal to its own `/beranda` | U1 |
| 15 | **Pictures.** `GET /media/soal/smoke.png` and `/media/soal/smoke.php` of `lokaujian` answer `403`; `GET /gambar/<32 zeros>` without a cookie redirects to login | U2 |
| 16 | **Scheduler.** The heartbeat of §21 exists, is younger than 60 s and carries the current commit | U3 |
| 17 | **SEB of LokaUjian.** As check 10, for `lokaujian`: `/seb` names the state of enforcement, `/seb/periksa` reports no header, `SEB_ENFORCEMENT` in the container equals the `.env`; `disabled` passes with the warning line | U4 |
| 18 | **Routes exist.** Named staff and student routes of LokaUjian without a cookie each redirect to login with the right `next` | U3, extended in U5 |

## §12 Tests — after U0

- From U3 the headless Chromium of §12 is started for `lokaujian` too (`lokaujian.lokandita.com` mapped to a test edge, every other name unresolvable). `tests/Peramban/` covers autosave, countdown, resume, working without a connection (network switched off through the DevTools Protocol), and the paste block; they fail, not skip, when the browser is missing.
- From U4 `infra/tes.sh lokaujian` serves `lokaujian-tes` (`SEB_ENFORCEMENT=disabled`) and `lokaujian-seb-tes` (`enabled`); SEB tests compute the headers by the formula of §8. No test can show that a real SEB client is accepted; that is the owner's gate in U4.
- From U3 `infra/tes.sh lokaujian` also runs the scheduler once against the test schema.

## §14 Export of a user's own data — U5

LokaUjian answers `GET /data-saya` and `POST /data-saya/unduh` under the rules of §14; neither is behind filter `seb`. File name `lokandita-lokaujian-data-saya.json`, `"aplikasi": "lokaujian"`. The Portal's `/data-saya` links to it when the user has `lokaujian` access (U6).

| App | Keys |
|---|---|
| LokaUjian | `pengerjaan[]` (`ujian` = title, `mata_kuliah`, `kelas`, `mulai`, `kumpul`, `cara_kumpul`, `tambahan_menit`, and `nilai_objektif`, `poin_objektif` only when results of that class are released), `jawaban[]` (`ujian`, `nomor` as the user saw it, `tipe`, `jawaban` = the user's own text or the letter chosen, `disimpan`), `peristiwa_integritas[]` (`created_at`, `kind`), `sesi_dijalankan[]` and `ujian_dibuat[]` for staff (titles and times only) |

Never in the export: question text, options, keys, pictures, points per question while results are not released, and any row of another person.

## §16 App list — U6

`apps/portal/app/Config/Aplikasi.php`: `lokaujian` becomes `status = aktif`, `alamat = LOKAUJIAN_URL`. Smoke check 12 keeps the six cards and their order.

## §20 LokaUjian exam file format (new) — U2

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

## §21 LokaUjian scheduler (new) — U3

`lokaujian-jadwal` runs `php spark ujian:jadwal` from the app image: one loop, one pass at least every 20 seconds, each pass idempotent. It has no state of its own; MySQL is the source of truth, and nothing a student may or may not do depends on it having run (`SPEC-LOKAUJIAN.md` §7.5).

- **Heartbeat:** Redis key `lokaujian:jadwal` (DB `3`), JSON `{ commit, pid, mulai, waktu, lintasan }`, written after every pass with a lifetime of 60 s and deleted on a clean stop. A missing key means no scheduler is running. `infra/deploy.sh lokaujian`, `infra/smoke.sh` (check 16) and the lecturer's session page read it.
- There is exactly one scheduler per schema; a second one that finds a live heartbeat of another process exits with one log line.
- It connects as `lokaujian_app` and through `ROSTER_READER_DSN`; it makes no HTTP call and no call to LokaAuth.
- Exit code 78 means a missing environment variable, as for the judge worker.

---

## Not in the contract

LokaUjian's own tables, routes and the format of its autosave requests are read by no other component. Each stage adds them to `KONTRAK.md` as it builds them, in the way §6 and §17 record LokaKode's, so that the tests against real containers have something to hold on to.
