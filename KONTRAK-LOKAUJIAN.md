# Additions to KONTRAK.md for LokaUjian — draft

**This file is not the contract.** It lists what `KONTRAK.md` gains for LokaUjian (`SPEC-LOKAUJIAN.md`), written 2026-11-24 for the owner to read first. `KONTRAK.md` itself is changed only by the stage that builds each item, in the same commit as the code, so that contract and code never disagree. The stage is named with every item. When the last item has been merged, this file is deleted.

Section numbers are those of `KONTRAK.md`. §18 (roster views) was merged in U0, §19 (access and scope) was written in U1, §20 (question bank, pictures and the exam file format) in U2 — with one change the owner ordered on 2026-11-25: an option may carry a picture — §21 (scheduler) and §22 (publishing, sessions and attempts) in U3, and §23 (Safe Exam Browser per exam, integrity events) in U4 — where the owner decided on 2026-11-27 that a configuration belongs to an exam, not to a course, so the draft's "§8 — LokaUjian uses the shared package" was replaced by `KONTRAK.md` §23. §24 (lecturer control, scoring, release, CSV, audit page) and LokaUjian's part of §14 (own-data export) were written in U5. What is left below belongs to U6.

The plan of a stage may change a name below when the code shows a better one; it then changes it here and in `KONTRAK.md` in the same commit. It may not change the shape of something two components share without the owner's approval.

---

## §3 Identity schema — nothing changes

`app_access.app` already has `lokaujian`. LokaUjian reads `session_identity`, `app_access` and `user_directory` through `identity_reader`, as LokaKode does. `paste_allowed` comes from `session_identity`.

## §4 Client authentication and public routes — U6

Filter `masuk`, `Routes::akun()` and the landing `/` were merged into `KONTRAK.md` §4 in U1, the public SEB routes of LokaUjian in U4.

`GET /beranda` in the Portal: `lokaujian` becomes an app that is built (address from `LOKAUJIAN_URL`) — **U6**, when the owner opens the host. Until then it stays "segera".

## §10 Environment variables — after U0

- `LOKAUJIAN_URL` in the Portal (address of LokaUjian for links) — U6.

## §11 Deploy and verification — after U0

Smoke checks 13–18 are in `KONTRAK.md` §11 (13 since U0; 14 since U1; 15 since U2; 16 and 18 since U3; 17 since U4; 18 extended in U5). Nothing left here.

## §12 Tests — after U0

- Nothing left: the load test (`infra/uji-beban.sh`) and the judge queue warning's test were written into `KONTRAK.md` §12 in U6.

## §14 Export of a user's own data — U6

LokaUjian's own export was merged into `KONTRAK.md` §14 in U5. Left: the Portal's `/data-saya` links to it when the user has `lokaujian` access (U6).

## §16 App list — U6

`apps/portal/app/Config/Aplikasi.php`: `lokaujian` becomes `status = aktif`, `alamat = LOKAUJIAN_URL`. Smoke check 12 keeps the six cards and their order.

## Not in the contract

LokaUjian's own tables, routes and the format of its autosave requests are read by no other component. Each stage adds them to `KONTRAK.md` as it builds them, in the way §6 and §17 record LokaKode's, so that the tests against real containers have something to hold on to.
