# Additions to KONTRAK.md for LokaUjian — draft

**This file is not the contract.** It lists what `KONTRAK.md` gains for LokaUjian (`SPEC-LOKAUJIAN.md`), written 2026-11-24 for the owner to read first. `KONTRAK.md` itself is changed only by the stage that builds each item, in the same commit as the code, so that contract and code never disagree. The stage is named with every item. When the last item has been merged, this file is deleted.

Section numbers are those of `KONTRAK.md`. §21 is a new section; §18 (roster views) was merged in U0, §19 (access and scope) was written in U1, and §20 (question bank, pictures and the exam file format) in U2 — with one change the owner ordered on 2026-11-25: an option may carry a picture.

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

## §9 Docker networks and names — scheduler U3

- From U3 the container `lokaujian-jadwal` (the same image `lokandita/lokaujian:<commit>`, runs the scheduler of §21, serves no HTTP, is on `data` only).

## §10 Environment variables — after U0

- `SEB_ENCRYPTION_KEY`, `SEB_ENFORCEMENT` for `lokaujian` — in the stage whose code first reads them (U3 for the test banner, U4 for configurations). `SEB_ENCRYPTION_KEY` of LokaUjian is its own secret, must not change while configurations exist, and is included in every backup.
- `LOKAUJIAN_URL` in the Portal (address of LokaUjian for links) — U6.
- `lokaujian-jadwal` uses `apps/lokaujian/.env`, the same file as `lokaujian` — U3.

## §11 Deploy and verification — after U0

- From U3 `infra/deploy.sh lokaujian` also starts `lokaujian-jadwal` and fails unless the scheduler's heartbeat (§21) carries the deployed commit.

New smoke checks (13, roster views, exists since U0; 14, the way in, since U1; 15, pictures, since U2):

| # | Check | Stage |
|---|---|---|
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

## §21 LokaUjian scheduler (new) — U3

`lokaujian-jadwal` runs `php spark ujian:jadwal` from the app image: one loop, one pass at least every 20 seconds, each pass idempotent. It has no state of its own; MySQL is the source of truth, and nothing a student may or may not do depends on it having run (`SPEC-LOKAUJIAN.md` §7.5).

- **Heartbeat:** Redis key `lokaujian:jadwal` (DB `3`), JSON `{ commit, pid, mulai, waktu, lintasan }`, written after every pass with a lifetime of 60 s and deleted on a clean stop. A missing key means no scheduler is running. `infra/deploy.sh lokaujian`, `infra/smoke.sh` (check 16) and the lecturer's session page read it.
- There is exactly one scheduler per schema; a second one that finds a live heartbeat of another process exits with one log line.
- It connects as `lokaujian_app` and through `ROSTER_READER_DSN`; it makes no HTTP call and no call to LokaAuth.
- Exit code 78 means a missing environment variable, as for the judge worker.

---

## Not in the contract

LokaUjian's own tables, routes and the format of its autosave requests are read by no other component. Each stage adds them to `KONTRAK.md` as it builds them, in the way §6 and §17 record LokaKode's, so that the tests against real containers have something to hold on to.
