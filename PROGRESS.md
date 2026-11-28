# Progress

**Platform stage A1 — the roster in the core schema `roster` (`KONTRAK.md` §18) — closed on 2026-11-28, tags `tahap-a1` on `c9b5eac` (the deployed commit) and `roster-a1` on the commit that holds this entry. The owner sent the gate report with its three blanks unfilled; the agent filled the two it could measure (enrolments 235 → 238 explained, smoke 99 pass, 0 fail) and checked the backup and the four exams; **the browser items are the owner's statement, not seen by the agent, and "anything that looked wrong" was left blank.** See "Gate result for stage A1" at the end of this file. Next: A2, not started.**

**LokaUjian stage U6 — readiness (`SPEC-LOKAUJIAN.md` §14, §15) — **closed on the owner's instruction on 2026-11-28: all five components are deployed at the commit that holds this entry, `lokaujian.lokandita.com` is open, the Portal card is a link, and the tag `lokaujian-u6` goes on that commit once smoke passes there.** The gate passed on 2026-11-27: 110 simulated students at once, 8,872 saves confirmed, none lost; again with the app restarted mid-exam; restore test passed. MySQL's connection limit is 500 (it was hit at 151 by 250 simulated students, and by a real LokaKode class on 2026-11-23). Owner's decisions: answers are kept (privacy page says so), all 241 accounts keep access. **Closed with the owner's own checks NOT done** (Arabic and dark-mode screenshots, Excel, a scored exam, reopening, correction, release in SEB, `/audit`, `/data-saya`), and four test exams still published in production. See "LokaUjian U6", "Load test results and what followed" and "LokaUjian U6 closed on the owner's instruction" at the end of this file.**

**Header on phones — not a numbered stage, no tag — built on 2026-11-27 on the owner's bug report (the Portal's header wrapped into several rows at 360–412 px). Below 768 px every public header is now one row — logo, *Masuk*, a menu button that opens a panel with the links and the languages — on the Portal and, by the owner's word, on the landings of LokaKode and LokaUjian too; the header after login is unchanged. The change is in the shared package, so all five components are redeployed at the commit that holds this entry. **Measured in a headless Chromium only; the owner's gate (DevTools at six widths, three languages, logged out and in, light and dark) is not done yet.** See "Header on phones" at the end of this file.**

**LokaUjian stage U5 — lecturer control, scoring and export (`SPEC-LOKAUJIAN.md` §7.3, §10, §11, §12, §13) — built and deployed on 2026-11-27; **the owner ran the gate on 2026-11-27 and reported every item passed: a lecturer-controlled session with two test students in real SEB 3.10.2, extra time, *Akhiri*, and both CSV files opened in Excel. Closed on the owner's instruction; all five components are redeployed at the commit that holds the gate result and the tag `lokaujian-u5` goes on it once smoke passes there.** Three things the gate did **not** exercise, by the server's record: no Arabic text was in any downloaded file, no answer began with `=` (so the formula guard was not triggered), and the exam had no automatically scored question (every score is 0 of 0). Reopening, correction, release, the review, the audit page and own data were not opened by anyone. The host is in maintenance. **Closed: on the owner's word all five components were redeployed at `225e513`, smoke 91 pass, 0 fail, tag `lokaujian-u5` on that commit** (see "Closing deploy of LokaUjian U5"). Next: U6 (readiness), begun 2026-11-27 with a plan for the owner's approval; nothing built yet. See "LokaUjian U5" and "Gate result for LokaUjian U5" at the end of this file.**

**LokaUjian stage U4 — SEB enforcement and integrity events (`SPEC-LOKAUJIAN.md` §9.1, §9.3) — built and deployed on 2026-11-27 with `SEB_ENFORCEMENT=enabled`; **closed on the owner's order on 2026-11-27, tag `lokaujian-u4` on `926aee4`, all five components run that commit, smoke 85 pass, 0 fail.** By the owner's decision of 2026-11-27 the SEB configuration is **per ujian/kuis**, not per course. **The owner ran the gate on 2026-11-27: a real SEB 3.10.2 client was accepted and finished an exam, the same student was refused outside SEB, and a lecturer in Chrome got in** (see "Gate result for LokaUjian U4"). The host is in maintenance. See "LokaUjian U4" at the end of this file.**

**LokaUjian stage U3 — exam setup and delivery (`SPEC-LOKAUJIAN.md` §6, §7, §8, §9.2) — built and deployed on 2026-11-25; **closed on the owner's order on 2026-11-26 with three clauses of the gate unseen; tag `lokaujian-u3` on `fdbf765`, all components run that commit, smoke 76 pass, 0 fail.** Next: U4 (SEB enforcement and integrity events), begun 2026-11-27 with a plan for the owner's approval. The owner ran the gate on 2026-11-26: one exam in each timing mode was completed by the test student on the deployed system; **pasting, closing and reopening the browser, and a save after the deadline were not tried** (see "Gate result for LokaUjian U3"). `auth`, `lokakode`, `portal` and the judge worker were not redeployed and still run `abf9717`, so smoke is 72 pass, 4 fail (check 0 and the worker's commit only). It runs with `SEB_ENFORCEMENT=disabled`; the host `lokaujian.lokandita.com` is in maintenance. See "LokaUjian U3" at the end of this file.**

**LokaUjian stage U2 — question authoring (`SPEC-LOKAUJIAN.md` §5, §6.3) — finished 2026-11-25, tag `lokaujian-u2` on `abf9717`, closed on the owner's order with gate step 7 skipped: **importing a file and export → import → export giving the same file have not been seen on the deployed system and rest on `ImporEksporTest` only.** All five components run `abf9717`; smoke 65 pass, 0 fail. The host `lokaujian.lokandita.com` is in maintenance. See "LokaUjian U2" and what follows it at the end of this file. Next: U3 (exam setup and delivery), begun 2026-11-25 with a plan for the owner's approval.**

**LokaUjian stage U1 — access (`SPEC-LOKAUJIAN.md` §3, §4) — finished 2026-11-25, tag `lokaujian-u1`, closed on the owner's order with half of the gate: the student part was done by the owner and is confirmed by the log; **the pengajar part (own classes only, changed class number → *Halaman tidak ditemukan*) was skipped by the owner and is proven by tests only.** The host `lokaujian.lokandita.com` is in maintenance again. See "LokaUjian U1" and "Gate result for LokaUjian U1" at the end of this file. Next: U2 (question authoring), not started; it begins in a new session with a plan.**

**LokaUjian stage U0 — contract and groundwork (`SPEC-LOKAUJIAN.md` §15) — finished 2026-11-25, tag `lokaujian-u0`, on the owner's report that the gate passed: real SEB 3.10.2 is accepted by LokaKode after the move, an ordinary browser is refused, and `lokaujian.lokandita.com` shows the maintenance page. See "LokaUjian U0" and "Gate result for LokaUjian U0" at the end of this file. LokaUjian stays in maintenance. Next: U1 (access), not started; it begins in a new session with a plan.**

**Stage 13 is closed and tagged `tahap-13` (2026-11-24, owner's order). TS-PY meetings 1–5 (32 problems each) and the practical mid-term at meeting 8 (6 problems, 51 points) are built, imported and approved on the owner's order; no TS-PY meeting is published and no window is open — publishing is the owner's. Left for later: the owner's rehearsal of the mid-term with a test student (no person has seen an exam page, none opened in Safe Exam Browser), and committing the exam files after the exam (they are outside Git, listed in `.git/info/exclude`, on this server only). See "Stage 13 — TS-PY" at the end of this file.**

**Stage 13 — problem production — all eleven meetings of the task (DDP101 4–7, PBO301 1–7) are built, imported and, on the owner's word of 2026-11-23 ("setujui semua soal yang sudah dibuat"), approved: 352 problems `APPROVED`. No meeting of the eleven is published and no class window is open — that is the owner's to do. No tag yet. See "Stage 13" at the end of this file.**

**Brand scheme and step cards — not a numbered stage, no tag — deployed 2026-11-22, running `ea483b6`; smoke 44 lulus, 0 gagal. New logos and icons on the three sites, "LokaAuth" shown to users as "Akun Lokandita", the Portal's app list is data (six cards), step numbers sit on the line of their text. Not seen by the owner in a browser yet; see "Brand scheme" at the end of this file. Next: stage 13 (problem production), not started.**

**Stage 10b — integrity and communication (`SPEC.md` §13.2, §14) — finished 2026-11-22, tag `tahap-10b`, on the owner's word "gate passed"; the production database shows no trace of the gate steps (see "Gate result for stage 10b" at the end of this file). Running `0ebd1a1`. Code similarity was dropped by the owner's decision. Next: stage 13 (problem production), not started.**

**Stage 10a — Safe Exam Browser (`SPEC.md` §13.1) — closed 2026-11-22, tag `tahap-10a`; the owner reported gate steps 1–6 passed for every course; **enforcement is ON in production since 2026-11-22 (`SEB_ENFORCEMENT=enabled`)**. The owner reported that a student is refused without SEB and staff are not; and that a student can log in and work inside SEB under enforcement (confirmed by the server log); Run, Submit and the heartbeat from real SEB under enforcement are not in the log yet. See "Stage 10a after the tag" at the end of this file. Next: stage 10b (similarity, typing anomaly, integrity page, notifications, clarifications, announcements), not started; it begins in a new session with a plan.**

**Stage 11c — public pages redesigned, replaceable hero photo, logout to the Portal, Kontak merged into Tentang — deployed 2026-11-21, running `a6637c0`; smoke 29 lulus. Checked by the owner in a browser the same day, first production upload works, restore test passed with real photo files; see "Stage 11c" at the end of this file. No tag (owner's decision).**

**Stage 11 — Portal and public pages (`SPEC.md` §15) — finished 2026-11-21, tag `tahap-11`, on the owner's report that the gate passed; see "Gate result for stage 11" at the end of this file.** All three hosts are open. **Next: stage 10 — SEB and integrity, communication (`SPEC.md` §13, §14), not started**; it begins in a new session with a plan for the owner's approval. Stage 13 (problem production) follows.

Stage 12 — production data — finished 2026-11-20, tag `tahap-12`, done ahead of stages 10 and 11 by the owner's decision; see "Stage 12" at the end of this file. Work done after the tag (class names, the log fix, the deadline for the initial password) is listed there too.

Stage 9b — leaderboard, analytics, exports, late policy, three languages and RTL — finished 2026-11-19, tag `tahap-9b`, on the owner's report that the gate passed; see "Gate result for stage 9" at the end of this file, which also lists the gate steps that left no trace in production. Stage 9a — student interface and scoring — finished 2026-11-17, tag `tahap-9a`. The next stage is the one after 9 in `SPEC.md` §18; it has not been started.

Stage 8 — Problems, concepts, importer — finished 2026-11-17, tag `tahap-8`; see "Gate result" at the end of this file.

Stage 7 — Judge worker — finished 2026-11-17, tag `tahap-7`; see "Gate result" at the end of this file.

Stage 6 — LokaKode academic core and policy layer — finished 2026-11-16, tag `tahap-6`; see "Gate result" at the end of this file.

Stage 5 — LokaAuth and the shared package — finished 2026-11-16, tag `tahap-5`; see "Gate result" at the end of this file. Plan approved by the owner 2026-11-15. See "Stage 5" at the end of this file for what is done; anything not listed there is not done.

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
- Step 7 — superadmin pages (`/admin/…`, superadmin only): user list with search, filters and CSV/XLSX export; create and edit (nomor induk fixed); suspend / disable / reactivate; reset password; remove MFA; app access; paste permission; bulk actions with a confirmation that states the number; bulk import with preview (CSV and XLSX); one-time download of temporary passwords; audit page with filters. `infra/tes.sh auth` → 94 tests, 808 assertions pass; `infra/tes.sh lokakode` → 19; `infra/tes.sh portal` → 19. Includes authorisation tests (non-superadmin refused on every admin route, another superadmin cannot run someone else's import preview). Not deployed; no page has been seen in a browser.

- Step 9 — close, 2026-11-15 (commands and output shown in the session):
  - `infra/tes.sh auth` → 94 tests, 808 assertions; `infra/tes.sh lokakode` → 19 tests; `infra/tes.sh portal` → 19 tests. All pass.
  - `infra/deploy.sh auth`, `lokakode`, `portal`: each verified at `/healthz`. The LokaAuth migrations ran on the real `identity` schema (7 tables, 2 views, 0 users) and `identity_reader` got its three grants.
  - `infra/smoke.sh` → 16 passed, 0 failed, 1 not active (check 6, judge, stage 7). Checks 3, 4 and 5 ran for the first time and passed.
  - Real request from the Caddy container to the deployed LokaAuth: `GET /login` → 200 with the login form, CSP with nonce, CSRF cookie `Secure; HttpOnly`; `GET /admin/pengguna` without a session → 302 to `/login`.
  - `infra/backup.sh` and `infra/uji-restore.sh` → passed; the 10 `identity` objects were restored and compared.
- Tailwind, 2026-11-15 (owner's decision after step 9): Tailwind CSS 4.3.5 is compiled inside the image build; `/aset/lokandita.css` is the compiled result. A test fetches it over HTTP and checks that it is compiled Tailwind, holds the shared components and loads nothing from another host. Test, deploy and smoke results for this change are in the session and in the list below.

Not verified, or weaker than it looks:

- **No page of stage 5 has been seen in a browser.** All three hosts are still in maintenance. Layout, the Arabic (right-to-left) pages, the QR code for TOTP and the downloads are tested only as HTTP responses.
- **The session-expiry banner and idle logout in the browser are JavaScript and are not exercised by any test**; only their server side is tested.
- **No superadmin exists in production.** Login on the real `identity` schema has never happened; every login test ran on `identity_test`.
- The restore test now covers real tables, but they hold no user rows yet. Rerun `infra/uji-restore.sh` after the first accounts exist.
- **The Tailwind change has not been seen in a browser either.** Tailwind resets browser defaults; paragraph spacing and list markers were restored by hand, but small visual differences (spacing, form controls) are possible and only a browser will show them.
- A request that reaches the app container directly from its own loopback (not through Caddy) gets a 500 on `/login`, because the app refuses to send a `Secure` cookie over plain HTTP. Not reachable from outside; the error page is just not friendly.
- Two admin tests read the CSRF and import tokens from the wrong response; found and fixed in step 7 (see `DECISIONS.md`). One of them had passed for the wrong reason.
- `infra/tes-infra.sh` was not re-run in this stage.

Owner's gate for stage 5 (`SPEC.md` §18):

1. Create the first superadmin in your own terminal (the temporary password is printed once, there):
   `docker exec -it auth php spark akun:superadmin`
2. Open the host: `infra/pemeliharaan.sh auth off` — the login page becomes public. The agent will not run this without being asked.
3. In a browser at `https://auth.lokandita.com`: log in, change the password, enrol TOTP, keep the 8 backup codes.
4. Create one dosen and one mahasiswa at `/admin/pengguna/baru`; log in as each in a private window (forced password change).
5. Press **Keluar**; then try to reuse the old session (back button or reload): it must land on the login page.
6. Run `infra/smoke.sh`. Check 1 now expects `auth` to answer 2xx/3xx instead of 503.

After the gate: tag `tahap-5`, record the result here.

Gate result, 2026-11-16 (the session of 2026-11-15 was cut off by the usage limit after the Tailwind commit; this is the closing session):

- The owner reported that his part of the gate is done and fine.
- What the audit log of the real `identity` schema shows (actions and roles only): superadmin created by `spark akun:superadmin`, logged in, enrolled TOTP, changed the password; created one dosen and one mahasiswa; both logged in and changed their password; 8 `logout` events, no live session left. `auth.lokandita.com` has been open since 2026-11-15 22:25 WIB.
- **Every one of those logouts was made on `auth.lokandita.com` itself.** `lokandita.com` and `lokakode.lokandita.com` were never opened, their containers never served `/beranda`, and no `logout` came from a client app. **Keluar from a client app, and login reaching LokaKode or the Portal without a second login, have not been seen in a browser**; they are covered only by the tests between real containers.
- Because the Portal is closed, a successful login without `next` lands on the 503 maintenance page (`SPEC.md` §4.6 sends users to the Portal). This confused the owner during the gate. It stays so until the Portal is opened.
- "Old session is refused" in the browser is the owner's observation; the audit log cannot show it.

Re-run by the agent in the closing session, at commit `f6947a4` (output shown in the session):

- `infra/tes.sh auth` → 95 tests, 822 assertions; `infra/tes.sh lokakode` → 19 tests, 104 assertions; `infra/tes.sh portal` → 19 tests, 104 assertions. All pass. (95 instead of 94: the Tailwind test.)
- `infra/tes-infra.sh` → 54 passed, 0 failed. Stage 4 recorded 55; the difference was not investigated.
- `infra/backup.sh` and `infra/uji-restore.sh` → passed, 12 schemas/tables restored and equal to the live ones, now with three real user rows.
- `infra/smoke.sh` → 16 passed, 0 failed, 1 not active (check 6, stage 7).

Second browser check, 2026-11-16 00:45 WIB, after the gap above was reported to the owner:

- The agent redeployed all three apps at `1ae90c3` and opened `lokandita.com` and `lokakode.lokandita.com` at the owner's request.
- The owner logged in as the dosen, reached `/beranda`, pressed **Keluar** and reported landing back on the login page after it. The audit log shows `logout` by the dosen with `app = lokakode`, and no live session afterwards; both client containers served `/beranda`. **This closes the gap for the dosen, on LokaKode.** Keluar pressed from the Portal, and the same check as mahasiswa, were not done in a browser.
- The owner then had all three hosts closed. `infra/smoke.sh` with the three hosts in maintenance → 16 passed, 0 failed, 1 not active. The tagged commit differs from `1ae90c3` only in this file.

**Stage 5 finished 2026-11-16, tag `tahap-5`.**

Carried into the next stages:

- All three hosts are in maintenance. The stage 6 gate needs `auth` and `lokakode` opened again (`infra/pemeliharaan.sh <app> off`).
- The three accounts in production are real rows. The dosen and the mahasiswa are kept active for the stage 6 gate; they cannot be deleted, only disabled, and should be disabled before the import of stage 12 unless the owner says they are real.
- While the Portal is closed, a login without `next` lands on the maintenance page.
- The items under "Not verified, or weaker than it looks" above still hold, except that a superadmin now exists and login on the real schema has happened.

## Stage 6 — 2026-11-16

Plan approved by the owner 2026-11-16 (8 steps, one commit each): 1 schema and harness · 2 policy layer, beranda, read pages · 3 tahun ajaran and mata kuliah · 4 kelas and pengajar · 5 enrollment · 6 pindah kelas, kelulusan, reset password · 7 new-year tool · 8 audit page and close.

Owner's decisions for this stage (2026-11-16): LokaKode keeps its own append-only `audit_log`, written in the same transaction as the change, with a superadmin page; pindah kelas moves the one enrollment row and records the move in `enrollment_moves` (`DIPINDAHKAN` is set by nothing); pengampu and pengajar can reset a student's password; a student without `lokakode` access or not `active` is enrolled and flagged, a non-mahasiswa is refused; the new-year tool only warns about students still `AKTIF`.

Built (LokaKode, `SPEC.md` §5 and the policy layer of §3):

- Schema `lokakode`: `academic_years`, `courses`, `classes`, `class_teachers`, `enrollments`, `enrollment_moves`, `audit_log`. MySQL itself refuses a second active year, a second enrollment per student per course per year, an enrollment pointing at a class of another course, and deleting a class that has enrollments.
- Policy layer `App\Policy\Akses`: SQL conditions used by every query in `App\Libraries\Akademik`. Outside scope → 404 (same page as "does not exist"); role never allowed → 403; visible but not manager → 403; archived year → read-only for dosen.
- Pages: `/beranda` (overview for staff with classes without pengajar flagged; own classes for mahasiswa), `/mata-kuliah`, `/mata-kuliah/{id}`, `/kelas/{id}`, `/tahun-ajaran`, `/tahun-ajaran/baru` (new-year tool), `/audit`.
- Actions: create/edit/activate academic year; create/edit course and change pengampu; create/edit/archive/delete class; assign and remove pengajar; enroll by single entry or CSV with preview; mengundurkan diri; pindah kelas; kelulusan (one, many, whole class, with date) and its cancellation; reset a student's password through LokaAuth; every change audited.
- Shared package: `ReaderDb`, `UserDirectory` (names read from LokaAuth's `user_directory`, never copied), a "select all" box in the shared script.
- `KONTRAK.md` §4, §5 and §6 updated; choices recorded in `DECISIONS.md`.

Verified by the agent (commands and output shown in the session), at commit `5599d0f`:

- `infra/tes.sh lokakode` → 83 tests, 932 assertions, all pass. Against real MySQL, a real `lokakode-tes` container and a real `auth-tes` container; nothing mocked.
  - `tests/Policy/MatriksAksesTest.php`: reads the registered routes and fails if one is missing from the matrix; all 31 LokaKode routes that need a session requested by six actors (superadmin, two pengampu, two pengajar, one mahasiswa); refused requests with valid payloads change no row and no password.
  - `tests/Policy/LingkupKelasTest.php`: **the gate case** — pengajar A requests `/kelas/<class of pengajar B>` and gets 404 with no name, nomor induk or class name of class B in the page; the answer is byte-for-byte the same as for a class number that does not exist.
  - Password reset by a pengajar goes through real HTTP to LokaAuth: the shown password is the one that works, the student's old session dies, both audit logs have a row.
- `infra/tes.sh auth` → 95 tests; `infra/tes.sh portal` → 19 tests. All pass (the shared package changed).
- `infra/cek-env.sh` → passed. No new environment variable in this stage.
- `infra/deploy.sh auth`, `lokakode`, `portal` → each verified at `/healthz`. The LokaKode migration ran on the real `lokakode` schema: 7 tables, all empty.
- `infra/smoke.sh` → 16 passed, 0 failed, 1 not active (check 6, judge, stage 7).
- Real requests from the Caddy container to the deployed `lokakode`, without a session: `GET /kelas/1`, `/mata-kuliah`, `/tahun-ajaran`, `/audit`, `/beranda` → 302 to the LokaAuth login with the right `next`; `POST /kelas/1/mundur` without CSRF → 403. From the internet `https://lokakode.lokandita.com/kelas/1` → 503 (maintenance).
- `infra/backup.sh` and `infra/uji-restore.sh` → passed, 19 schemas/tables restored and equal, now including the LokaKode tables (empty).
- `infra/tes-infra.sh` → 55 passed, 0 failed (stage 5 recorded 54; the earlier difference is still not explained).
- After this entry was committed the three apps were deployed again so that `/healthz` reports `HEAD`; the smoke result of that deploy is in the session.

Not verified, or weaker than it looks:

- **The gate itself has not been done on the deployed system.** Production has no academic year, course or class, and only one dosen, so the agent could not make the real request "pengajar A opens class B" against the deployed container without inventing production accounts and data. It is proven only by the tests between real containers. The owner's gate below is the real check.
- **No page of stage 6 has been seen in a browser.** Layout, the Arabic (right-to-left) pages, the roster form with its many buttons, the file upload and the date fields are tested only as HTTP responses.
- **The "select all" box is JavaScript and is not exercised by any test.** Without it every row can still be ticked by hand.
- The Arabic texts were written by the agent and not reviewed by an Arabic reader.
- CSV upload was tested with small files only; the 2000-row limit and the 1 MB limit are enforced in code but not tested at that size.
- Class capacity only warns (see `DECISIONS.md`); say so if it should block.
- Password reset from LokaKode has never run against the real `identity` schema.

Owed by later stages (nothing exists yet to do them on):

- Stage 9: after pindah kelas, recompute per-class aggregates and leaderboards (`Pendaftaran::pindah` has the note); refuse submissions from a student whose enrollment is `LULUS`.
- Stage 7: the judge worker's MySQL user must get no right on the academic tables (`KONTRAK.md` §6).
- Stage 11: `GET /` of LokaKode is still only a redirect to `/beranda`; the public landing replaces it.
- Stage 12: seed data (`SPEC.md` §17) and the real CSV files; nothing was read from `/srv/lokandita-data` in this stage.

Owner's gate for stage 6 (`SPEC.md` §18: a pengajar cannot open another pengajar's class by changing the URL):

1. Open the two hosts: `infra/pemeliharaan.sh auth off` and `infra/pemeliharaan.sh lokakode off`. The agent will not run this without being asked.
2. In LokaAuth as superadmin, create a **second dosen** (`/admin/pengguna/baru`, with access to `lokakode`) and log in once as that dosen to change the password. The three existing accounts already have `lokakode` access.
3. At `https://lokakode.lokandita.com` as superadmin: *Tahun ajaran* → add one and press **Aktifkan**; *Mata kuliah* → *Tambah mata kuliah* with the first dosen as pengampu.
4. On the course page add two classes, for example A and B. Open class A and assign the **second dosen** as pengajar. Open class B, assign the first dosen as pengajar, and enroll the mahasiswa (type the nomor induk, *Periksa dulu*, then *Daftarkan 1 mahasiswa*). Note the number in the URL of class B.
5. In a private window log in as the **second dosen**: the beranda must list class A only. Open class A, then replace the number in the URL with the number of class B → the page must say *Halaman tidak ditemukan* and show nothing about class B. Try `/kelas/<B>/ubah` as well.
6. Optional: as the mahasiswa, the beranda shows the class; `/kelas/<B>` answers 403.
7. As superadmin open `/audit`: every step above is listed with who did it.
8. Run `infra/smoke.sh`. Check 1 now expects `auth` and `lokakode` to answer 2xx/3xx instead of 503.

After the gate: tag `tahap-6`, record the result here. The rows created during the gate are real rows in production; classes with enrollments cannot be deleted, only archived.

Gate result, 2026-11-16:

- The owner reported step 5: the second dosen replaced the class number in the URL and got *"Halaman tidak ditemukan. Data yang Anda cari tidak ada, atau Anda tidak berhak melihatnya."* **The gate of `SPEC.md` §18 passes.**
- The owner ran `infra/smoke.sh` and reported `16 lulus, 0 gagal, 1 belum aktif`.
- **The one check that is "belum aktif" is check 6: "judge worker berjalan dan satu job RUN sederhana selesai"** (`KONTRAK.md` §11, item 6). It is switched off at the top of `smoke.sh` because the judge worker does not exist yet; it is built in stage 7. It is counted separately and is not a pass: **nothing about running or judging code is proven until stage 7 switches it on.**
- What the server shows, read by the agent (actions, roles and counts only):
  - LokaKode `audit_log`: `tahun.dibuat` 1, `tahun.diaktifkan` 1, `matkul.dibuat` 1, `kelas.dibuat` 2, `pengajar.ditugaskan` 3, `pengajar.dicabut` 1, `pendaftaran.dibuat` 1. Now in `lokakode`: 1 active academic year, 1 course, 2 classes with one pengajar each, 1 `AKTIF` enrollment. LokaAuth has 2 active dosen.
  - The `lokakode` container log has exactly one `access.denied` with reason `outside_scope_or_missing`, on `/kelas/1`. The refused account is a dosen who owns no course and is pengajar of class 2 only; class 1 has a different pengajar and holds the enrollment. This matches the owner's report.
- Not done in a browser, or not reported: the optional step 6 (mahasiswa on `/kelas/<id>` → 403; the log has no such denial, so it was not tried), `/kelas/<id>/ubah` as the second dosen, and step 7 (the `/audit` page). The owner did not comment on how the pages look, on the Arabic pages, or on the "select all" box; the items under "Not verified, or weaker than it looks" above still hold, except that stage 6 pages have now been used in a browser by the owner and LokaKode has real rows.
- Password reset from LokaKode was not part of the gate and has still never run against the real `identity` schema.
- After this entry was committed and tagged, the three apps were deployed again so `/healthz` reports the tagged commit, and the agent ran `infra/smoke.sh`; the output is in the session.

**Stage 6 finished 2026-11-16, tag `tahap-6`.**

Carried into the next stages:

- `auth.lokandita.com` and `lokakode.lokandita.com` are **open**; the Portal is in maintenance, so a login without `next` still lands on the 503 page. Closing them again is the owner's call (`infra/pemeliharaan.sh <app> on`).
- The rows created during the gate (one year, one course, two classes, one enrollment, a second dosen) are real production rows. The class with the enrollment cannot be deleted, only archived. Decide before the import of stage 12 whether they are real or should be archived and the test accounts disabled.
- Smoke check 6 becomes active in stage 7.
- The "Owed by later stages" list above.


## Stage 7 — 2026-11-16

Plan approved by the owner 2026-11-16 (7 steps): 1 contract and harness · 2 images and seccomp · 3 sandbox and hostile-program tests · 4 worker · 5 web side · 6 service and deploy · 7 close. Steps 4–6 share two commits because the first session was cut off by the usage limit.

Owner's decisions for this stage (2026-11-16): the worker is a systemd *user* service of `deploy` with linger (enabled by the owner); `RUN` works end to end and the other job kinds answer `FAILED` / `unsupported_kind` until stages 8–9; a time limit is CPU time measured inside the container, with a wall-clock limit of 2 × limit + 1 s; auth and portal were redeployed only to report the current commit.

Built (`SPEC.md` §10, `KONTRAK.md` §6):

- `judge_jobs` in the `lokakode` schema; MySQL user `judge_worker` with `SELECT, UPDATE` on that table only; `judge/.env`.
- Images `judge-c`, `judge-cpp` (GCC 13), `judge-python` (3.12, NumPy 2.5.5, Matplotlib 3.11.2), base images pinned by digest; seccomp profile generated from Docker's default; `pelari`, the supervisor inside each container.
- Sandbox (`judge/lokandita_judge/kotak.py`): one new container per program with every limit of the §10 table; verdicts OK/AC, WA, CE, RE, TLE, MLE, OLE; four comparison modes; fails closed.
- Worker: normal queue before low, conditional claim, progress, two retries then `judge_unavailable`, recovery after a crash and of rows that never reached Redis, reaper by label, heartbeat with the running commit.
- LokaKode: `App\Libraries\Antrean`, `GET /api/executions/{id}` (own jobs only), `php spark judge:coba`.
- `infra/tes.sh judge`, `infra/deploy.sh judge`, smoke check 6, `judge/uji-terima.sh`.
- `KONTRAK.md` §6, §10, §12 updated; choices in `DECISIONS.md`.

Verified by the agent (commands and output shown in the session), at commit `e23423e`:

- `infra/tes.sh judge` → 137 passed. Real hostile programs in real containers; nothing skipped (a skipped test fails the run).
- `infra/tes.sh lokakode` → 93 tests, 1023 assertions, all pass, with a real test worker on the host. The authorisation matrix covers the new route: a job of mahasiswa A answers 404 to another mahasiswa, the pengajar, the pengampu and the superadmin.
- `judge/uji-terima.sh` → 10 of 10 items LULUS. 200 simultaneous `RUN` jobs took 109–133 s at `JUDGE_CONCURRENCY=4`.
- `infra/deploy.sh lokakode` and `infra/deploy.sh judge` → verified by `/healthz` and by the worker's heartbeat. The LokaKode migration created `judge_jobs` in the real `lokakode` schema.
- `infra/smoke.sh` check 6 → five lines LULUS: service active, heartbeat at HEAD, linger on, a real `RUN` through the deployed LokaKode returned the right output (about 1.8 s), no judge container left.
- After this entry was committed all four components were deployed again so that every one reports `HEAD`; the outputs of that deploy, of `infra/smoke.sh`, `infra/backup.sh`, `infra/uji-restore.sh` and `infra/tes-infra.sh` are in the session and will be recorded with the gate result.

Not verified, or weaker than it looks:

- **Only `RUN` with custom input does anything.** `SUBMIT`, `GENERATE`, `VALIDATE`, `ANALYZE` and `RUN` with an `instance_id` answer `FAILED` / `unsupported_kind`. The engine that judges a list of tests (`mesin.nilai_tes`) is tested directly but nothing calls it yet. No scoring, no concept analysis.
- **No student can run code yet**: there is no page and no endpoint that creates a job; the only producer is `spark judge:coba`. `GET /api/executions/{id}` without a session redirects to the login page; the browser code of stage 9 must handle that.
- **The worker has not been through a reboot.** Linger is on, but that it starts by itself after a restart of the server has not been tested.
- **One unexplained slow run**: once, in a full test run, a compile that normally dies at the memory limit in 3 s was instead stopped by the 10 s watchdog. Four repeats by hand behaved normally. The test accepts both outcomes (`DECISIONS.md`).
- Throughput: every test of a submission will be its own container (about 0.55 s overhead each). A submission with 12 hidden tests is therefore about 8 s of work; 200 of them queued at once would take roughly 6–7 minutes at concurrency 4. This is an estimate, not a measurement; only `RUN` was measured.
- Isolation is Docker with runc, as `SPEC.md` says: limits, seccomp, no capabilities, no network. It is not a virtual machine; a kernel or runc vulnerability is outside what these tests can show.
- The worker runs as `deploy`, which is in the `docker` group and is therefore root-equivalent on this host.
- A compile of `#include <bits/stdc++.h>` peaked at 170 MB of the 256 MB limit; heavy template code may hit the limit and be reported as a compile error with reason `memory`.
- `infra/tes.sh auth` and `infra/tes.sh portal` were not run in this stage (the shared package did not change).
- Each `infra/smoke.sh` run leaves one small system `RUN` row in the real `judge_jobs`. There is no clean-up of old rows yet; decide retention in stage 9.

Owed by later stages:

- Stage 8: `VALIDATE`, `GENERATE`, `ANALYZE` (tree-sitter, `ast`); the grant script must then name every further table the worker touches.
- Stage 9: `SUBMIT` and `RUN` on sample tests; the endpoints that create jobs, with rate limits; compiler flags per meeting; retention of `judge_jobs`; plus the items stage 6 left for stage 9.

Owner's gate for stage 7 (`SPEC.md` §18: all acceptance tests pass on this host):

1. `judge/uji-terima.sh` — about 3 minutes; loads the CPU for about 2 of them; uses the test schema only. Expect ten lines `LULUS` and `uji terima: LULUS — 10 dari 10 butir`.
2. `infra/smoke.sh` — expect `21 lulus, 0 gagal, 0 belum aktif`.
3. Optional: `systemctl --user status lokandita-judge` and `journalctl --user -u lokandita-judge -n 20`.
4. Optional, at a quiet moment: reboot the server, wait two minutes, run `infra/smoke.sh` again — this is the only way to prove the worker comes back by itself.

After the gate: tag `tahap-7`, record the result here.

Results of the closing deploy of 2026-11-16, at commit `14b1836` (output shown in the session):

- `infra/deploy.sh auth`, `lokakode`, `portal`, `judge` → each verified (`/healthz`, worker heartbeat). Auth and portal were redeployed with the owner's approval, only so that they report the current commit.
- `infra/smoke.sh` → 21 passed, 0 failed, 0 not active. Check 6 is active for the first time.
- `infra/backup.sh` → passed, now with 5 `.env` files (`judge/.env` included). `infra/uji-restore.sh` → passed, 20 schemas/tables restored and equal, `judge_jobs` among them.
- **`infra/tes-infra.sh` → 53 passed, 1 failed.** The failing line is "auth dibuka: host lain tetap pemeliharaan": the test expects `lokakode.lokandita.com` to answer 503, but that host has been open by the owner's choice since the stage 6 gate. The test's assumption is out of date; the edge behaves as configured. **Not fixed in this stage.** The test left the open/closed state of the hosts as it was.

Gate result, 2026-11-17:

- **The owner's message closing the stage contained the four result lines as an unfilled template** (no values for `uji-terima.sh`, containers left, the smoke line, or the reboot). Nothing below is the owner's report; it is what the server shows and what the agent ran itself.
- What the server shows (counts and times only):
  - The server was rebooted at 2026-11-17 00:36 WIB and now runs kernel 6.8.0-149 (it was 6.8.0-147). All six containers came back healthy.
  - **The worker came back by itself**: `lokandita-judge.service` became active 18 seconds after boot with 0 restarts and logged `mulai` at commit `14b1836`. This closes "the worker has not been through a reboot" above.
  - `lokakode_test.judge_jobs` held 200 `DONE` rows created 00:32–00:34 WIB, four minutes before the reboot and long after the agent's last run: the 200-job acceptance test was run by someone other than the agent and every job finished. The earlier items of that run leave no trace in the database, so their result is not known to the agent.
- Run by the agent after the reboot (output shown in the session):
  - `infra/smoke.sh` → `21 lulus, 0 gagal, 0 belum aktif`; the real `RUN` through LokaKode returned the right output in about 1.5 s.
  - `judge/uji-terima.sh` → `uji terima: LULUS — 10 dari 10 butir`; 200 jobs in 98 s; 0 judge containers left afterwards. **The gate of `SPEC.md` §18 passes on this host.**
- The tagged commit differs from the deployed `14b1836` only in this file. **It has not been redeployed**, so `infra/smoke.sh` check 0 and the worker heartbeat line fail until `infra/deploy.sh` is run for the four components; nothing that runs has changed.

**Stage 7 finished 2026-11-17, tag `tahap-7`.**

Carried into the next stages:

- Redeploy the four components at the tagged commit when convenient, or let the first deploy of stage 8 do it.
- `infra/tes-infra.sh` section I assumes every host but auth is in maintenance; make it follow the real state of the hosts.
- The items under "Not verified, or weaker than it looks" and "Owed by later stages" of stage 7 still hold, except the reboot.
- `auth.lokandita.com` and `lokakode.lokandita.com` are open; the Portal is in maintenance.

Added 2026-11-17, after the tag:

- The owner confirmed that his own acceptance run matched the agent's (10 of 10). His message ended with a colon and no details after it, so no figures of his run are recorded here.
- With the owner's approval the four components were redeployed at the commit that holds this note (one commit after `tahap-7`, differing only in this file); the deploy and `infra/smoke.sh` output are in the session.

## Stage 8 — 2026-11-17

Plan approved by the owner 2026-11-17 (8 steps, one commit each): 0 `tes-infra.sh` follows the real state of the hosts · 1 contract and schema · 2 concept catalogue and minimal meeting pages · 3 concept analyser · 4 worker: batch mode, `VALIDATE`, `GENERATE`, `ANALYZE` · 5 importer and CLI · 6 web: upload, problem bank, review · 7 close.

Owner's decisions for this stage (2026-11-17): `{{param}}` in a statement is filled from an optional `params` object of the problem, the same for every student; statements are Markdown rendered on the server with raw HTML disabled; a translation may carry `"machine": true`, shown as a badge the pengampu can clear; a pengajar who is not the pengampu has no access to the problem bank in this stage; a minimal `meetings` table (number, title, language, concepts, enabled) is built now and the rest of `SPEC.md` §6 stays in stage 9; the sample files target `DDP101`, the one course in production.

Not in this stage: target, composition, publishing and class windows; student pages; `SUBMIT`; applying `CONCEPT_NOT_MET` to a score; whatever triggers pre-generation; calibration (`SPEC.md` §8.4); seeding the three courses; real problem production.

Done:

- Step 0 — `infra/tes-infra.sh` section I no longer assumes `lokakode` is in maintenance. `infra/tes-infra.sh` → 57 passed, 0 failed (auth was open, so the "closed again" assertion did not run; with auth closed the count is 58). This also explains the 54/55 difference left open in stages 5–7.
- Step 1 — schema: `concepts` (45 rows loaded from `app/Data/konsep.json`), `meetings`, `meeting_concepts`, `course_base_concepts`, `problems`, `problem_versions`, `meeting_problems`, `problem_instances`, `imports`, `import_items`; `judge_worker` gains `SELECT` on `problem_versions` and `SELECT, INSERT` on `problem_instances`. `infra/tes.sh lokakode` → 100 tests, 1054 assertions; `infra/tes.sh judge tests/test_kontrak_db.py` → 32 passed. Exists only in `lokakode_test`; not deployed.
- Step 2 — minimal meetings and concepts: `/mata-kuliah/{id}/pertemuan` (list, add, base concepts of the course), `/pertemuan/{id}` (number, title, language, used-for-practice switch, concepts added; inherited concepts ticked and locked). Superadmin and the pengampu manage; a pengajar gets 403, another pengampu 404. A meeting that has problems can no longer change number or language. `infra/tes.sh lokakode` → 107 tests, 1146 assertions; the authorisation matrix covers the five new routes. Not deployed; no page seen in a browser.
- Step 3 — concept analyser: image `judge-analisis` (tree-sitter 0.26.1 with the C and C++ grammars, pinned by hash; Python's `ast`), `kotak.analisis()` runs it in a judge container with the same limits as every other one; the analysed code is parsed, never run. Detectors for all 45 catalogue concepts; 39 corpus programs in `judge/tests/korpus/` each state the exact set of concepts that must be found. `infra/tes.sh judge tests/test_analisis.py tests/test_image.py` → 39 passed, including the gate case (`cout << x << " " << y` with doubles is not `STRING`), keywords inside comments and literals, and deliberately deep nesting. Rules for the owner to review: `docs/DETEKSI-KONSEP.md`. Nothing calls the analyser yet (step 4).
- Found in step 3: the running production worker computed the judge image tag from the working tree on every job, so adding files under `judge/images/` made it look for images that did not exist. No endpoint creates jobs yet, so only smoke check 6 was exposed; images with the new tag were built at once. Fixed in code (tag computed once per process; old images removed only by `infra/deploy.sh judge`), **but the production worker still runs the old code until it is redeployed in step 7**.
- Step 4 — worker: `kelompok`, a small batch runner inside the judge images, runs every test of a generator or reference solution in one container (`SPEC.md` §8.2), emptying `/box` and killing left-over processes between runs; `VALIDATE`, `GENERATE` and `ANALYZE` work; `SUBMIT` still answers `unsupported_kind`. `infra/tes.sh judge` → **236 passed in 12 min 42 s**, nothing skipped: the stage 7 suite, 20 batch-mode tests with hostile generators (fork bomb, memory, network, `/etc/passwd`, symlink, write outside `/box`, forged result frames), 37 tests of the validation logic, 14 end-to-end through the real worker and MySQL. **Measured: one C++ problem (12 tests × 20 seeds, plus the determinism runs) validates in 13.8 s** on this server; a file of 30 problems at `JUDGE_CONCURRENCY=4` is therefore roughly two minutes — an estimate from that one figure, not a measurement.
- Step 5 — importer: `App\Libraries\Soal\Impor` (file check with messages that say what to change, JSON line of every problem and field, one `VALIDATE` job per problem, new version `PENDING_REVIEW` only when the judge answers `ok`, idempotent by slug, audited), `php spark soal:impor [file] --oleh <nomor induk superadmin>` and `php spark soal:ekspor <course> <meeting>`. An export imported again reports every problem unchanged.
- Step 6 — web: `/impor` (upload and history, superadmin only; pengampu and pengajar 403 on page and endpoint), `/impor/{id}` (progress and per-problem report; reloads itself by the HTTP `Refresh` header, no JavaScript), `/soal/{id}` (statement as Markdown with `{{param}}` filled and raw HTML shown as text; fallback and machine-translation badges; reference solution and generator; approve, reject with a reason, clear the machine badge), `/soal/{id}/ubah` (edit as a new validated version). PHP's upload limit raised to 9 MB in the app image.
- Steps 5 and 6 share one commit: the pages, the CLI and their tests were finished together. `infra/tes.sh lokakode` → **133 tests, 1577 assertions, all pass**, against real MySQL, Redis, a real `auth-tes`, a real judge worker and real judge containers; plus 3 seed tests added afterwards (run with the schema and matrix tests: 16 tests, 593 assertions). Covered: the gate case through `spark soal:impor` (a file with `cout << x << " " << y` on doubles is accepted; a problem using `if` is rejected with "baris 6, kolom 5" and the JSON line); idempotence and new versions; export → import; whole-file refusals with their messages; a 3 MB upload over HTTP; approve / reject / new version; edit as a validated version; the authorisation matrix for all 9 new routes, with refused requests changing nothing.
- The owner's sample files `docs/contoh-soal/ddp101-pertemuan-2.json` (3 problems) and `…-salah.json` (1 good, 1 using `if` at line 7) were validated by the real judge with the concepts of DDP101 meeting 2: `infra/tes.sh judge tests/test_soal.py -k contoh` → 2 passed.

Verified by the agent at commit `c76fa9f` (commands and output shown in the session):

- `infra/tes.sh auth` → 95 tests, 822 assertions; `infra/tes.sh portal` → 19 tests, 105 assertions (the shared stylesheet and the app Dockerfile changed); `infra/tes.sh lokakode` → 136 tests, 1571 assertions; `infra/tes.sh judge` → 238 passed in 14 min 32 s, nothing skipped. All pass.
- `judge/uji-terima.sh` → `uji terima: LULUS — 10 dari 10 butir`; 200 jobs in 106 s. The sandbox gained a batch mode in this stage; the stage 7 acceptance items still hold.
- `infra/deploy.sh lokakode`, `judge`, `auth`, `portal` → each verified (`/healthz`, worker heartbeat at `c76fa9f`, image tag `35289448f54f`). The migration ran on the real `lokakode` schema: 19 tables now, 45 concepts, 0 meetings, 0 problems. `judge_worker` has exactly the three grants of `KONTRAK.md` §6 on `lokakode`.
- `infra/smoke.sh` → **22 passed, 0 failed, 0 not active**. Check 7 is new: one real `VALIDATE` through the deployed LokaKode and the production worker answered `ok` with 240 tests in 15.1 s.
- Real requests without a session: `https://lokakode.lokandita.com/impor`, `/pertemuan/1`, `/soal/1`, `/soal/1/ubah`, `/mata-kuliah/1/pertemuan`, `/impor/<uuid>` → 302 to the LokaAuth login with the right `next`; `POST /soal/1/setujui` and `POST /impor` without CSRF → 403. In the deployed container: `php spark soal:ekspor DDP101 2` → "pertemuan 2 tidak ada", exit 1 (read-only; proves the command and its database access); `upload_max_filesize` is 9M.
- `infra/backup.sh` and `infra/uji-restore.sh` → passed, 30 schemas/tables restored and equal, the new tables among them. `infra/tes-infra.sh` → 57 passed, 0 failed.

Not verified, or weaker than it looks:

- **The gate has not been done on the deployed system.** Production has no meeting and no problem. A real import needs meetings created by the pengampu or superadmin, and `--oleh` would have written the owner's account into the audit log for something the agent did, so the agent did not import anything in production. The gate case is proven by tests against real containers and by the real judge validating the two sample files; the owner's gate below is the real check.
- **No page of stage 8 has been seen in a browser**: the meeting page with its many checkboxes, the upload, the report page that reloads itself (the `Refresh` header is tested only as a header), the Markdown statement, the Arabic (right-to-left) pages, the edit form.
- **Concept detection is a set of syntactic rules, not a compiler.** 39 corpus programs cover every catalogue concept once or twice; real problems will contain constructs the corpus does not. A wrong "found" refuses an import, visibly and with a line number; a wrong "not found" lets a concept through silently. The OOP concepts that are about design (`ENCAPSULATION`, `KOMPOSISI_AGREGASI`, `INTERFACE`) are the weakest. `docs/DETEKSI-KONSEP.md` is waiting for the owner's review.
- **Nothing uses `GENERATE`, `ANALYZE` or the seed yet.** They work and are tested, but no page creates an instance, no submission is analysed and `CONCEPT_NOT_MET` is applied nowhere. `SUBMIT` still answers `unsupported_kind`. No student can do anything new.
- Validation time (13.8 s and 15.1 s) was measured on tiny problems. A reference solution that needs most of its time limit on each of 240 tests will take minutes; the 30-problem estimate above is not a measurement.
- The limits of 8 MB and 200 problems per file are enforced in code but were not tested at that size; the largest upload tested is 3 MB (two problems padded with spaces).
- An import whose report page is closed stays "sedang divalidasi" until somebody opens it again (by design, see `DECISIONS.md`). What happens to an import when the worker dies in the middle was not tested end to end; the jobs themselves are recovered as in stage 7.
- Changing the concepts of a meeting does not re-check the problems already stored for it.
- `SPEC.md` §10 says the memory limit can be set per problem; the import format has no such field and none was added. Every problem runs with 256 MB.
- The Arabic texts of this stage were written by the agent and not reviewed by an Arabic reader.
- Each `infra/smoke.sh` run now leaves two system rows in the real `judge_jobs` (one `RUN`, one `VALIDATE`). Retention is still to be decided in stage 9.
- Between step 3 and the deploy the old production worker looked up judge images by a tag computed from the edited working tree. The images were rebuilt at once each time and no endpoint creates jobs, so nothing failed; the cause is fixed in the deployed worker.

Owed by later stages:

- Stage 9: the rest of `SPEC.md` §6 on top of the minimal `meetings` table (target, composition, class windows, publishing, starter template, compiler flags); creating instances (`Benih` + `GENERATE`) when a student opens a problem and pre-generation when a window opens; `SUBMIT`, `RUN` on sample tests, `CONCEPT_NOT_MET` through `ANALYZE`; showing statements to students with the fallback badge; calibration (`SPEC.md` §8.4); whether a pengajar may see the problem bank (owner's decision in stage 8: not yet); Monaco in the edit form; retention of `judge_jobs` and `imports`; memory limit per problem if wanted.
- Stage 12: the 16 meetings of each course with their concepts from `docs/PETA-KONSEP.md`, and PBO301's base concepts — the tables and pages exist.
- Stage 13: producing the problems through this importer.

Owner's gate for stage 8 (`SPEC.md` §18: a sample file imports; a deliberately wrong problem is rejected with a line number; `cout << x << " " << y` with doubles is accepted):

1. In a browser at `https://lokakode.lokandita.com` as superadmin: *Mata kuliah* → `DDP101` → **Pertemuan dan bank soal**. Add meeting **1** (any title, C++); on its page tick `IO_DASAR`, `VARIABEL_TIPE`, `ARITMETIKA` and save. Go back, add meeting **2** (C++); on its page the three concepts are ticked and locked; tick `KONVERSI_TIPE` and save. Do **not** tick `STRING` or `PERCABANGAN`: the gate depends on them not being allowed yet.
2. Menu **Impor soal** → choose `docs/contoh-soal/ddp101-pertemuan-2.json` (from the repository; `git pull` on your computer, or download it from VS Code) → *Unggah*. The page reloads itself for about half a minute. Expect: **Selesai: 3 diterima, 0 ditolak**.
   Or on the server: `docker exec -i lokakode php spark soal:impor --oleh <nomor induk Anda> < docs/contoh-soal/ddp101-pertemuan-2.json`
3. Upload `docs/contoh-soal/ddp101-pertemuan-2-salah.json`. Expect: **0 diterima, 1 ditolak, 1 tidak berubah**, and for `sisi-terpanjang`: *solusi referensi, baris 7, kolom 5, baris JSON 51: Solusi referensi memakai konsep PERCABANGAN, yang belum diizinkan di pertemuan ini: «if».*
4. Open meeting 2 → *Bank soal* → **Tukar Dua Bilangan Pecahan**. The reference solution contains `cout << y << " " << x << endl;` with `double x, y`, and "Konsep di solusi referensi" lists input/output and variables only — no string concept. Press **Setujui soal ini**.
5. Optional: log in as the first dosen (the pengampu) — the same pages work, *Impor soal* answers "tidak berhak"; as the second dosen `/pertemuan/<id>` answers "tidak berhak"; try *Ubah soal* on one problem; as superadmin `/audit` lists `soal.impor` and `soal.disetujui`.
6. Run `infra/smoke.sh` — expect `22 lulus, 0 gagal, 0 belum aktif`.
7. Read `docs/DETEKSI-KONSEP.md` when convenient and say which rules should change.

The meetings and problems created in the gate are real production rows. Meetings cannot be deleted, only switched off; DDP101 needs meetings 1 and 2 anyway.

After the gate: tag `tahap-8`, record the result here.

Gate result, 2026-11-17:

- The owner reported, in his words: step 2 (valid file) "safe"; step 3 (wrong file) "rejection for sisi-terpanjang"; step 4 (`cout` with doubles, no string concept, approved) "yes"; `infra/smoke.sh` → `smoke: 22 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`; pages in the browser and the Arabic layout "fine". He did not quote the counts of steps 2 and 3 or the line numbers; those below are what the server shows.
- What the server shows, read by the agent (actions, roles, counts and statuses only):
  - `DDP101` has meeting 1 (C++; `ARITMETIKA`, `IO_DASAR`, `VARIABEL_TIPE`) and meeting 2 (C++; `KONVERSI_TIPE`). `STRING` and `PERCABANGAN` are not allowed at either, as the gate requires.
  - Two imports by web upload on 2026-11-17 at 08:31 and 08:32 WIB, both finished: `ddp101-pertemuan-2.json` → 3 accepted, 0 rejected; `ddp101-pertemuan-2-salah.json` → 0 accepted, 1 rejected, 1 unchanged. Nothing is left waiting.
  - The rejected problem is `sisi-terpanjang`: `concept_not_allowed`, `PERCABANGAN`, construct `if`, source line 7, column 5, JSON line 51 — exactly the expected reason.
  - `tukar-dua-pecahan` version 1 is `APPROVED` and is the version meeting 2 uses; its detected concepts are `VARIABEL_TIPE` and `IO_DASAR` only. The other two problems are still `PENDING_REVIEW`.
  - LokaKode `audit_log`, all by the superadmin: `pertemuan.dibuat` 2, `pertemuan.diubah` 2, `soal.impor` 2, `soal.disetujui` 1.
  - **The three parts of the gate of `SPEC.md` §18 pass on the deployed system.**
- Not done in the gate, or not reported: the optional step 5 (the pages as pengampu, the 403 for a pengajar, *Ubah soal*, `/audit`) — the audit log has no `soal.disunting` and the container log no `access.denied`, so none of it was tried in production; the CLI import; step 7, the review of `docs/DETEKSI-KONSEP.md`. "Fine" for the pages is the owner's judgement; which pages he opened is not known to the agent. The items under "Not verified, or weaker than it looks" above still hold, except that stage 8 pages have now been used in a browser and one real import, one real rejection and one real approval have happened in production.
- The tagged commit differs from the deployed `5ffce85` only in this file. **It has not been redeployed**, so `infra/smoke.sh` check 0 and the worker heartbeat line fail until `infra/deploy.sh` is run for the four components; nothing that runs has changed.

**Stage 8 finished 2026-11-17, tag `tahap-8`.**

Carried into the next stages:

- Redeploy the four components at the tagged commit when convenient, or let the first deploy of stage 9 do it.
- Meetings 1 and 2 of `DDP101` and their three problems are real production rows; two problems still await review. Meetings cannot be deleted, only switched off.
- `docs/DETEKSI-KONSEP.md` still awaits the owner's review.
- The "Owed by later stages" list of stage 8, and the items stages 6 and 7 left for stage 9.
- `auth.lokandita.com` and `lokakode.lokandita.com` are open; the Portal is in maintenance.

## Stage 9a — 2026-11-17

Plan approved by the owner 2026-11-17 (8 steps, one commit each): 1 contract and schema · 2 worker: `SUBMIT`, `RUN` on samples, compiler flags · 3 meeting settings, publishing, class windows, pre-generation · 4 scoring · 5 student meeting page · 6 problem page with Monaco · 7 paste and copy blocking, watermark · 8 close.

Owner's decisions for this stage: see `DECISIONS.md`, 2026-11-17.

Done (anything not listed here is not done):

- Step 1 — contract and schema: `KONTRAK.md` §6 (payloads and results of `SUBMIT` and `RUN` on samples, flag whitelist, the new tables); migration `BuatLatihanDanNilai` (`meetings` gains target, composition, starter template, flags, publishing; `meeting_class_windows`, `submissions`, `problem_scores`, `meeting_scores`, `drafts`, `integrity_events`, `ui_prefs`). `infra/tes.sh lokakode --filter Skema` → 29 tests, 93 assertions pass. Exists only in `lokakode_test`; not deployed. The worker does not do what the contract now says until step 2.
- Step 2 — worker: `SUBMIT` (hidden tests only, per-test verdicts, concept analysis when everything passed and concepts are required → `CONCEPT_NOT_MET`), `RUN` with `instance_id` (sample tests only, program output returned), compiler flags from the whitelist of `KONTRAK.md` §6. The worker refuses an instance that does not belong to the job's creator. `infra/tes.sh judge tests/test_worker_kiriman.py tests/test_kontrak_db.py` → 47 passed in 2 min 37 s, real containers; hidden input and output were searched for in results, progress and the worker log and are not there. The full judge suite is run at the close. **The production worker still runs the stage 8 code until the deploy of step 8.**
- Step 3 — meeting settings, publishing, class windows, pre-generation:
  - `/pertemuan/{id}`: practice settings (target, minimum composition, starter template, compiler flags from the whitelist, autocomplete) and publishing. Publishing is refused with the exact shortfall per level and in points (2 × target), warns below 3 × target; a published meeting cannot be changed into one that breaks the rule; withdrawing keeps all data.
  - `/kelas/{id}/pertemuan` and `/kelas/{id}/pertemuan/{m}`: schedule in WIB, manual open / close / follow schedule, what students see after closing, and the pre-generation panel with progress (the page reloads itself by the `Refresh` header while instances are being made). Allowed to whoever sees the class: superadmin, pengampu, the pengajar of that class.
  - Pre-generation queues one `GENERATE` per active student per problem on the low queue when a window is saved or opened for a published meeting, when a meeting with windows is published, and on the button; a failed job is re-queued in place.
  - `infra/tes.sh lokakode --filter 'Penerbitan|Jendela|Matriks'` → 20 tests, 803 assertions pass, with the real worker. Covered: the cross-class case (pengajar A on class B gets the same 404 as a class that does not exist, on all five window routes, and nothing is written), a meeting of another course, an archived year, instances only for `AKTIF` students of that class, different tests per student, a failed generator reported and retried.
  - **Measured: 4 `GENERATE` jobs (12 tests each) finished in 5.0 s** on the test worker, i.e. roughly 4–5 s per job with 4 running at once. A class of 40 students × 30 problems (1,200 jobs) would then take roughly 20–25 minutes. That is an extrapolation from four jobs, not a measurement of a full class.
  - Not deployed; no page seen in a browser.
- Step 4 — scoring (`App\Libraries\Penilaian`, the only place a score is computed): submission score = points × passed / total to 2 decimals, compile error 0, `CONCEPT_NOT_MET` × 0.5; problem score = best of all judged submissions with the attempt count; meeting score with the 4 best very-easy problems re-selected on every submission, cap 100, surplus; `reached_at` found by replaying the submissions in judging order. A judge failure is not an attempt. Changing a meeting's target recomputes every student. `infra/tes.sh lokakode --filter 'Penilaian|Penerbitan'` → 12 tests, 114 assertions pass against real MySQL, including emptying and rebuilding the derived tables with an identical result. **In these tests the judge results were inserted into `judge_jobs` by hand in the contract's shape; the path from a real Submit to a score is tested in step 6.**
- Steps 5 and 6 (one commit; the pages, their endpoints and their tests were built together) — student side:
  - `/beranda` lists the student's meetings; `/latihan/{meeting}` is the meeting page: cards with number, title, level badge (five colours), points, status and points earned; header with points toward target, "Mudah sekali: n/4 terhitung", points still needed, time left; filter and sort.
  - `/latihan/soal/{id}` is the three-panel problem page: statement with language toggle and fallback badge, samples, Monaco, tabs *Output*, *Test Cases*, *Input Kustom*, *Riwayat*; resizable panels saved per user; stacked tabs below 1024 px; starter template and Reset; draft autosave every 5 s; Run (samples or custom input) and Submit with 1 s polling; shortcuts. If the instance is not there yet the student gets the "Menyiapkan soal Anda" screen with an estimate from the queue and measured `GENERATE` times.
  - Paste blocking, copy blocking, print blocking and the watermark are written (the page script) **but are exercised by no test yet**; that is step 7.
  - `infra/tes.sh lokakode --filter 'Ruang|Kirim|Matriks|Eksekusi|LingkupKelas'` → all pass with the real worker (35 tests / 1,447 assertions in the first run with three test mistakes, then the corrected 21). Covered: the four modes; a withdrawn or unpublished meeting; an archived year; a graduated student reads but cannot submit; **student B cannot open student A's problem, draft, submission or judge job (404 equal to a missing one) and nothing changes**; the page of one problem contains its two samples and none of the ten hidden inputs or outputs, no generator, no reference solution, no other problem; a real Submit in C++ and in Python from the HTTP request to the score in MySQL and on the page; partial score, compile error, `CONCEPT_NOT_MET` × 0.5; rate limits; a judge failure is not an attempt; a result is not lost when nobody polls. **Every response body received in the Run and Submit tests was searched for the hidden test inputs and outputs: none.**
  - **Found: a stage 6 test proved nothing.** "A forbidden class and a missing class get the same answer" compared a response with itself (the test HTTP helper returns the same object). Corrected in this step; the property holds (the two pages differ only in their CSRF tokens and in the path the user typed). The sentence "byte-for-byte the same" in the stage 6 entry above was therefore not backed by a test when it was written.
- Step 7 — paste and copy blocking, watermark, and everything else that only a browser can show. `infra/tes.sh lokakode` now starts a headless Chromium that opens the test LokaKode over HTTPS like a student's browser. `infra/tes.sh lokakode --filter HalamanSoal` → 12 tests, 195 assertions pass in 1 min 40 s (10 of them in the browser):
  - Monaco loads from our own host with **zero CSP violations and zero script errors**; C++ highlighting works; every resource of the page comes from the app's own origin.
  - Draft autosave reached the server 2.5–3.9 s after the last keystroke without any action; the draft is restored in a new browser; Reset asks, restores the template, and that is saved too.
  - **Paste is blocked on every path** — Ctrl+V, Shift+Insert, the paste event, drag and drop, middle click, the context menu, `beforeinput` of paste type, and Monaco's own paste command — the editor content stayed exactly the template, the message appeared, and the five recorded paths are in `integrity_events`. A `paste_allowed` user pressing the same keys does get the clipboard text, so the blocked result is not an artefact of the harness.
  - The statement cannot be selected; Ctrl+C / X / A outside the editor and Ctrl+P are prevented; the clipboard did not receive the statement; the context menu is suppressed; in print media the page content is hidden; the watermark carries nomor induk, name and time, covers the statement and does not take clicks. Copying one's own code from the editor still works.
  - Run and Submit from the buttons and from Ctrl+Enter / Ctrl+Shift+Enter; the score, the best score and the meeting progress update without a reload; the history shows the submission; **the page's whole DOM was searched for the ten hidden inputs and outputs: none**.
  - A session that ended is reported with a clear message; panels resize by keyboard and mouse, the size is saved per user and restored; below 1024 px the three panels become tabs; the "Menyiapkan soal Anda" screen turns into the problem page by itself.
  - **Found by the browser, not by any HTTP test:** Monaco's worker was refused by our CSP (it came from a `blob:` URL). Fixed with a same-origin worker script, not by allowing `blob:`. Measured as well: the one relaxation the problem page has (`style-src 'unsafe-inline'`) is needed; without it Monaco raises dozens of violations.
  - Weaker than it looks: the paste event and the drop were dispatched by script (the browser's own paste path was exercised through Ctrl+V and Shift+Insert); the tests run in Chromium only, not in Firefox or Safari, and not in Safe Exam Browser (stage 10); the copy blocking is a deterrent — a student with developer tools can read the page source, which SEB will prevent, not this code.
- Measured (as promised in the plan), on the test schema with the test worker at `JUDGE_CONCURRENCY=4`, the production value: **pre-generation of one full class, 40 students × 30 problems = 1,200 instances of 12 tests, made 1,199 instances in 28 min 53 s** — about 1.45 s per instance of wall time, 0.69 instances per second. The production worker was idle at the time; with students submitting, pre-generation waits behind them (low queue) and takes longer. A window should therefore be saved or opened at least half an hour before a class of that size starts; students who arrive earlier get their problem on demand on the normal queue.
- **Found by that measurement: job 1,200 failed for good** with `generation_failed` / `reference_compile_error`, although the same reference solution compiled 1,199 times. The job took 19 s, so the compile most probably hit the 10 s watchdog while four jobs loaded all four CPUs (the worker does not record the reason; this is an inference). The pengajar page reported it ("1 soal gagal dibuat") and *Siapkan sekarang* would have re-queued it, but nobody should have to. Fixed: a compile timeout or out-of-memory of an approved reference solution is now retried by the worker like any infrastructure failure. **The measurement was not repeated after the fix**; the fix is covered by a test with a simulated timeout, not by a second 1,200-job run.
- The same cause can reach a student under heavy load: a correct program whose compile passes 10 s gets `CE` with reason `timeout` and score 0 for that submission. The best score is unaffected and the student can submit again, but the attempt is counted. Not changed in this stage (the 10 s watchdog is a stage 7 acceptance item); raise it if it shows up in class.
- Step 8 — close, 2026-11-17 (commands and output shown in the session), at commit `448bdf8`:
  - `infra/tes.sh lokakode` → **189 tests, 2,847 assertions, all pass** (15 min 34 s), with the real worker and the real browser. `infra/tes.sh auth` → 95 tests, 822 assertions; `infra/tes.sh portal` → 19 tests, 105 assertions (the shared layout, CSP filter and stylesheet changed). All pass.
  - `infra/tes.sh judge` — **no fully green full run was obtained.** Three full runs: (1) 251 passed, 1 failed, 1 error; (2) 252 passed, 1 error; (3) 250 passed, 2 failed. Every red was about time or a race, in sandbox code this stage did not write, and in no case did a hostile program escape a limit:
    - `while(1)` was killed as `TLE` within its CPU limit, but the whole test took 8.1 s against the test's own limit of 8 s (run 1); a program that leaves a child behind finished correctly in 4.006 s against a limit of 4 s (run 3). **These two limits were left as they are and the tests are therefore flaky on a loaded server.**
    - After the stderr-flood test a judge container was still being removed when the "nothing left" check ran (runs 1 and 2). Cause: two overlapping `docker rm -f`; the second returned at once. Fixed: removal now returns only when the container is gone. Did not recur in run 3.
    - The reaper test lost a race with the **production** worker's reaper, which removes any expired judge container on this host by label (run 3). The race exists since stage 7. Fixed in the test (its "expired" container expires in the future and the test's reaper is run with a later clock).
    - After the fixes: `infra/tes.sh judge tests/test_kotak_jahat.py tests/test_worker.py` → 67 passed; `judge/uji-terima.sh` → `uji terima: LULUS — 10 dari 10 butir`, 0 judge containers left. **A fourth full run was not made.**
  - **The sandbox was slower this afternoon and I do not know why.** 200 simultaneous `RUN` jobs took 171 s (98–133 s in stages 7 and 8); one sandbox run measured 0.8–1.3 s at one moment and 0.6 s an hour later with the server idle. An A/B against the stage 8 code showed the same speed (0.60–0.65 s against 0.59 s), so this stage's change to container removal is not the cause.
  - `infra/cek-env.sh` → passed; no new environment variable in this stage.
  - `infra/deploy.sh lokakode`, `judge`, `auth`, `portal` → each verified (`/healthz`, worker heartbeat). The migration ran on the real `lokakode` schema: 26 tables now; meetings 1 and 2 of `DDP101` got the defaults (target 60, composition 8 / 8 / 6 / 6 / 4) and are **not published**; 0 windows, 0 instances, 0 submissions. `judge_worker` still has exactly the three grants of `KONTRAK.md` §6.
  - `infra/smoke.sh` → **22 passed, 0 failed, 0 not active**.
  - Real requests to the deployed LokaKode, without a session: `GET /latihan/1`, `/latihan/soal/1`, `/api/soal/1/keadaan`, `/api/soal/1/riwayat`, `/kelas/1/pertemuan`, `/kelas/1/pertemuan/1` → 302 to the LokaAuth login with the right `next`; `POST` to the five student endpoints and to publish / practice settings / window status / pre-generate without CSRF → 403. Monaco (`/monaco/vs/…`, 3.8 MB main script, its licence file) and the page scripts are served by the app itself; the Monaco directory is empty in the auth and portal images.
  - `infra/backup.sh` → done; `infra/uji-restore.sh` → passed, 37 schemas/tables restored and equal, the seven new tables among them; `infra/tes-infra.sh` → 57 passed, 0 failed.

Not verified, or weaker than it looks:

- **No student has run or submitted anything on the deployed system.** Production has no published meeting and no window, and I did not create accounts, publish or open anything there. Run, Submit, scoring and the pages are proven on the test containers (real MySQL, Redis, worker, sandbox, browser) and by smoke checks 6 and 7 in production — not by a real student. The owner's check below is the real one.
- **No page of 9a has been seen by a person.** The browser tests prove behaviour, not looks: layout, colours, the five level badges, the watermark's legibility, dark mode and the narrow layout need eyes. Arabic was not opened at all (RTL polish is 9b); its texts were written by the agent and not reviewed by an Arabic reader.
- Browser tests run in headless Chromium only. Firefox, Safari and Safe Exam Browser (stage 10) were not tried.
- Paste and copy blocking are deterrents until SEB: developer tools can read the statement and can type into the editor programmatically.
- The full judge suite is not green in one run (above), and the 1,200-instance measurement was not repeated after the retry fix.
- Under heavy load a student's correct program can get `CE` / `timeout` at compile (above).
- Pre-generation of a full class takes about half an hour on an idle server; if a window is opened minutes before class, the first students wait for their problem on demand (the screen says so, with an estimate).
- The estimate on the "Menyiapkan soal Anda" screen uses whole-second job times (the database stores seconds), so it is rough for short queues.
- If Redis is down the Run / Submit rate limits let requests through (logged); the MySQL guards (one submission being judged per problem, two unfinished Runs per student) still hold.
- Rate-limit, panel-size and integrity endpoints were tested with one student at a time, not under concurrency.
- `SPEC.md` §6 lists "leaderboard anonymisation" and "practice-after-close" among the meeting settings: their columns exist, their form fields do not (9b). `late_policy` likewise.
- Each `infra/smoke.sh` run still leaves two system rows in `judge_jobs`; every Run leaves one row. Retention is not decided (9b).

Owed by 9b (next session): leaderboard; analytics and the staff view of submissions and source; exports; late policy `multiplier` and practice-after-close; anonymisation; calibration (`SPEC.md` §8.4); recompute of per-class aggregates after pindah kelas; retention of `judge_jobs` and `imports`; the three languages and RTL polish; whether a pengajar may see the problem bank; Monaco in the problem edit form; memory limit per problem if wanted. Then the gate of `SPEC.md` §18 for stage 9.

Owner's check for 9a (not the §18 gate; that comes after 9b). Everything is at `https://lokakode.lokandita.com`:

1. As superadmin or the pengampu: *Mata kuliah* → `DDP101` → **Pertemuan dan bank soal** → meeting 2. Under *Pengaturan latihan* set **Target poin** so that 2 × target does not exceed the points available (the page shows them under *Penerbitan*), and lower the five minimums to what the meeting has; save; press **Terbitkan pertemuan**. Try once with the defaults first: it must refuse and say exactly what is missing.
2. Open the class that holds the test mahasiswa → **Jadwal pertemuan** → meeting 2 → **Buka sekarang**. The section *Soal mahasiswa* should count up to "n dari n" within a few seconds.
3. In a private window log in as the test mahasiswa. The beranda lists the meeting; the meeting page shows the cards, "Mudah sekali: 0/4 terhitung", points needed.
4. Open a problem. Check by eye: three panels, the statement with the watermark (your test student's nomor induk, name, time), Monaco with the C++ template. Try to select or copy the statement; try Ctrl+V, Shift+Insert, right-click and dragging text into the editor — each paste must be refused with a short message. Print preview (Ctrl+P is blocked; use the browser menu) must show no statement.
5. Type a solution. Wait ten seconds, close the tab, open the problem again: the code is back. **Reset** restores the template.
6. **Jalankan** (or Ctrl+Enter): the two samples with your program's output. **Input Kustom**: your own input. **Kirim** (or Ctrl+Shift+Enter): verdict, score, and ten hidden tests shown as status only. Submit a wrong and a non-compiling program too; the best score must not drop. *Riwayat* lists them.
7. Back on the meeting page: status, points earned, progress to target.
8. As the second dosen (pengajar of the other class): `/kelas/<id of the first class>/pertemuan` must say *Halaman tidak ditemukan*.
9. As superadmin `/audit`: `pertemuan.latihanDiubah`, `pertemuan.diterbitkan`, `jendela.dibuka`.
10. `infra/smoke.sh` — expect `22 lulus, 0 gagal, 0 belum aktif`.
11. Say how the pages look, and anything that reads badly in Indonesian.

The meeting published and the submissions made in this check are real production rows, like those of the earlier gates. After the check: tag `tahap-9a`, record the result here.

### Result of the owner's check for 9a — 2026-11-17

- **The owner ran the check himself and reported:** "everything looks good including arabic how test student open and so on." Tag `tahap-9a`. The agent did not watch the check; the eleven items were not reported one by one, so this entry does not claim each of them individually.
- What the server shows at the time of tagging (read-only queries and commands in the session):
  - `lokakode`, `auth` and `portal` all answer `/healthz` with commit `2ea0b23`, the commit the tag's parent points at; the only change after it is this file.
  - Audit: `pertemuan.diterbitkan` × 2 (meetings 1 and 2 of `DDP101`, 09:06 and 09:07 UTC), `jendela.dibuka` × 2 (09:34 UTC). No `pertemuan.latihanDiubah`: the practice settings were left at their defaults, which the 32 approved problems of each meeting satisfy, so item 1's "must refuse with the defaults" could not occur and was **not exercised in production** (it is covered by tests only).
  - 2 class windows, 64 instances, **1 submission by 1 student**, 1 meeting score, 1 integrity event.
  - `infra/smoke.sh` → `22 lulus, 0 gagal, 0 belum aktif` (run by the agent while a test suite was running; the `VALIDATE` check took 28 s instead of the usual few seconds).
- Not covered by this check, still open: everything under "Not verified, or weaker than it looks" above except the two items the owner's eyes now cover (pages seen by a person; Arabic opened). The gate of `SPEC.md` §18 for stage 9 comes after 9b.
- Outside 9a, same day: the base concepts of `DDP101` were ticked by mistake (seven concepts, `PERCABANGAN` among them) and corrected in the data with the owner's approval (audit rows 134 and 135, `lewat: sql`). The page change that makes the origin of a locked concept visible is **written but not committed or deployed at this tag**; it gets its own commit and entry.

### Problems for DDP101 meeting 2 — 2026-11-17 (owner's request during the 9a session; this is stage 13 work done early for one meeting)

- 29 problems written by the agent: 6 very easy, 7 easy, 6 easy-medium, 6 medium, 4 hard, using only the concepts allowed at meeting 2 (no `if`, no loops, no strings, no output formatting). Source: `docs/soal/buat-ddp101-pertemuan-2.py` → `docs/soal/ddp101-pertemuan-2.json`.
- Checked before import (output in the session): `soal.validasi` in judge containers → 29 of 29 accepted, 240 tests each, no warning, only allowed concepts; reference output against an independent Python calculation → 29 of 29 equal on 36 generated tests each.
- Imported into production by `spark soal:impor` under the owner's superadmin account (owner's decision) → `29 diterima, 0 ditolak, 0 tidak berubah`.
- Approved by the new `spark soal:setujui DDP101 2` under the same account (owner's decision) → `29 disetujui, 3 dilewati` (the three already approved). `infra/tes.sh lokakode --filter 'SetujuiCli|BankSoal|Matriks'` → 19 tests, 1,039 assertions pass.
- What the server shows now for meeting 2: approved 8 / 8 / 6 / 6 / 4 against the minimum 8 / 8 / 6 / 6 / 4; 232 points available against 120 needed (2 × target 60), above 3 × target; 0 problems awaiting review; **not published** — publishing is the owner's step. Audit: `soal.impor` 1, `soal.disetujui` 29 with `lewat: cli`.
- Weaker than it looks:
  - **Nobody has read these 29 statements except the agent that wrote them.** The owner chose approval without review. Wording, difficulty labels and whether each problem suits week 2 are the agent's judgement; the "hard" ones are hard only relative to having no branching.
  - Difficulty is a guess until students attempt them; calibration (`SPEC.md` §8.4) arrives in 9b.
  - Statements are Indonesian only; English and Arabic are empty.
  - Student code is never restricted, so a student who already knows `if` can solve the "without branching" problems with it. No problem requires a concept in the submission.
  - Five problems expect a fractional number printed with `cout`'s default precision and are compared with tolerance 1e-6; inputs were chosen so answers have at most six significant digits. A student who prints with another precision is still accepted within that tolerance.
  - The full `infra/tes.sh lokakode` suite was not re-run after adding `soal:setujui`; only the three test classes above.

### Problems for DDP101 meetings 1 and 3, and English and Arabic for meetings 1–3 — 2026-11-17 (owner's request, same session)

- Written by the agent: 32 problems for meeting 1 (only input/output, variables, arithmetic; no casts) and 32 for meeting 3 (output formatting and strings; exact output, mode `trimmed`), each 8 / 8 / 6 / 6 / 4. English and Arabic texts for all 96 problems of meetings 1–3, including the three gate problems of meeting 2. Sources: `docs/soal/bangun.py`, `buat-ddp101-pertemuan-{1,2,3}.py`, `terjemahan_p2.py` → `docs/soal/ddp101-pertemuan-{1,2,3}.json`. `docs/contoh-soal/` (the stage 8 gate files) is unchanged.
- Checked before import (output in the session): `soal.validasi` in judge containers with each meeting's own allowed concepts → meeting 1: 32 of 32, meeting 3: 32 of 32 (240 tests each, no warning); reference output against an independent Python calculation → 32 of 32 for each (36 generated tests per problem); the worked examples quoted in the statements → 13 of 13 (meeting 1) and 33 of 33 (meeting 3) correct. The meeting 2 solutions and generators did not change; only texts were added.
- Production, under the owner's superadmin account (owner's decision): `soal:impor` meeting 1 → `32 diterima, 0 ditolak`; meeting 2 → `32 diterima, 0 ditolak` (version 2 of every problem, because the texts changed); `soal:setujui` → 32 approved for each. What the server shows: meetings 1 and 2 each have 8 / 8 / 6 / 6 / 4 approved, every approved version has `en` and `ar` texts marked as machine translation, nothing awaits review, no instance existed (so no student is on an old version), **neither meeting is published**.
- **Meeting 3 is not in production.** The meeting itself does not exist there yet; the owner chose to create it in the browser (number 3, C++, concepts `FORMAT_OUTPUT` and `STRING`). Its file is ready and validated; importing and approving it is the remaining step.
- Weaker than it looks:
  - **Nobody but the agent has read any of these 96 statements, in any language.** The English and Arabic texts were written by the agent, not by a translator, and carry the "machine translation" badge until the pengampu clears it. The Arabic in particular has not been seen by an Arabic reader; mixed right-to-left text with code and Latin letters may display awkwardly, and RTL polish is 9b.
  - The fixed words a program must print in meeting 3 (`Halo, `, `Selamat datang, `, `Total`, `Nama`, `Umur`, `Tinggi`, `IPK: `, `Subtotal`, `Pajak`, `tahun`) are Indonesian in all three language versions, because the expected output is one and the same.
  - Meeting 3 problems demand exact output (leading spaces included). A student whose answer differs by one space gets "wrong output" with no hint which character; the *Test Cases* tab shows expected and actual output side by side for the samples.
  - Decimals in meeting 3 were chosen so rounding never falls on an exact half; the judge still compares text, so a student who rounds by hand instead of using `setprecision` could differ in rare cases.
  - Difficulty labels are the agent's guess; calibration arrives in 9b. Student code is never restricted, so "without branching" is a hint, not a rule.
  - The titles of meetings 1 and 2 in production are the concept names typed during the stage 8 gate ("IO_DASAR, VARIABEL_TIPE, ARITMETIKA" and "KONVERSI_TIPE"); students will see them as the meeting titles. Left as they are.

### Base concepts of DDP101 ticked by mistake; locked concepts now name their origin — 2026-11-17 (owner's request, after tag `tahap-9a`)

- Reported by the owner: `PERCABANGAN` showed ticked, locked and "diwarisi" on meeting 1 and could not be released.
- Cause, from read-only queries: it was never a concept of any meeting. Audit row 122 (`matkul.konsepDasar`, 09:12:22 UTC) saved seven concepts as **base concepts of the course** (the form directly below "add meeting" on the meeting list): the six of meetings 1–3 plus `PERCABANGAN`. Base concepts are inherited by every meeting, meeting 1 included, and the badge said "diwarisi" for both kinds without naming the origin. Not a storage bug; the form on the list page could release them.
- Data, with the owner's explicit approval, by SQL in one transaction: the 7 rows of `course_base_concepts` for `DDP101` deleted; `PERCABANGAN` added to meeting 4. Audit rows 134 and 135 under the owner's account, marked `lewat: sql`. State now: base concepts 0; meeting 1 `IO_DASAR, VARIABEL_TIPE, ARITMETIKA`; 2 `KONVERSI_TIPE`; 3 `FORMAT_OUTPUT, STRING`; 4 `PERCABANGAN`. **Blemish:** in audit row 134 the `sebelum` value is a JSON string holding the list, not a JSON array; the content is right and audit rows are never edited.
- No problem was affected: allowed concepts are only used at import, and all 64 problems of meetings 1 and 2 were imported before 09:12 UTC.
- Code (commit `548e63c`, approved by the owner): the badge reads "konsep dasar" or "dari pertemuan N" (`id`, `en`, `ar`); the meeting page links to the base-concept form when a base concept is locked there; that form is folded while empty and warns that it is not a meeting's concepts. Two new HTTP tests: a wrongly ticked base concept and a wrongly ticked meeting concept can each be released, and the later meeting is released with it.
- Tests: `infra/tes.sh lokakode --filter PertemuanTest` → 9 tests, 58 assertions pass. Full `infra/tes.sh lokakode`: **first run 195 tests, 1 failure, and I do not know which test** — my own output filter discarded the detail; `infra/smoke.sh` was running on the same server at that moment. Second run, nothing else running → `OK (195 tests, 2878 assertions)`. The first failure is therefore unexplained; a load-sensitive test is the likely cause, not a proven one.
- Deploy: `infra/cek-env.sh` passed; `infra/deploy.sh lokakode` → `/healthz` reports `548e63c`. `infra/smoke.sh` right after: **19 passed, 3 failed** — the three are `auth`, `portal` and the judge worker still reporting `2ea0b23`, because only `lokakode` had been deployed; every feature check passed. All four components are redeployed at the commit that holds this entry; that smoke run is shown in the session.
- **Not verified:** the new badges, the link and the folded form were not seen in production — the agent has no session there (without one `/pertemuan/1` and `/mata-kuliah/1/pertemuan` answer 302 to the login). The deployed container holds the new view and language files. Owner to check: meeting 1 shows `PERCABANGAN` unticked and unlocked; meeting 2 shows the three concepts of meeting 1 as "dari pertemuan 1"; on the meeting list "Konsep dasar mata kuliah (0)" is folded. The Arabic texts of the new strings were written by the agent.
- Left as it is: meetings 8 and 9 of `DDP101` have no concepts yet (meeting 9 was created at 09:20 UTC and not edited).

### Problems for DDP101 meeting 3 imported and approved — 2026-11-17 (owner's request)

- The owner asked why the meeting 3 problems had not appeared: the file was ready since the earlier session but never imported, because meeting 3 did not exist in production until 09:11 UTC.
- Imported after the base concepts were cleared (before that, an import would have allowed `PERCABANGAN` at meeting 3): `spark soal:impor` under the owner's superadmin account → `32 diterima, 0 ditolak, 0 tidak berubah`. Approved without the owner reading them (his decision, "langsung"): `spark soal:setujui DDP101 3` → `32 disetujui, 0 dilewati`. Audit: `soal.impor` 1, `soal.disetujui` 32 with `lewat: cli`.
- What the server shows: meeting 3 has 32 problems, 32 approved, 8 / 8 / 6 / 6 / 4 by level, none whose detected concepts include `PERCABANGAN`, target 60, **not published** — publishing and opening a class window are the owner's steps.
- Weaker than it looks: the same as for meetings 1 and 2 above — nobody but the agent has read these statements in any language; meeting 3 demands exact output.

## Stage 9b — 2026-11-19

Plan approved by the owner 2026-11-19 ("approve all", including the six decisions; see `DECISIONS.md`): 0 baseline · 1 flaky judge tests · 2 compile timeout on a busy server · 3 schema and contract · 4 late policy and practice after close · 5 "why can't I see this meeting" · 6 leaderboard · 7 staff analytics · 8 exports · 9 three languages and RTL · 10 close. Steps 3–8 share one commit (`f878193`): the pieces share routes, language files and the scoring library too closely to split honestly.

Done (anything not listed here is not done):

- Step 0 — baseline before any change, at `689e2a9`: `infra/tes.sh lokakode` → `OK (195 tests, 2923 assertions)`. The owner's message said this suite had not been run since the approval command was added; this file shows it had been (twice at `548e63c`, the first run with one failure that was never identified). This run had no failure, so that earlier failure stays unexplained.
- Step 1 — the two load-sensitive isolation tests (`while(1)` and the orphaned child). Their limits (8 s, 4 s) are unchanged but now apply to the run alone instead of compile + run, and both also assert what `pelari` measured inside the container. Repeated five times with four CPU burners running (load average above 6): 5 of 5 passed, the whole `while(1)` test taking 5.6–6.1 s.
- Step 2 — a compile that reaches the 10 s limit while its container used less than 8 s of CPU is an infrastructure failure (retried twice, then `judge_unavailable`, which is not an attempt), not a `CE`; for `RUN`, `SUBMIT` and the reference solution in `VALIDATE`. `pelari` now reports the container's CPU from cgroup. Measured: 10,023–10,033 ms of CPU with a whole CPU; 9,044 / 7,082 / 5,103 / 3,046 ms at 0.9 / 0.7 / 0.5 / 0.3 CPU. Tested with a real compile throttled to 0.3 CPU in a real container.
- **Full judge suite, run once, at `39e0717`: `256 passed in 1047.06s (0:17:27)`**, nothing skipped, no judge container left. This is the first fully green full run since stage 8.
- Steps 3–4 — `late_until` on a class window, `late_factor` and `practice` on a submission (additive migration). Late: after the scheduled close, policy `multiplier`, until `late_until` or a manual close; the factor is fixed on the submission when it is created. Practice after close: judged and shown, never counted. Forms for the pengajar (late policy) and the pengampu (practice, anonymised leaderboard). Real submissions through the real worker in `TelatTest`.
- Step 5 — a student's home page and meeting page name the reason a meeting of their own course cannot be worked on: not published, not opened for their class, or scheduled. Problems of such a meeting, meetings of other courses and meetings not used for practice stay 404.
- Step 6 — leaderboard per class (whoever sees the class) and per course in the active year (superadmin and pengampu; a pengajar gets 403); the student board shows their own class only, names without nomor induk, "Peserta n" when anonymised. Cached in Redis, dropped when a submission is scored or an enrollment changes. This closes "recompute per-class aggregates after pindah kelas" owed since stage 6: nothing per class is stored except that cache.
- Step 7 — for staff: grade distribution with mean, median and standard deviation, a Chart.js histogram drawn from the table on the page, statistics and calibration hint per problem, students at risk, every submission of the class with source and per-test status, class comparison for the pengampu.
- Step 8 — export of meeting scores as CSV and XLSX per class and per course, audited as `nilai.diekspor`.
- Step 9 — a test that fails when a text is missing in `id`, `en` or `ar`, has different placeholders, is left untranslated in Arabic, or is called by code without existing; the views were swept for text outside the language files (none found beyond technical labels such as "IP" and "ms"); "back" arrows follow the page direction; browser tests open the student pages and the analytics page in Arabic and check direction, mirrored layout, Latin digits, no horizontal overflow, zero CSP violations.
- Every new route (3 leaderboard, 7 analytics and export) is in the authorisation matrix with the cross-class case; the leaderboard, analytics, submission and export tests each assert that the pengajar of another class gets the same 404 as for a class that does not exist, with no name in the body.

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh lokakode` at `577156f` → **`OK (219 tests, 9149 assertions)`**, with the real worker and the real browser. One commit followed (`fd73737`: wording on the pengajar's window page, two texts, one test); after it only `--filter 'Telat|JendelaTest|KunciBahasa|Papan'` was run → `OK (22 tests, 5894 assertions)`. **The full suite was not repeated at `fd73737`.**
- `infra/tes.sh auth` → `OK (95 tests, 822 assertions)`; `infra/tes.sh portal` → `OK (19 tests, 105 assertions)`, both at `fd73737`.
- `infra/cek-env.sh` → passed; no new environment variable. Commits pushed to `origin/main`.

Deployed 2026-11-19, after the owner's go-ahead ("do all you recommend"; the first attempt had been refused by the agent's permission system and was not worked around). At commit `57dee87` (output shown in the session):

- `infra/deploy.sh lokakode`, `judge`, `auth`, `portal` → each verified: `/healthz` of the three apps and the worker's heartbeat report `57dee87`; judge image tag `e2b13df94b7c`, the old images removed.
- The migration ran on the real `lokakode` schema: `meeting_class_windows.late_until`, `submissions.late_factor`, `submissions.practice` exist; still 26 tables; the 1 existing submission has `late = 0`, `practice = 0`; both existing windows keep `late_policy = reject`. Nothing was dropped or rewritten.
- `infra/smoke.sh` → **`22 lulus, 0 gagal, 0 belum aktif`**.
- Real requests to the deployed LokaKode, without a session: the seven analytics and export routes and the three leaderboard routes → 302 to the LokaAuth login with the right `next`. `/grafik/chart.umd.min.js` (210,082 bytes), its licence and `/js/grafik.js` → 200 from the app itself; the same path on `auth.lokandita.com` → 404 (Chart.js is only in the LokaKode image). The deployed stylesheet contains the new rules.
- `infra/backup.sh` → done; `infra/uji-restore.sh` → passed, 37 schemas/tables restored and equal to the live ones; `infra/tes-infra.sh` → 57 passed, 0 failed.

**Not done:**

- **No request with a session was made against production.** The agent has no account there and created none; every new page was exercised only on the test containers. What a logged-in lecturer or student sees on the deployed system is the owner's gate.
- `judge/uji-terima.sh` was not run in this stage (its items are part of the full judge suite that did run).
- The tag `tahap-9b`.

Not verified, or weaker than it looks:

- **No page of 9b has been seen by a person**, in any language. The browser tests prove direction and behaviour, not looks.
- Calibration thresholds other than "MUDAH below 30 %" are the agent's numbers, and "median time" is the median CPU time of best submissions, because the system does not measure how long a student worked (`DECISIONS.md`).
- Distribution, mean and class comparison count an active student without a submission as 0, also for a meeting that was never opened for that class; the comparison table says so, but a class that has not started a meeting looks like a class that failed it.
- Viewing a student's source code is not audited; exports are.
- Leaderboards and analytics were tested with classes of five students. Nothing was measured at 40 students × 16 meetings; the queries are single joins on indexed columns, which is an expectation, not a measurement.
- The starved-compile rule was proven by throttling a container, and the worker's retry by the existing stage 7 tests; no test drives a starved compile through the worker process end to end.
- **Found, not fixed:** scores are keyed by student and meeting, not by enrollment. A student who repeats a course in a later academic year would start with last year's scores for the same meeting. It cannot occur before the first new academic year.
- The server's uptime was 31 minutes at 20:37 WIB, so it was restarted shortly before this session; the agent did not do that and did not look into it.

Still owed (not in this stage by the owner's decision): retention of `judge_jobs` and `imports`; whether a pengajar may see the problem bank; Monaco in the problem edit form; a memory limit per problem.

Owner's gate for stage 9 (`SPEC.md` §18: a test student completes one meeting end to end; hidden tests show status only). Everything is at `https://lokakode.lokandita.com`:

1. As the test mahasiswa: the beranda lists every practice meeting of `DDP101`; one that is not published says *Belum diterbitkan dosen pengampu*, one without a schedule for the class says *Belum dibuka untuk kelas Anda*.
2. Open a meeting that is open, solve problems until the target is reached (or as far as you like): each Submit shows verdict, score and the hidden tests as status only; the header counts toward the target. This is the §18 gate.
3. On the meeting page press **Lihat papan peringkat**: your own row is marked, no nomor induk is shown.
4. As pengajar or pengampu: class page → **Jadwal pertemuan** → the meeting. Under *Kiriman sesudah waktu tutup* choose "Diterima sebagai terlambat", factor 0.5, set the closing time a minute ahead, save. After that minute the student page says the submission is late; a correct Submit scores half and the best score does not drop.
5. On the meeting's settings (pengampu) tick **Boleh berlatih sesudah pertemuan ditutup**, press *Tutup sekarang* on the class window, and submit as the student: judged, marked "latihan", score unchanged.
6. Class page → **Analitik**: the score table and "Mahasiswa berisiko"; click a meeting number: distribution with the chart, statistics per problem; click a submission count → the student's submissions → *Lihat* shows the source code.
7. **Unduh nilai (XLSX)** and open it in a spreadsheet: one row per student, columns `P01`, `P02`, …, a second sheet *Rincian*. `/audit` (superadmin) lists `nilai.diekspor`.
8. Course page → **Perbandingan kelas** (pengampu or superadmin). As the second dosen the same address must say *tidak berhak*, and `/kelas/<other class>/analitik` must say *Halaman tidak ditemukan*.
9. Switch the language to Arabic in the account menu and look at the beranda, a problem page and the analytics page: mirrored layout, editor and code left-to-right.
10. `infra/smoke.sh` — expect `22 lulus, 0 gagal, 0 belum aktif`.

The submissions, the window changes and the export made in this gate are real production rows, like those of the earlier gates. After the gate: tag `tahap-9b`, record the result here.

### Arabic reviewed by an Arabic speaker — 2026-11-19 (reported by the owner)

- The owner reported: the Arabic display and texts have been confirmed by an Arabic speaker. The two notes saying the Arabic texts were still unreviewed (in the result of the 9a check and in "Not verified" of 9b) are removed on his instruction. The older notes of stages 6, 8 and 9a are left as the record of those days and are closed by this entry.
- The agent did not see the review and cannot judge Arabic itself. Which pages and texts the speaker read was not stated; in particular it is not known whether the review covered the Arabic problem statements of `DDP101` meetings 1–3, so that note and their "machine translation" badge stay until the pengampu clears them.

### Gate result for stage 9 — 2026-11-19

- **The owner reported:** all ten steps of the gate done and passed; `infra/smoke.sh` → 22 lulus, 0 gagal, 0 belum aktif; Arabic display and texts confirmed by an Arabic speaker. Asked about the points below, he decided: tag now. Tag `tahap-9b`. The agent did not watch the gate.
- What agrees with the report (read by the agent at 15:03 UTC):
  - `infra/smoke.sh` run again by the agent → `smoke: 22 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`; `lokakode`, `auth`, `portal` and the worker heartbeat report `7fe8207`.
  - The LokaKode container log of the day has requests for the beranda, a meeting page, a problem page, the class analytics, a meeting's analytics and one submission page. Audit: one `jendela.dibuka` at 14:59 UTC.
- **What the production `lokakode` schema does not show** (counts only):
  - Step 2, the `SPEC.md` §18 gate itself (a student submits): still 1 submission, the one of 2026-11-17 09:37 UTC. No `SUBMIT` job on 2026-11-19; the day's judge jobs are 4 `RUN` and 4 `VALIDATE`, all from smoke runs.
  - Step 4 (late, factor 0.5): all three class windows have `late_policy = reject` and no `late_until`; no submission with a late factor below 1.
  - Step 5 (practice after close): no submission marked `practice`.
  - Step 7 (export): `audit_log` has never had a `nilai.diekspor` row.
  - Weaker, from the container log only (it starts at the last deploy): no request for a leaderboard (step 3), the class comparison (step 8) or a download.
- So on the deployed system the late policy, practice after close, the export and a 9b submission through the real worker remain **proven by the test suites only** (`TelatTest` and the others listed above), not by use. The agent has no explanation for the difference from the owner's report. If Submit was pressed in production and looked successful, a submission was lost and that is a bug to find; nothing in the logs read here points to one.
- The items under "Not verified, or weaker than it looks" above still hold, except that the pages have now been seen by a person and the Arabic reviewed (next section above).
- The tagged commit differs from the deployed `7fe8207` only in this file. **It has not been redeployed**, so `infra/smoke.sh` check 0 fails until `infra/deploy.sh` is run for the four components; nothing that runs has changed.

**Stage 9b finished 2026-11-19, tag `tahap-9b`.**

## Stage 12 — production data (2026-11-19 to 2026-11-20, before stages 10 and 11)

Plan approved by the owner 2026-11-19 (8 steps): 1 contract and curriculum seed · 2 file check (after the owner confirms the files are in place) · 3 account import · 4 teaching assignments and enrollments · 5 count check (the gate) · 6 tests · 7 production run · 8 close. Owner's decisions: `DECISIONS.md`, 2026-11-19.

Consequences of doing this before stages 10 and 11, told to the owner: no SEB (ordinary browsers; paste and copy blocking are deterrents only); the Portal is in maintenance, so a login without `next` lands on the 503 page; "export of own data" does not exist; a password reset from LokaKode and a Submit since the 9b deploy have never happened in production.

Done:

- Step 1 — curriculum seed: `apps/lokakode/app/Data/benih.json`, `App\Libraries\BenihKurikulum`, `spark benih:kurikulum` (`KONTRAK.md` §13). `infra/tes.sh lokakode --filter BenihKurikulum` → `OK (4 tests, 123 assertions)` against real MySQL and `auth-tes`: preview writes nothing; a run creates 3 courses, 48 meetings, 9 classes and PBO301's base concepts with 61 audit rows; a second run changes nothing; a DDP101 that already exists is completed, its differing concepts, language and practice switch reported and left alone; a non-superadmin, a missing pengampu and a missing active year are refused. **Not deployed; nothing has been written to production. The full `infra/tes.sh lokakode` suite was not run at this step.**
- Step 3 (before step 2, because its file format is already fixed by `SPEC.md` §4.1) — account import: `spark akun:impor` in LokaAuth (`KONTRAK.md` §13). `infra/tes.sh auth --filter ImporAkunCli` → `OK (3 tests, 57 assertions)` against real MySQL and `auth-tes`: preview creates nothing and prints no name or nomor induk; a run creates only the mahasiswa rows with the shared password from `SANDI_AWAL`, which then really logs in over HTTP and is forced to `/ganti-password`; a second run with another password changes no hash; a bad row is named by line and refuses the whole write; a non-superadmin, a missing or weak password and a file without the required columns are refused. **Not deployed. The full `infra/tes.sh auth` suite was not run at this step. If the real file has other column names, it must be converted or the command adapted (step 2 will show).**
- Deploy refused once on 2026-11-19 by the agent's permission system and not worked around; done the same day on the owner's word ("do no 1. deploy") at `0bd7c75`: `infra/deploy.sh lokakode`, `judge`, `auth`, `portal` each verified (`/healthz`, worker heartbeat); `infra/smoke.sh` → `22 lulus, 0 gagal, 0 belum aktif`. The seed preview on production then showed `50 dibuat, 8 diubah, 1 beda, 5 sudah sama`; six of the eight changes were titles the owner had already typed by hand, so the seed file was changed to keep them (`DECISIONS.md`). The one `BEDA` is DDP101 meeting 6, which has `FUNGSI, PARAMETER_REF` where the map also has `REFERENSI`; left alone.
- Step 2 — file check, 2026-11-20, after the owner confirmed the files (counts and structure only; no row was printed): `import-mahasiswa.csv` 235 rows, 235 distinct nomor induk, all `mahasiswa`, no empty cell, columns `nomor_induk, nama, global_role, mata_kuliah, kelas`; per class DDP101 A–D 26 each, PBO301 A 25 and B–D 26 each, TS-PY A 28; `import-dosen.csv` 2 rows; `penugasan-pengajar.csv` 9 rows, one per class, two lecturers (5 and 4 classes). All UTF-8 with CRLF.
  - **The "test mahasiswa" of the earlier gates is a real student**: that account is in the file, for DDP101 A. It is therefore not disabled (this reverses the owner's decision 2 of 2026-11-19, which assumed a test account), and 234 accounts are new. It is `AKTIF` in the gate class 7A with 1 submission, 1 meeting score and 64 instances.
  - Both lecturers of `import-dosen.csv` already exist as active dosen; no lecturer account has to be created.
- Steps 4 and 5 — `spark data:pengajar`, `data:pendaftaran`, `data:cocokkan` (`App\Libraries\DataAwal`, `KONTRAK.md` §13). `Pendaftaran::jalankan` now names its actor to the audit log, so it can run from a command.
- Step 6 — tests, at the commit that holds this entry: `infra/tes.sh lokakode` → `OK (228 tests, 9430 assertions)`; `infra/tes.sh auth` → `OK (98 tests, 879 assertions)`. New: `DataAwalCliTest` (5 tests) — preview writes nothing; the count check fails on an empty database and passes after the imports; it fails again for a missing row, a withdrawn student, two students swapped between classes, a suspended account, an account or enrollment or class or pengajar outside the files, and the wrong pengajar; a bad row is named by line and refuses the whole write; a withdrawn student is re-activated on the same enrollment row. `infra/tes.sh portal` and `infra/tes.sh judge` were not run (neither changed).

- Step 7 — production run, 2026-11-20, on the owner's word ("deploy.. shared password is … do no 3"). Every command was previewed first; output below is as printed.
  - Deploy of `f182094`: `infra/cek-env.sh` → `cek-env: LULUS`; `infra/deploy.sh lokakode`, `judge`, `auth`, `portal` each `LULUS` on that commit; `infra/smoke.sh` → `smoke: 22 lulus, 0 gagal, 0 belum aktif`.
  - `spark benih:kurikulum --jalankan` → `ringkasan: 50 dibuat, 2 diubah, 1 beda (tidak disentuh), 10 sudah sama` / `DITULIS.`; run again → `0 dibuat, 0 diubah, 1 beda (tidak disentuh), 62 sudah sama`. The two changes filled empty concept lists (DDP101 meeting 9, PBO301 base concepts); no title was overwritten. The one `BEDA` is still DDP101 meeting 6 without `REFERENSI`.
  - `spark data:pengajar --jalankan < penugasan-pengajar.csv` → `penugasan baru: 9 · sudah ada: 0 · bermasalah: 0` / `DITULIS: 9.`
  - `spark akun:impor --jalankan < import-mahasiswa.csv` with `SANDI_AWAL` → `akun baru: 234 · sudah terdaftar (tidak diubah): 1 · dosen (dilewati): 0 · bermasalah: 0` / `DITULIS: 234 akun dibuat, 0 dilewati.` LokaAuth now has 235 active mahasiswa (234 with forced password change), 2 dosen, 1 superadmin; each mahasiswa has the four apps. `password_verify` of `SANDI_AWAL` against three random new accounts → `cocok` three times. No login as a real student was made.
  - Classes 7A and 7B, by SQL in one transaction with the owner's explicit approval: 1 enrollment set to `MENGUNDURKAN_DIRI`, 2 classes archived; audit rows `pendaftaran.mundur` (1) and `kelas.diarsipkan` (2) with `lewat: sql`, actor the superadmin.
  - `spark data:pendaftaran --jalankan < import-mahasiswa.csv` → `pendaftaran baru: 234 · diaktifkan lagi: 1 · sudah terdaftar: 0 · bermasalah: 0` / `DITULIS: 235.` The student of 7A is `AKTIF` in DDP101 A on the same enrollment row, still with 1 submission and 1 meeting score.
  - **The count check (gate of `SPEC.md` §18):** `spark data:cocokkan mahasiswa` → `COCOK` (accounts 235 ↔ 235; DDP101 A–D 26 each, PBO301 A 25 and B–D 26 each, TS-PY A 28, the same people in each class; enrollments 235 ↔ 235); `data:cocokkan dosen` → `COCOK` (2 ↔ 2); `data:cocokkan pengajar` → `COCOK` (assignments 9 ↔ 9, classes 9 ↔ 9). All three exit 0.
  - LokaKode audit rows of the run: `matkul.dibuat` 2, `matkul.konsepDasar` 1, `pertemuan.dibuat` 39, `pertemuan.diubah` 1, `kelas.dibuat` 9 (`benih`); `pengajar.ditugaskan` 9, `pendaftaran.dibuat` 234, `pendaftaran.diaktifkan` 1 (`cli`); the three `sql` rows above.
  - After the run: `infra/smoke.sh` → `22 lulus, 0 gagal, 0 belum aktif`; `infra/backup.sh` → `backup SELESAI`; `infra/uji-restore.sh` → `uji-restore: LULUS — 37 skema/tabel pulih dan sama dengan yang hidup`.

Went wrong, or weaker than it looks:

- **`akun:impor --jalankan` printed LokaAuth's structured log lines (`user.created`, one per account, with the nomor induk, no name) to the terminal**, because the app log goes to stderr. They reached the agent's tool output, against the rule that no row is shown; a few lines were visible there. The saved copy of that output was deleted. The command should send that log elsewhere when run from a terminal; not fixed.
- The shared initial password was given in the chat and appears in the agent's session transcript on the server. It is short and easy to guess, and until each student changes it, anyone who knows a nomor induk can take that account. 234 accounts are in that state.
- No student has logged in; that the forced change works on these accounts is shown only by the test suite and the `must_change_password` flag.
- The Portal is still in maintenance, and `portal` and `judge` suites were not run in this stage.

Gate and close, 2026-11-20:

- The owner did not check the gate himself; he told the agent to do it and to tag ("do no 1 and 2"). **The gate of this stage was therefore run by the agent, not by the owner.**
- `spark data:cocokkan mahasiswa`, `dosen`, `pengajar` run again → `COCOK` three times, exit 0.
- Independently of that command: the file counted with a short script (235 rows, 235 distinct nomor induk; DDP101 A–D 26 each, PBO301 A 25 and B–D 26 each, TS-PY A 28) and the database counted by direct SQL (the same nine non-archived classes of the active year with the same `AKTIF` counts and 1 pengajar each; 235 active mahasiswa, 2 dosen, 1 superadmin; 16 meetings in each of the three courses). They agree.
- Browser check by the owner after the tag, reported 2026-11-20 in his words: "as pengampu all nine classes show with the right counts; as the second dosen only their four classes show; as one DDP101 student the initial password forced a change, the class meeting appeared and a problem opened." This is the owner's report; the agent did not see it. It covers one student of 235 and one of the three courses from the student's side; a Submit by that student was not reported.
- The tagged commit `dbd3558` was deployed the same day at the owner's command: four times `LULUS` on that commit, `infra/smoke.sh` → `smoke: 22 lulus, 0 gagal, 0 belum aktif`.

After the tag, 2026-11-20 — classes renamed at the owner's request ("lakukan semuanya. kelas TS-PY A jadi kelas TS-6"):

- New names: DDP101 A–D → 7A–7D, PBO301 A–D → 6A–6D, TS-PY A → TS-6. The two archived gate classes of DDP101 had to give up their names first: 7A → `7A-uji`, 7B → `7B-uji`.
- In the database by SQL, one transaction, after `infra/backup.sh` (`backup SELESAI`): `baris audit: 11`, `kelas arsip diganti nama: 2`, `kelas aktif diganti nama: 9`. The audit rows are `kelas.diubah` with `sebelum`/`sesudah` as the page writes them, plus `lewat: sql`. Enrollments, pengajar, windows and scores hang on the class id and did not move.
- In the seed: `apps/lokakode/app/Data/benih.json`, `BenihKurikulumTest`, and the class line of `SPEC.md` §17.
- In the source files: the `kelas` column of `import-mahasiswa.csv` (235 rows) and `penugasan-pengajar.csv` (9 rows) rewritten by a script that refused to continue unless every other column was unchanged; the files as the owner delivered them are kept beside them as `*.asli`.
- `spark data:cocokkan mahasiswa`, `dosen`, `pengajar` after the rename → `COCOK` three times, exit 0, with the nine classes under their new names and the same counts as before.
- Tests at the commit that holds this entry: `infra/tes.sh lokakode` → `OK (228 tests, 9462 assertions)`; `infra/tes.sh auth` → `OK (99 tests, 884 assertions)`. `portal` and `judge` suites not run.

After the tag, 2026-11-20 — `akun:impor` no longer shows personal data on the terminal (the owner's request after the leak noted above):

- Cause: every audit row was mirrored as a JSON line to stderr, and from `docker exec` stderr is the terminal; the `user.created` line carried the nomor induk.
- Fix: no mirror line from `spark` (LokaAuth and LokaKode); LokaAuth mirror lines from the web never carry `nomor_induk`, `nama`, `name` or `email` (`KONTRAK.md` §13). `ImporAkunCliTest` now asserts that stdout and stderr of a real `--jalankan` run hold no nomor induk, no name and no log line, and `Audit::untukLog` is tested directly.
- A first attempt (CLI log lines to the container's stderr) failed in the test container and in production and was removed before any commit; `infra/tes.sh auth` showed `Tests: 99, Assertions: 866, Failures: 1` for it.
- Deploy refused once by the agent's permission system; done 2026-11-21 on the owner's word ("do no 1") at `9f9aabb`: four times `LULUS`, `infra/smoke.sh` → `smoke: 22 lulus, 0 gagal, 0 belum aktif`.
- Evidence in production, 2026-11-21: `akun:impor --jalankan` with the real file, stdout and stderr captured separately → `akun baru: 0 · sudah terdaftar (tidak diubah): 235`, `DITULIS: 0 akun dibuat, 0 dilewati`, stderr 0 bytes, 0 JSON log lines, 0 of the file's 235 nomor induk and 0 of its names anywhere in the output; the audit row `user.imported` (`lewat: cli`) was still written. This run created no account, so the `user.created` case is shown by the test only.
- `benih:kurikulum` preview with the renamed seed → `0 dibuat, 0 diubah, 1 beda (tidak disentuh), 62 sudah sama`.

After the tag, 2026-11-21 — deadline for the shared initial password (owner's choice: "option A", 1 December 2026):

- 233 of 235 student accounts still carried the shared password. `spark akun:batas-sandi-awal` sets a deadline per account; `LoginGuard` refuses the initial password after it; any new password clears it (`KONTRAK.md` §13, migration `2026-11-21-000001_TambahBatasSandiAwal`).
- Tests at the commit that holds this entry: `infra/tes.sh auth` → `OK (101 tests, 926 assertions)`; `infra/tes.sh lokakode` → `LULUS` (count in the next entry). New: `BatasSandiAwalTest` (2 tests) — only active mahasiswa still on their first password get the deadline; before it the login is forced to `/ganti-password`; after it the login answers 422 with reason `initial_password_expired` and no lockout count; a wrong password is still `wrong_password`; an account that has changed its password is not affected; an admin reset clears the deadline; a non-superadmin, a malformed, impossible or past date are refused.
- `infra/tes.sh lokakode` at `8c28852` → `OK (228 tests, 9394 assertions)`.
- Production, 2026-11-21, at `8c28852`: `infra/backup.sh` → `backup SELESAI`; four deploys `LULUS` (the `auth` deploy ran the migration; the column exists); `infra/smoke.sh` → `smoke: 22 lulus, 0 gagal, 0 belum aktif`. `spark akun:batas-sandi-awal --sampai 2026-12-01` previewed, then `--jalankan` → `DITULIS: 233 akun diberi batas, 0 sudah berbatas sama.`; run again → `DITULIS: 0 akun diberi batas, 233 sudah berbatas sama.` The database shows 233 active mahasiswa with `must_change_password = 1` and the deadline `2026-12-01 17:00:00` UTC (1 December 23:59 WIB); the 2 mahasiswa who changed their password, the 2 dosen and the superadmin have none. Two `password.initial_deadline` audit rows (`lewat: cli`).
- Not verified in production: the refusal after the deadline (it has not passed; shown by `BatasSandiAwalTest` only), and a reset by a pengajar from a class page, which has still never happened in production.
- Still true until 1 December: any student who knows a classmate's nomor induk can log in as a classmate who has not logged in yet.
- The owner reported on 2026-11-21 that the students and the second lecturer are being told, and that a password reset from a class page is his to try ("no 1 and 2 okay"); neither is verified by the agent.

## Stage 11 — Portal and public pages (2026-11-21, before stage 10)

Plan approved by the owner 2026-11-21 (8 steps): 1 contract and environment · 2 shared package · 3 Portal public pages · 4 Portal after login · 5 LokaKode public landing · 6 own-data export · 7 tests · 8 deploy, smoke, close. Owner's decisions and the agent's choices: `DECISIONS.md`, 2026-11-21.

Built:

- Contract (`KONTRAK.md`): §4 public routes, `GET /bahasa/{id|en|ar}`, the Portal exempt from the app-access step; §10 `LOKAKODE_URL` (Portal; added to `apps/portal/.env.example` and, by the agent, to `apps/portal/.env` on this server — not a secret); §11 smoke check 8; new §14 own-data export.
- Shared package: public layout (indexable, description, canonical, Open Graph, language links, **Masuk**), `Controllers\Bahasa`, `Publik`, `Ekspor`, the shared "Data saya" page; footer of every page links to the Portal's privacy and terms; the temporary stage 5 pages are gone.
- Portal: `/` (landing with app cards: LokaKode a link, LokaUjian and LokaSiber "segera" and not links), `/tentang`, `/kontak`, `/privasi`, `/ketentuan`, `/sitemap.xml`, `/robots.txt`, all in `id` / `en` / `ar`; `/beranda` (name, role, apps from `app_access`, a LokaAuth admin card for a superadmin), `/data-saya`.
- LokaKode: public landing at `/` (features, how it works, C / C++ / Python, SEB announced with the official download link and "belum diberlakukan", FAQ, contact), `robots.txt`, Open Graph image from `docs/branding`.
- Own-data export in LokaAuth and LokaKode: `GET /data-saya`, `POST /data-saya/unduh`.
- `infra/tes.sh portal` also serves a real `lokakode-tes`; `infra/smoke.sh` check 8.

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh auth` → `OK (105 tests, 986 assertions)`; `infra/tes.sh portal` → `OK (39 tests, 1395 assertions)`; `infra/tes.sh lokakode` → `OK (236 tests, 9974 assertions)`. Real MySQL, Redis, HTTP between containers, real worker and browser for LokaKode; nothing mocked. `infra/tes.sh judge` not run (the worker did not change).
- **The gate case in containers** (`apps/portal/tests/GerbangTest.php`, four real containers, as mahasiswa and as dosen): Masuk on the Portal → LokaAuth login with `next` → back on the Portal's `/beranda` with the LokaKode card → the same `lokandita_sid` on LokaKode's `/beranda` answers 200 with the user's name and no redirect; the database then holds one session, one successful login attempt and one `login.success`; **Keluar** pressed on the Portal makes LokaKode send that cookie back to the login page. A user without `lokakode` access is not offered LokaKode and gets 403 from LokaKode itself.
- Export, both apps: A's file holds nothing of B (also for a superadmin, with any parameter), no password hash, session value, audit detail, hidden test, generator or reference solution; refused without session or CSRF; second download within a minute → 429; audited.
- `infra/cek-env.sh` → `cek-env: LULUS`. `infra/deploy.sh auth`, `portal`, `judge` → `LULUS` at `c28ed68`.
- `infra/smoke.sh` at `c28ed68` → `smoke: 27 lulus, 0 gagal, 0 belum aktif` (22 old lines plus the 5 of check 8).
- Real requests after the deploy. From the internet: `https://lokakode.lokandita.com/` without cookie → 200, `index, follow`, the landing; with a made-up `lokandita_sid` → 302 `/beranda`; `/bahasa/ar` then `/` → `lang="ar" dir="rtl"`; `/data-saya` without session → 302 to the LokaAuth login with the right `next` (LokaKode and LokaAuth); `POST /data-saya/unduh` without session or CSRF → 403 (both); `https://lokandita.com/privasi` → 503 (maintenance). At the `portal` container directly: the five public pages, `sitemap.xml`, `robots.txt` → 200; `/beranda`, `/data-saya` → 302 to login; an unknown path → 404.

Went wrong:

- **`infra/deploy.sh lokakode` failed once** with a Docker name conflict (`655cbddd700c_lokakode` already in use) while Compose recreated the container. A minute later the container was up and healthy on the new commit and `https://lokakode.lokandita.com/` answered 200; the deploy was run again and ended `LULUS`. Cause not investigated; a leftover of the test run that had just ended is the agent's guess, not a finding. LokaKode may have been unreachable for some seconds around 12:10 WIB.
- The LokaKode test run was reported "stopped" to the agent when its session restarted; it was in fact still running and ended with the result above.
- A read of `app_access` counts per role was refused by the agent's permission system during planning and not worked around; the owner's decision 4 made it unnecessary.

Not verified, or weaker than it looks:

- **The gate has not been done on the deployed system or in a browser.** The Portal is in maintenance and the agent has no account. It is proven between real containers only.
- **No page of this stage has been seen in a browser**: layout of the landing pages, the header on a narrow screen, the Arabic (right-to-left) pages, dark mode, the download dialog of the export.
- **Privacy policy and terms are the agent's draft, not legal advice**, and are not yet read by the owner. They name no operator (owner's decision) and say accounts are disabled, never deleted. English and Arabic texts of the whole stage were written by the agent and not reviewed.
- **Since the deploy, the footer of every LokaAuth and LokaKode page links to `lokandita.com/privasi` and `/ketentuan`, which answer 503 until the Portal is opened.**
- `https://lokakode.lokandita.com/` now shows the landing to a visitor without a session cookie instead of going straight to the login page (one more click on **Masuk**); with a cookie it still goes to `/beranda`.
- Search engines see the Indonesian version only (one URL for three languages). No Open Graph image and no logo for the Portal; it shows the LokaAuth icon.
- The export of a real account has never been downloaded in production. A user with thousands of large submissions could make a big file (at most 5000 rows per list, source included); not tested at that size. The 60 s limit is not enforced when Redis is down (by design).
- The export has no password re-entry: whoever holds a live session can download that user's data. On shared lab computers the forced idle logout is the protection.
- The SEB link was checked once (`200` on 2026-11-21); it is an outside address the project does not control.
- `infra/tes-infra.sh`, `infra/backup.sh` and `infra/uji-restore.sh` were not run in this stage (no schema changed).

Owed by later stages:

- Stage 10: SEB enforcement, the `.seb` download and the instructions page; then the SEB section of the LokaKode landing and the terms (similarity checks) must be updated.
- Portal artwork (logo, favicon, Open Graph image) when the owner supplies it.

Owner's gate for stage 11 (`SPEC.md` §18):

1. Read the privacy and terms texts first (`apps/portal/app/Language/id/Portal.php`), because step 2 makes them public.
2. `infra/pemeliharaan.sh portal off`. The agent will not run this without being asked.
3. Private window → `https://lokandita.com`: landing; switch to English and العربية; open Tentang, Kontak, Privasi, Ketentuan.
4. Press **Masuk**, log in as a student → the Portal shows name, role and the LokaKode card → click it → LokaKode's beranda opens **without a second login**.
5. Back on the Portal press **Keluar** (never yet done in a browser), then reload the LokaKode tab → login page.
6. Without a session open `https://lokakode.lokandita.com` → the LokaKode landing. Logged in, open *Data saya* on the Portal and download the file once from LokaAuth and once from LokaKode.
7. `infra/smoke.sh` → expect `27 lulus, 0 gagal, 0 belum aktif`; check 1 then expects `lokandita.com` to answer 2xx/3xx.

After the gate: tag `tahap-11`, record the result here.

Gate result for stage 11, 2026-11-21 — the owner's report, in his words:

- "Privacy and terms texts read: fine." · "`infra/pemeliharaan.sh portal off` run." · "Three languages, four text pages, Masuk → LokaKode card without second login, Keluar then reload shows login, 'Data saya' downloaded from each app: all fine." · "`infra/smoke.sh`: 27 lulus, 0 gagal, 0 belum aktif."
- **The gate of `SPEC.md` §18 passes** — on the owner's report; the agent did not see the browser. This also closes, on his word, "Keluar pressed from the Portal" (open since stage 5) and the first real export downloads.
- **One defect found by the owner, to be fixed before the tag:** on the Arabic landing of `lokakode.lokandita.com` the hero text was centre-aligned inside a block right of centre, lining up with neither the page centre nor the right edge; and the top bar had the language links and login button at the left "with nothing at the right". Required: Arabic is the mirror of Indonesian — hero right-aligned from the right edge of the content column, top bar starting at the right (brand right, actions left), content column of the same width and position, mirrored; all public pages of the three apps checked; a browser test; screenshots.

Fix, commit `7cf51a8`:

- Cause, seen in screenshots of the unfixed pages taken in the test browser: the hero (`.pembuka`) was `text-align: center` in every language, and the text column (`.prosa`) was a centred column whose text was start-aligned — in Arabic a right-aligned block floating in the middle. Both now start at the start edge of the content column (left for Indonesian and English, right for Arabic). **This also moved the Indonesian and English hero from the centre to the left edge.** The language links in the top bar sat 20 px lower than the button (a margin meant for the login page); fixed.
- **The top-bar part could not be reproduced.** In Chromium 158 at 1280 px and 390 px the Arabic top bar already had the brand at the right and the actions at the left before the fix. The test now enforces it; if the owner still sees an empty right side, the browser and window width are needed.
- New browser test `apps/portal/tests/Peramban/ArabPublikTest.php` (real headless Chromium through a test Caddy, as in production) on seven public pages — Portal `/`, `/tentang`, `/kontak`, `/privasi`, `/ketentuan`, LokaKode `/`, LokaAuth `/login` — in Indonesian and Arabic at 1280 px and in Arabic at 390 px: brand at the right edge of the top bar, actions at the left edge and left of the brand; content column equally wide and mirrored; every child of the hero (heading, paragraph, button, note) right-aligned and starting at the right edge of the content column, at the mirrored position of the Indonesian one; text column and its paragraphs likewise; no horizontal overflow, no CSP violation. Against the unfixed pages it failed for both landing pages ("pembuka anak ke-1 harus rata kanan, ternyata center").
- For this, `infra/tes.sh portal` runs the test browser with three host names mapped to the test containers, and the browser helper moved to the shared package (`KONTRAK.md` §12).
- Tests at `7cf51a8` (output in the session): `infra/tes.sh auth` → `OK (105 tests, 986 assertions)`; `infra/tes.sh portal` → `OK (53 tests, 1765 assertions)`; `infra/tes.sh lokakode` → `OK (236 tests, 10014 assertions)`.
  - The first LokaKode run of this fix ended `Tests: 236, Assertions: 10048, Failures: 1`: `ImporTest` expected two imported problems in a fixed order in the export, but the order follows which `VALIDATE` job the judge finishes first. Not caused by this stage; the test now checks the content, not the order, and the rerun above is clean. The export itself still orders by problem id.
- Deploy: `infra/cek-env.sh` → `cek-env: LULUS`; `infra/deploy.sh auth`, `lokakode`, `portal`, `judge` → each `LULUS` on `7cf51a8`, no name conflict this time; `infra/smoke.sh` → `smoke: 27 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`. The stylesheet served by `https://lokandita.com` and `https://lokakode.lokandita.com` (`lokandita.css?v=7cf51a82de6a`) contains `.pembuka{text-align:start;…}`.
- Screenshots, from the test run at this code (test containers, 1280 × 900 and 390 × 900): `docs/tangkapan/tahap-11/` — `lokakode-depan-ar.png`, `portal-depan-ar.png`, their `-id` twins, `lokakode-depan-ar-sempit.png`, `portal-privasi-ar.png`, `lokaauth-login-ar.png`.

Went wrong in this stage besides the above:

- **Two copies of the agent ran at once** (`claude --continue` twice on the same conversation). Both committed and deployed at about 12:10 WIB: the container-name conflicts recorded above and one `502` on `https://lokakode.lokandita.com/` came from that, not from a leftover test run. From the fix onward one copy stood down and only one acted. A session that is resumed should first check `ps` for another copy.

Not verified, or weaker than it looks, after the fix:

- **The fixed pages have not been seen by the owner**, and not in any browser but Chromium 158. The screenshots are of test containers, not of production; production serves the same commit and the same compiled rule.
- The test measures positions, not appearance: it does not judge whether the left-aligned Indonesian hero looks good, dark mode, or fonts.
- Pages behind a session were not part of this check; their Arabic layout was tested in stage 9b for LokaKode only.
- The remaining items of "Not verified, or weaker than it looks" above still hold, except: the gate has now been done in a browser, the texts have been read by the owner, and exports have been downloaded in production.

**Stage 11 finished 2026-11-21, tag `tahap-11`.** The tagged commit differs from the deployed `7cf51a8` only in this file; **it has not been redeployed**, so `infra/smoke.sh` check 0 and the worker heartbeat line fail until `infra/deploy.sh` is run for the four components. Nothing that runs has changed.

Carried into the next stages:

- Stage 10: SEB enforcement, `.seb` download and instructions page; then update the SEB section of the LokaKode landing and the terms. Integrity pages, similarity, communication.
- Portal artwork when the owner supplies it; an Open Graph image for the Portal.
- All three hosts are open. 233 student accounts carry the initial-password deadline of 1 December 2026.

After the tag, 2026-11-21 — hero centred again for Indonesian and English (the owner's correction: "I still want the English and Indonesian to be centre aligned but Arabic right aligned"):

- The hero (`.pembuka`) and the text column (`.prosa`) are centred as they were when the gate was run; for Arabic only (`[dir=rtl]`) they are right-aligned and start at the right edge of the content column. The sentence above that the Indonesian and English hero moved to the left edge no longer holds.
- `ArabPublikTest` asserts both sides: for `id` (seven pages) and `en` (the two landings) every hero child is `text-align: center` with its box centred in the content column, and the text column is centred; for `ar` the assertions of the fix are unchanged. `infra/tes.sh portal` → `OK (54 tests, 1778 assertions)`; `infra/tes.sh auth` → `OK (105 tests, 986 assertions)`.
- **`infra/tes.sh lokakode` was not rerun for this change** when this entry was written (stylesheet rules for two public-page classes and a Portal test only); its last result is the `OK (236 tests, 10014 assertions)` above.
- The owner confirmed the Arabic pages of the first fix in his browser ("No 1. is okay"), which also settles the top-bar report.
- The screenshots in `docs/tangkapan/tahap-11/` were replaced by those of this version; `portal-depan-en.png` added.
- The tag `tahap-11` stays on `0bb9f79`; this change is one commit after it. Deploy and smoke results of that commit are in the next entry.
- Deploy of `a94bfb0`, 2026-11-21: `infra/cek-env.sh` → `cek-env: LULUS`; `infra/deploy.sh auth`, `lokakode`, `portal`, `judge` → each `SELESAI`; `infra/smoke.sh` → `smoke: 27 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`. The stylesheet served by `https://lokandita.com` and `https://lokakode.lokandita.com` (`lokandita.css?v=a94bfb062668`) holds `.pembuka{text-align:center;…}` and `[dir=rtl] .pembuka{text-align:start}`.
- The centred Indonesian and English pages of this version have not been seen by the owner; the screenshots are of test containers.
- The commit that holds this line differs from `a94bfb0` only in this file and was deployed right after it at the owner's word ("do no 3"), so that `/healthz` reports `HEAD`; the output of that deploy and its smoke run is in the session.
- The second agent process the owner asked to close (pid 1911501) had already ended by itself; one `claude` process remained.

## Stage 11b — text editor for the Portal's public pages (2026-11-21, after `tahap-11`)

The owner reported on 2026-11-21 that he has seen the centred Indonesian and English pages of the last correction, and asked for "a page where I can edit the wordings of the portal in superadmin mode". Not in `SPEC.md`; plan presented and approved the same day ("approve", recommended options). Decisions: `DECISIONS.md`, 2026-11-21. Contract: `KONTRAK.md` §15.

Built (Portal only):

- `/admin/teks` (superadmin only; a card on the superadmin's Portal beranda): six pages × three languages — landing, app card descriptions, Tentang, Kontak, Privasi, Ketentuan. Per page and language: a form in the shape of the page, **Pratinjau** (the real page partial, nothing saved), **Simpan dan tayangkan** (live at once), **Kembalikan ke teks bawaan**. `/admin/teks/riwayat`: every change with the text before and after.
- The language files stay the defaults. Overrides live in the Portal's own database: tables `teks` and `audit_log` (the first tables of the `portal` schema). Public pages read through `App\Libraries\Teks`; when the table cannot be read they show the default.
- A translation older than the changed Indonesian text is flagged in the list. "Terakhir diperbarui" on Privasi and Ketentuan becomes the day of the save.

Verified by the agent (output in the session):

- `infra/tes.sh portal` → `OK (62 tests, 2488 assertions)`, with real MySQL (`portal_test`), real `auth-tes` and `lokakode-tes`, and the browser tests of stage 11. New `TeksAdminTest` (7 tests): dosen and mahasiswa get 403 on every page and endpoint, also with a valid payload, and nothing is stored; no session → login; preview stores nothing and the public page stays; a save is on the public page at once, other pages and languages unchanged; `<script>` typed into a paragraph is shown as text; `{email}` becomes the contact address; empty or over-long fields, a missing section and control characters are refused with 422; unknown keys and an extra section are ignored; restore to default; one audit row per real change and none for an unchanged save; no route changes the history; the date follows the save in three languages (Asia/Jakarta); with the table renamed away the public pages answer 200 with the default text.

Not verified, or weaker than it looks:

- **The editor has not been seen in a browser** — not by the agent, not by the owner: the long forms (Privasi has eight sections), the preview frame, the Arabic form.
- `infra/tes.sh auth` and `infra/tes.sh lokakode` were **not** rerun for this change; of the shared code only the stylesheet gained six rules. `infra/tes.sh lokakode` was last run before the hero was centred again (`OK (236 tests, 10014 assertions)`).
- A save is live at once and there is no second person to approve it. A mistake is undone by saving again or restoring the default; the old text is in the history.
- English and Arabic texts of the editor itself were written by the agent and not reviewed.
- Privacy and terms can now be changed without a commit: Git no longer shows what the public pages say. The database (and its daily backup) and `/admin/teks/riwayat` do.
- The fallback was tested by renaming the table, not by stopping MySQL.

Deploy of stage 11b, 2026-11-21, at `002d4bf` (output in the session):

- `infra/cek-env.sh` → `cek-env: LULUS`; `infra/deploy.sh portal`, `auth`, `lokakode`, `judge` → each `LULUS` on that commit. The Portal migration ran on the real `portal` schema: `teks` 0 rows, `audit_log` 0 rows. `infra/smoke.sh` → `smoke: 27 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`.
- Real requests to `https://lokandita.com` without a session: `GET /admin/teks`, `/admin/teks/privasi/id`, `/admin/teks/riwayat` → 302 to the LokaAuth login with the right `next`; `POST /admin/teks/privasi/id` → 403; `/`, `/privasi`, `/ketentuan` still show their default headings.
- **Nothing has been edited in production**, so a real save, the preview and the history have run only in the test containers. The owner's check: log in as superadmin at `https://lokandita.com`, open *Teks halaman publik*, change one sentence of Tentang, press Pratinjau, then Simpan, look at `https://lokandita.com/tentang` in a private window, then *Kembalikan ke teks bawaan*.
- No new tag: `tahap-11` stays on `0bb9f79`. The commit that holds these lines differs from `002d4bf` only in this file and was deployed after it so that `/healthz` reports `HEAD`; that output is in the session.

## Stage 11c — public pages redesigned, replaceable hero photo, logout to the Portal, Kontak merged into Tentang (2026-11-21, after stage 11b, before stage 10)

Asked for by the owner on 2026-11-21; plan presented and approved the same day ("approve all"). During the work he added: "redirect all logout to the portal lokandita.com", "combine Tentang and Kontak page", then "remove kontak and just put it contents in Tentang". Decisions: `DECISIONS.md`, 2026-11-21. Contract: `KONTRAK.md` §2, §4, §9, §11, §15, new §16. Commits: `e62b6e2` (logout), `4ce836d` (everything else). No tag, by the owner's decision.

The first deploy attempt was refused by the agent's permission system and not worked around; the owner then said "deploy". Results are under "Deploy of stage 11c" below; the lists "Not verified" and "For the owner" that follow here were written before the deploy and are corrected there.

Built:

- **Logout**: LokaAuth's own Keluar and `/keluar-selesai` (where the other apps send the browser) end on `PORTAL_URL/`. The login page no longer says "you have logged out".
- **Hero**: split layout on both landings — text at the start edge, image on the other side, mirrored for Arabic, stacked on a phone; never text over the photo. Portal: `<picture>` with AVIF / WebP / JPEG at 480, 768, 1200 and 1600 px, `width` and `height`, alt text in three languages, credit "Foto: Josh Sorenson / Pexels" in the footer. LokaKode: the same layout with `lokakode/logo.png` from the branding archive.
- **Icons**: Lucide 1.77.0 (ISC), 24 SVG files in `packages/lokandita-auth/ikon/`, inline, one size and stroke width; app cards, features, steps, contact, footer links. Decorative ones `aria-hidden`; arrows, log-in/out, send and external-link flip in Arabic. The footer of every page of the three apps now has icons (one shared partial).
- **Polish**: spacing scale, one card style, hover and focus states, underlined links inside sentences, "Segera" badge with readable contrast. Existing colour tokens only.
- **Replaceable hero photo** in the existing editor: `/admin/teks/foto` — upload (alt text id/en/ar, source, licence, tick box), preview on the real landing partial in Indonesian and Arabic, publish, publish an earlier one again, back to default. Tables `portal.foto` and `portal.foto_tayang`; actions in `portal.audit_log` and on `/admin/teks/riwayat`.
- **Upload pipeline** (`App\Libraries\Gambar`, GD rebuilt with JPEG / WebP / AVIF, plus `exif`): type from content, 10 MB, 30 megapixels read from the header, at least 1600 × 900, re-encoded into twelve files with random names, original discarded. The default photo's files are made by the same code at image build.
- **Operations**: volume `lokandita_portal_media` at `/srv/media` in `portal`; Apache serves `/media/` with PHP off and only server-made names; `infra/backup.sh` adds `media.tar.gz`; `infra/uji-restore.sh` unpacks and verifies it; `infra/tes-infra.sh` section A2; `infra/smoke.sh` check 9; `docs/RESTORE.md`.
- **Brand**: used `lokakode/logo.png` (LokaKode hero) and the icons already placed in stage 11. Not placed: `lokaauth/logo.png`, `lokaauth/og-image.png`, the `icon-512` and `icon-16` files (no page of this scope needs them). The archive holds nothing for the Portal: it keeps the LokaAuth icon, and its Open Graph image is a 1200 × 630 crop of the hero photo.
- **Kontak**: the page is gone. Its content is the last four sections of the Tentang text ("Kontak", "Lupa kata sandi", "Belum punya akun", "Melaporkan masalah"), in three languages; one editor entry; `/kontak` answers 301 to `/tentang`; navigation entry "Tentang" (the owner's choice; first built as "Tentang & kontak"); sitemap without `/kontak`. A stored override whose shape no longer matches the default is not shown (`teks.bentuk_usang`).
- `docs/branding/LISENSI.md`: photo (source, licence, download date) and icon set.

Verified by the agent, in test containers (output in the session):

- `infra/tes.sh portal` → `OK (85 tests, 6159 assertions)`; `infra/tes.sh lokakode` → `OK (236 tests, 10018 assertions)`; `infra/tes.sh auth` → `OK (105 tests, 986 assertions)`. Real MySQL, Redis, HTTP between containers, real GD, real Chromium. `infra/tes.sh judge` not run (the worker did not change).
- `FotoAdminTest` (7 tests): a PHP file named `.jpg`, a JPEG header followed by PHP, GIF, SVG and text are refused; a valid JPEG with PHP appended is accepted and none of its twelve served files contains the appended bytes; a JPEG with real EXIF (camera make, GPS latitude and longitude — checked to be present in the fixture) comes out without them in AVIF, WebP and JPEG; dosen and mahasiswa get 403 on every page and endpoint, also with a valid photo, and nothing is stored; no CSRF → 403; too small, too many pixels (a PNG whose header claims 20000 × 20000), over 10 MB, truncated, missing fields and missing tick box each refused with their reason; upload does not change the public page, publish does, publishing B then A again brings A back, back to default restores the exact default page; six audit rows in order; with `foto_tayang` renamed away the landing shows the default; `/media/` answers only server-made names, with `nosniff`, `immutable` and no cookie.
- `PembukaTest` (16 cases: 2 sites × id/ar × light/dark × 1280/390 px): image loaded as AVIF or WebP from the same origin; a small file on the phone; photo box 4:3; text beside the image on desktop (mirrored in Arabic) and above it on the phone; layout shift below 0.01; every text of hero, cards, steps, header and footer at 4.5 : 1 or better in both modes; every icon 20 × 20 with stroke 2, hidden or labelled, directional ones mirrored only in Arabic; no CSP violation.
- `ArabPublikTest` rewritten for the split hero: Arabic is the mirror of Indonesian, image included.
- Lighthouse 12.8.2, mobile, `https://lokandita.com/`, **before** (production at `70b3f0e`), three runs: performance 97 / 97 / 100, accessibility 91 / 91 / 91, best practices 100 / 100 / 100; FCP 0.83–0.95 s, LCP 1.16–1.24 s, CLS 0.000–0.001. The accessibility failures were the gold "Segera" text (2.03 : 1), grey text on the 80 %-transparent cards (4.13 : 1) and a link told apart by colour only; all three are changed in this stage.
- Screenshots from the test containers: `docs/tangkapan/tahap-11c/` — the 16 combinations as `<site>-<id|ar>-<terang|gelap>-<desktop|ponsel>.png`, plus `portal-tentang-id.png` and `-ar.png`.
- `infra/cek-env.sh` → `cek-env: LULUS`. No environment variable was added.

Went wrong:

- **The deploy did not happen** (above).
- A first version of the Kontak change kept two texts joined on one page; the owner's second message replaced it before it was committed.
- A JPEG cut off after 700 bytes was at first accepted, because GD by default opens a damaged JPEG as far as it can. Found by the test; `gd.jpeg_ignore_warning = 0` now refuses it.
- The browser test once measured a layout shift of 0.017. What moved was the header and the main column, pushed by the page scrollbar appearing — not the photo. `scrollbar-gutter: stable` fixed it but broke the Arabic mirror, so it was withdrawn; the test hides scrollbars instead. **On a desktop browser with classic scrollbars that small shift can still happen, as it could before.**

Not verified, or weaker than it looks:

- **Nothing of this stage runs in production.** Not done: the Portal migration (`foto`, `foto_tayang`) on the real schema, creation of the volume, `infra/backup.sh` and `infra/uji-restore.sh` with the photo archive, `infra/tes-infra.sh`, `infra/smoke.sh` (expected: 29 lulus — the 27 old lines plus the 2 of check 9), the CSP header from the live site, and **Lighthouse after**. The claim "no slower first paint" is therefore unproven; only the test-container measurements above exist.
- The CSP code (`SecurityHeaders::policy`) is untouched and the browser tests saw no violation, but the response header has not been shown from production after the change.
- `infra/uji-restore.sh` and the new part of `infra/backup.sh` have **never been run**; they passed `bash -n` only.
- No real photo from a phone or camera has been uploaded; the tests use generated images. Memory was measured only with the 18-megapixel default photo (146 MB peak).
- GD ignores colour profiles; AVIF support in very old browsers falls back to WebP or JPEG and was tested in Chromium 158 only.
- English and Arabic texts of the photo editor, the alt text of the default photo and the navigation label were written by the agent and not reviewed.
- The download date in `LISENSI.md` (2026-11-21) is the day the file reached the server, not confirmed by the owner.
- The editor pages and the upload form have not been seen in a browser by anyone.
- Production has one text override (`depan` / `id`, saved 2026-11-21); it has the unchanged shape and is expected to show on the new landing — not seen.

For the owner, in this order (or allow the agent to deploy and it will do them and report):

1. `infra/deploy.sh portal`, then `auth`, `lokakode`, `judge` — each must end `LULUS` on the current commit.
2. `infra/backup.sh`, then `infra/uji-restore.sh` — expect `uji-restore: LULUS` with the new tables and "0 foto".
3. `infra/tes-infra.sh` and `infra/smoke.sh` (expect `29 lulus, 0 gagal, 0 belum aktif`).
4. `curl -sI https://lokandita.com/ | grep -i content-security-policy` — must equal the header of before.
5. Browser: `https://lokandita.com` in three languages and dark mode; **Keluar** from LokaKode lands on the Portal; `/kontak` lands on Tentang; as superadmin *Teks halaman publik* → *Kelola foto pembuka* → upload a photo, preview, publish, then *Kembali ke foto bawaan*.

### Deploy of stage 11c — 2026-11-21, at `6dde6b7` (output in the session)

After the owner's "1. deploy 2. just \"Tentang\"": the navigation entry is plain "Tentang" / "About" / "عن المنصة" (`infra/tes.sh portal` again `OK (85 tests, 6159 assertions)`), then:

- `infra/cek-env.sh` → `cek-env: LULUS`; `infra/deploy.sh portal`, `auth`, `lokakode`, `judge` → each `LULUS` on `6dde6b7`. The Portal migration created `portal.foto` and `portal.foto_tayang`; the container has the volume `lokandita_portal_media` at `/srv/media`.
- `infra/smoke.sh` → `smoke: 29 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`, with check 9: `/merek/hero/hero-480.avif?v=6dde6b750d36 -> 200 image/avif`, `smoke.php -> 403`.
- `infra/tes-infra.sh` → `tes-infra: 63 lulus, 0 gagal`, including section A2 (volume on `portal` only, writable by `www-data`, a PHP file placed in it answers 403 and is removed again, no directory index).
- `infra/backup.sh` → `media.tar.gz 134 byte, 0 berkas foto`. `infra/uji-restore.sh` **failed the first time** (`media.tar.gz tidak dapat dibongkar`): the script's own start-up clean-up deleted the temporary directory it was about to unpack into. Fixed (the directory is created after that clean-up); second run → `uji-restore: LULUS — 41 skema/tabel pulih dan sama dengan yang hidup; 0 berkas foto pulih`.
- Response headers of `GET https://lokandita.com/` after the change: `content-security-policy: default-src 'self'; script-src 'self' 'nonce-…'; style-src 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self' https://lokandita.com https://auth.lokandita.com`, plus `x-content-type-options: nosniff`, `x-frame-options: DENY`, `referrer-policy: same-origin`, HSTS. The header was not captured before the change; `SecurityHeaders.php` has no diff against `70b3f0e`.
- **Lighthouse 12.8.2, mobile, `https://lokandita.com/`, three runs each:**

  | | performance | accessibility | best practices | FCP | LCP | CLS |
  |---|---|---|---|---|---|---|
  | before (`70b3f0e`) | 97 / 97 / 100 | 91 / 91 / 91 | 100 / 100 / 100 | 0.83–0.95 s | 1.16–1.24 s | 0.000–0.001 |
  | after (`6dde6b7`) | 98 / 100 / 100 | 100 / 100 / 100 | 100 / 100 / 100 | 0.77–0.89 s | 1.00–1.07 s | 0.000–0.001 |

  Run from a throwaway container on this same server, so the network path is short; the numbers compare before and after, they are not what a phone on a mobile network sees.
- Real requests to production without a session: `/kontak` → 301 `https://lokandita.com/tentang`; `/tentang` has the headings Tentang Lokandita, Aplikasi, Untuk siapa, Cara kami memperlakukan data, Pengelola, Kontak, Lupa kata sandi, Belum punya akun, Melaporkan masalah; the landing shows `<picture>` with the twelve default files and "Foto: Josh Sorenson / Pexels"; `/media/hero/` and `/media/hero/x.php` → 403; `https://auth.lokandita.com/keluar-selesai` → 302 `https://lokandita.com/`; `GET /admin/teks/foto` → 302 to the LokaAuth login with the right `next`; `POST /admin/teks/foto` → 403. The existing text override (`depan` / `id`) still renders.

Still not verified after the deploy:

- **Nothing has been seen in a browser by the owner**, and no photo has been uploaded in production: upload, preview, publish and restore have run in test containers only.
- **The restore test has only ever seen an empty photo archive.** Its checks of real files (twelve per photo, the published one present, equal to the live volume) have not run against a real upload. After the first production upload: `infra/backup.sh`, then `infra/uji-restore.sh`.
- **Keluar has not been pressed in a browser** since the change; proven by the container tests and by the redirect of `/keluar-selesai` above.
- The remaining points of "Not verified, or weaker than it looks" above still hold: only generated test images, memory measured with one 18-megapixel photo, colour profiles ignored, unreviewed English and Arabic texts, the download date in `LISENSI.md`, the desktop scrollbar shift.

Owner's check: `https://lokandita.com` in three languages and in dark mode, on a phone too; `/tentang`; **Keluar** from LokaKode lands on the Portal; as superadmin *Teks halaman publik* → *Kelola foto pembuka* → upload a photo, look at the preview, publish, then *Kembali ke foto bawaan*; then `infra/backup.sh` and `infra/uji-restore.sh`.

The commit that holds these lines differs from `6dde6b7` in this file and in `infra/uji-restore.sh` only; it was deployed after it so that `/healthz` reports `HEAD`.

### Owner's check of stage 11c — 2026-11-21

The owner's report, in his words: "all no 1 to 4 done" and, asked about the upload, "the upload was succesful". The four steps were: the landing in three languages, dark mode and on a phone, and `/tentang`; **Keluar** from LokaKode lands on the Portal; as superadmin upload a photo, preview, publish, back to default; `infra/backup.sh` and `infra/uji-restore.sh`.

- Steps 1 and 2 (pages seen in a browser, Keluar lands on the Portal): on the owner's report; the agent did not see the browser. The Portal log shows a `POST /keluar` → 303 at 11:01:48 UTC.
- Step 3, what production shows (seen by the agent): `portal.foto` has one photo (1600 × 900, JPEG, uploaded 11:01:25 UTC), `foto_tayang` points at it since 11:01:32, `audit_log` has `foto.diunggah` and `foto.ditayangkan`; the volume holds its 12 files; `https://lokandita.com/` serves `/media/hero/<random name>-1200.jpg` with a photo credit in the footer; the three formats answer 200 with the right content type; the served 1600 px JPEG carries no camera or GPS EXIF. **The first real upload in production works.** There is no `foto.bawaan` row: the uploaded photo is the one that is live now, the default was not put back — which is fine if that is what the owner wants.
- The agent's first look at the database, a minute or two before the upload, found no photo and said so; the upload came after that look.
- Step 4, run by the agent after the upload: `infra/backup.sh` → `media.tar.gz 1662502 byte, 12 berkas foto`; `infra/uji-restore.sh` → `1 foto di portal.foto, semuanya lengkap di arsip (12 berkas per foto)`, `foto yang sedang tayang ikut pulih`, `uji-restore: LULUS — 41 skema/tabel pulih dan sama dengan yang hidup; 12 berkas foto pulih`. **This closes "the restore test has only ever seen an empty photo archive".**

Still open: "publish an earlier photo again" and "back to default" have not been done in production (container tests only); the uploaded photo's AVIF at 1200 px is 127 kB against 22 kB for the default, so Lighthouse numbers measured with the default photo no longer describe the live page exactly; the remaining points of "Still not verified after the deploy" except the two closed above.

Deploy of `a6637c0` (the commit that holds the lines above), 2026-11-21, at the owner's word ("deploy"); output in the session:

- `infra/cek-env.sh` → `cek-env: LULUS`; `infra/deploy.sh portal`, `auth`, `lokakode`, `judge` → each `LULUS` on `a6637c0`; `infra/smoke.sh` → `smoke: 29 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`.
- **The uploaded photo survived the redeploy**: the landing still serves `/media/hero/<the same random name>-1200.jpg`, and smoke check 9 got `200 image/avif` for its 480 px file. First proof in production that the volume `lokandita_portal_media` outlives a deploy.

The commit that holds *this* deploy record differs from the running `a6637c0` only in this file and is **not deployed**: `infra/smoke.sh` check 0 and the worker heartbeat line report a mismatch until the next `infra/deploy.sh` of the four components. Nothing that runs has changed.

## Stage 10a — Safe Exam Browser (2026-11-21)

Stage 10 was split by the owner's decision into 10a (SEB) and 10b (the rest of `SPEC.md` §13.2 and §14). Plan approved "approve all"; decisions in `DECISIONS.md`.

Built (LokaKode unless noted):

- Tables `seb_configs`, `seb_exam_keys` (migration `000006`).
- `App\Libraries\Seb`: one settings array written as the XML plist of the `.seb` file and as the SEB-JSON whose SHA-256 is the Config Key; configurations stored encrypted with `SEB_ENCRYPTION_KEY`; verification of both headers per `KONTRAK.md` §8.
- Filter `seb` after `masuk`: mahasiswa only, by role. `enabled`: both headers must match one configuration of a course where the student is `AKTIF`; otherwise `403` (page or JSON) and an integrity event `seb_rejected` / `seb_terminated`. `disabled`: no check, red banner "MODE UJI: penegakan SEB dinonaktifkan" on every student page, logged at container start; any other value stops the container.
- Heartbeat `POST /api/seb/denyut` every 25 s, lease 30 s in Redis.
- Pengampu panel `/mata-kuliah/{id}/seb`: create or rotate (quit password ≥ 12 characters, only its hash kept), download the `.seb`, register Browser Exam Keys.
- Public pages `/seb` (instructions, supported client stated), `/seb/unduh/<code>.seb`, `/seb/periksa` (do my headers match?), `/seb/keluar` (quit link). The landing page links to `/seb` and its SEB text follows the mode.
- Shared package: `Lokandita\Auth\Sisipan` (banner and extra script in the shared layout), startup validation of `SEB_ENFORCEMENT`.
- `infra/tes.sh lokakode` serves the image twice (`disabled` and `enabled`); `infra/smoke.sh` check 10.
- `apps/lokakode/.env` in production: `SEB_ENFORCEMENT` changed from `enabled` to `disabled` on the owner's instruction, before the first deploy of this code.

Config keys for the owner's hardening list, as written to the file: `createNewDesktop` true / `killExplorerShell` false; `monitorProcesses` true, `permittedProcesses` empty, `prohibitedProcesses` (71 entries, `strongKill`, no system process); `allowVirtualMachine` false; `enablePrintScreen`, `allowScreenCapture`, `allowScreenSharing` false; `allowedDisplaysMaxNumber` 1; `sendBrowserExamKey` true.

App Signature Key: researched, **not built** — it exists only for SEB Server sessions.

Verified by the agent, 2026-11-21 (output shown in the session):

- `infra/tes.sh lokakode` → 252 tests, 10878 assertions, pass. 16 of them are new (`tests/Seb/`): Config Key of the downloaded file equals the stored one; both headers needed, for the same configuration and for that exact URL; a header of another URL, another course's keys, the User-Agent and a forged `Host` do not help; staff are not checked; rotation kills the old file and keys; IDOR on the panel (other pengampu 404, pengajar 403, student 403); heartbeat, lease and the two event kinds; banner in `disabled` mode for students only.
- `infra/tes.sh auth` → 105 tests, 986 assertions; `infra/tes.sh portal` → 85 tests, 6161 assertions; `infra/tes.sh judge` → 258 passed. All pass.
- Deployed 2026-11-21 at `48d2539` after `infra/backup.sh` (`backup SELESAI`): `infra/deploy.sh auth`, `lokakode`, `portal`, `judge`, each verified at `/healthz` (the worker by its heartbeat). Migration `000006` ran on the real `lokakode` schema: `seb_configs` and `seb_exam_keys` exist and are empty.
- `infra/smoke.sh` → 32 lulus, 0 gagal, 0 belum aktif; check 10 reports `SEB_ENFORCEMENT=disabled` in `.env` and in the container, with its warning.
- Container log at start: `[lokakode] PERINGATAN: MODE UJI — penegakan SEB dinonaktifkan (SEB_ENFORCEMENT=disabled)`.
- Real requests to `https://lokakode.lokandita.com`: `/seb` 200 (state "belum"), `/seb/periksa` 200, `/seb/keluar` 200, `/seb/unduh/DDP101.seb` 404 (no configuration yet), `/beranda` 302 to the LokaAuth login.
- **Not seen by the agent:** a student page in production with the banner (that needs a student session); the banner is proven only on the test container.
- The commit that holds these lines differs from `48d2539` only in this file and was deployed after it so that `/healthz` reports `HEAD`; that output is in the session.

**Not verified — this is the whole point of the gate:**

- **No real Safe Exam Browser has touched this code.** Every "SEB request" in the tests is a header computed by the test with the formula of `KONTRAK.md` §8.
- **Whether SEB 3.10.2 computes the same Config Key as the server.** Our calculation follows the client's source (`Json.cs`) as read on GitHub, not a published test vector.
- **Whether the Browser Exam Key shown by the SEB Configuration Tool is the one the client sends** for a file the tool did not write. If it is not, there is no way to register a key and enforcement cannot be switched on as built; the agent must be told.
- Whether SEB accepts the file at all (format `gzip("plnd" + gzip(XML))`), the URL filter expressions `https://.<host>`, login through LokaAuth inside SEB, Monaco inside SEB's Chromium, the quit link, and the quit password hash (SHA-256 per the key PDF; the client code that compares it was not read).
- `originalName` of the prohibited processes that are not in SEB's built-in list (browsers, AnyDesk, RustDesk, Quick Assist, ShareX, WhatsApp, Signal, LINE, WeChat, Messenger, bdcam, parsecd, UltraViewer, msra, SnippingTool) was not checked against real Windows binaries; a wrong name may mean that process is not killed.
- The heartbeat script and the banner were not looked at in a browser; the Arabic texts of `Seb.php` were written by the agent and not read by an Arabic speaker.
- Two additions of the agent that were not in the approved plan — the quit link (`quitURL`, button on the student's home page inside SEB), and `/data-saya` staying open without SEB — were **confirmed by the owner on 2026-11-21 ("both yes")**. Whether the quit link actually closes SEB is still part of the gate.
- The terms page (Portal) is unchanged; it gets its sentence on similarity checks in 10b.

Owner's gate for stage 10a (`SPEC.md` §18: "verified with real SEB 3.10.x on Windows from login to submit"). Enforcement stays `disabled` during steps 1–6, so students are not affected:

1. As pengampu open `https://lokakode.lokandita.com/mata-kuliah` → a course → **Safe Exam Browser** → set a quit password → *Buat konfigurasi*. Download the `.seb` file.
2. On Windows with SEB 3.10.2: double-click the file. Expected: SEB starts on a new desktop and shows the LokaAuth login page. **If SEB refuses the file, stop and report the message.**
3. Inside SEB log in as a test **mahasiswa** enrolled in that course. The home page shows the red MODE UJI banner and, because SEB sent its headers, a link **Periksa Safe Exam Browser**. Open it. Expected: both headers "Ya", and for your course **Config Key: cocok**; Browser Exam Key says "tidak cocok" (nothing registered yet). **If the link is missing (no headers arrived) or Config Key says "tidak cocok", stop and report** — the agent must fix the calculation.
4. Open the same `.seb` file in the **SEB Configuration Tool 3.10.2**, tab *Exam*, copy the Browser Exam Key **without saving the file**, paste it into the panel, *Simpan daftar kunci*.
5. Start SEB with the file again and open the check page as in step 3. Expected: **Browser Exam Key: cocok**. If not, report.
6. Still inside SEB as that student: open a meeting, a problem, type, Run, Submit. Try: a second monitor, PrintScreen, Alt+Tab, starting Chrome beforehand (SEB should offer to close it), the quit link on the home page, and closing SEB with the quit password.
7. Only when 2–6 are fine, and every course with active students has a configuration and a key: set `SEB_ENFORCEMENT=enabled` in `apps/lokakode/.env`, commit nothing (the file is not in Git), run `infra/deploy.sh lokakode` and `infra/smoke.sh`. Then check as the test student: an ordinary browser gets the "harus dibuka melalui Safe Exam Browser" page; SEB works from login to submit; a staff account still works in an ordinary browser.

After the gate: tag `tahap-10a`, record the result here.

Gate result for stage 10a, 2026-11-22:

- **The owner wrote: "assume every gate is done."** He did not report the steps one by one. The agent did not watch the gate. Tag `tahap-10a` is set on that instruction, not on evidence seen by the agent.
- What production shows (counts, actions and log lines only):
  - three configurations created through the panel on 2026-11-22 (DDP101, PBO301, TS-PY, each version 1; three `seb.dibuat` audit rows) — step 1;
  - the DDP101 file downloaded once, and one client with `SEB/3.10.2 (x64)` in its User-Agent opening `/beranda`, being redirected, and loading the LokaAuth login page with its stylesheet, script and icon — so SEB 3.10.2 accepted the generated file, and the URL filter lets LokaAuth through — step 2.
- What production does **not** show:
  - **no login from that SEB client** (no request after the login page), and no request for `/seb/periksa` from it: nothing here shows that the Config Key computed by SEB equals the server's (step 3);
  - **`seb_exam_keys` is empty** for all three courses: no Browser Exam Key was registered (step 4), so steps 5 and 6 cannot have happened on this server as written;
  - no `seb.kunci` audit row, no `seb_*` integrity event;
  - `SEB_ENFORCEMENT` is still `disabled` in `.env` and in the container (step 7 not done).
- **Consequence that matters:** with `SEB_ENFORCEMENT=enabled` and no registered key, every student of every course is refused (`kunci_peramban`). Before switching it on: register the Browser Exam Key for each course and see "cocok" twice on `/seb/periksa` from inside SEB.
- The items under "Not verified" above stay open as far as this file knows.
- The tagged commit differs from the deployed `feeffdd` only in this file. **It has not been redeployed**: the working tree holds changes that are not the agent's (`docs/branding/`: one zip deleted, four new directories), and `infra/deploy.sh` refuses a tree with uncommitted changes. Until those are committed or removed and the four components are redeployed, `infra/smoke.sh` check 0 fails. Nothing that runs has changed.

**Stage 10a closed 2026-11-22, tag `tahap-10a`.**

Stage 10a after the tag, 2026-11-22:

- **The owner reported: "Steps 1–6 of the stage 10a gate passed for every course."** The agent did not watch them.
- The entry above ("no Browser Exam Key is registered") was true when it was written and is no longer: the keys were registered afterwards, at about 18:31 WIB. Read from the live database on the owner's request, nothing changed: DDP101 — 104 `AKTIF` students, configuration version 1, 1 key; PBO301 — 103, version 1, 1 key; TS-PY — 28, version 1, 1 key. 235 `AKTIF` students in the active year, none in more than one course. No course with active students lacks a configuration or a key. Audit: 3 `seb.dibuat`, 4 `seb.kunci`.
- The access log shows an SEB 3.10.2 client opening `/beranda`, `/seb/periksa` and `/latihan/…`; what the check page displayed is not in the log and is taken from the owner's report.
- Branding files under `docs/branding/` (12 PNG in four directories; the old zip removed) are the owner's and were committed as they were, on his word, so that `infra/deploy.sh` runs again. `docs/branding/LISENSI.md` still names the removed zip.
- **Enforcement switched on 2026-11-22, about 18:54 WIB, on the owner's instruction** ("do no 3"), after `infra/backup.sh` (`backup SELESAI`): `SEB_ENFORCEMENT=enabled` in `apps/lokakode/.env`, `infra/cek-env.sh` LULUS, `infra/deploy.sh auth`, `lokakode`, `portal`, `judge` verified at `1b3bb15`. Container log at start: `[lokakode] penegakan SEB aktif (SEB_ENFORCEMENT=enabled)`. `infra/smoke.sh` → 32 lulus, 0 gagal, 0 belum aktif; check 10: "SEB_ENFORCEMENT=enabled di .env dan di kontainer: mahasiswa wajib memakai SEB". `https://lokakode.lokandita.com/seb` shows state "berlaku"; `/seb/unduh/DDP101.seb` → 200.
- At the moment of the switch 3 class windows were open and 4 student sessions were alive; the owner had been told that typing in an already open editor is no longer saved after the switch, and chose to go ahead.
- **Not verified by the agent: any real student request under enforcement.** In the minutes after the deploy the log held no student request at all — neither a refusal of an ordinary browser nor a pass from SEB — and the agent does not create sessions in production. The owner's check as a test student (ordinary browser refused; SEB from login to submit; staff unaffected) is still to be reported.
- **The owner reported after the switch: "student cant login without seb while staff can."** The server agrees for the first half: 2 log lines `seb.ditolak` with reason `tanpa_header` and one `seb_rejected` integrity event (one student, rate-limited to one per minute), no 5xx. **Still not reported and not in the log: a student working inside SEB under enforcement** — no request from an SEB client has arrived since the switch. Steps 5–6 of the gate showed SEB working while enforcement was off; the same path with enforcement on is unconfirmed until a student or the owner does it.
- **The owner then reported: "student login inside sb okay."** The server agrees: with enforcement on, a client with `SEB/3.10.2` was answered `200` on `/beranda` (3 times), `/latihan/…` and `/seb/periksa`, and loaded `/js/seb.js`; no refusal of that client, no 5xx. So real SEB 3.10.2 passes both header checks in production. **Not in the log under enforcement:** a problem page, Run, Submit, and a heartbeat (`POST /api/seb/denyut`) — the client was not on a page long enough, or left before 25 s. Those were done by the owner with enforcement off (gate step 6); the heartbeat has never been seen from a real SEB.
- The commit that records this report differs from the deployed `fe84365` only in this file and was **not** redeployed, to avoid restarting the containers under students for a note; `infra/smoke.sh` check 0 fails until the next deploy.
- To switch back: `SEB_ENFORCEMENT=disabled` in `apps/lokakode/.env`, `infra/deploy.sh lokakode`, `infra/smoke.sh`.
- The commit that holds these lines differs from `1b3bb15` only in this file and was deployed after it so that `/healthz` reports `HEAD`.
- **Owner's decision, 2026-11-22: "skip it and tag 10a"** — Run, Submit and the heartbeat from a real SEB under enforcement are not waited for. They stay unconfirmed in this file. The tag `tahap-10a` was already set on `579bfa9` and stays there (as with `tahap-11`, a pushed tag is not moved); everything after it changes only this file and `docs/branding/`. The code that runs is the tagged code.

**Stage 10a finished 2026-11-22, tag `tahap-10a`. Enforcement is on.**

## Stage 10b — integrity and communication (2026-11-22)

Plan approved by the owner in the session. His decisions (`DECISIONS.md`): **code similarity is not built**; the terms and the privacy page get a sentence on typing events instead; notifications are only the three of `SPEC.md` §14; a clarification published to the class shows who asked. `SPEC.md` §13.2 carries a note that similarity was dropped.

Built (LokaKode unless noted; contract in `KONTRAK.md` §17):

- Migration `000007`: tables `notifications`, `clarifications`, `announcements`.
- **Typing anomaly**: `public/js/soal.js` measures what was added to the editor during the last two seconds; at ≥ 300 characters and ≥ 150 per second it reports once to `POST /api/integritas` (`jenis=ketik`), and the server checks both thresholds again and stores `typing_anomaly`. Reset and draft restore are not counted; undo/redo is counted and labelled; `paste_allowed` users are never recorded.
- **Integrity page per class** `GET /kelas/{id}/integritas` with filters and paging, and export `…/ekspor/{csv|xlsx}` (audited as `integritas.diekspor`). Linked from the class page.
- **Notifications** (`pertemuan_dibuka`, `tutup_24jam`, `sandi_direset`): created when the student loads a page, never twice; `GET /notifikasi`, `POST /notifikasi/baca`; unread count in a new student navigation bar (home, meeting, leaderboard, notification and announcement pages).
- **Clarifications**: a tab *Klarifikasi* on the problem page (`GET`/`POST /api/soal/{mp}/klarifikasi`); staff page `GET /kelas/{id}/klarifikasi` and `POST …/{kid}/jawab` (private, or to the class with the asker's name); unanswered count on the class page.
- **Announcements**: `GET`/`POST /kelas/{id}/pengumuman`, `POST …/{aid}/tarik`; students read them on `/beranda` (newest three) and `/pengumuman`. A class that has an announcement or a clarification can no longer be deleted (it says how many).
- Own-data export gains `notifikasi`, `klarifikasi`, `jawaban_klarifikasi`, `pengumuman` (`KONTRAK.md` §14).
- Portal: one sentence on unusual typing in the terms and in the privacy page, `id`, `en`, `ar`; "last updated" is now 22 November 2026.
- `infra/smoke.sh` check 11. No new environment variable. The judge worker and its grants are untouched.

Things that differ from the approved plan, all recorded in `DECISIONS.md` or `KONTRAK.md` §17:

- `clarifications` has no `course_id` column.
- No `tutup_24jam` for a window that is 24 hours long or shorter (its close time is already in "opened").
- Withdrawing an announcement twice is not an error.
- The problem page has no navigation bar, so the unread count is not shown there.

Verified by the agent, 2026-11-22 (output shown in the session):

- `infra/tes.sh lokakode` → **282 tests, 12230 assertions, pass** (252 before this stage). New: schema (4), `tests/Integritas` (7), `tests/Komunikasi` (15: notifications 5, clarifications 5, announcements 5), browser tests `KetikanTest` (3) and `KlarifikasiTabTest` (1) in headless Chromium, and every new route in `MatriksAksesTest` with the cross-class cases (pengajar of another class and pengampu of another course `404`, student `403`). On the SEB-enforced test container the new student routes answer `403` to an ordinary browser.
- `infra/tes.sh auth` → 105 tests pass. `infra/tes.sh portal` → 86 tests, 6221 assertions pass.
- `infra/tes.sh judge` → **257 passed, 1 failed** in the full run: `test_submit_benar_semua_tes_tersembunyi_lulus_dan_hasil_tidak_memuat_tes`. The test searches the worker log for each expected output as bare text; this time the output `784` matched four digits inside a random job id (`…14ab8784d0a3`). Run alone it failed once more and then passed three times in a row. It is a chance match in the test, not a leak, and the judge code has not changed since `tahap-10a` (`git diff --stat tahap-10a HEAD -- judge` is empty). **The test was not changed** (the judge is outside this stage); it will fail by chance again until it compares whole values.
- Production, read only: `portal.teks` holds one override, `depan`/`id`. None for `ketentuan` or `privasi`, so the new sentences will show after deploy.

**Not done and not verified:**

- **Nothing of this stage is deployed.** Migration `000007` has not run on the real `lokakode` schema; `infra/smoke.sh` has not been run with check 11; no real request has been made to a new route in production.
- The typing measurement has been seen only in headless Chromium, never in Safe Exam Browser. It is the browser's measurement; a student who can change the page's script can suppress it.
- The new pages were not looked at by a person, not in dark mode and not in Arabic (RTL); the Arabic texts of `Integritas.php`, `Komunikasi.php`, the new `Latihan.php` keys and the two Portal sentences were written by the agent and not read by an Arabic speaker.
- A password reset made inside LokaAuth by a superadmin produces no notification (by design, stated in `KONTRAK.md` §17).
- The privacy page does not yet say that a classmate sees a student's **name** next to a question whose answer a lecturer publishes to the class. The student is told this on the form itself. Whether the privacy page should say it is the owner's decision.

Before deploy the owner must say: (1) what to do with the uncommitted changes under `docs/branding/` (12 PNG deleted, `LOKANDITA-BRAND.md` and `newbranding.zip` new) — `infra/deploy.sh` refuses a dirty tree; (2) when `lokakode` may restart: enforcement is on, and a student typing at that moment loses what was typed since the last autosave (at most 5 s) and gets an error until the container is back.

Owner's gate for stage 10b (after deploy):

1. As a test student in SEB: paste is still blocked; open a problem, tab **Klarifikasi**, ask a question; after a meeting is opened for the class the navigation shows **Pemberitahuan** with a count.
2. As pengajar of that class, on the class page: **Klarifikasi soal** — answer once *Hanya penanya* and once *Seluruh kelas*; **Pengumuman** — post one; **Integritas** — look at the list and download CSV and XLSX.
3. As a second student of the class: the published answer (with the asker's name) and the announcement are visible, the private answer is not.
4. As pengajar of another class: `/kelas/<id of the first class>/integritas`, `/klarifikasi` and `/pengumuman` answer "tidak ditemukan".
5. Reset the test student's password from the class page; after the forced change the student sees the notification naming who reset it.
6. Read the new sentence on `lokandita.com/ketentuan` and `/privasi` in the three languages.
7. Optional, inside SEB: with an editor tool that inserts a long block at once (or undo of a large deletion), check that **Ketikan tak wajar** appears on the Integritas page.

### Deploy of stage 10b — 2026-11-22 (output in the session)

- Owner's answers: branding files "commit them as they are"; restart "now"; privacy sentence about the asker's name "yes"; fix the judge test "yes".
- Added after the first report (`78201fc`): the privacy page says, in `id`, `en`, `ar`, that classmates see the asker's name next to a clarification question whose answer is published to the class; the judge test now compares each expected output as a whole word of every text in result, progress and log (`kata_teks`), with a guard test that the check still finds a real leak.
- `0ebd1a1`: the owner's branding files committed as they were (12 PNG deleted, `LOKANDITA-BRAND.md` and `newbranding.zip`, 16 MB, added). `docs/branding/LISENSI.md` was not checked against them.
- `infra/cek-env.sh` LULUS; `infra/backup.sh` → `backup SELESAI`; `infra/deploy.sh auth`, `lokakode`, `portal`, `judge` each verified at `0ebd1a1` (`/healthz`; the worker by its heartbeat).
- `infra/smoke.sh` → **39 lulus, 0 gagal, 0 belum aktif**; check 11 passes; check 10: `SEB_ENFORCEMENT=enabled`, unchanged.
- Migration `2026-11-22-000007` is the last row of `lokakode.migrations`; `notifications`, `clarifications`, `announcements` exist and were empty right after deploy.
- Real requests from the internet without a session: `/notifikasi`, `/pengumuman`, `/kelas/1/integritas`, `/kelas/1/integritas/ekspor/csv`, `/kelas/1/klarifikasi`, `/kelas/1/pengumuman`, `/api/soal/1/klarifikasi` of `lokakode.lokandita.com` → `302` to the LokaAuth login with the right `next`. `lokandita.com/ketentuan` and `/privasi` show "Terakhir diperbarui: 22 November 2026" and the new sentences.
- No error line in the `lokakode` container log in the five minutes after the restart.
- `infra/tes.sh judge`, complete, with the fixed test → **259 passed** (it ran while the deploy was going on). `infra/tes.sh portal` for the changed pages → 30 tests pass; the full `lokakode`, `auth` and `portal` suites were last run before `78201fc` (282, 105, 86 pass) and nothing of `lokakode` or `auth` changed after that.
- **Not verified by the agent: any page behind a session in production** — the new student navigation, the Klarifikasi tab, the staff pages, the exports with real data, and the typing event from real SEB. That is the owner's gate (steps above).
- The commit that holds these lines differs from the deployed `0ebd1a1` only in this file and was **not** redeployed, so as not to restart the containers under students a second time; `infra/smoke.sh` check 0 fails until the next deploy.
- After the gate: tag `tahap-10b`.

### Gate result for stage 10b — 2026-11-22

- **The owner wrote: "gate passed."** He did not report the steps one by one. The agent did not watch the gate. Tag `tahap-10b` is set on that instruction, not on evidence seen by the agent.
- **What production shows right after that message (counts only): nothing of the gate.** `clarifications` 0 rows; `announcements` 0 rows; `notifications` 0 rows; in the last 24 hours no audit row `klarifikasi.*`, `pengumuman.*`, `integritas.diekspor` or `sandi.direset`, and one integrity event (`seb_rejected`), no `paste_blocked` and no `typing_anomaly`.
- So on this server, since the deploy: no student has loaded a LokaKode page with an open meeting (that alone would have written a notification), no question was asked or answered, no announcement posted, no integrity export downloaded, and no password reset made through LokaKode. Gate steps 1, 2, 3 and 5 as written leave rows; none exist. Steps 4 and 6 leave none and may well have been done.
- Everything listed under "Not verified" for this stage therefore stays open as far as this file knows: the pages behind a session in production, the typing event in real SEB, dark mode, Arabic.
- The tagged commit differs from the deployed `0ebd1a1` only in this file; not redeployed, so `infra/smoke.sh` check 0 fails until the next deploy.

**Stage 10b finished 2026-11-22, tag `tahap-10b`.**

## Brand scheme and step cards (2026-11-22, after `tahap-10b`; the owner's two tasks of that day)

Owner's decisions: plan approved; TOTP issuer becomes "Lokandita"; `docs/branding/baru/` is committed; the site palette stays; **the Portal's Open Graph image stays the hero photo crop**; "Segera" icons in greyscale; `lokakode` may restart at once.

Built (commit `ea483b6`; contract in `KONTRAK.md` §11 check 12, §15, §16 "Brand files" and "App list"; choices in `DECISIONS.md`):

- `docs/branding/newbranding.zip` extracted as it is to `docs/branding/baru/` (172 files: 7 brands × 24, two previews, a README, a manifest). Every image was measured; all are transparent PNG/WebP or ICO, every brand has logo and icon. Nothing from the archive was run.
- `infra/buat-merek.sh` + `infra/merek/buat-merek.php`: derived files for each site under `apps/<app>/public/`; `--periksa` compares them with what the originals produce.
- Shared layout: brand logo in the header (light/dark), icon links, manifest, `theme-color`. LokaKode landing: new hero logo.
- Portal: `app/Config/Aplikasi.php` — LokaKode (Tersedia, link), LokaUjian, LokaKelas, LokaSiber, LokaRetas, LokaData (Segera, not links); neutral ring for an app without an icon.
- "LokaAuth" → "Akun Lokandita" in 13 texts × 3 languages (`Auth.php` 1, `Kode.php` 7 keys, `Portal.php` 2, plus the upcoming-apps sentence and six app descriptions), the identity site's title, the admin card and the data link on the Portal's `/beranda` and `/data-saya`, and the TOTP issuer.
- Step cards ("Cara memulai" on the Portal, "Cara kerjanya" on LokaKode): CSS only. The number starts the text on the same line, bold in the accent colour; five cards are laid out three and two centred. The card markup exists twice (`apps/portal/app/Views/publik/_depan.php`, `apps/lokakode/app/Views/depan.php`) and was **not** merged; both use the one set of CSS rules.
- Removed as superseded: `merek/apple-touch-icon.png` in the three apps, `merek/logo.png` and `logo.webp` in LokaKode. Replaced in place: `favicon.ico`, `icon-32.png`, `icon-192.png`, LokaKode's `og-image.png`.

Verified by the agent (output shown in the session):

- `infra/tes.sh portal` → **89 tests, 9048 assertions, pass**, at the final code. In headless Chromium, for both landings in id/ar × light/dark × 1280/390: header logo loaded, right variant, 40 px, not squeezed, on the start side; six cards in order, icons 56 × 56, right variant, name beside icon, status under name, only LokaKode a link, "Segera" icons grey; step number on the line of its text, second line under the number, icon in place, rows 4 / 3 + 2 / 1 and centred; contrast ≥ 4.5 : 1; CLS < 0.01. This test caught two real defects before deploy: the header logo was squeezed at phone width, and app names broke inside a word when name and status shared a line.
- `infra/tes.sh auth` → 109 tests, 1642 assertions, pass. `infra/tes.sh lokakode` → 285 tests, **284 pass, 1 failed**: the new brand test found the LokaKode header logo file 235 px wide where the page says 117 × 2. Fixed in the generator (even widths). **The full `lokakode` and `auth` suites were not run again after that fix and after the step-card CSS**; run again were `infra/tes.sh lokakode tests/Publik` (10 tests pass), `infra/tes.sh auth tests/Login/MerekTest.php` (4 tests pass) and the whole Portal suite (which measures the LokaKode landing too).
- `infra/buat-merek.sh --periksa` → LULUS. `infra/cek-env.sh` → LULUS; no environment variable was added. `infra/backup.sh` → `backup SELESAI`.
- `infra/deploy.sh auth`, `portal`, `lokakode`, `judge` → each verified at `ea483b6` (`/healthz`; the worker by its heartbeat). `infra/smoke.sh` → **44 lulus, 0 gagal, 0 belum aktif**, check 12 included.
- CSP header of `https://lokandita.com/`, `https://auth.lokandita.com/login` and `https://lokakode.lokandita.com/` after deploy: character for character what was captured before the change, apart from the nonce. The tests compare it with those strings too.
- Lighthouse 12.8.2, mobile, `https://lokandita.com/`, three runs each:

  | | performance | accessibility | best practices | FCP | LCP | CLS | page weight | requests |
  |---|---|---|---|---|---|---|---|---|
  | before (`0ebd1a1`) | 99 / 95 / 98 | 100 | 100 | 0.8–1.2 s | 1.1–1.4 s | 0 | 117 kB | 6 |
  | after (`ea483b6`) | 96 / 99 / 100 | 100 | 100 | 0.8–0.9 s | 1.1 s | 0 | 101 kB | 13 |

  More requests (six card icons and the logo, 4 kB each, the cards lazily) and less weight (the old header icon was a 44 kB file shown at 36 px).
- Screenshots: `docs/tangkapan/merek/` — 24 from production (`portal-depan-`, `login-`, `lokakode-depan-` × id/ar × terang/gelap × desktop/ponsel), the step sections from the test containers (`langkah-*.png`), the tab icons on light and dark tab colours (`ikon-tab.png`), the logos on the site's colours (`logo-di-atas-warna-situs.png`).

**Not verified, or weaker than it looks:**

- **No person has looked at the deployed pages.** The tab icon in a real browser tab, the icon on an iPhone home screen, and how a shared link looks in WhatsApp or Telegram (Open Graph) were not seen; link previews are cached by those services for days.
- The 16 px icon is not recognisable (it is the original, unchanged). Safari ignores the dark variant of the tab icon.
- English and Arabic renderings of "Akun Lokandita" and the six app descriptions were written by the agent; the Arabic was not read by an Arabic speaker.
- The neutral ring for an app without an icon is tested as a view partial and as served CSS; no live page shows it, because all seven brands have icons.
- Pages behind a session (the `/beranda` of the Portal with the renamed admin card, the LokaKode staff pages with the renamed texts, the TOTP enrolment page) were tested in containers, not opened in production.
- Existing TOTP entries on phones are still labelled "LokaAuth".
- `/admin/teks` → *aplikasi* now has six fields; an override of that page saved before this deploy would have the old shape and be ignored (`teks.bentuk_usang`). Production had none.
- At widths between 640 and 1024 px five step cards are 2 + 2 + 1; the "none alone" layout holds from 1024 px.
- The commit that holds these lines and the screenshots differs from the deployed `ea483b6` only in `PROGRESS.md` and `docs/tangkapan/`; it was **not** redeployed, so `infra/smoke.sh` check 0 fails until the next deploy.

Owner's check:

1. Open the three sites in a normal browser, light and dark: tab icon, header logo, the six cards, the step cards; then the same in Arabic.
2. Read the changed sentences on `lokandita.com/tentang` and `/privasi` in three languages.
3. As superadmin: the card "Akun Lokandita" on the Portal's beranda; in LokaKode, enroll a nomor induk that does not exist and read the message.

## Stage 13 — problem production (from 2026-11-23)

Task from the owner: DDP101 meetings 4–7, then PBO301 meetings 1–7; 8 / 8 / 6 / 6 / 4 per meeting in `id`, `en`, `ar`; left `PENDING_REVIEW`; no approval, publishing or class window by the agent; TS-PY untouched. Plan and the owner's decisions of 2026-11-23: `DECISIONS.md`.

How one meeting is made (the next session repeats this for the next row):

1. `docs/soal/buat-<course>-pertemuan-<n>.py` (problems) and `docs/soal/cek-<course>-pertemuan-<n>.py` (separate Python calculation per problem, and every example quoted in a statement); `python3 docs/soal/buat-….py` writes the JSON.
2. `judge/.venv/bin/python docs/soal/periksa.py docs/soal/<file>.json <allowed concepts of the meeting, read from production>` — must end with "32 dari 32 soal lulus".
3. `docker exec -i lokakode php spark soal:impor --oleh <superadmin> --nama <file>.json < docs/soal/<file>.json` — must end with `32 diterima, 0 ditolak`.
4. `python3 docs/soal/lembar-tinjauan.py docs/soal/<file>.json` writes the review sheet; one commit per meeting; update the table.

| Course | Meeting | Built | Imported | Review sheet | Reviewed by owner | Approved |
|---|---|---|---|---|---|---|
| DDP101 | 4 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-4.md` | no | yes, 2026-11-23 |
| DDP101 | 5 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-5.md` | no | yes, 2026-11-23 |
| DDP101 | 6 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-6.md` | no | yes, 2026-11-23 |
| DDP101 | 7 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-7.md` | no | yes, 2026-11-23 |
| PBO301 | 1 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-1.md` | no | yes, 2026-11-23 |
| PBO301 | 2 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-2.md` | no | yes, 2026-11-23 |
| PBO301 | 3 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-3.md` | no | yes, 2026-11-23 |
| PBO301 | 4 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-4.md` | no | yes, 2026-11-23 |
| PBO301 | 5 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-5.md` | no | yes, 2026-11-23 |
| PBO301 | 6 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-6.md` | no | yes, 2026-11-23 |
| PBO301 | 7 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-7.md` | no | yes, 2026-11-23 |

Before any problem was written (read-only queries, output in the session): the eleven meetings are all `cpp`, enabled, target 60, minimum 8 / 8 / 6 / 6 / 4, unpublished, without problems. Their added concepts equal `docs/PETA-KONSEP.md` **except DDP101 meeting 6, which has `FUNGSI, PARAMETER_REF` and no `REFERENSI`** (the "BEDA" of stage 12); meeting 7 inherits the gap. Reported to the owner, who approved going on; it was still unticked when meeting 4 was imported. PBO301's base concepts are the 21 of the 2026-11-19 decision.

### DDP101 meeting 4 — Struktur Kontrol: Percabangan (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points against 120 needed. Reference solutions add only `if`, `switch` and the logical operators to what meetings 1–3 allow; no loop, array or own function.
- `periksa.py` with the seven allowed concepts → `32 dari 32 soal lulus`: each problem 240 tests through the judge's `soal.validasi` with no warning; reference output equal to the separate Python calculation on 36 generated tests each; 72 examples quoted in statements equal to the reference output.
- `spark soal:impor` under the superadmin account → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), 0 problems attached to the meeting, meeting not published, one `soal.impor` audit row.
- Weaker than it looks:
  - **Nobody but the agent has read these statements in any language.** English and Arabic were written by the agent and carry the machine-translation badge.
  - The words a program must print (`positif`, `LULUS`, `sama kaki`, `bentrok … menit`, …) are Indonesian in all three language versions; the English and Arabic statements explain each word once.
  - The "separate calculation" was written by the same agent as the reference solution. It is a second implementation in another language and style, not a second person.
  - Student code is never restricted: "without loops and without arrays" in the four hard problems is advice, not a rule.
  - `kategori-imt` prints a decimal; inputs within 0.001 of a category limit or of a rounding half are never generated, so answers do not depend on the order of the floating-point operations.
  - Difficulty labels are the agent's judgement until calibration data exists.
  - The statements were changed from tables to lists **after** the judge check and before the import; solutions and generators were not touched, and the importer validated the final file itself.

### DDP101 meeting 5 — Struktur Kontrol: Perulangan (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. Loops over data read one item at a time; no array and no own function in any reference solution. Syllabus items covered: `for`, `while`, sentinel loops, `break` / `continue`, nested loops and patterns, simple statistics, searching a value.
- `periksa.py` with the ten allowed concepts → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to the separate Python calculation; 48 quoted examples equal).
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), meeting not published.
- Weaker than it looks:
  - The same as meeting 4: read by nobody but the agent, English and Arabic by the agent, Indonesian output words in all three versions, difficulty labels unproven.
  - Pattern problems (`ketupat-berongga`, `papan-catur`, the star shapes) need exact leading spaces; only trailing spaces are forgiven. A wrong pattern is "wrong output" without a hint.
  - `tripel-pythagoras` with three plain nested loops and no `break` is about 4.5 million steps at n = 300 and passes the 2 s limit easily; the statement's remark about stopping early is advice, not something the judge enforces.
  - `rata-rata-n` and `statistik-nilai` print two decimals; inputs whose average falls within 0.001 of a rounding half are never generated.
  - No `do … while` problem demands that form: every problem can be solved with `for` or `while`.

### DDP101 meeting 6 — Fungsi dan Parameter Passing (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. Every statement names the function to write (for example `void tukar(int &a, int &b)`); 11 problems are built around by-reference parameters. No array, recursion, overloading or default argument in any reference solution.
- `REFERENSI` was still not ticked for this meeting in production when the file was checked and imported (12 allowed concepts), so no reference solution declares a reference variable; by-reference parameters are `PARAMETER_REF` and allowed.
- `periksa.py` with those 12 concepts → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to the separate Python calculation; 59 quoted examples equal).
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), meeting not published.
- Weaker than it looks:
  - **These problems do not require a function in the student's code.** `required_concepts_in_submission` is empty: a student who writes everything in `main` and prints the right output gets full marks. The owner approved a requirement for recursion only; requiring `FUNGSI` or `PARAMETER_REF` here is his decision and would be a new version of each problem.
  - Overloading and default arguments, which the syllabus lists for this meeting, are in meeting 7, where the concept map first allows them.
  - Inline functions from the syllabus have no problem: `inline` cannot be seen in output.
  - `keliling-segitiga-titik` prints two decimals from `sqrt`; inputs whose perimeter falls within 0.001 of a rounding half are never generated.
  - `romawi-ke-angka` refers to "soal pertemuan lalu" (the Roman numeral problem of meeting 4).
  - Otherwise as meetings 4 and 5: read by nobody but the agent; English and Arabic by the agent; Indonesian output words in all three versions.

### DDP101 meeting 7 — Fungsi Lanjutan, Rekursi, dan Debugging (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points: 28 recursion problems and 4 on overloading and default arguments (`luas-dua-bentuk`, `volume-bawaan`, `gabung-atau-jumlah`, `format-waktu`).
- **The 28 recursion problems carry `required_concepts_in_submission: ["REKURSI"]`** (owner's decision): correct output without a recursive function gives `CONCEPT_NOT_MET` and half the score. Each of those statements says so. Production shows the requirement stored on 28 versions and empty on 4.
- `periksa.py` with the 14 allowed concepts → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to the separate Python calculation, which uses no recursion anywhere — closed formulas, loops, simulation, `math.comb`, `itertools`; 63 quoted examples equal; every required concept is present in its own reference solution).
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), meeting not published.
- Skipped from the syllabus of this meeting: function templates, lambdas and functions as parameters (their concepts — `TEMPLATE_FUNGSI`, `MODERN_CPP`, `POINTER` — are not allowed in DDP101 at meeting 7), and the debugging material (GDB, breakpoints, watch variables), which output cannot judge. Scope and lifetime of variables have no problem of their own; `hitung-panggilan` touches them through a by-reference counter.
- Weaker than it looks:
  - **`CONCEPT_NOT_MET` has not been exercised with these problems.** I checked that the requirement is stored and that the reference solutions satisfy it; no student-style submission without recursion was sent through the deployed judge, because that needs a student session and an open class window, which are the owner's.
  - The recursion check is presence only: one recursive function anywhere in the file satisfies it, even if the answer is computed by a loop.
  - The four overloading problems require nothing: a student can solve them with one function and an `if`.
  - Several recursive reference solutions are deliberately exponential (`fibonacci` to 30, `kombinasi` to C(25, 12), `partisi` to 60, `catalan` to 15); the slowest validated run stayed under half the 2 s limit (no `reference_slow` warning). A student who calls `langkah(n − 1)` twice in `hanoi-langkah` gets a time limit at large n; the statement warns about it.
  - Otherwise as the earlier meetings: read by nobody but the agent; English and Arabic by the agent.

**DDP101 meetings 4–7 are all built and imported; 128 problems await the owner's review.**

### PBO301 detection rules, checked on the real analyser (2026-11-23)

Twelve probe programs were run through `kotak.analisis` (the judge's own analyser container; output in the session). They confirm `docs/DETEKSI-KONSEP.md` and add what the owner should know before reviewing PBO301 problems:

- Any `class` with a body counts as `CLASS_OBJECT`, used or not: `class X {};` above purely procedural code satisfies the requirement. A `struct` with a member function counts too.
- A class whose data members sit in the default-private part of `class`, or under `private:` / `protected:`, is `ENCAPSULATION`. Reference solutions for meetings 2 and 3 therefore put everything under `public:`.
- **An association that exists only as a method parameter (`void baca(const Buku &b)`) is not `KOMPOSISI_AGREGASI`**; only a data member of another class type is. Meeting 5 problems on pure association cannot require that concept.
- `class Darat : virtual public Kendaraan` is `INHERITANCE` and not `POLYMORPHISM`. A method `info()` that calls `Kendaraan::info()` is reported as `REKURSI` (a false positive; harmless in PBO301, where `REKURSI` is a base concept).
- `enum class`, `constexpr`, `static` members, `const` methods, `friend`, `this->`, `= default` / `= delete` are not counted as any concept.

### PBO301 meeting 1 — Pengantar OOP, C++ Modern, UML (2026-11-23)

- 32 procedural problems in modern C++ style, 8 / 8 / 6 / 6 / 4, 232 points: range-based `for` over arrays and strings, `auto`, `nullptr` with `new` / `delete`, uniform initialisation, lambdas, `enum class`, structs, arrays and matrices, a linked list. No class, STL container, STL algorithm or exception in any reference solution (22 allowed concepts: the 21 base concepts and `MODERN_CPP`).
- `periksa.py` → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to the separate Python calculation; 43 quoted examples equal).
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), meeting not published.
- Skipped from the syllabus of this meeting: UML diagrams and UML tools (not judgeable from output); the comparison of paradigms as prose; and, by the owner's decision of 2026-11-23, the history of C++ and the toolchain setup.
- Weaker than it looks:
  - **Nothing forces the modern style.** No concept is required in the student's code: a C-style solution with index loops and `NULL` gets full marks. The statements recommend the modern construct; the judge only sees output.
  - These are in effect review problems on arrays, strings, structs and pointers (DDP101 meetings 9–12 and SDA), which is what a meeting "before classes" leaves to practise.
  - `std::vector` is not used anywhere although modern C++ practice would use it: `STL_CONTAINER` is not allowed until meeting 13. Students may use it; reference solutions may not.
  - Read by nobody but the agent; English and Arabic by the agent.

### PBO301 meeting 2 — Class, Object, Class Diagram (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. **Every problem carries `required_concepts_in_submission: ["CLASS_OBJECT"]`** and its statement names the class, the attributes and the methods expected, and says that correct output without a class gets half the marks. Covered: attributes and methods, objects as parameters and return values, arrays of objects, `this` and chaining, static members, the syllabus examples `Prajurit` (Soldier), `Kendaraan` (Vehicle) and `Senjata` (Weapon).
- Reference solutions keep every member under `public:`, have no constructor or destructor, and never hold an object of another class (those concepts arrive at meetings 4, 3 and 5). Objects are filled through public members, aggregate initialisation or default member values.
- `periksa.py` with the 23 allowed concepts → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to a separate, class-free Python calculation; 44 quoted examples equal; `CLASS_OBJECT` present in every reference solution).
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), the requirement stored on all 32, meeting not published.
- Skipped from the syllabus of this meeting: UML class diagrams and the mapping between diagram and code; the split into `.h` and `.cpp` with include guards (a submission is one file); `constexpr`.
- Weaker than it looks:
  - **The judge checks only that some class exists in the file.** It does not check the class name, the attributes or the methods named in the statement, nor that the class is used. `class X {};` above procedural code passes. Whether students really wrote the class asked for needs a look at their code.
  - **Public data is taught here on purpose and untaught at meeting 4.** A student who already writes `private:` is not penalised (student code is never restricted), but the reference solutions and the statements show public attributes.
  - `CONCEPT_NOT_MET` has not been exercised with a real student submission (same reason as DDP101 meeting 7).
  - Read by nobody but the agent; English and Arabic by the agent; Indonesian output words in all three versions.

### PBO301 meeting 3 — Constructor, Destructor, Copy Semantics (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. All 32 require `CLASS_OBJECT` and `CONSTRUCTOR_DESTRUCTOR` in the student's code; the 14 copying problems also require `COPY_SEMANTICS` (stored so in production). Every statement names the class and members and states the requirement.
- **Order is observable, as asked.** In 18 problems the constructors and destructors themselves print a line (in one more the assignment operator does), and the input decides names, counts and scopes: reverse destruction of locals, blocks, loop bodies, `new` / `delete` in a chosen order, `new[]` / `delete[]`, temporaries, by-value parameters, delegating constructors, global / `static` local / local lifetimes, early `return` (RAII). The other problems make copying observable instead: deep versus shallow copy shown by changing a copy, counted copies and assignments, chained assignment.
- `periksa.py` with the 25 allowed concepts → first run **31 of 32**: `simulasi-cakupan` could generate an input with no object in it, so the expected output was empty (the judge's warning `reference_empty_output`); the generator now always creates at least one object and the constraint says so. Second run, whole file → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to a separate Python simulation that derives the order from the language rules without any object mechanism; 38 quoted examples equal).
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), meeting not published.
- Skipped from the syllabus of this meeting: the debugger demonstration of a double free; `explicit`, `= delete` and other points whose substance is "this does not compile".
- Weaker than it looks:
  - **The requirement is "a constructor or a destructor exists"** — either one satisfies it — and for the copy problems "a copy constructor, an `operator=` or `std::move` exists". What forces the real thing is the output, not the concept check.
  - **A student can print the expected lines without any object lifetime behind them** (plain `cout` in the right order) and add an unused class with a constructor: output right, concept present, full marks. The judge cannot tell; only reading the code can.
  - Problems whose output would depend on copy elision were avoided on purpose: no class whose copy constructor prints is ever returned from a function. `parameter-nilai` relies on the by-value parameter being destroyed before the next statement of the caller prints; GCC 13 does that, and the standard allows nothing later than the end of the calling full-expression.
  - Reference solutions still keep all data `public` (encapsulation is meeting 4), which looks odd next to rule-of-three classes that own memory.
  - `objek-statis` has only six possible inputs (n from 0 to 5) and `barisan-prajurit` ten; a student's twelve tests repeat.
  - Read by nobody but the agent; English and Arabic by the agent.

### PBO301 meeting 4 — Encapsulation and Access Control (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. All 32 require `CLASS_OBJECT` and `ENCAPSULATION` in the student's code (stored so in production); every statement names the class and members and states the requirement. The problems are built around **invariants**: data under `private:`, validating setters, read-only getters, `const` methods, `mutable`, `friend` functions and a `friend` class, private static members, private helper methods; the output shows each illegal change being refused.
- Reference solutions hold no object of another class (meeting 5) and use no STL container.
- `periksa.py` with the 26 allowed concepts → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to a separate Python calculation; 39 quoted examples equal). `kartu-atm` had dead code in its generator, removed before the run.
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), `["CLASS_OBJECT", "ENCAPSULATION"]` on all 32, meeting not published.
- Skipped from the syllabus of this meeting: UML visibility notation and `{readOnly}`; the cases whose substance is "this does not compile" (access to a private member, a non-const method on a const object).
- Weaker than it looks:
  - **The requirement is "some `private:` / `protected:` section or non-public data member exists".** Whether the data is really protected is not checked: a class with one unused private field next to public data passes. The refusals in the output can be produced by plain `if` statements outside any class.
  - `CONCEPT_NOT_MET` has still not been exercised with a real student submission.
  - Read by nobody but the agent; English and Arabic by the agent.

### PBO301 meeting 5 — Relations between Objects, Object Diagram (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. All 32 require `CLASS_OBJECT` and `KOMPOSISI_AGREGASI` in the student's code (stored so in production); every statement names the classes and members and states the requirement.
- The three relations of the syllabus and how the output shows them: **composition** — a member object, with the constructor / destructor order of part and whole printed; **aggregation** — a pointer to an object owned elsewhere: the part outlives the whole, and a change to the part is seen by everyone who refers to it; **association** — a pointer that may be empty (0..1), many (1..*), or two-way.
- Every reference solution keeps the relation as a **data member** of another class type, because that is what the detector recognises. No STL container (plain arrays and pointers).
- `periksa.py` with the 27 allowed concepts → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to a separate procedural Python calculation; 35 quoted examples equal). Before that run the quoted example of `kamar-hotel` lacked one output line (`Kamar 5: Citra`); the comparison of the examples with the separate calculation caught it and it was corrected in the three languages.
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), `["CLASS_OBJECT", "KOMPOSISI_AGREGASI"]` on all 32, meeting not published.
- Skipped from the syllabus of this meeting: UML notation for the relations and object diagrams (not judgeable from output).
- Weaker than it looks:
  - **The detector does not tell the three relations apart.** Any data member of another user class type satisfies the requirement; a student who uses composition where the statement asks for aggregation passes whenever the output happens to agree.
  - **An association held only through a method parameter is not detected** and would give `CONCEPT_NOT_MET` (half marks) although it is legitimate design. The statements therefore ask for the relation as a member.
  - `CONCEPT_NOT_MET` has still not been exercised with a real student submission.
  - Read by nobody but the agent; English and Arabic by the agent.

### PBO301 meeting 6 — Inheritance (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. All 32 require `CLASS_OBJECT` and `INHERITANCE` in the student's code (stored so in production); every statement names the classes and members and states the requirement.
- Covered, each made visible in the output: inherited members used by the derived class, `protected`, passing arguments to the base constructor, **constructor / destructor order in a hierarchy** (base first, derived first on destruction; bases, then members, then body; multilevel), calling `Base::method()`, name hiding and `using Base::name;`, multilevel, multiple, private and **virtual inheritance** (the diamond, with the bottom class naming the virtual base itself).
- **No `virtual` member function anywhere** (`POLYMORPHISM` arrives at meeting 7): every call is static, a derived method hides the base one, and objects are used through their own type. `tanpa-virtual` shows the base version being called through `const Hewan &` on purpose, as the lead-in to meeting 7.
- `periksa.py` with the 28 allowed concepts → `32 dari 32 soal lulus` on the first run (240 judge tests per problem, no warning; 36 generated tests per problem equal to a separate procedural Python calculation; 34 quoted examples equal). So the analyser does accept `class B : virtual public A` and `using Base::name;` without reporting a concept outside the list.
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), `["CLASS_OBJECT", "INHERITANCE"]` on all 32, meeting not published.
- Skipped from the syllabus of this meeting: UML generalisation notation; the effects of `protected` / `private` inheritance that are compile errors; "when to prefer composition" as prose.
- Weaker than it looks:
  - **The requirement is "some class has a base clause".** The hierarchy named in the statement is not checked; one empty derived class next to flat code passes. Constructor-order lines can be printed with plain `cout`.
  - **Changed while writing:** `duel-ksatria-penyihir` first said "at most 20 rounds", under which a draw could never happen (the knight always wins or loses within 10). It is now 6 rounds and every third test is built to end in a draw.
  - Several statements dictate a design that a careful programmer would question (a `Kubus` deriving from `Persegi`, `protected` data everywhere). They follow the syllabus examples; the owner may want different ones.
  - `roda-kendaraan` and `tanpa-virtual` have only two kinds of input (apart from the name), so a student's tests repeat.
  - `CONCEPT_NOT_MET` has still not been exercised with a real student submission.
  - Read by nobody but the agent; English and Arabic by the agent.

### PBO301 meeting 7 — Polymorphism and Virtual Functions (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points. All 32 require `CLASS_OBJECT`, `INHERITANCE` and `POLYMORPHISM` in the student's code (stored so in production); every statement names the classes and members and states the requirement.
- Covered, each made visible in the output: a call through a base pointer or reference reaching the derived method; virtual against ordinary methods through the same pointer; **virtual destructors and their order** on `delete` through a base pointer; object slicing (by-value parameter, array of base objects against array of base pointers); a virtual call inside a base constructor; `Base::method()` from an override; overriding only some methods over three levels; `final`; `dynamic_cast`; and designs that rest on polymorphism (template method, factory function, undoable commands, filter chain, composite file tree, expression tree, board pieces, scheduler).
- **No pure virtual function** (`= 0`, `ABSTRACT_CLASS`, meeting 9): every base class has default behaviour. **No STL container** (meeting 13): polymorphic objects live in arrays of base pointers with `new` / `delete`; every `new` has its `delete`, and every base deleted through a base pointer has a virtual destructor.
- `periksa.py` with the 29 allowed concepts → first run **31 of 32**: `virtual-atau-biasa` read only a kind word, so every seed produced the same input (the judge's warning `generator_ignores_seed`). The problem now also reads a unit name and prints it. Second run, whole file → `32 dari 32 soal lulus` (240 judge tests per problem, no warning; 36 generated tests per problem equal to a separate Python calculation without classes or virtual calls; 43 quoted examples equal). `dynamic_cast` is accepted by the analyser without a concept outside the list.
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), the three required concepts on all 32, meeting not published.
- Skipped from the syllabus of this meeting: UML notation; how the vtable works and what a virtual call costs; the cases whose substance is "this does not compile" (`override` without a base method, overriding a `final` method).
- Weaker than it looks:
  - **The requirement is "a base clause exists and some member is `virtual` / `override` / `final`".** Whether the call really goes through a base pointer is not checked. Most of these outputs can be produced with `if` on the kind word; an unused two-class hierarchy with one `virtual` then earns full marks.
  - **Without pure virtual functions some base classes are artificial**: `Bentuk::luas()` returns 0, `Operasi::hitung()` returns 0, `Bidak::huruf()` returns `?`. At meeting 9 these become abstract; here they are defaults nobody should call.
  - `final` and `dynamic_cast` cannot be seen in the output: `sensor-final` and `hitung-kucing` ask for them in the statement only.
  - `pohon-ekspresi` accepts single-digit operands only, and `papan-bidak` has no capturing; both are simplifications to keep the statements short.
  - `CONCEPT_NOT_MET` has still not been exercised with a real student submission, in any of the eleven meetings.
  - Read by nobody but the agent; English and Arabic by the agent.

### Approval of the eleven meetings (2026-11-23)

- The owner wrote "setujui semua soal yang sudah dibuat tinggal diterbitkan saja". Approved with `php spark soal:setujui <course> <meeting> --oleh <superadmin>` for each of the eleven meetings (`KONTRAK.md` §7; the same `Tinjau::setujui` as the web action, audit rows `lewat: cli`). No table was written directly.
- Output: 32 approved in nine meetings; **DDP101 meeting 4: 21 approved, 11 skipped; PBO301 meeting 1: 31 approved, 1 skipped.** The twelve skipped were already `APPROVED`: their `reviewed_at` is 22:46–22:49 UTC, a few minutes before the command ran (22:50–22:51), by one reviewer — approved in the web panel, not by the agent.
- Read back from production: DDP101 4–7 and PBO301 1–7 each show 32 versions `APPROVED` and 32 rows in `meeting_problems`; `published_at` is NULL for all eleven.
- The "reviewed" column of the table stays "no": the owner approved without the agent knowing which statements were read. The weaknesses listed per meeting above still hold.
- Not done by the agent, by instruction: publishing the meetings and opening class windows.


## Stage 13 — TS-PY (from 2026-11-23)

Task from the owner (2026-11-23): TS-PY only; DDP101 and PBO301 untouched. Practice problems for meetings 1–5; a practical mid-term held in LokaKode covering meetings 1–5; everything else switched off, kept for reference. No pandas, NumPy or Matplotlib in any problem; the `judge-python` image is not changed.

Owner's decisions of 2026-11-23:

1. **Numbering follows RPS Rev2 (option B):** 7 = "Review Integratif dan Simulasi UTS" (off), **8 = "Ujian Tengah Semester (UTS) — Praktik" (exam)**, 9–16 retitled per Rev2 (off). Meetings 6–16 carry no concepts, so the exam inherits exactly the 13 concepts of meetings 1–5. The exam covers meetings 1–5 only (the owner's instruction; Rev2 itself says P1–P6).
2. The layout is applied by the agent in one audited transaction.
3. During the exam a student sees after Submit only "kiriman diterima" and compile errors — no verdict, no tests passed, no points. Run on sample tests works as usual.
4. A "Buka selama 150 menit" button: a hard server cut-off and a countdown; manual close stays possible.
5. After release a student sees the total and points per problem number; no statements; never a leaderboard for an exam.

Steps:

| Step | State |
|---|---|
| 1 — meeting layout | **Applied 2026-11-23** on the owner's "go": one transaction on the 16 TS-PY meeting rows (titles per Rev2; 6, 9, 10 switched off; concepts of 6–16 removed), 14 audit rows `pertemuan.diubah` with `lewat: sql …`; DDP101 and PBO301 rows untouched (read back, output in the session). `benih.json`, `BenihKurikulumTest` (4 tests pass) and `docs/PETA-KONSEP.md` §4, §6 are in the same commit. Meeting 8 stays switched on, because a switched-off meeting cannot get a class window; it is still an ordinary practice meeting until step 2 is deployed. The deployed container still carries the old `benih.json` until the next deploy; nobody runs the seed in between. |
| 2 — exam-type meeting | **Deployed 2026-11-23, 20:04–20:06 WIB** (commit `6298a26`, migration `2026-11-23-000008` applied). Meeting 8 was set to `ujian` on the owner's order at 21:49 WIB by one audited SQL statement (read back: `ujian`, not published, no window, 47 other meetings `latihan`). **No exam page has been seen in a browser.** See "Exam-type meeting" below. |
| 3 — practice problems, meetings 1–5 | see table |
| 4 — mid-term problems, meeting 8 | Composition approved by the owner 2026-11-23 (6 problems, 3 + 5 + 8 + 10 + 10 + 15 = 51 points). Written and checked; **imported 2026-11-23 23:05 WIB, `PENDING_REVIEW`; kept out of Git until after the exam**. See "TS-PY meeting 8" below. |

| Course | Meeting | Built | Imported | Review sheet | Reviewed by owner | Approved |
|---|---|---|---|---|---|---|
| TS-PY | 1 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ts-py-pertemuan-1.md` | not stated | **yes, 2026-11-24, on his order** |
| TS-PY | 2 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ts-py-pertemuan-2.md` | not stated | **yes, 2026-11-24, on his order** |
| TS-PY | 3 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ts-py-pertemuan-3.md` | not stated | **yes, 2026-11-24, on his order** |
| TS-PY | 4 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ts-py-pertemuan-4.md` | not stated | **yes, 2026-11-24, on his order** |
| TS-PY | 5 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ts-py-pertemuan-5.md` | not stated | **yes, 2026-11-24, on his order** |
| TS-PY | 8 (UTS) | yes, 6 problems, 51 points; judge check passed 2026-11-23; **files on the server only, not in Git** | yes, 23:05 WIB: `6 diterima, 0 ditolak` | `docs/soal/tinjauan/ts-py-pertemuan-8.md` on the server (all six statements in full) | not stated | **yes, 2026-11-24, on his order** |

How one meeting is made: as in "Stage 13 — problem production" above, with `buat-ts-py-pertemuan-<n>.py`, `cek-ts-py-pertemuan-<n>.py`, and `waktu_ms=5000`.

### TS-PY meeting 1 — Pengenalan Pemrograman, Algoritma, dan Python (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points, in id, en, ar. Reference solutions use only `input()`, `int(input())`, `print()` and `+ - * / // % **`: no `if`, loop, function, list, formatting, `round()`, conversion outside reading, string concatenation, or import.
- Contexts: concrete volume (the RPS practicum), material quantities (bricks, tiles, bars, stirrups, mix proportions, bill of quantities), unit conversion, column stress, slope, channel discharge, rainfall on a roof and a storage tank, earthwork (trucks, haul cycles, trench and footing excavation), traffic counts and passenger car units, levelling readings, interpolation between pegs.
- `periksa.py` with `IO_DASAR,ARITMETIKA,VARIABEL_TIPE` → `32 dari 32 soal lulus` on the first run (240 judge tests per problem, no warning; 37 tests per problem equal to the separate calculation; 33 quoted examples equal).
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 versions `PENDING_REVIEW` (8 / 8 / 6 / 6 / 4), 0 attached to the meeting, meeting not published.
- Weaker than it looks:
  - **Nobody but the agent has read these statements.** English and Arabic were written by the agent and carry the machine-translation badge. Output words (`Proyek:`, `Sisa … sak`, `Truk penuh:`, …) are Indonesian in all three versions.
  - **Fourteen problems print a fraction and are compared in `float` mode** (tolerance 1e-6): a student who rounds to two decimals is marked wrong, and each of those statements says not to round. Sample outputs are what Python prints, so a few look long (`148.14814814814815`).
  - **A prompt inside `input("…")` makes every answer wrong**; each statement warns about it, but first-week students will still do it.
  - "Sulit" here means several steps of unit handling and whole-number rounding; with arithmetic only, nothing is algorithmically hard. Difficulty labels are the agent's judgement.
  - The practice factors (motorcycle 0.25 and truck 2.5 passenger car units, d² ÷ 162, π = 3.14, VAT 11 %) are labelled "parameter latihan" in the statements; the owner should confirm he wants these values.
  - `galian-pondasi` takes the trench length on the centre line, 2 × (p + l), with no corner correction; `sengkang-balok` counts L // s + 1 stirrups. Both rules are stated in the problem; a lecturer in structures may prefer other conventions.
  - The separate calculation was written by the same agent, by a different route (exact fractions, `datetime`, simulation loops).

### TS-PY meeting 2 — Variabel, Tipe Data, Operasi Matematika, dan Satuan (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points, in id, en, ar. Reference solutions add `float(input())`, `int()` / `float()` / `round()` on values, `import math` (`sqrt`, `pi`, `sin`, `cos`, `atan`, `degrees`, `radians`, `ceil`) and f-strings with a format, printed directly. No `if`, loop, function, list or string operation.
- Contexts: unit conversion (mm ↔ m, km/h ↔ m/s, MPa ↔ kg/cm², m² ↔ ha, decimal hours and degrees), self-weight of a beam and hydrostatic pressure (the RPS practicum), cylinder strength, water content, map scale, slope angle, pipe and Manning discharge, the rational formula, Mononobe intensity, deflection and bending stress, polar coordinates, stopping distance, required reinforcement, a triangular plot, travel time.
- `periksa.py` with `IO_DASAR,ARITMETIKA,VARIABEL_TIPE,KONVERSI_TIPE,FORMAT_OUTPUT` → `32 dari 32 soal lulus` on the first run; 34 quoted examples equal.
- `spark soal:impor` → `32 diterima, 0 ditolak, 0 tidak berubah`. Production shows 32 more versions `PENDING_REVIEW`; meeting not published.
- **Run while students were working** (the owner's order "run judge checks now"): 13–18 students were submitting during the check. Their time from submission to verdict went from 10–13 s before to 16–19 s on average during it (31 s at most), and no submission was left waiting. The check ran at low CPU priority with two parallel problems.
- Weaker than it looks:
  - **Nobody but the agent has read these statements**; English and Arabic carry the machine-translation badge.
  - **Every answer is compared as text** with a fixed number of decimals. The generators never produce a result within 0.002 of a rounding boundary, so the order of operations cannot change the printed digits; a student who prints a different number of decimals is marked wrong, and each statement gives the format.
  - The practice constants (g = 9.81, 1 MPa = 10.197 kg/cm², concrete 24 kN/m³, the Mononobe, Manning and stopping-distance formulas) are labelled "parameter latihan"; the owner should confirm them.
  - The separate calculation is by the same agent, by another route (`decimal` with 40 digits, `cmath`, `atan2`).

### TS-PY meeting 3 — Logika dan Percabangan (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points, in id, en, ar. Reference solutions add `if` / `elif` / `else`, `and` / `or` / `not` and nested branching; no loop, function, list or string operation.
- Contexts: strength and slump acceptance, column stress against the allowable stress, cut or fill, road class from daily traffic, rainfall category, slope class, tiered water bill, overtime pay, Reynolds number, squareness check with three stakes, azimuth to quadrant bearing, reinforcement ratio limits, simplified USCS soil classification, channel velocity and freeboard, governing load combination, shear and moment at a section (two point loads), specimen age from two dates, strength reduction factor and flexural strength, winning bid.
- `periksa.py` with the six allowed concepts → `32 dari 32 soal lulus` on the first run; 52 quoted examples equal. Locally 820 comparisons with the separate calculation, 0 different.
- `spark soal:impor` at about 21:13–21:23 WIB on 2026-11-23, when no student had submitted for ten minutes → `32 diterima, 0 ditolak, 0 tidak berubah`.
- Weaker than it looks:
  - **Nobody but the agent has read these statements**; English and Arabic carry the machine-translation badge.
  - Class limits, tariffs and acceptance rules are simplified and labelled "parameter latihan" (rain categories, road classes by daily traffic, the three load combinations, ρ max = 0.025, strain limits 0.002 / 0.005 for φ, the USCS rules without dual symbols). A lecturer in the subject may want other values.
  - The generators never put a fractional result exactly on a class limit (for example a slope of exactly 8 %), because the binary fraction at the limit depends on the order of operations; whole-number limits are tested.
  - `umur-benda-uji` needs twenty-two `if` statements without loops or lists; it is labelled sulit for its length, and students who know arithmetic tricks can shorten it.
  - `faktor-reduksi` produces unrealistically heavy reinforcement in its compression-controlled tests.

### TS-PY meeting 4 — Perulangan (Iteration) dengan for dan while (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points, in id, en, ar. Reference solutions add `for` with `range()`, `while`, counters and accumulators, a sentinel value, nested loops, `break` and `continue`, and `print(..., end="")`; no list, function or string operation — data are read and processed one value at a time.
- Contexts: haul trips, stake positions, conversion tables, average strength, rainy days, largest axle load, stair heights, rainfall summary, pumping a flooded pit, pass percentage, peak traffic hour, cement reorder day, traffic growth, total rise and fall, longest dry spell, river cross-section by the trapezoid rule, two trucks arriving together, depreciation, pile grid coordinates, sensor readings with failures, column layout drawing, loan instalments, maximum three-day rainfall, choosing bars for a required steel area, cutting bars in order, largest square tile (GCD), reservoir simulation, normal depth by bisection, combinations of full trucks, mixer queue at one pump.
- `periksa.py` with the nine allowed concepts → `32 dari 32 soal lulus` on the first run; 35 quoted examples equal. Locally 803 comparisons, 0 different.
- `spark soal:impor` at about 21:13–21:23 WIB on 2026-11-23, when no student had submitted for ten minutes → `32 diterima, 0 ditolak, 0 tidak berubah`.
- Weaker than it looks:
  - **Nobody but the agent has read these statements**; English and Arabic carry the machine-translation badge.
  - Input is one value per line with the count first (or a closing `0`); a student who already knows lists may use them and will be refused by the concept check, since `PY_LIST` arrives at meeting 5.
  - `kedalaman-biseksi` fixes the method in the statement (bounds 0 and 10, 50 halvings) so that every correct program prints the same three decimals; the separate calculation uses 400 halvings in 50-digit decimals.
  - `pilih-tulangan` and `kombinasi-truk` are exhaustive searches with nested loops; their rules (2 to 8 bars in a layer, truck sizes 5 / 8 / 12 m³) are exercise parameters.
  - `denah-kolom` prints characters with `end=""`, which the RPS may not have shown by meeting 4; the statement explains it.

### TS-PY meeting 5 — Struktur Data dan Fungsi (2026-11-23)

- 32 problems, 8 / 8 / 6 / 6 / 4, 232 points, in id, en, ar. Reference solutions add lists (and a list of lists), tuples, dictionaries, sets, string methods and slices, and functions with parameters and `return`; several values may now share one input line. No list comprehension, `lambda`, `try` / `except`, or anything outside the standard library.
- **Five problems require a function in the student's code** (`required_concepts_in_submission: FUNGSI`): `fungsi-luas-pelat`, `fungsi-volume-telapak`, `fungsi-kemiringan`, `interpolasi-tabel`, `luas-poligon`. Their statements say so. This is the agent's choice, after the DDP101 recursion problems; the owner may want it on more problems, or on none.
- Contexts: reversed elevation list, point loads, project name and abbreviation, specimen and drawing codes, a material price dictionary, staff readings, results above the average, median, pad footing volumes, vehicle class counts, bar diameters in use, station text to metres, as-built against design elevations, bar length per diameter, rainfall mass curve, failed specimens, steepest grade, average without the extremes, reservoir storage table interpolation, rainfall histogram, materials in short supply, centroid and moment of inertia of a built-up section, excavation volume from a grid, ranking bids, area and perimeter of a polygonal plot, results per concrete grade, forward pass of a project schedule, truck trip log.
- `periksa.py` with the thirteen allowed concepts: first run `31 dari 32` — `harga-material` was refused because its generator printed the same input for every seed (one of five material names by test index). The problem now also reads a quantity and prints unit price × quantity. Second run → `32 dari 32 soal lulus`; 37 quoted examples equal. Locally 805 comparisons, 0 different.
- `spark soal:impor` at about 21:35 WIB, when no student had submitted for ten minutes → `32 diterima, 0 ditolak, 0 tidak berubah`.
- Weaker than it looks:
  - **Nobody but the agent has read these statements**; English and Arabic carry the machine-translation badge.
  - The concept check only sees that a `def` exists and is used; it cannot tell whether the function is the one the statement names.
  - `sorted()` on tuples and on dictionary keys is used for ordering; `lambda` and `key=` are avoided, so `log-ritase` sorts by `(-volume, plate)`.
  - `inersia-komposit` and `luas-poligon` are the only problems of this meeting with structural or surveying formulas; most of the rest is data handling, which is what the meeting teaches.

### TS-PY meeting 8 — UTS Praktik (2026-11-23)

- Six problems, 51 points, in id, en, ar, levels mudah sekali / mudah / mudah-sedang / sedang / sedang / sulit; the fifth requires a function. Nothing from meeting 6. None repeats the task of a practice problem.
- `periksa.py` with the thirteen concepts → `6 dari 6 soal lulus`; 8 quoted examples equal. Locally 152 comparisons with the separate calculation, 0 different.
- **The exam files are not in Git.** `docs/soal/buat-ts-py-pertemuan-8.py`, `cek-ts-py-pertemuan-8.py`, `ts-py-pertemuan-8.json` and `tinjauan/ts-py-pertemuan-8.md` exist only in the working tree on the server, untracked: committing and pushing statements and reference solutions of an exam was refused by the session's permission layer, and the agent did not try another way. The owner decides whether they go into the repository before or after the exam. Until then `git status` shows them, and `infra/deploy.sh` refuses to run while they are there.
- **Not imported**: at 22:20 WIB four submissions had arrived in the last ten minutes, and the importer in production still queues at normal priority.
- The agent's choices where the owner did not answer (`DECISIONS.md`): one problem set for everyone; problem 5 refuses a submission without `def`.
- Weaker than it looks: nobody but the agent has read the statements or solved the problems under time, so the 150-minute sizing (about 130 minutes of work) is an estimate; every student gets the same six tasks with different test data; `target_points` of meeting 8 still shows 60 and follows the approved problems (51) when they are approved.

### Late on 2026-11-23 — the owner's "do your recommendations"

- **Exam files stay out of Git until the exam is over** (agent's recommendation, accepted). The four meeting 8 files and the owner's RPS `.docx` are listed in `.git/info/exclude` on the server, so `git status` is clean and `infra/deploy.sh` runs. They exist only in `/srv/lokandita/docs/soal/` on this server: **there is no second copy.** After the exam, remove the four lines from `.git/info/exclude` and commit the files.
- **Deployed** at 23:02–23:03 WIB: `infra/cek-env.sh` → LULUS; `infra/deploy.sh lokakode`, `auth`, `portal`, `judge` → each reports commit `b7b5b85` (contains the importer priority change `0b8e7ed`). `infra/smoke.sh` → **44 pass, 0 fail**.
- **Exam imported** at 23:05 WIB, 608 s after the last student submission: `spark soal:impor` → `6 diterima, 0 ditolak, 0 tidak berubah`. Read from production: 6 versions `PENDING_REVIEW`; the six `VALIDATE` jobs have priority `low`; meeting 8 is `ujian`, not published, 0 problems attached, 0 windows; one `soal.impor` audit row. One student submitted during the import and was judged in 14 s.
- **Unsettled submissions:** new test `AnalitikTest::testHalamanNilaiDosenMenyelesaikanKirimanYangTertinggal` — a submission still `MENUNGGU` with its job `DONE` is settled and its meeting score written when the lecturer opens the meeting analytics page. `infra/tes.sh lokakode --filter AnalitikTest` → 9 tests, 275 assertions, pass. By reading the code, the same call is in every analytics page, the submission list and both exports; it is **not** in the class window page or in "Rilis nilai" — releasing does not need it, because the student's own pages settle their submissions before showing a score. Production had 14 such submissions at 23:07 WIB; they settle when their students or a lecturer open one of those pages.
- Still not done: the full suite was not rerun; no exam page has been seen in a browser; `target_points` of meeting 8 shows 60 until the 51 points are approved.

### 2026-11-24 — lock rule, password resets, approval (owner's orders)

- **Login lock for students: 5 minutes after every 5 failures**, no longer growing (commit `9961833`; `dosen` and `superadmin` keep 1/5/15/60). `infra/tes.sh auth --filter 'LoginTest|MfaLogin'` → 16 tests, 212 assertions, pass. Deployed at about 18:38 WIB on the owner's order: `infra/deploy.sh auth`, `lokakode`, `portal`, `judge` → each reports `9961833`; `infra/smoke.sh` → 44 pass, 0 fail. Not tested with a real failed login in production.
- **Three student passwords set to a value the owner chose** (LokaAuth can only generate a random one): written by SQL the way `UserAdmin::resetPassword` writes it — hash, `password_history`, lock cleared, sessions revoked, audit `password.reset` with `lewat: sql`. `must_change_password = 1` kept. Stored hashes verified; no real login tried.
- **All TS-PY problems approved on the owner's order ("approve all problems but let me publish them myself")**, without the owner having said he read the sheets: `spark soal:setujui TS-PY <n>` for 1, 2, 3, 4, 5 → `32 disetujui, 0 dilewati` each; for 8 → `6 disetujui, 0 dilewati`. Read from production: 32 approved and attached per practice meeting, 6 for the exam, meeting 8 `target_points` now 51, **no meeting published, no window**; 166 `soal.disetujui` audit rows. Publishing and opening are the owner's.

- **Full suites rerun on 2026-11-24, 18:45–19:25 WIB, no student submitting:** `infra/tes.sh auth` → 110 tests, 1691 assertions, pass; `portal` → 89 tests, 9048 assertions, pass; `judge` → 259 passed; `lokakode` → 295 tests, 16926 assertions, **294 pass, 1 error** — the error was in the new browser test below, a flaw of the test itself (it read the previous submission's result still on the page). Fixed and rerun alone; the full `lokakode` suite was not run a third time.
- **Exam page in a real browser** (headless Chromium of the test setup, real judge): new `tests/Peramban/UjianPerambanTest.php` → 2 tests, 23 assertions, pass. Shown: the countdown on the meeting page counts down from 150 minutes; after Submit a correct and a wrong submission give exactly the same panel ("Kiriman diterima", no score, no test list); a compile error shows the compiler log and no score; the Arabic page is right-to-left with Latin digits in the countdown and the Arabic "received" text; no script error, no CSP violation. **Still not done by anyone: looking at these pages with human eyes, and any exam page in Safe Exam Browser.**

- **Stage closed 2026-11-24:** tag `tahap-13` on the commit that holds this line, then `infra/deploy.sh` for all four components so production runs the tagged commit. The three students whose passwords were set have changed them (`must_change_password = 0` for all three, read from production).

### Importer priority (2026-11-23) — committed, NOT deployed

`0b8e7ed`: the importer's `VALIDATE` jobs get priority `low` and enter at the front of that queue (`KONTRAK.md` §6, §7). `infra/tes.sh lokakode tests/Soal/ImporTest.php` → 8 tests, 89 assertions, pass (one new assertion: the job is `low`); `tests/Soal/BankSoalTest.php` → 10 tests, 212 assertions, pass. The full suite was not run. **The deploy was refused by the session's permission layer** and was not attempted another way; production runs `6298a26` and still imports at normal priority.

### Imports held (2026-11-23)

The meeting 2 import delayed students' verdicts by 1 to 5 minutes, because the importer's `VALIDATE` jobs share the normal queue with students' jobs. The owner ordered the deploy and the judge checks to run now; he was not told about this effect of an import before it happened. So meetings 3–5 are checked in the judge containers (which costs students about 6 s per verdict) but **imported only when no student has submitted for ten minutes**. That happened from about 21:13 WIB; meetings 3, 4 and 5 were imported then, with no student submission during any of the three imports. Proposal for the owner, not done: let the importer queue its `VALIDATE` jobs with priority `low`, like pre-generation, so an import never stands in front of a student.

### Exam-type meeting (2026-11-23) — deployed, not yet seen in a browser

Built (`SPEC.md` §6 "Exam meetings", §7, §12; `KONTRAK.md` §6, §14; choices in `DECISIONS.md`):

- Migration `2026-11-23-000008`: `meetings.kind` (`latihan` | `ujian`), `meeting_class_windows.scores_released_at` / `scores_released_by`.
- Rules in `App\Libraries\Ruang` and `Latihan`, not in window settings: an exam accepts only normal submissions while open; closed means problems hidden; scores visible only after release (`Latihan::sqlNilaiTampak`, `Ruang::nilaiTampak`).
- Scoring: all problems count, no very-easy quota, surplus 0; the target follows the total points of the approved problems.
- Staff: type selector on the meeting page (locked once published or once it has submissions); "Buka selama N menit" (default 150) and "Rilis nilai" / "Tarik rilis" on the class window page; the schedule form is refused for an exam.
- Student: no score, points, verdict or per-test result until release — pages, history, `GET /api/executions/{id}` and the own-data export; no leaderboard for an exam ever; after release the total and points per problem number.
- Added beyond the plan: the own-data export names exam problems `soal-<id>` instead of the slug; `POST /api/integritas` answers 404 unless the problem page can be opened now (for every meeting type).

Verified by the agent (output in the session):

- `infra/tes.sh lokakode tests/Latihan/UjianTest.php` → 7 tests, 1286 assertions, pass, against real MySQL, Redis, LokaAuth and the judge worker. They include the two the owner asked for: an enrolled student cannot see an exam problem **before the window opens** (five states: no window, window not opened, instance pre-generated, closed by hand, unpublished) and **after it closes** (closed by hand, time up, loosened window settings, scores released) — problem page and nine problem endpoints 404, and no title, statement or slug in the home page, meeting page, notifications, announcements, clarifications or export; plus a student of another class of the same course.
- Full suite `infra/tes.sh lokakode` → 292 tests, 17343 assertions, **291 pass, 1 error**: `EksekusiTest` "Submit untuk instans yang bukan milik pembuatnya": my change removed the judge's error message from a failed `SUBMIT` that has no submission row. Fixed (the result is now kept for a failed job without a submission); then `EksekusiTest` + `UjianTest` → 17 tests, 1381 assertions, pass. **The full suite was not run again after that one-line fix.**
- `infra/cek-env.sh` → LULUS (no new variable).

Not verified, or weaker than it looks:

- **Deployed on the owner's order ("deploy now") at 20:04–20:06 WIB on 2026-11-23, while 13–17 students were working.** `infra/deploy.sh lokakode`, `auth`, `portal`, `judge` → each reports commit `6298a26`. The untracked RPS file of the owner made `deploy.sh` refuse (it wants a clean tree), so it was listed in `.git/info/exclude` for the duration of the deploy and removed from there afterwards; `docs/**` is not part of any image.
- Read from production after the deploy: `migrations` has `2026-11-23-000008 TambahUjian`; `meetings.kind` (`latihan` default) and the two release columns exist; all 48 meetings are `latihan`; 11 class windows, none released. `POST /kelas/11/pertemuan/40/buka-selama` and `…/rilis` without a session → 403 (the routes exist; a path that does not exist → 404). The new container's log has no error line in its first 8 minutes.
- `infra/smoke.sh` → **43 pass, 1 fail**: "2 kontainer judge tertinggal". The check counts every judge container, and students' submissions were being judged at that moment; six samples taken right afterwards showed none left over. Not rerun to a clean pass, because students were still submitting.
- **What the evening's work did to students (submission created → judged):** 10–13 s before; 16–19 s on average (31 s at most) during the meeting 2 judge check; **1 to 5 minutes for the 19 submissions made between 19:58 and 20:03**, while the meeting 2 import ran its 32 `VALIDATE` jobs — they enter the same queue as students' jobs with the same priority and occupied the four worker slots; back to 8–40 s from 20:04. The deploy itself (20:04–20:06) shows no delay beyond that. No submission failed.
- Seen while checking, by design and not changed: three submissions are still `MENUNGGU` although their judge job is `DONE` (one from 17:55, two from this evening). A submission is settled by `Penilaian::selesaikan` when its student opens the home page, a practice page or the result, or when staff open an analytics page; these students left first. **To check before the mid-term:** a student who submits and closes the browser as the exam ends has an unsettled submission until one of those pages is opened — whether the page the lecturer uses to read exam scores settles it has not been tested.
- **Not verified:** nothing of the exam type has been requested from production with a logged-in session.
- **No exam page has been seen in a browser**, by anyone. The countdown, the "kiriman diterima" message after Submit (JavaScript in `public/js/soal.js`) and the Arabic pages are untested in a browser; the tests check HTTP responses only.
- The Arabic and English exam texts were written by the agent.
- `auth` and `portal` tests were not run (the shared package did not change).
- Staff analytics ("students at risk", class comparison) treat an exam meeting like any other meeting.
- A student sees `progress` (queued, compiling, running test k of n) while an exam submission is judged; it carries no pass or fail.
- If a problem is approved into an exam after students have submitted, the target changes and every score of that meeting is recomputed.

### What production showed on 2026-11-23 (read-only, counts only) — for the owner

- DDP101 meetings 1–2 were published and opened for 7A–7D at 12:17–12:25 WIB; 6592 pre-generation jobs were queued. Between 14:00 and 17:45 WIB about 35–55 students per quarter hour submitted 100–170 times per quarter hour.
- **Judging delay (submission created → judged) rose from about 30 s at 14:00 to an average of 20 minutes at 15:30–15:45 (maximum 21.5 minutes)**, and was back to about 20 s from 17:15. Pre-generation was still running the whole time. The agent ran nothing between 06:30 and 17:30 WIB, so this is the system's own capacity at `JUDGE_CONCURRENCY=4`; the cause was not investigated.
- **The agent ran test suites from about 17:35 to 18:30 WIB (seed test, exam tests three times, the full suite for 36 minutes) without first checking whether students were working.** 34 students (17:30), 22 (17:45) and 2 (18:00–18:30) were. Their judging time in those slots was 19–21 s on average, 40 s at most. Tests use `lokakode_test` and their own worker but share the four CPUs. From now on: check `submissions` and `judge_jobs` before any test run, judge check or deploy.

## LokaUjian U0 — contract and groundwork (2026-11-24)

Plan approved by the owner the same evening ("approve everything else", then "approve all" for both deploys). Two commits, two deploys: `5eced7b` (A, the SEB move) and `fbb2ed6` (B and C, roster views and the empty app). Choices are in `DECISIONS.md`.

Built:

- **SEB in the shared package** (`KONTRAK.md` §8): library, filter, both controllers, views, texts and scripts moved from `apps/lokakode` to `packages/lokandita-auth` (`Lokandita\Auth\Seb`). LokaKode supplies `App\Libraries\SumberSeb` and `app/Config/Seb.php`; paths, Redis keys, file names, reason codes and texts are the same.
- **Roster views** (`KONTRAK.md` §18): migration `2026-11-24-000009` creates five `roster_*` views in `lokakode`; MySQL user `roster_reader` with `SELECT` on those five and nothing else (`infra/mysql-hak-roster.sh`); `Lokandita\Auth\RosterDb` is the connection, no queries yet.
- **Empty LokaUjian**: `apps/lokaujian` answers `/healthz`, `/bahasa/…`, `/robots.txt` and brand files; everything else 404. Container `lokaujian`, schema `lokaujian` (no table), user `lokaujian_app`, Redis DB 3, `SERVICE_TOKEN_LOKAUJIAN`, host `lokaujian.lokandita.com` in maintenance.
- LokaAuth: the new token; `/internal/password-reset` with it answers `403 app_not_allowed`; `ALLOWED_HOSTS` holds the new host.
- Compose, Caddy, `deploy.sh`, `pemeliharaan.sh`, `cek-env.sh`, `buat-env.sh`, `backup.sh`, `uji-restore.sh`, `tes.sh`, `tes-infra.sh`, `buat-merek.sh`; smoke checks 0–5 and 12 extended, check 13 new.
- `KONTRAK-LOKAUJIAN.md` now lists only what later stages add.

Verified by the agent (commands and output shown in the session):

- **LokaKode suite before and after the move:** at `692f5bf` → `OK (295 tests, 16936 assertions)`; at `5eced7b` alone → `OK (295 tests, 16702 assertions)`; the list of 295 tests and their results is identical. The assertion count varies between runs of the same code (tests that poll the judge). In `tests/Seb/` only three `use` lines changed.
- **Texts:** every SEB text in `id`, `en`, `ar`, composed as the pages compose it, compared with the old file → 234 texts, 0 different.
- **Real requests to production LokaKode, before, after deploy A and after deploy B/C:** `/seb`, `/seb/periksa`, `/seb/keluar`, a missing download (each in three languages), three redirects to login, three `.seb` files → 18 of 18 the same, ignoring only the nonce, the CSRF token and the `?v=<commit>` suffix of asset links. The `.seb` files are byte-identical. `/js/seb.js` and `/js/seb-periksa.js` unchanged.
- **Config Key** of the three production configurations (DDP101, PBO301, TS-PY), recomputed by the moved code in the running container from the stored configuration → equal to the stored key for all three; no key printed.
- Deploy A 21:19–21:21 WIB, deploy B/C 21:58–21:59 WIB, each with 0 submissions in the last 10 minutes and 0 active student sessions. `infra/deploy.sh` for every component reports the commit.
- After B/C: `infra/tes.sh lokaujian` → 10 tests, 60 assertions; `auth` → 111 tests, 1706 assertions; `portal` → 89 tests, 9048 assertions; `lokakode` → 298 tests, 16781 assertions (the 295 with the same results, plus 3 view tests). All pass.
- `infra/smoke.sh` → **54 pass, 0 fail**. Check 13 from inside `lokaujian`: `roster_reader` reads 1 year, 3 courses, 10 classes, 10 teacher assignments, 235 enrollments; refused (1142) on `enrollments`, `submissions` and on an insert into a view; counts equal the tables'.
- `infra/tes-infra.sh` → 74 pass, 0 fail. `infra/cek-env.sh` → LULUS.
- `https://lokaujian.lokandita.com/` → 503, maintenance page, Let's Encrypt certificate valid until 2027-02-22; `/internal/session/touch` → 404.
- `infra/backup.sh` and `infra/uji-restore.sh` → pass; the restore holds the five views and the empty `lokaujian` schema; `env.tar.gz` holds `apps/lokaujian/.env`.

Not verified, or weaker than it looks:

- **No request with real SEB headers has reached production since the move.** Tests compute the headers themselves; nobody has opened LokaKode in Safe Exam Browser after 21:20 WIB. This is the owner's gate, and it should be done before the TS-PY mid-term is scheduled.
- The staff SEB panel (`/mata-kuliah/{id}/seb`) was not requested in production with a session; it is covered by the tests only.
- The maintenance page of the new host was requested with curl, not seen in a browser.
- A 404 for an unknown path carries no `Content-Security-Policy` in any app (seen on live LokaKode too; pages that go through a route do carry it). Not changed.
- `PROGRESS.md` is committed after the deploy, so `infra/smoke.sh` check 0 reports a different commit until the closing deploy at the tag, as at the end of stage 13.

Mistakes of this session:

- `infra/tes.sh` was edited while a run of it was in progress; bash read the changed file and the run ended with a syntax error after PHPUnit had printed `OK`. The result stands; from then on unfinished work was stashed for the length of a run.
- `apps/auth/.env` got `ALLOWED_HOSTS` with the new host before deploy A, so LokaAuth accepted `lokaujian.lokandita.com` as a login target about 40 minutes before the commit that describes it. The owner had been told deploy A held only the SEB move.
- `infra/mysql-init.sh` was run before the second deploy (the tests need the users and schemas), not as part of it.
- Two expectations in the new LokaUjian test were wrong on the first run (exact `Cache-Control` text; CSP on a 404) and were corrected.

Changed on the server outside Git (no value shown anywhere): `infra/.env` gained `DB_PASSWORD_LOKAUJIAN` and `DB_PASSWORD_ROSTER_READER`; `apps/auth/.env` gained `SERVICE_TOKEN_LOKAUJIAN` and the host in `ALLOWED_HOSTS`; `apps/lokaujian/.env` is new, mode 600. All are in the backup of 2026-11-24 15:01 UTC.

Also to know: 238 accounts already have `lokaujian` in `app_access` (granted earlier, in LokaAuth). They will be let in on the day the host is opened; review that list before U6.

Owner's gate for U0:

1. In Safe Exam Browser 3.10.x with a course's `.seb` file: a student logs in to LokaKode, opens a practice page, and the page is still there after a minute (the heartbeat).
2. The same student in ordinary Chrome is refused with the "harus dibuka melalui Safe Exam Browser" page; a lecturer in Chrome gets in and can open *Safe Exam Browser* on a course page.
3. `https://lokaujian.lokandita.com` in a browser: valid certificate, maintenance page.

After the gate: tag `lokaujian-u0` on the commit that records it, then `infra/deploy.sh` for all five components and `infra/smoke.sh`.

### Gate result for LokaUjian U0 — checks 1 and 2 (2026-11-25)

The owner reported "no 1 and no 2 is done". What the server recorded for the test account he had made for it (student `Dummy`, enrolled in TS-PY class TS-6 on his order; TS-PY meetings 1–4 published by him at 08:40–08:42 WIB):

- **Check 1, real SEB after the move:** login from a client that names itself `SEB/3.10.2` at 08:49 WIB; 36 requests from that client answered 200 and none 403, among them a practice problem page; 5 heartbeats (`POST /api/seb/denyut`) answered 200 between 08:50:05 and 08:52:14 WIB. This is the first request with real SEB headers since the move, and it is accepted with the TS-PY configuration and the Browser Exam Key registered before the move.
- **Check 2, refusal outside SEB:** the same account from an ordinary browser at 08:53 WIB → 403, integrity event `seb_rejected` with reason `tanpa_header`. One `GET /mata-kuliah/{id}/seb` answered 200 in the same period (the staff panel, opened from an ordinary browser).
- **Check 3 (maintenance page of `lokaujian.lokandita.com` seen in a browser): not reported yet.** By curl it answers 503 with a valid certificate. No tag until the owner has said so.

Left in production by the gate: the account `Dummy` is an `AKTIF` member of TS-6 (29 students now) and appears in that class's lists, leaderboard and exports; two `seb_rejected` events (one from the agent's own check with curl on 2026-11-24, one from the owner's Chrome check). The owner decides when it is set to `MENGUNDURKAN_DIRI`.

- **Check 3 (2026-11-25):** the owner reported that `https://lokaujian.lokandita.com` showed the maintenance page in his browser. The gate of U0 is passed; tag `lokaujian-u0` on the commit that holds this line, then `infra/deploy.sh` for all components so production runs the tagged commit.

## LokaUjian U1 — access (2026-11-25)

Plan approved by the owner ("confirm all approve all"), with four decisions recorded in `DECISIONS.md`: §17 point 9 confirmed; the class page lists the `AKTIF` students; lists show the active academic year only; the host is opened for the gate and closed again. Three commits: `588b644` (way in), `fe64add` (policy layer and pages), `69239ee` (smoke check 14).

Built:

- **Way in** (`KONTRAK.md` §4): filter `masuk`, the account routes, and `GET /` — a short public page with "Masuk", `noindex` (`Publik::data(…, indeks: false)` in the shared package; the other apps are unchanged).
- **Policy layer** (`KONTRAK.md` §19): `App\Policy\Akses` holds the rules as SQL conditions over the roster views; `App\Libraries\Roster` holds every roster query on the `roster_reader` connection. Scoped roles are derived on every request.
- **Pages:** `/beranda` (staff: their classes of the active year; mahasiswa: the empty exam list and their own classes), `/mata-kuliah`, `/mata-kuliah/{id}`, `/kelas/{id}` (with the `AKTIF` students). Read-only; texts in `id`, `en`, `ar`.
- No table, no migration, no environment variable.
- Smoke check 14; the shared `ClientAppTest` now runs in LokaUjian's suite.

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh lokaujian` → `OK (50 tests, 1435 assertions)`, against real MySQL, `auth-tes`, `lokaujian-tes` and the roster views in `lokakode_test`: the 20 shared client tests (login, logout through LokaAuth, no app access → 403); `MatriksAksesTest` (every registered route, six actors; fails when a route is missing from the matrix; no session → login); `LingkupKelasTest` — **the gate case**: pengajar A on class B gets 404 with no name, nomor induk or class name of B, and the answer is identical to that for an id that does not exist; a removed assignment and a replaced pengampu are refused on the next request; a student sees the empty list and only their own class; `LULUS`, withdrawn, archived class and archived year give no class.
- Because the shared package changed: `infra/tes.sh auth` → 111 tests, 1706 assertions; `portal` → 89 tests, 9048 assertions; `lokakode` → 298 tests, 16754 assertions. All pass, the same numbers of tests as at U0.
- `infra/cek-env.sh` → LULUS. `infra/deploy.sh lokaujian` → verified at `/healthz`, commit `69239ee`.
- Real requests to the deployed container (from `portal`, as Caddy would send them): `/` → 200; `/beranda`, `/mata-kuliah`, `/kelas/1`, `/kelas/99999999` → 302 to the LokaAuth login with the right `next`; `/data-saya` → 404 (U5). From the internet `https://lokaujian.lokandita.com/beranda` → 503.
- `infra/smoke.sh` → 55 pass, **4 fail**: check 0 for `auth`, `lokakode`, `portal` and the judge worker, which were not redeployed. Check 14 passes (5 lines).

Not verified, or weaker than it looks:

- **No logged-in request has reached the deployed LokaUjian.** The agent does not create sessions for production accounts; everything behind a login is proven only by the tests between real containers. The owner's gate is the real check.
- **No page of U1 has been seen in a browser**, by a person or by a test: layout, the Arabic pages and dark mode. The headless browser joins LokaUjian's suite in U3. The Arabic texts were written by the agent.
- The roster query has run in production only through smoke check 13 (counts), not through the pages.
- **Not built although the plan named it:** id lists handed out by `Akses` for later stages — nothing would call them yet (`DECISIONS.md`).
- The notice about Safe Exam Browser on the student's home waits for U4.
- `infra/tes-infra.sh`, `infra/backup.sh` and `infra/uji-restore.sh` were not run: U1 adds no table, volume or infrastructure.

Owner's gate for U1:

1. Open the host: `infra/pemeliharaan.sh lokaujian off`. From then on every account with `lokaujian` access (238 at U0) can log in and sees an empty home.
2. As a dosen who is pengajar of exactly one class and not its pengampu: `https://lokaujian.lokandita.com/beranda` lists that class only. Open it, then change the number in `/kelas/<n>` to another class → *Halaman tidak ditemukan*.
3. As the student `Dummy`: the home says there is no exam or quiz yet and lists TS-PY class TS-6 only.
4. `infra/smoke.sh` (check 1 then expects `lokaujian` to answer 2xx/3xx).
5. Close the host again: `infra/pemeliharaan.sh lokaujian on`.

After the gate: tag `lokaujian-u1` on the commit that records it.

### Gate result for LokaUjian U1 (2026-11-25)

- The agent opened the host on the owner's order ("do no 1") shortly before 12:20 WIB and closed it again on his order a few minutes later (the exact minutes were not noted; an earlier version of this line gave times that were guessed); it answers 503 from the internet.
- **Step 3 (student): done.** The owner reported "no 3 done". The container log has one `GET /beranda` → 200 at 12:20:38 WIB followed by `POST /keluar` → 303, and LokaAuth's audit log has one `logout` with `app = lokaujian` by an account of role mahasiswa at 12:20:47 WIB. This is the first logged-in request to the deployed LokaUjian, and the first logout from it. What the page showed is the owner's observation.
- **Step 2 (pengajar sees only own classes; a changed class number gives *Halaman tidak ditemukan*): skipped by the owner** ("just skip no 2"). No dosen has opened the deployed LokaUjian; the log has no request to `/kelas/…` or `/mata-kuliah` with a session and no `access.denied`. That half of the gate of `SPEC-LOKAUJIAN.md` §15 rests on `LingkupKelasTest` and `MatriksAksesTest` only.
- **Step 4:** `infra/smoke.sh` with the host open → **54 pass, 5 fail**. All five are check 0: `auth`, `lokakode`, `portal` and the judge worker run `0059b3f`, `lokaujian` runs `69239ee`, and `HEAD` was the commit that holds only `PROGRESS.md`. Check 1 for the open host and check 14 pass.
- **Closing (owner: "lakukan keduanya"):** the owner ordered both the redeploy of every component and closing the stage with step 2 skipped. Right before the deploy LokaKode had 0 submissions and 0 active student sessions in the last 10 minutes and no unfinished judge job. The deploy of all five components and `infra/smoke.sh` are run at the commit that holds this line; their output is in the session, and the tag `lokaujian-u1` is put on this commit only when that smoke run passes.

Carried into U2:

- The pengajar half of the U1 gate has not been seen on the deployed system by anyone. The first dosen who opens LokaUjian is the real check; do it when the host is next opened.
- No page of LokaUjian has been checked in Arabic or in dark mode by a person or a browser test.
- `Akses` does not yet hand out id lists for LokaUjian's own tables; U2 writes that with the question bank, together with the audit table.
- The Portal card stays "segera" until U6 (the owner asked about it on 2026-11-25 and was told why).
- 238 accounts have `lokaujian` access and get in whenever the host is open.

## LokaUjian U2 — question authoring (2026-11-25)

Plan presented and answered by the owner: §17 point 10 **overruled** ("possible picture on options instead of narrative"), the minimal draft exam approved, the host to be opened for the gate, and a sample exam with every type of content asked for. Choices are in `DECISIONS.md`. Two commits: `858704f` (application, `KONTRAK.md` §20) and `d186408` (picture volume, backup, restore test, smoke check 15). **Built and deployed; not closed — the owner's gate is open, no tag yet.**

Built:

- **Tables** (the first of LokaUjian): `audit_log`, `images`, `question_groups`, `questions`, `question_options`, `exams`, `exam_items`.
- **What U1 left:** `Akses::idMatkulDiampu()` / `diKelola()` — the ids of the courses a user manages, read from the roster once per request; every query of the bank, the pictures and the exams is bound to it and to the course of the route. The audit table and `App\Libraries\Audit` came with their first callers.
- **Bank per course:** the four types with points; groups with shared text and/or picture and 1–20 questions in fixed order; archive and delete (delete only when no exam uses the item); search and filter; preview of a question, a group and a whole exam through the partial the student page will use, with no key in it.
- **Pictures:** checked from the content, rewritten from the pixels, alternative text required, one per question, per group and — the owner's change — per option, whose text may then be empty. Stored in the volume `lokandita_lokaujian_media`; answered only by `GET /gambar/<kunci>` after authorisation.
- **Draft exam** from ticked bank items; **JSON export and import** on the web and as `spark ujian:impor` / `ujian:ekspor`; a strict importer that reports every mistake with JSON path and line and stores nothing when there is one.
- **Sample:** `apps/lokaujian/contoh/ujian-contoh.json` — nine questions: true/false, multiple choice with text options, with a picture, and with four picture options, short answer, long answer, and a group with text, a picture and three questions. It names course `DDP101`.
- `infra/backup.sh` writes `media-lokaujian.tar.gz`; `infra/uji-restore.sh` checks it; `infra/tes-infra.sh` section A3; smoke check 15; `docs/RESTORE.md`.
- No environment variable. No change in `auth`, `lokakode`, `portal`, the shared package or the judge.

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh lokaujian` → `OK (64 tests, 4435 assertions)`, against real MySQL, Redis, `auth-tes` and `lokaujian-tes`. Among them:
  - `MatriksAksesTest`: all 28 routes × six actors. For the 23 new routes of the bank and the exams: pengampu of another course 404, pengajar of the course 403, mahasiswa 403; for `/gambar/<kunci>` all three get 404. Every refused write is sent with a valid CSRF token and the checksum of all seven tables is the same before and after.
  - Cross-course (`SoalTest`): the pengampu of Y gets 404 for a question of X under either course's address, the answer equals that for an id that does not exist, four kinds of write change nothing, and a draft in Y cannot hold a question of X. A replaced pengampu loses the bank on the next request.
  - Pictures (`GambarTest`): a PNG with PHP appended is stored without the appended bytes and under a random name; `/gambar/<kunci>` answers the pengampu and the superadmin, redirects to login without a session, and is 404 — identical to an unknown key — for the pengampu of another course, a pengajar and a student; `/media/soal/<kunci>.png` of the stored file is 403. PHP named `.png`, SVG, GIF, a truncated PNG, more than 5 MB and a missing alternative text are refused with nothing stored.
  - The gate (`ImporEksporTest`): one question of each type, picture options and a group with a picture → export → import → export is **byte-identical**, and the import adds no question and no picture to the bank. The sample file imported into an empty bank exports as itself. 45 single mistakes each refuse the whole file, name their JSON path, and leave all tables unchanged; three mistakes in one file are reported together with lines 8, 11 and 14.
  - `spark ujian:impor` / `ujian:ekspor`: accepted for the pengampu and the superadmin, exit 1 for a pengajar, another pengampu and a student; audit rows carry `lewat: cli`.
- `infra/cek-env.sh` → LULUS. `infra/deploy.sh lokaujian` → migration done, volume `lokandita_lokaujian_media` created, `/healthz` reports `d186408`.
- `infra/smoke.sh` → 61 pass, **4 fail**: check 0 for `auth`, `lokakode`, `portal` and the judge worker, which still run `f3134f4` and were not redeployed (the owner's instruction). Check 15 passes (6 lines).
- `infra/tes-infra.sh` → 74 pass, **5 fail**, all five because `auth`, `lokakode` and `portal` do not run `HEAD`. Section A3 passes: the volume is mounted, writable, and `/media/` serves neither a file named like a question picture nor PHP.
- `infra/backup.sh` → four files, `media-lokaujian.tar.gz` with 0 pictures. `infra/uji-restore.sh` → LULUS, 59 schemas/tables, the seven new tables among them.
- Production schema `lokaujian` holds the seven tables, all empty. `https://lokaujian.lokandita.com/mata-kuliah/1/bank` from the internet → 503 (maintenance).

Not verified, or weaker than it looks:

- **No logged-in request has reached the deployed bank.** The agent does not create sessions for production accounts. Everything behind a login — forms, upload, preview, export, import — is proven only by the tests between real containers. The owner's gate is the first real use.
- **No page of U2 has been seen in a browser**, by a person or by a test: layout, the forms with six option rows, pictures in the preview, Arabic, dark mode. The headless browser joins LokaUjian's suite in U3. The Arabic texts (189 keys) were written by the agent.
- **The restore test has compared no picture yet:** production has none. The check of every `images` row against its file runs for the first time on the backup made after the gate.
- The `spark` commands were run in production only without `--oleh` (they answer with their usage); no file has been imported into production.
- Export to a file above 24 MB is possible in principle (40 pictures of up to 5 MB) and such a file would be refused on import. Not guarded.
- When a form is refused, chosen picture files must be chosen again; a form whose pictures together exceed 25 MB is dropped by PHP and shows a generic refusal.
- The suites of `auth`, `lokakode` and `portal` were not run: no file of theirs or of the shared package changed (`git diff f3134f4 --stat` lists only `apps/lokaujian`, `infra`, `docs/RESTORE.md` and the root documents).
- `PROGRESS.md` is committed after the deploy, so `infra/smoke.sh` check 0 reports a different commit for `lokaujian` too until the closing deploy.

Mistakes of this session:

- Two expectations in the new tests were wrong on the first run (a number formatted with a thousands separator; the actor's own name counted as a secret of the target), one string and one array order on the second. No application code changed because of them.
- The plan promised four commits; there are two (`DECISIONS.md`).

Owner's gate for U2 (`SPEC-LOKAUJIAN.md` §15):

1. Open the host: `infra/pemeliharaan.sh lokaujian off`. Every account with `lokaujian` access can then log in; students see only the empty home.
2. As superadmin or pengampu: *Mata kuliah* → a course → **Bank soal**. Write one question of each type.
3. *Kelompok soal*: a shared text and a picture, then two questions in it.
4. Open each question and the group: the preview shows them as a student will see them, the picture included.
5. Copy the address of a picture (`/gambar/…`) into a private window: the login page appears, not the picture.
6. Back in the bank: tick everything, give a title, **Buat draf ujian**; look at the preview; **Ekspor ke berkas JSON**.
7. *Ujian dan kuis* → **Impor** that file; export the new draft; compare: `sha256sum lokaujian-*.json` must print the same value twice.
8. Optional: import `apps/lokaujian/contoh/ujian-contoh.json` into DDP101 to see every type, picture options included.
9. Optional, carried from U1: as a pengajar, only your own classes are listed and the bank of the course is refused.
10. Close the host: `infra/pemeliharaan.sh lokaujian on`. Then the agent runs `infra/smoke.sh`, `infra/backup.sh` and `infra/uji-restore.sh` again, now with real pictures.

After the gate: tag `lokaujian-u2` on the commit that records it. Redeploying `auth`, `lokakode`, `portal` and the judge worker so that check 0 passes needs the owner's word.

For the owner to decide before U3: whether the student's question shows its maximum points (it does now, `DECISIONS.md`).

Carried into U3:

- The pengajar half of the U1 gate, unless step 9 above is done.
- No page of LokaUjian has been checked in Arabic or dark mode by a person or a browser test.
- `Akses` hands out course ids only; the list of class ids comes with sessions.
- Students get pictures only from U3 (`Media::dariKunci` knows no attempt yet).
- Reordering, settings, publishing and freezing of exams; a draft follows bank edits until then.
- The Portal card stays "segera" until U6; 238 accounts have `lokaujian` access and get in whenever the host is open.

### Gate result for LokaUjian U2 (2026-11-25)

The agent opened the host on the owner's order ("do no 1") at 19:11 WIB and closed it at 19:25 WIB after he reported "have done no 1 to 9 - assume some skipped"; it answers 503 from the internet again. What the server recorded in that window (counts, routes and statuses only):

- **Steps 2 and 3 (one question of each type; a group with a picture and two questions): done.** The bank of one course holds one question each of `benar_salah`, `pilihan_ganda`, `isian` and `uraian`, and one group with a picture and two `isian` questions. Six `POST …/bank/soal` → 303. One stored picture (a JPEG); no question picture and no option picture was tried.
- **Step 4 (preview): done by request.** Six `GET …/bank/soal/N` → 200, five `GET …/bank/kelompok/N` → 200, four `GET /gambar/<kunci>` → 200. What the pages looked like is the owner's observation; he reported nothing wrong.
- **Step 5 (picture without login): one `GET /gambar/<kunci>` → 302** to the login page. The log cannot tell whether this was his private window.
- **Step 6 (draft and export): done.** One draft with five items, `ujian.dibuat`; one export, `ujian.diekspor`, `POST …/ekspor` → 200.
- **Step 7 (import the file, export again, compare): not done.** No `POST …/ujian/impor`, no `ujian.diimpor`. **The half of the gate that says "exports and re-imports the file without change" has not happened on the deployed system**; it rests on `ImporEksporTest` only.
- **Step 8 (sample file): not done.** **Step 9 (pengajar): not done** — one account acted in the whole window; the pengajar half of the U1 gate stays unseen.
- The forms refused twice (`POST …/bank/kelompok` → 422, once on creating and once on editing the group) and then accepted. The log does not keep the reason; a missing alternative text is the likely one. Two `POST …/arsip` → 303 and nothing is archived now: archive and restore were tried once.
- These were the first logged-in requests to the deployed bank: 25 routes answered as expected, none with 500.

After closing:

- `infra/smoke.sh` → 60 pass, **5 fail**, all check 0: `HEAD` is the commit that holds `PROGRESS.md`; `lokaujian` runs `d186408`, the others `f3134f4`.
- `infra/backup.sh` → `media-lokaujian.tar.gz` with 1 picture.
- **`infra/uji-restore.sh` failed on its first real picture**: it called the server's own file "not made by the server". The mistake was the agent's, in the file-name pattern of the new check (an interval that `find`'s default pattern syntax does not know); with no picture in production the first run had not exercised it. Corrected to `-regextype posix-extended`, tried on four names (one valid kept, three foreign refused), and run again → **LULUS**: 1 row in `lokaujian.images`, its file in the archive with the recorded size and SHA-256, archive equal to the live volume.

Left in production by the gate: six questions, one group, one picture and one draft exam in one course's bank. Nothing of it reaches students.

**Not closed, no tag.** Open for the owner: close U2 with step 7 skipped, or do step 7 first.

### LokaUjian U2 closed by the owner with step 7 skipped (2026-11-25)

The owner ordered "Close with step 7 skipped". U2 is closed on these terms:

- Seen on the deployed system by the owner: writing the four types and a group with a picture, the previews, a draft exam, one export (steps 2–6).
- **Not seen on the deployed system by anyone:** importing a file, and export → import → export giving the same file. That half of the gate of `SPEC-LOKAUJIAN.md` §15 rests on `ImporEksporTest` only. The first real import is the real check; compare the two files with `sha256sum` when it happens.
- Also unseen: question pictures and option pictures (only a group picture was uploaded), the sample file, the `spark` commands with `--oleh`, and the pengajar's view (carried from U1).

`lokaujian` is deployed at the commit that holds this entry. The tag `lokaujian-u2` goes on that commit once `infra/smoke.sh` passes, which needs `auth`, `lokakode`, `portal` and the judge worker redeployed — the owner's word, not yet given.

### Closing deploy of LokaUjian U2 — 2026-11-25

Recorded on the owner's word at the start of the U3 session: all five components were redeployed at `abf9717`, `infra/smoke.sh` passed with 65 pass and 0 fail, and the tag `lokaujian-u2` was placed. The output of that deploy and that smoke run is in the session that made them, not in this one.

What the U3 session read for itself before writing this entry (19:44 WIB, read-only):

- `GET /healthz` inside `auth`, `lokakode`, `portal` and `lokaujian` → each answers commit `abf97173ef2ce6eb3510591f4b879ca34ae023c0`.
- `git rev-parse lokaujian-u2^{commit}` → the same commit; `git ls-remote --tags origin` lists `lokaujian-u2`; `main` equals `origin/main`.
- `infra/smoke.sh`, run again → `smoke: 65 lulus, 0 gagal, 0 belum aktif`, `smoke: LULUS`. Its check 6 passed, so the judge worker's heartbeat carries that commit as well.
- `infra/pemeliharaan.sh status` → `lokaujian.lokandita.com` in maintenance (503); the other three hosts open.

Not read again: `infra/tes-infra.sh`, `infra/backup.sh` and `infra/uji-restore.sh` at `abf9717`.

This entry is committed after that deploy, so `infra/smoke.sh` check 0 reports a different commit for every component until the next deploy, as at the end of the earlier stages.

## LokaUjian U3 — exam setup and delivery (2026-11-25)

Plan presented and answered by the owner with "approve all". Read as: `SPEC-LOKAUJIAN.md` §17 points 3, 4, 5, 12 and 14 confirmed; and — the agent's reading of two questions the answer did not address in words — maximum points stay visible to students, and the gate uses a separate test class, not TS-6. Choices are in `DECISIONS.md`. Two commits: `e22f6f9` (the stage: code, `KONTRAK.md` §21 and §22, infrastructure) and `7694ee3` (a defect in the deploy check, found on the second deploy; below). **Built and deployed; not closed — the owner's gate is open, no tag.**

Built:

- **Exams** (`KONTRAK.md` §22): the draft page gained settings (title, kind, instructions, mode, duration, the two shuffles) and the order of its items (up, down, remove, add from the bank). **Publishing freezes the content** into `exam_groups`, `exam_questions`, `exam_options`; it is refused with a list of exactly what is missing. Withdraw to draft (no attempt yet), archive, delete (no attempt). The export of a published exam writes the frozen copy.
- **Sessions:** a published exam is given to a class, one session per exam per class. `per_mahasiswa`: schedule, open and close by hand. `dikendalikan_dosen`: Mulai, set or change the end (minutes from now, or a clock time), Akhiri announced 30 seconds ahead, automatic end 8 hours after the start. A plain session page: state, server time, scheduler alive or not, three counts. A pengajar runs the sessions of their own classes and can preview the exam; `Akses` now hands out class ids (carried from U2).
- **The time rule, once:** `App\Libraries\Tenggat`, SQL over MySQL's clock. The deadline is not stored; an attempt past its deadline is ended whatever its row says. Saves are accepted up to 10 seconds after the deadline.
- **Scheduler** (`KONTRAK.md` §21): container `lokaujian-jadwal`, `php spark ujian:jadwal`, a pass every 10 seconds, heartbeat in Redis. It only marks ended attempts as submitted.
- **Student side:** the home list; the page before the start; the waiting page that turns into the exam when the lecturer starts; the exam page — one question at a time, question map, ragu-ragu, Hapus pilihan, character counts, countdown from the server's seconds, three save states, answers held and retried without a connection, resume, takeover by the newest page, review panel, Kumpulkan, receipt. Pictures reach a student only while their attempt runs.
- **Copy and paste:** paste blocked on every path of §9.2, copy and cut blocked, text selection and printing off; `paste_allowed` exempts. `paste_blocked` and `page_taken_over` are recorded in the new `integrity_events`.
- **SEB in this stage:** `SEB_ENFORCEMENT` joined LokaUjian's variables. With `disabled` every student page carries the red banner; with `enabled` students are refused (enforcement itself is U4).
- Compose, `deploy.sh`, `lib.sh`, `tes.sh` (second container with `enabled`, headless Chromium, one scheduler pass), `tes-infra.sh` section A4, smoke checks 16 and 18. Texts in `id`, `en`, `ar` (two new language files).
- No file of `auth`, `lokakode`, `portal`, the shared package or the judge changed (`git diff abf9717 --stat` lists only `apps/lokaujian`, `infra` and the root documents).

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh lokaujian` at the final code → `OK (101 tests, 8765 assertions)`, against real MySQL, Redis, `auth-tes`, `lokaujian-tes`, `lokaujian-seb-tes` and a real headless Chromium. Among them:
  - `MatriksAksesTest`: all 53 routes × seven actors (a second student was added). The 25 new routes: pengajar B and the pengampu of another course get 404 on the session of class XA; a pengajar gets 404 on schedule and delete; staff get 403 on student routes; **student B gets 404 on the session of class XA**; every refused write is sent with a valid CSRF token and leaves the checksum of all fourteen tables unchanged.
  - **The gate cases, on test containers** (`PengerjaanTest`, `HalamanUjianTest`): a student completes an exam in each timing mode; the browser is closed mid-exam, five minutes pass, and the reopened page has the same answers, the same order and 15 of 20 minutes; a save 7 seconds after the deadline is accepted and one 12 seconds after is refused with `409 waktu_habis`, nothing changed; Ctrl+V, Shift+Insert, the paste event, drop, `beforeinput`, middle click and the context menu are each refused in an `isian` box, an `uraian` box and a box inside a group, by real key and mouse events.
  - Cross-class (`PengerjaanTest`, `SesiTest`): six kinds of outsider — a student of another class, of another course, a `LULUS` one, a withdrawn one, one of an archived class, one of an archived year — get an answer identical to "does not exist" on every student route, cannot save with student A's page token, and change nothing. A session of class XA answers pengajar B exactly like a session that does not exist.
  - Freezing (`TerbitTest`): after publishing, an edit in the bank changes neither the preview nor the frozen rows, and the export is byte-identical to the one taken before the edit.
  - One attempt: a second *Mulai* returns the same attempt, and a direct second `INSERT` is refused by MySQL (1062).
  - Counter: an older save never overwrites a newer one. Takeover: the old page gets `409 diambil_alih`, one event is recorded however often it retries, and in the browser the old page stops with the sentence.
  - Without a connection (network switched off through the DevTools Protocol): the header says *Belum tersimpan — mencoba lagi*, the banner shows, nothing reaches the database, the typed text stays; back online both answers arrive and the header says *Tersimpan*.
  - Scheduler: marks nothing inside the 10-second grace; then `waktu_habis` with `submitted_at` equal to the deadline; `diakhiri_dosen` for a session the lecturer ended; a second pass changes nothing. As a real process: heartbeat with commit and pid; a second scheduler exits with its log line; a missing variable exits 78.
  - With `SEB_ENFORCEMENT=enabled` (second container): a student gets 403 on every exam route and on the JSON endpoints, the home list still opens, staff are unaffected.
  - Layout: the exam page in Indonesian, Arabic and dark mode at 1280 and 390 pixels — direction, nothing overflowing, countdown left-to-right, no CSP violation and no script error. The agent looked at the six screenshots.
- `infra/tes.sh auth`, run because `infra/tes.sh` itself changed → `OK (111 tests, 1706 assertions)`, the same numbers as at U1.
- `infra/cek-env.sh` → LULUS (13 keys for `lokaujian`).
- `infra/deploy.sh lokaujian` at `7694ee3` → migration done; `/healthz` reports the commit; the scheduler's heartbeat carries the commit and the host name of the new container.
- `infra/smoke.sh` → **72 pass, 4 fail**: check 0 for `auth`, `lokakode`, `portal` and the worker's commit in check 6 — not redeployed, by the owner's instruction. Checks 16 (3 lines) and 18 (8 lines) pass; check 18 prints the warning that students are not required to use SEB.
- `infra/tes-infra.sh` → 83 pass, **5 fail**, all five because `auth`, `lokakode` and `portal` do not run `HEAD`. Section A4 (9 lines) passes.
- `infra/backup.sh` → done, 1 question picture. `infra/uji-restore.sh` → LULUS, 66 schemas/tables (the seven new ones among them).
- Production schema `lokaujian`: the seven new tables exist and are empty; the one exam is still a `draf`. From the internet `/beranda`, `/sesi/1`, `/ujian-saya/1/kerjakan` and `/js/ujian.js` → 503 (maintenance); from inside, `/js/ujian.js` → 200.

Not verified, or weaker than it looks:

- **No logged-in request has reached the deployed U3 code.** The agent does not create sessions for production accounts. Publishing, sessions, the exam page and every save are proven only between test containers. The owner's gate is the first real use.
- **No person has seen any page of U3.** The agent saw screenshots of the student's exam page and its review panel, rendered by the test browser. The lecturer's pages — the draft's edit page, the exam page with its sessions, the session page — and the student's home list, start page, waiting page and receipt have been seen by nobody; tests read their HTML only.
- **No real Safe Exam Browser.** This stage has none by design; the gate is done in an ordinary browser with the red banner. Whether the exam page behaves inside SEB 3.10.x (key handling, the paste block next to SEB's own) is U4.
- **The paste block was proven in Chromium on Linux only.** Not in Firefox, not in Safari, not on Windows.
- **No load.** One browser at a time; the largest test world has three students. 110 concurrent students is U6. Each exam page asks the server every 10 seconds and each request passes the shared session check.
- **A student who closes the browser while offline loses what was typed since the last *Tersimpan*** — unsent answers live in the page only (`DECISIONS.md`). The page asks before closing.
- The watermark's clock is the browser's (display only; no decision uses it).
- The lecturer's session page does not refresh itself, shows three counts and no student. Extra time and reopening have no screen yet: if a student's laptop dies during the gate there is nothing to press (U5).
- **No score exists.** Ended attempts are marked, not scored (U5).
- A session that opens or closes by its schedule leaves no audit row.
- The suites of `lokakode` and `portal` were not run: no file of theirs or of the shared package changed, but `infra/tes.sh`, which runs them, did.
- The Arabic texts (195 new keys) were written by the agent and read by no Arabic speaker.
- `PROGRESS.md` is committed after the deploy, so `infra/smoke.sh` check 0 reports a different commit for `lokaujian` too until the closing deploy.

Mistakes of this session:

- **The deploy check accepted a dead scheduler's heartbeat.** As first written, `infra/deploy.sh lokaujian` only compared the commit in the heartbeat. A heartbeat outlives its writer by up to 60 seconds, so the second deploy of `e22f6f9` reported "LULUS" about 25 seconds before the new scheduler had taken over (`--periksa` in the new container answered "no heartbeat" at that moment). The first deploy was not affected (there was no older heartbeat). Corrected in `7694ee3`: deploy, smoke check 16 and `tes-infra.sh` now require the heartbeat to come from the running container; the third deploy waited 47 seconds and showed the new container's name.
- A view variable named `kelas` reached the picture partial and the page of a draft with a picture answered 500. Found by two tests of the first run, before any deploy; corrected (`DECISIONS.md`).
- An answer was written into the `value` attribute with attribute escaping, which turns spaces into `&#x20;`; correct, but the test that reads the page failed. Changed to ordinary escaping.
- Two expectations in the new tests were wrong on the first run (the exact text of `Cache-Control`; the word "correct" inside `autocorrect`). No application code changed because of them.
- The plan named three or four commits; the stage is one, plus the correction (`DECISIONS.md`).
- `KONTRAK-LOKAUJIAN.md` said the scheduler deletes its heartbeat on a clean stop and passes "at least every 20 seconds". It never deletes it (the image has no `pcntl`) and passes every 10 seconds; `KONTRAK.md` §21 says what the code does.

Changed on the server outside Git (no value shown anywhere): `apps/lokaujian/.env` gained `SEB_ENFORCEMENT=disabled` (not a secret). The container `lokaujian-jadwal` is new. Production schema `lokaujian`: migration `2026-11-25-000002` ran (seven tables, two columns on `exams`). All are in the backup of 2026-11-25 13:46 UTC.

Owner's gate for U3 (`SPEC-LOKAUJIAN.md` §15). It is done in an ordinary browser; every student page shows the red banner "MODE UJI".

1. **Before opening the host:** in LokaKode, a class whose only `AKTIF` student is `Dummy` (the test class the plan asked for). While the host is open and SEB is off, any student of a class that has a session could start that exam; with a separate class only `Dummy` can.
2. Tell the agent to open the host (`infra/pemeliharaan.sh lokaujian off`).
3. As superadmin or pengampu: *Mata kuliah* → the course → *Ujian dan kuis* → the draft → **Sunting**: mode *Per mahasiswa*, durasi `5`, **Simpan setelan**; back to the exam → **Terbitkan** → **Berikan ke kelas**: the test class, opening a few minutes ago, closing in an hour.
4. As `Dummy`: the home lists the exam → open it → **Mulai**. Answer a choice, type in a short and a long answer; the header must say *Tersimpan* with a time. Press **Ctrl+V** in each text box: refused, with a message.
5. **Close the browser.** Open it again, log in, **Lanjutkan**: the answers are there and the countdown has gone on.
6. Let the 5 minutes run out while typing: the page stops with *Waktu habis*; the receipt says how many were answered. (The agent then reads from the log whether a save arrived after the deadline and was refused.)
7. Second exam, lecturer-controlled: in the bank tick a few questions → **Buat draf ujian** → **Terbitkan** (the mode stays *Dikendalikan dosen*) → **Berikan ke kelas** → open the session (the class name in the table).
8. As `Dummy`, open that exam: *Menunggu dosen memulai ujian*. As the lecturer press **Mulai** (10 minutes): Dummy's page becomes the exam by itself. Answer, **Selesai** → check the review → **Kumpulkan**.
9. Optional: a third exam, started and then ended with **Akhiri** while Dummy is typing: Dummy sees the notice and the page stops within about 40 seconds.
10. Optional, carried from U1: as a pengajar of the test class, the session opens and **Mulai** works; the bank is refused.
11. Tell the agent to close the host. The agent then runs `infra/smoke.sh`, `infra/backup.sh` and `infra/uji-restore.sh` and reports what the server recorded.

After the gate: tag `lokaujian-u3` on the commit that records it. Redeploying `auth`, `lokakode`, `portal` and the judge worker so that check 0 passes needs the owner's word.

Carried into U4:

- The alias `seb` is a stand-in; U4 replaces it with the shared `WajibSeb`, adds `SEB_ENCRYPTION_KEY`, the configuration tables and panel, `lokaujian`'s SEB routes, smoke check 17, and the remaining events of §9.3.
- The pengajar half of the U1 gate and the import half of the U2 gate are still unseen on the deployed system unless steps 10 above and a real import happen.
- No page of the lecturer's side has been seen in a browser by a person or a layout test; Arabic is unread by a speaker.
- No extra time, no reopening, no live view per student, no score, no CSV (U5). No load test (U6).
- The Portal card stays "segera" until U6; 238 accounts have `lokaujian` access and get in whenever the host is open — **from U3 on that matters**: with SEB off they can start any exam given to their class.

### Gate result for LokaUjian U3 (2026-11-26)

The owner reported "i did no 1" (the test class) and ordered the host opened; the agent opened it at 23:30 WIB and closed it at 23:49 WIB after he reported "done all exams". It answers 503 from the internet again. What the server recorded in that window (counts, routes, statuses and times only):

- **The test class:** both sessions belong to one class with exactly one `AKTIF` student. One student account acted; it has no `paste_allowed`.
- **Exam 1, `per_mahasiswa`, 5 minutes, 6 questions: done.** Settings saved (three `POST …/ujian/1` → 303), published 23:32:02, given to the class 23:32:48 (by a superadmin). The student pressed *Mulai* at 23:33:26; nine saves → 200 between 23:33:50 and 23:36:54; all 6 questions answered. The deadline, 23:38:26, passed with the page open; the scheduler marked the attempt `waktu_habis` with that time. The receipt page was opened at 23:38:30.
- **Exam 2, `dikendalikan_dosen`, 3 questions, shuffled: done.** Made from the bank 23:40:25, published 23:41:20, given to the class by the course's pengampu 23:43:14, **started at 23:43:46 by a dosen who is pengajar of that class and not its pengampu**, with an end five minutes later. The student pressed *Mulai* at 23:44:08; three saves → 200; all 3 answered. The session ended at 23:48:46 and the attempt was marked `waktu_habis`.
- These were the first logged-in requests to the deployed U3 code: 32 routes answered, **no 4xx and no 5xx in the window**; 12 saves, 58 state polls, 7 pictures served.
- **A pengajar has now used the deployed LokaUjian** (carried from U1): the session page opened for them and *Mulai* worked. That a class of another pengajar and the bank are refused to them was not tried.

Gate steps that left no trace — **not done on the deployed system, proven by tests only:**

- **Step 4, Ctrl+V refused:** no `POST …/peristiwa` and no `paste_blocked` row. No paste, copy or cut was blocked during the gate, so none was attempted in a text box.
- **Step 5, close the browser and return:** each attempt has exactly one `GET …/kerjakan`. The exam page was never opened a second time.
- **Step 6, a save after the deadline refused:** no save arrived after either deadline (the last ones were 92 seconds and 4 minutes 25 seconds before), so nothing was refused; no `409`.
- **Step 8, the waiting page turning into the exam:** the student opened exam 2 at 23:44:06, twenty seconds after the lecturer's *Mulai*, so the waiting page was not on screen when the session started. **Selesai → Kumpulkan:** no `POST …/kumpulkan`; both attempts ended by time, none was submitted by the student.
- **Step 9, Akhiri:** not pressed.

So of the gate of `SPEC-LOKAUJIAN.md` §15 — "a test student completes an exam in each timing mode; closing the browser mid-exam and returning keeps answers and remaining time; submitting after the deadline is refused; Ctrl+V is refused in every text box" — **the first clause was seen on the deployed system; the other three rest on `PengerjaanTest` and `HalamanUjianTest`.** What the pages looked like is the owner's observation; he reported nothing wrong.

After closing:

- `infra/smoke.sh` → 70 pass, **6 fail**, all the commit check: `HEAD` is the commit that holds `PROGRESS.md`; `lokaujian` and the scheduler run `7694ee3`, the others `abf9717`.
- `infra/backup.sh` → done. `infra/uji-restore.sh` → LULUS, 66 schemas/tables, now with the gate's sessions, attempts and answers in them.

Left in production by the gate: two published exams, two sessions and two ended attempts (9 answers) of the test student in the test class. They can no longer be withdrawn or deleted, only archived — by design. The test class stays in LokaKode until the owner removes or archives it.

**Not closed, no tag.** Open for the owner: close U3 with the three clauses unseen, or do them first (a few minutes with the host open: paste into a box, close and reopen the browser, type across the end of a 5-minute exam).

### LokaUjian U3 closed by the owner with three clauses of the gate unseen (2026-11-26)

The owner ordered "close as it is". U3 is closed on these terms:

- Seen on the deployed system: a test student completed one exam in each timing mode, with every answer saved and no error; a pengajar started a session of their own class.
- **Not seen on the deployed system by anyone:** Ctrl+V refused in a text box; closing the browser mid-exam and returning with answers and remaining time kept; a save after the deadline refused. Also unseen: the waiting page turning into the exam, *Selesai* → *Kumpulkan*, and *Akhiri*. All of these rest on `PengerjaanTest` and `HalamanUjianTest` only. The first real exam in which a student pastes, loses a browser or types across the deadline is the real check.

`lokaujian` and its scheduler are deployed at the commit that holds this entry. The tag `lokaujian-u3` goes on that commit once `infra/smoke.sh` passes, which needs `auth`, `lokakode`, `portal` and the judge worker redeployed — the owner's word, not yet given.

### Closing deploy of LokaUjian U3 — 2026-11-26/27

On the owner's word ("do it", 2026-11-26): before the redeploy LokaKode had 0 submissions, 0 unfinished judge jobs and 0 active student sessions in the last 10 minutes (23:57 WIB). `infra/deploy.sh auth`, `lokakode`, `judge` and `portal` each verified at `fdbf765`; `lokaujian` and its scheduler already ran it. `infra/smoke.sh` → **76 pass, 0 fail** (with the warning that LokaUjian runs with SEB enforcement off). The tag `lokaujian-u3` was placed on `fdbf765` and pushed. `infra/tes-infra.sh` → 88 pass, 0 fail. The output is in that session.

Read again at the start of U4 (2026-11-27 00:04 WIB, read-only): `/healthz` of `auth`, `lokakode`, `portal` and `lokaujian` each answers `fdbf765…`; `infra/smoke.sh` → `76 lulus, 0 gagal`; `lokaujian.lokandita.com` in maintenance.

This entry is committed after that deploy, so `infra/smoke.sh` check 0 reports a different commit for every component until the next deploy.

## LokaUjian U4 — SEB enforcement and integrity events (2026-11-27)

Started in the same session as the closing of U3, on the owner's order. Plan presented; the owner **overruled `SPEC-LOKAUJIAN.md` §17 point 11**: "seb configuration for lokaujian is per ujian/kuis", approved a plain list of events per session and approved that enforcement stays on after the gate. The agent then described what per exam changes and costs (a Browser Exam Key registered for every exam; one file per exam for students) and asked where students get the file; he answered **A**: from their own home list after login, no public list. The agent took "A" as approval of the revised plan. One commit: `9e506ce`. **Built and deployed; not closed — the owner's gate is open, no tag.**

Built (`KONTRAK.md` §23):

- **Configuration per exam:** tables `seb_configs` (by `exam_id`) and `seb_exam_keys`; a panel on the exam page for superadmin and the pengampu — create or rotate with a quit password, download, register Browser Exam Keys. The file is `LokaUjian-<course code>-<exam id>.seb` and starts SEB at `/ujian/<id>/masuk`, which sends a student to their own session of that exam. Rotation is refused (409) while the exam is running.
- **Real enforcement:** filter `seb` is now `App\Filters\SebUjian`. A route that carries a session accepts only the configuration of that session's exam; the heartbeat and pictures accept any exam of the student. Heartbeat every 25 seconds, lease 30 seconds. Pictures are behind SEB for students.
- **The file for students:** on the home list next to each exam, after login, in an ordinary browser (`/ujian-saya/{s}/seb`). Public `/seb` (instructions) and `/seb/periksa` (did the two headers arrive) name no exam; there is no public download.
- **Integrity events:** `seb_rejected` (at most one a minute per student) and `seb_terminated` from the filter; `focus_lost` with its duration, `display_changed` and `reconnected` (more than 60 seconds) from the exam page; a list per session at `/sesi/{id}/peristiwa`. The exam page and the session page show whether the exam has a configuration and a registered key.
- `SEB_ENCRYPTION_KEY` for LokaUjian (`.env.example`, startup validation, `KONTRAK.md` §10); smoke check 17; `SPEC-LOKAUJIAN.md` §3, §9.1 and §17 point 11 rewritten.
- From the shared package only the verification, the file builder, the encryption and the lease are used. **No file of the shared package, `auth`, `lokakode` or `portal` changed** (`git diff fdbf765 --stat` lists only `apps/lokaujian`, `infra/smoke.sh` and the root documents).

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh lokaujian` → `OK (109 tests, 10142 assertions)`, 11 minutes 45 seconds. New in U4, against the container with enforcement **on** and headers computed by the formula of `KONTRAK.md` §8:
  - with valid headers for every address a student opens the exam, starts, saves, sends the heartbeat and submits; without headers, with headers of another address, with a wrong Config Key, and with an unregistered Browser Exam Key every one is refused (403) and nothing but the event table changes;
  - **the file of exam A does not open exam B** of the same class (`kunci_konfigurasi`), no attempt is created; the heartbeat accepts either; an exam without a configuration is refused for everybody (`tanpa_konfigurasi`);
  - the Config Key computed from the content of the downloaded file equals the stored one, for two exams with different keys; the file starts at `/ujian/<id>/masuk`; the stored configuration is encrypted; the quit password is in no column, no page and no audit row;
  - rotation: new version, old keys gone, the other exam untouched; refused with 409 while a session runs, accepted afterwards;
  - the student downloads the same bytes the pengampu does, without SEB; a student of another class gets 404; `/seb` holds no exam title and no file link; `/seb/unduh/…` is 404;
  - `seb_rejected` once for three refusals in a row, `seb_terminated` with the attempt when a lease existed; the list page shows them to the class's pengajar and is 404 for another pengajar;
  - a student who left the class after starting still passes inside SEB; a picture is 403 outside SEB and served inside;
  - `MatriksAksesTest`: 61 routes × seven actors.
  - In the browser: losing focus for two seconds, a change of the screen size, and 65 seconds without a connection each record their event with the measured value; a 12-second gap records nothing.
- `infra/cek-env.sh` → LULUS. `infra/deploy.sh lokaujian` at `9e506ce` → migration done, `/healthz` and the scheduler's heartbeat report the commit; the container logs `penegakan SEB aktif`.
- `infra/smoke.sh` → **81 pass, 4 fail**: the commit check of `auth`, `lokakode`, `portal` and the worker, not redeployed. Check 17 passes (10 lines).
- `infra/tes-infra.sh` → 83 pass, **5 fail**, all five the same commit check. `infra/backup.sh` → done. `infra/uji-restore.sh` → LULUS, 68 schemas/tables.
- From inside, `/seb` → 200; from the internet → 503 (maintenance).

Not verified, or weaker than it looks:

- **No real Safe Exam Browser has sent a request to LokaUjian.** Every "accepted" above is a header the test computed itself. Whether SEB 3.10.x computes the same Config Key from this file, whether the Browser Exam Key read from its Configuration Tool matches, and whether the exam page behaves inside SEB are the owner's gate and cannot be shown by any test.
- **No logged-in request has reached the deployed U4 code, and no person has seen any page of U4**: the panel, the SEB card on the exam and session pages, the home list with its download links, the instructions, the refusal page, the event list. Tests read their HTML only; none was rendered in a browser, in any language.
- **That a refused heartbeat stops the exam page in the browser was not tested.** The server's 403 is tested, and the page's handling of a 403 is code read, not exercised: the test browser cannot compute SEB headers per request.
- `focus_lost` was triggered by dispatching the window's blur and focus events from a script, not by a real change of window; `display_changed` by emulating the screen size. Inside SEB, on its own desktop, these events may fire differently or not at all.
- With enforcement on, **the two exams of the U3 gate have no configuration, so nobody can open them**; and from now on every exam needs a configuration and a registered key before any student can start.
- A student whose only route into the exam is the file needs to download it before entering SEB: inside SEB downloads are forbidden.
- The suites of `auth`, `lokakode` and `portal` were not run: no file of theirs, of the shared package or of `infra/tes.sh` changed.
- The Arabic texts (97 new keys) were written by the agent and read by no Arabic speaker.
- The run of the suite now takes about 12 minutes.

Mistakes of this session:

- The first run of the suite was started in the foreground and was cut off before it printed anything; it was run again in the background.
- Three new tests failed on their first complete run, all in the tests: two compared the order of keys of a JSON column, which MySQL reorders, and one fetched a picture from the second test container, which never received the uploaded file (test containers share no volume). No application code changed because of them.
- The first U4 plan assumed configuration per course, as the specification said; the owner's answer changed it before any code was written.
- Smoke check 17 is printed after check 18.

Changed on the server outside Git (no value shown anywhere): `apps/lokaujian/.env` gained `SEB_ENCRYPTION_KEY` (generated on the server) and `SEB_ENFORCEMENT` went from `disabled` to `enabled`. Production schema `lokaujian`: migration `2026-11-27-000003` ran (two tables). All are in the backup made after the deploy. **`SEB_ENCRYPTION_KEY` must not change from now on**; it is in every backup's `env.tar.gz`.

Owner's gate for U4 (`SPEC-LOKAUJIAN.md` §15). Needs a Windows computer with SEB 3.10.x.

1. Tell the agent to open the host.
2. In Chrome, as superadmin or pengampu: make a new exam from the bank, **Terbitkan**, **Berikan ke kelas** (the test class), then **Atur Safe Exam Browser** → a quit password → **Buat konfigurasi** → **Unduh berkas .seb**.
3. On Windows, open that file with the **SEB Configuration Tool** of SEB 3.10.x, tab *Exam*, copy the **Browser Exam Key**, paste it into the panel, **Simpan kunci**. The exam page must then say the configuration is ready.
4. As `Dummy` in Chrome: the home lists the exam with **Unduh berkas .seb**; opening the exam itself is refused with "harus dibuka melalui Safe Exam Browser". Download the file.
5. Double-click the file: SEB opens, log in as `Dummy`, the exam's page appears; **Mulai** (start the session as lecturer first if it is lecturer-controlled). Answer, **Selesai**, **Kumpulkan**, quit SEB.
6. As the lecturer in Chrome during or after: the session page opens, and **Peristiwa integritas** lists what happened (the refusal of step 4 is there).
7. The three things never tried on the deployed system in U3, now inside SEB: press **Ctrl+V** in a text box; quit SEB mid-exam with the quit password, open the file again and continue; keep typing across the end of the time.
8. Optional: open a second exam's page inside SEB started with the first exam's file — refused.
9. Tell the agent to close the host; it reports what the server recorded.

After the gate: tag `lokaujian-u4` on the commit that records it. Redeploying `auth`, `lokakode`, `portal` and the judge worker so that the commit check passes needs the owner's word.

Carried into U5:

- Live view per student, extra time, reopening, scoring and correction, CSV (answers and events), result release and the review inside SEB, the audit page, own-data export.
- Still unseen on the deployed system unless the gate does them: pasting refused, close-and-return, a save after the deadline (U3); importing an exam file (U2); a pengajar refused another class and the bank (U1).
- The test class and `Dummy` remain in LokaKode; `Dummy` is also still an `AKTIF` member of TS-6.
- Retention of answers (§17 point 1) is undecided; no real exam before it is.

### Gate result for LokaUjian U4 (2026-11-27)

The agent opened the host on the owner's order at 01:39 WIB and closed it at 08:24 WIB after he reported "done the gates"; it answers 503 from the internet again. The gate itself took place between 08:13 and 08:23 WIB. What the server recorded (counts, routes, statuses and times only):

- **Step 2, exam and configuration: done.** A new exam (lecturer-controlled, 2 questions) was made from the bank and published at 08:13; its SEB configuration, version 1, was created at 08:14 and its file downloaded by the lecturer; given to the test class at 08:19 and started at 08:19:50.
- **Step 3, Browser Exam Key: done.** One key was registered at 08:17 (`seb.kunci`, count 1).
- **Step 4, refused outside SEB: done.** At 08:20:29 the student account requested the exam from a client without SEB headers → `403`, `seb_rejected` with reason `tanpa_header`. The same account also tried one of the U3 exams → `403`, `tanpa_konfigurasi`. The file was downloaded from the student's home (`GET /ujian-saya/3/seb` → 200).
- **Step 5, real SEB: done. This is the first time a real Safe Exam Browser reached LokaUjian, and it was accepted.** A client that names itself `SEB/3.10.2` sent 44 requests, **every one answered 200, 302 or 303 and none 403**: the file's start address, the exam's page, *Mulai* at 08:21:23, the exam page, five saves, the heartbeat, *Kumpulkan* at 08:22:45 (`submit_how = mahasiswa`, 2 of 2 answered), and the receipt. So the Config Key the server computes equals the one SEB 3.10.2 computes from the downloaded file, and the registered Browser Exam Key matches what that client sends — for every address, including one with a query string.
- **A lecturer in Chrome still gets in:** the panel, the session page and *Mulai* were answered to clients without SEB headers.
- **Carried from U3, now seen on the deployed system, inside real SEB:**
  - **Ctrl+V refused:** one `paste_blocked` with path `keyboard` at 08:21:30, from the SEB client.
  - **Quit and return:** the exam page was opened at 08:21:23 and, after the file's start address was requested again at 08:22:01 and 08:22:17 (SEB reopened, login), a second time at 08:22:19; saves went on with rising counters (highest 3) on the same attempt. Whether the earlier answers were on screen is the owner's observation; the attempt and its answer rows are the same ones.
- One `focus_lost` of 6 seconds was reported by the page inside SEB at 08:22:31 — the event does fire inside SEB.
- No 5xx in the whole window. About 270 requests from the internet for paths like `/.env` and `phpinfo.php` arrived while the host was open; all were answered 404 by the app (scanners; nothing was served).

Not done on the deployed system:

- **Typing across the end of the time, and a save after the deadline refused:** the attempt was submitted by the student two minutes before the session's end; no `409`. This clause of the U3 gate still rests on `PengerjaanTest` and `HalamanUjianTest` only.
- **Step 6, the list of integrity events:** `/sesi/3/peristiwa` was never requested. Three events are stored for that session; no person has seen the page that lists them.
- **Step 8, the file of one exam refused on another:** not tried; only one exam has a configuration.
- **A refused heartbeat stopping the exam page:** not exercised. One heartbeat was sent and accepted.
- The waiting page turning into the exam, *Akhiri*, rotation of a configuration, a second SEB version with a second key: not tried.

After closing:

- `infra/smoke.sh` → 79 pass, **6 fail**, all the commit check: `HEAD` is the commit that holds `PROGRESS.md`; `lokaujian` and the scheduler run `9e506ce`, the others `fdbf765`.
- `infra/backup.sh` → done. `infra/uji-restore.sh` → LULUS, 68 schemas/tables.

Left in production by the gate: a third published exam with its SEB configuration and one registered key, one session, one submitted attempt, three integrity events — all of the test student in the test class.

**Not closed, no tag.** The gate of `SPEC-LOKAUJIAN.md` §15 for U4 — "with real SEB 3.10.x on Windows a student starts and finishes an exam; the same student in ordinary Chrome is refused; a lecturer in Chrome still gets in" — **was seen on the deployed system in all three parts.** Open for the owner: close U4, and whether the other components are redeployed for the tag.

### LokaUjian U4 closed by the owner (2026-11-27)

The owner ordered: "add 3.10.3 to SEB version that can be used. after that close u4 and redeploy".

- **SEB 3.10.3: nothing in the code or the configuration names a version, so there was nothing to add.** The server never reads the client's version or User-Agent; it accepts a client exactly when both headers match, and the Browser Exam Key in the second header differs for every SEB version. The pages already say "3.10.x". SEB 3.10.3 becomes usable for an exam when **its** Browser Exam Key — read from the SEB Configuration Tool of 3.10.3 with that exam's file — is added as a second line in that exam's panel (for LokaKode: in the course's panel). The server cannot compute that key; only a person with 3.10.3 installed can read it. **Not verified:** that 3.10.3 computes the same Config Key from the file as 3.10.2 does; the first student with 3.10.3 and a registered key is the check.
- U4 is closed on these terms. Seen on the deployed system: a real SEB 3.10.2 client accepted through a whole exam, the student refused outside SEB, a lecturer in Chrome getting in, a paste refused and SEB quit and reopened mid-exam. **Not seen by anyone:** the list of integrity events, the file of one exam refused on another, a refused heartbeat stopping the exam page, rotation, a save after the deadline refused (carried from U3).

All five components are deployed at the commit that holds this entry; the tag `lokaujian-u4` goes on that commit once `infra/smoke.sh` passes there.

### Closing deploy of LokaUjian U4 — 2026-11-27

Recorded on the owner's word at the start of the U5 session: on 2026-11-27 all five components were redeployed at `926aee4`, `infra/smoke.sh` passed with 85 pass and 0 fail, and the tag `lokaujian-u4` was placed. The output of that deploy and that smoke run is in the session that made them, not in this one.

Read again at the start of U5 (2026-11-27 08:40 WIB, read-only):

- `GET /healthz` inside `auth`, `lokakode`, `portal` and `lokaujian` → each answers commit `926aee4409358fd8b853109091777045bcfd0853`; the scheduler's heartbeat carries the same commit.
- `git rev-parse lokaujian-u4^{commit}` → the same commit; `git ls-remote --tags origin` lists `lokaujian-u4`; `main` equals `origin/main`.
- `infra/smoke.sh` → `85 lulus, 0 gagal`.
- `infra/pemeliharaan.sh status` → `lokaujian.lokandita.com` in maintenance (503); the other three hosts open.

Not read again: the judge worker's commit on its own (smoke check 6 covers it and passed), `infra/tes-infra.sh`, backup and restore.

This entry is committed after that deploy, so `infra/smoke.sh` check 0 reports a different commit for every component until the next deploy.

## LokaUjian U5 — lecturer control, scoring and export (2026-11-27)

Plan presented and answered by the owner with "approve all": `SPEC-LOKAUJIAN.md` §17 points 2, 6, 7, 8, 13 and 15 confirmed; release only after the class's session is over, and no reopening while a class is released with the answers; correction allowed while attempts run; SEB stays enforced for the gate; a pengajar gets the long CSV with its keys. Choices are in `DECISIONS.md`. One commit: `4febf28`. **Built and deployed; not closed — the owner's gate is open, no tag.**

Built (`KONTRAK.md` §24, and LokaUjian's part of §14):

- **Scoring:** `attempts.score`, the objective score, computed in full when an attempt ends — on *Kumpulkan*, and in the scheduler's pass, which now marks and then scores.
- **Extra time:** 1–240 minutes for the ticked students of the live table, any number of times, only adding; a student who has not started gets it at the start; one selected student who has already finished refuses the whole request.
- **Reopen for one student:** 1–600 minutes and a reason; the same attempt with every answer, deadline counted from the reopening; also for a student who never started once the session is over. Refused while the class is released with the answers.
- Both are two requests: a page that names the students, the minutes and the reason, then the same form with a confirmation. Both are audited, one row per student.
- **Live view:** the session page lists every `AKTIF` student of the class (and the owner of an attempt who has left it): not arrived, waiting, working (connected, or disconnected since hh:mm), finished and how; answered out of total; last save; time left; extra and reopening minutes; integrity events; totals on top. It refreshes itself every 5 seconds from `/sesi/{id}/langsung` without reloading. No answer and no score on it.
- **Correction:** `/mata-kuliah/{m}/ujian/{id}/koreksi` shows every question of the published exam with its key; key or points of one question can be changed, every submitted attempt is rescored in the same transaction, and the audit row holds old value, new value and how many scores changed. The bank's question is not touched.
- **Release per class:** `nilai` or `nilai_dan_kunci`, and withdrawal, for superadmin and the pengampu, only when the session is over. Releasing answers while another class of the exam is unfinished shows a warning that names the class and needs a second confirmation. Students see the score on their home and receipt; the review (`/ujian-saya/{s}/tinjau`) is inside SEB with that exam's file, and the home keeps the file's link for it. Pictures are answered for a released review.
- **CSV:** long, wide and integrity events for one session; long and wide for all sessions of an exam the requester may see. UTF-8 with BOM, CRLF, semicolon or comma, apostrophe before a cell that starts like a formula, times in Asia/Jakarta, downloads audited.
- **Audit page** `/audit` for the superadmin (filter by action, course, actor's nomor induk, dates; read-only) and **own data** at `/data-saya`, outside SEB.
- Migration `2026-11-27-000004`; texts in `id`, `en`, `ar` (one new language file, 164 keys); smoke check 18 extended; `SPEC-LOKAUJIAN.md` §7.3, §11, §12 and §17 updated with the owner's answers; `KONTRAK-LOKAUJIAN.md` now holds only what U6 adds.
- **No file of `auth`, `lokakode`, `portal`, the judge or the shared package changed** (`git diff c7fc75c --stat` lists only `apps/lokaujian`, `infra/smoke.sh` and the root documents).

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh lokaujian` at the final code → `OK (121 tests, 12546 assertions)`, 15 minutes 23 seconds, against real MySQL, Redis, `auth-tes`, `lokaujian-tes`, `lokaujian-seb-tes` and a real headless Chromium. New in U5:
  - `MatriksAksesTest`: 74 routes × seven actors. The 13 new ones: pengajar B and the pengampu of another course get 404 on the live view, extra time, reopening and both CSV routes of class XA; a pengajar gets 404 on release and 403 on correction; only the superadmin opens `/audit`; student B gets 404 on student A's review; every refused write is sent with valid fields and a valid CSRF token and leaves the checksum of all seventeen tables unchanged.
  - `KendaliTest`: the live view through a whole session (not arrived, waiting, working with 2 of 6, disconnected since a time, unknown with Redis empty, finished) and no answer text in it; extra time with its confirmation page, applied to a running attempt and to a student who starts later, **a save accepted after the session's end because of it while the other student's is refused**; every refusal changes nothing; reopening: refused while running, then the same attempt id, the same order and the stored answers, deadline 30 minutes from the reopening, extra time moving it, `waktu_habis` and a full rescore when it ends; a student who never started may start after the session is over and only that student.
  - `NilaiRilisTest`: scores at *Kumpulkan* and after the scheduler; a corrected key turns 1 and 1 into 0 and 2 with "2 rescored, 2 changed" in the audit row; points 2,5; key letters `B` → `A`; invalid points and keys refused with nothing changed; the bank question and the frozen text unchanged. Release: refused before the session is over and to a pengajar; `nilai` shows "Skor objektif: 1 dari 2" and opens no review; `nilai_dan_kunci` with class XB unfinished shows the page that names XB and changes nothing until confirmed; the review holds the student's own answers and the keys and nobody else's answer; a correction after release reaches the student's page; withdrawal hides everything again. With enforcement **on**: the review and its picture are 403 without SEB headers and 200 with the headers of that exam.
  - `CsvTest`: both files and the event file fetched over HTTP and read back — BOM, every record ending in CRLF, the long file's 15 headers and 6 rows, `'=1+1` and `'-minus di depan`, an answer with a line break, a semicolon, quotes, a comma and Arabic text coming back byte for byte, `2,5` with the semicolon and `2.5` with the comma, the wide file with the student who never started and with the one who left the class, "Sedang mengerjakan" while the session runs. A pengajar's "all classes" file holds only their own class; five kinds of outsider get 404 and no audit row.
  - `AuditDataSayaTest`: the audit page and its filters; no route of any method other than `GET /audit` names the audit; the own-data file holds the student's attempts, answers by the number they saw and events, no question text, option, key or other person's row, the score only after release, and a second download within a minute is 429.
  - `TampilanLangsungTest`, in the browser: the table updates itself (status, answered, time left, extra minutes) while `window` is not reloaded and a ticked box stays ticked; a changed session state shows the reload notice; no CSP violation and no script error.
- The first complete run had 4 failures out of 121; the second run passed. See "Mistakes" below.
- `infra/cek-env.sh` → LULUS. Before the deploy: 0 attempts in progress in production, host in maintenance.
- `infra/deploy.sh lokaujian` at `4febf28` → migration done; `/healthz` and the scheduler's heartbeat (from the new container) report the commit.
- `infra/smoke.sh` → **87 pass, 4 fail**: the commit check of `auth`, `lokakode`, `portal` and the worker — not redeployed, by the owner's instruction. Check 18 passes with its six new lines.
- **The scheduler scored the three attempts already in production** on its first pass: 2.00 of 2, 1.00 of 2 and 1.00 of 2 objective points (all of the test student). `time_grants` is empty and no session is released.
- From inside, `/js/langsung.js` → 200; from the internet `/sesi/1/langsung` → 503 (maintenance).
- `infra/backup.sh` → done. `infra/uji-restore.sh` → LULUS, 69 schemas/tables.

Not verified, or weaker than it looks:

- **No logged-in request has reached the deployed U5 code, and no person has seen any page of U5**: the live table, the confirmation pages, correction, release, the review, the audit page, own data. One page — the live view — was driven by the test browser; the others were read as HTML by tests only. None was looked at in Arabic or dark mode.
- **No CSV has been opened in Excel.** The tests prove bytes and structure. Whether Excel with Indonesian settings shows the columns, the Arabic text, the line breaks and `2,5` as a number is the owner's gate. The decimal comma is the agent's reasoning about Excel, not something seen.
- **No real SEB has opened the review.** Its acceptance is a header the test computed itself. Inside SEB a student reaches it from the exam's start address → the receipt → *Buka tinjauan*.
- "Connected" is a mark in Redis written when the student's page asks the server; a student whose page is open but whose network is gone shows as disconnected after 30 seconds, and a student in the 10-second gap between two polls may show as disconnected for a moment after a restart of Redis.
- The time left in the live table is the server's value every 5 seconds; it does not count down in between.
- A correction or a release takes effect at once and is audited, but nothing asks "are you sure" on the server for a correction — only a browser dialog.
- With several hundred students the two "all classes" files are built in memory; not measured (U6).
- The warning about queued judge jobs on the session page (`SPEC-LOKAUJIAN.md` §14) is **not built**; it needs LokaUjian to read LokaKode's state (U6).
- The suites of `auth`, `lokakode` and `portal` were not run: no file of theirs, of the shared package or of `infra/tes.sh` changed.
- The Arabic texts (164 new keys) were written by the agent and read by no Arabic speaker.
- `PROGRESS.md` is committed after the deploy, so smoke's commit check reports a different commit for `lokaujian` too until the closing deploy.

Mistakes of this session:

- **The student's home page wrote to the database on every load.** To "bring the state up to date" the agent first ran the scheduler's pass on every view of the home and the receipt. An existing test caught it (a refused save "changed data"). Corrected before any deploy: those two pages run the pass only when a released result has no score yet.
- **A save after the time ran out could be answered "dikumpulkan".** Once an ended attempt had been marked, the save endpoint said the student had submitted. It now says `waktu_habis` unless the student submitted. The same answer was possible before U5 whenever the scheduler had already passed; U5 made it visible.
- The staff CSV and live view first reused the student's wording for how an attempt ended ("Anda kumpulkan sendiri"); staff now have their own words.
- Three tests were wrong or out of date on the first runs: one uploaded a picture to another test container than the one that served it (the same slip as in U4), one assumed the live rows were ordered by name (they are ordered by nomor induk), and two older tests still described `/data-saya` as absent and the old three-count sentence.
- The first full run was cut off with the session and had to be read from the test container; nothing was lost.
- The plan said the live view would be drawn from JSON by the page; it is drawn by the server and the page copies the server's sentences — one renderer instead of two (`DECISIONS.md`).

Changed on the server outside Git: nothing in any `.env`. Production schema `lokaujian`: migration `2026-11-27-000004` ran (one table, four columns on `attempts`, three on `sessions`), and the three existing attempts received a score. All of it is in the backup of 2026-11-27 02:43 UTC.

Owner's gate for U5 (`SPEC-LOKAUJIAN.md` §15): a lecturer-controlled session with two test students, ended, and both CSV files opened in Excel with correct characters and every answer present. SEB is enforced, so both students work in SEB on Windows — on two machines, or one after the other on one (the second joins late with the time that is left).

1. **Before opening the host:** a second test student in the test class, `AKTIF`, with `lokaujian` access (LokaAuth and LokaKode). Give one of the two a name or type an answer with Arabic text, so the characters can be checked.
2. Tell the agent to open the host.
3. In Chrome, as superadmin or pengampu: a new exam from the bank with at least one multiple-choice, one short and one long question → **Terbitkan** → **Atur Safe Exam Browser** → create the configuration, register the Browser Exam Key of your SEB (as in the U4 gate) → **Berikan ke kelas** (the test class) → open the session.
4. The session page lists both students as *Belum hadir*. Press **Mulai** with 15 minutes.
5. Student 1 in SEB: open the exam's file, **Mulai**, answer; in one text box type a line break and an answer that begins with `=` (for example `=1+1`). Watch the lecturer's page: the row turns to *Mengerjakan — tersambung*, the answered count and the last save move by themselves.
6. Tick student 1, **Tambahan waktu** 5 minutes → the confirmation names the student → confirm. The student's countdown gains 5 minutes within about 10 seconds.
7. Student 1: **Selesai** → **Kumpulkan**. Student 2 in SEB: start late, answer something, stay on the page.
8. Press **Akhiri**. After about 40 seconds both rows say *Selesai*.
9. **Unduh CSV**: *Lebar* and then *Panjang*, separator *Titik koma*. Open both in Excel: one student per row in the wide file, one row per question in the long one, Indonesian and Arabic characters correct, the line break inside its cell, `=1+1` shown as text and not as 2, every answer present. If your Excel splits nothing into columns, download again with *Koma* and say so.
10. Optional, a few minutes each: **Buka ulang** for student 1 with 5 minutes and a reason (the student continues with the old answers); **Koreksi kunci dan poin** on one question; **Rilis hasil** *Skor objektif saja* and look at the student's home; `/audit` as superadmin; `/data-saya` as a student.
11. Tell the agent to close the host; it reports what the server recorded and runs smoke, backup and the restore test.

After the gate: tag `lokaujian-u5` on the commit that records it. Redeploying `auth`, `lokakode`, `portal` and the judge worker so that the commit check passes needs the owner's word.

Carried into U6:

- Arabic and dark-mode review by a person; the load test at 110 students (the live view and the two polls per student belong in it); the warning about queued judge jobs; retention of answers (`SPEC-LOKAUJIAN.md` §17 point 1) and the privacy text; the Portal card and its `/data-saya` link; maintenance off.
- Still unseen on the deployed system unless a gate does them: a save after the deadline refused (U3); the list of integrity events, one exam's file refused on another, a refused heartbeat stopping the exam page, rotation (U4); importing an exam file (U2); a pengajar refused another class and the bank (U1).
- The test class, `Dummy` and three published test exams remain in production.

### Gate result for LokaUjian U5 (2026-11-27)

The owner reported that the second test student was in the test class; the agent opened the host at 09:51 WIB and closed it at 11:30 WIB on his report. It answers 503 from the internet again. The owner had one laptop and one phone: the lecturer's pages were used from two addresses (setup and downloads from one, the running of the session from another), and the two students worked one after the other in SEB.

**The owner's report:** nothing went wrong in the session; the countdown of the student who was given extra time grew and the other's did not; both files held all answers; `=1+1` was shown as text; a line break stayed in one cell; Arabic was readable; scores were correct; nothing else looked wrong. He left the line about *Akhiri* unfilled.

**What the server recorded** between 11:14 and 11:28 WIB (counts, routes, statuses and times only):

- **Exam and session:** a new lecturer-controlled exam with 2 questions, both `uraian`, made from the bank and published at 11:14; its SEB configuration created at 11:15 and one Browser Exam Key registered at 11:16; given to the test class (2 `AKTIF` students) at 11:16; **started at 11:21:20 with 30 minutes**.
- **First student:** *Mulai* at 11:22:18; one of two questions answered (last save 11:22:46); **submitted by the student at 11:23:35**.
- **Extra time:** at 11:23:10, while that student was working, the confirmation page was answered (`200`) and then 5 minutes were granted (`303`, `waktu.ditambah`, one row in `time_grants`). The attempt carries `extra_minutes = 5`; the other student's carries 0.
- **Second student:** *Mulai* at 11:25:02, joining late; both questions answered, last save 11:25:57.
- **Akhiri:** pressed at 11:25:29; the session's end became 11:25:59, and the second student's attempt was marked `diakhiri_dosen` with that time — two seconds after the last save. The first student had already submitted and was not affected.
- **Live view:** `/sesi/4/langsung` was asked 57 times, every answer `200`.
- **CSV:** *Lebar* at 11:27:21 (2 rows) and *Panjang* at 11:27:27 (4 rows = 2 students × 2 questions), both with the semicolon, both `200`, both audited.
- **Real SEB:** 57 requests from a client naming itself `SEB/3.10.2`, answered 200, 302 or 303 and **none 403**. No integrity event was recorded.
- 200 requests in the window besides the health check: 170 × 200, 29 redirects, one 404 (a `POST /` from a script on the internet). **No 5xx.**
- These were the first logged-in requests to the deployed U5 code.

**What the gate did not exercise — the report says "yes" or "correct", the server's record shows the case did not occur:**

- **Arabic:** no stored answer, no question, no exam title and neither student's name contains a character outside ASCII. No Arabic text was in either downloaded file, so its display in Excel has **not** been seen. It rests on `CsvTest` only.
- **`=1+1`:** one answer contains `=`, but not as its first character (it follows a line break). A cell is treated as a formula only when it **begins** with `=`, and only then does the app add the apostrophe. So the answer was rightly shown as text, and the formula guard itself was **not** triggered. It rests on `CsvTest` only.
- **Scores:** the exam had no `benar_salah` or `pilihan_ganda` question. Both attempts have the objective score 0.00 out of 0 points available — correct, and no test of scoring. Scoring on real data is the three older attempts the scheduler scored at the deploy (2, 1 and 1 of 2), which nobody has compared with the answers.
- **Not opened by anyone:** reopening, correction, release of results, the student's review, `/audit`, `/data-saya`, the events CSV, the "all classes" download, the comma separator. No route of them appears in the log.
- **Not seen as a class would produce it:** the two students were never working at the same moment, so the live table never showed two rows in *Mengerjakan*, and *Akhiri* stopped one page, not two.

What the pages and the two files looked like is the owner's observation; he reported nothing wrong.

So the gate of `SPEC-LOKAUJIAN.md` §15 for U5 — "the owner runs a lecturer-controlled session with two test students, ends it, and opens both CSV files in Excel with correct characters and every answer present" — **was done on the deployed system**: the session, the ending and the two downloads are in the server's record, and the files' appearance in Excel is the owner's report. "Correct characters" was seen for Latin text and a line break only.

### LokaUjian U5 closed on the owner's instruction (2026-11-27)

The owner's instruction with his report: "If everything passed, redeploy auth, lokakode, portal and the worker so the commit check passes, run infra/smoke.sh, then tag lokaujian-u5 and update PROGRESS.md." Every item of his report passed, and the written gate is met; the agent took that as the condition fulfilled and says here plainly what the gate left unexercised (above), so that closing is not read as "everything of U5 was seen".

Right before the redeploy: 0 LokaKode submissions in the last 10 minutes, 0 unfinished judge jobs, no active student session other than the gate's own two accounts, 0 LokaUjian attempts in progress.

All five components are deployed at the commit that holds this entry, and `infra/smoke.sh` is run there; the tag `lokaujian-u5` is put on this commit only when that run passes. Their output is in the session; this file is not changed again afterwards, because a later commit would make the commit check fail for every component.

Left in production by the gate: a fourth published exam with its SEB configuration and one key, one session, two ended attempts (3 answers) and one grant of extra time — all of the two test students in the test class.

Carried into U6, in addition to the list under "LokaUjian U5":

- Open a CSV that holds Arabic text and an answer beginning with `=` in Excel; run one exam with automatically scored questions and compare a score with the answers; open reopening, correction, release with the review inside SEB, `/audit` and `/data-saya` once on the deployed system.
- A second test student now exists in the test class.

## Header on phones (2026-11-27) — not a stage

**The report.** On a 412 px Android Chrome the Portal's header showed the logo on the left and the links, the three languages and *Masuk* piled in three or four rows beside it.

**Where it was.** Not in `apps/portal`: the header is `packages/lokandita-auth/src/Views/tata_letak.php` with `gaya/lokandita.css` and `aset/lokandita.js`, used by every page of the four apps. The owner was told before anything was touched and approved changing the shared package; he then asked for the landings of LokaKode and LokaUjian to change as well. Alpine.js, named in the request, is not in this system (`DECISIONS.md`, 2026-11-15); the menu is 25 lines of plain JavaScript.

**What changed.**

- Below 768 px a public header is one row: logo at the start; *Masuk* and a menu button at the end. The button (`aria-expanded`, `aria-controls`, a label in three languages) opens a panel under the header with the links (Portal only) and the three languages. It closes on Escape, a second click, a click outside, a click on one of its links, and when the screen becomes 768 px or wider. Focus goes to the first link on opening and back to the button on Escape or the second click. In Arabic the panel hangs from the left edge.
- From 768 px the row is as before but cannot wrap; between 768 and 1024 px the gaps are smaller. It fits at 768 px in all three languages, so **no compact language dropdown was built**.
- Below 768 px *Masuk* is slightly smaller (padding and font), because the Arabic "تسجيل الدخول" did not fit at 360 px.
- The header after login (logo, account menu) has the same markup and style as before.
- Every page with the public header changes, which is more than the three landings: the Portal's `/tentang`, `/privasi`, `/ketentuan`, and the public SEB pages of LokaKode and LokaUjian. LokaAuth's login page has no public header and is unchanged.
- No new external asset, no change to the CSP, no route, no variable. `KONTRAK.md` changes in §12 only (the new tests).

**Tests, all against real containers through `infra/tes.sh`:** portal 103 tests, auth 111, lokaujian 124, lokakode 298 — each `OK`. New: `apps/portal/tests/Peramban/KepalaTest.php` and `apps/lokaujian/tests/Peramban/KepalaDepanTest.php` on the shared `Lokandita\Auth\Testing\Kepala` (what they measure is in `KONTRAK.md` §12).

**Said plainly:**

- `PembukaTest` ("kartu lokakode ikon tidak termuat", phone width) failed **once** during this work and passed in the three runs after it, the full suite included. It asks whether an app icon has loaded; the header is not involved as far as can be seen, but why it failed that once was not found.
- `PembukaTest` was changed: it now measures only icons that are drawn, because the menu icon is in the page but not displayed from 768 px.
- The first run of LokaKode's suite was cut off when the session's background job was stopped; the result above is a second, complete run.
- A test browser is not a phone. The 20 px of spare room demanded at 360 px is a margin for other fonts, not proof for every phone.
- Without JavaScript the panel's content is a second row. That was written, not tested in a browser with JavaScript off.
- The deploy and `infra/smoke.sh` are run at the commit that holds this entry; their output is in the session. This file is not changed afterwards, because a later commit would make the commit check fail for every component.

**The owner's gate:** Chrome DevTools at 360, 390, 412, 768, 1024 and 1440 px, in Indonesian, English and Arabic, logged out and logged in, light and dark — one row everywhere, nothing overlapping. Also worth one look on the real phone that showed the bug.

### Closing deploy of LokaUjian U5 — 2026-11-27

Recorded on the owner's word at the start of the U6 session: on 2026-11-27 all five components were redeployed at `225e513`, `infra/smoke.sh` passed with 91 pass and 0 fail, and the tag `lokaujian-u5` was placed. The output of that deploy and that smoke run is in the session that made them, not in this one.

Read again at the start of U6 (2026-11-27 14:35 WIB, read-only):

- `git rev-parse lokaujian-u5^{commit}` → `225e513da6727655c57c0f814b3d8ce5e3f385b0`; `git ls-remote --tags origin` lists `lokaujian-u5` on the same commit; `main` equals `origin/main`.
- **The containers no longer run `225e513`.** After the closing deploy the header change ("Header on phones", above) was deployed: `GET /healthz` inside `auth`, `lokakode`, `portal` and `lokaujian` → each answers commit `cb387d310bb4f378ce43260529eb3d3f40360da8`, which is `HEAD`. So the deploy at `225e513` cannot be read from the server any more; it rests on the owner's word and on the tag.
- `infra/smoke.sh` at `cb387d3` → `91 lulus, 0 gagal, 0 belum aktif`.
- `infra/pemeliharaan.sh status` → `lokaujian.lokandita.com` in maintenance (503); the other three hosts open.
- Counts only: 241 accounts have `lokaujian` in `app_access` (237 mahasiswa, 3 dosen, 1 superadmin — every active account; earlier entries say 238). Production schema `lokaujian`: 4 exams, 4 sessions, 5 attempts, none in progress, 1 picture.

Not read again: `infra/tes-infra.sh`, backup and restore, any test suite.

This entry is committed after that deploy, so `infra/smoke.sh` check 0 reports a different commit for every component until the next deploy. Nothing is deployed for it: the owner's instruction for U6 is not to deploy `lokakode`, `auth` or `portal` without asking.

## LokaUjian U6 — readiness (started 2026-11-27)

Begun with a plan for the owner's approval (`SPEC-LOKAUJIAN.md` §14 and §15, and both "Carried into U6" lists). The owner answered "approve all".

**Where it stands (2026-11-27, 15:40 WIB): built and `lokaujian` deployed at `9ee168a`; NOT closed, no tag. The gate of the stage — 110 students at once with no lost answer — has NOT been run: the full run waits for a time named by the owner. The load test has only been tried with 6 and with 25 students.**

Built (commits `af23367`, `9ee168a`; `KONTRAK.md` §6, §10, §11, §12, §24):

- **Load test:** `infra/uji-beban.sh <students> [minutes] [--restart]`, PHPUnit suite `Beban` (not part of `infra/tes.sh lokaujian`). One process plays every student's page as a port of `ujian.js` and `seb.js`: real login through LokaAuth, waiting page, *Mulai*, state every 10 s, SEB heartbeat every 25 s, saves with rising counters and retries; some students submit, lose the connection for 75 s or reopen the page; a lecturer watches the live view, gives extra time and downloads CSV. Every save answered `200 ok` is compared with `answers` and with the long CSV. The helper functions of `infra/tes.sh` moved unchanged into `infra/lib-tes.sh`.
- **Kept from production:** only `*_test` schemas and Redis DB 15, test containers, invented accounts. The script refuses to start, and stops a run, when LokaKode has a submission or an unfinished judge job; the generator is limited to one core.
- **Judge queue warning** on the session page and in the live view: LokaUjian reads the length of LokaKode's two judge queues (new variable `JUDGE_QUEUE_REDIS_URL`), remembers "busy" for 60 seconds, shows a notice; nothing is refused because of it.
- **Restore test:** `infra/uji-restore.sh` now prints LokaUjian's row counts (restored / live) and compares a checksum of `answers` and `attempts`.
- **Arabic and dark mode:** `tests/Peramban/HalamanArabGelapTest.php` opens 18 pages of lecturer, superadmin and student in Arabic light, Arabic dark, Indonesian dark and Arabic dark at 390 px, and writes 82 screenshots to `docs/tangkapan/lokaujian-u6/` for the owner to look through.
- **Portal side, on branch `u6-portal` (`0f30044`), not merged, not deployed, NOT TESTED:** the LokaUjian card as a link (`LOKAUJIAN_URL`), the `/data-saya` link, a smoke line, `KONTRAK.md` §4, §10, §11, §14, §16. The privacy text is not written: it needs the owner's decision on retention.

Verified by the agent (commands and output shown in the session):

- `infra/tes.sh lokaujian` → `OK (126 tests, 13884 assertions)`: the 124 of before plus `AntreanJudgeTest` (2). This run also proves the moved functions of `infra/lib-tes.sh` for `lokaujian`; `worker_tes` (used only by the LokaKode suite) moved with them and was **not** run.
- `infra/tes.sh lokaujian --filter 'HalamanArabGelap|testArabDanModeGelap'` → `OK (2 tests, 665 assertions)`: every page has the right direction, nothing overflows sideways at 1280 or 390 px, no script error, no CSP violation.
- `infra/uji-beban.sh 6 3` → pass: 124 saves sent, 124 confirmed, 0 lost. `infra/uji-beban.sh 25 4` → pass: 602 saves sent, 602 confirmed, 0 repeated, 0 lost, 0 answers of 4xx or 5xx; one page reopened with its counters intact; the scheduler marked and scored all 25; long CSV 1000 rows. Slowest save 2.0 s, 95 % under 0.15 s. Average CPU during the run: `lokaujian` 13 %, `mysql` 17 %, generator 3 % of one core; the public landing of production LokaKode never took longer than 0.3 s. **These two runs prove the harness, not the gate.**
- `infra/cek-env.sh` → LULUS. Before the deploy: 0 attempts in progress in production, host in maintenance.
- `infra/deploy.sh lokaujian` at `9ee168a` → `/healthz` and the scheduler's heartbeat report the commit.
- In the deployed container `App\Libraries\AntreanJudge::baca()` → `{"antre":0,"sibuk":false}` while `LLEN lokandita:judge:normal` is 0. The "busy" state was seen in the test only; nothing was pushed into the real queue.
- `infra/smoke.sh` → **87 pass, 4 fail**: the commit check of `auth`, `lokakode`, `portal` and the worker, not redeployed by the owner's instruction.
- `infra/backup.sh` → done (`lokandita-20261127-082617`, 1 question picture). `infra/uji-restore.sh` → LULUS, 69 schemas/tables; attempts 5 / 5, answers 14 / 14, checksum of `answers` and `attempts` equal; the one picture restored with its recorded size and SHA-256.

**Found while measuring — production, not LokaUjian:**

- **MySQL ran out of connections during a real LokaKode class.** `Max_used_connections` is 152 with a limit of 151, reached on 2026-11-23 at 13:52 WIB; `Connection_errors_max_connections` is 1,369 since MySQL started seven days ago. Between 13:45 and 14:30 WIB that day LokaKode took 477 Run and 124 Submit jobs. What students saw is not known: the app containers have been recreated since and their logs are gone.
- Each app container allows 150 Apache workers and a request of a client app opens up to three MySQL connections (own schema, identity reader, roster reader for LokaUjian); MySQL allows 151 for everything. Nothing was changed: `max_connections` belongs to `infra` and changing it restarts MySQL for every app. The 110-student run will show how many connections one exam needs; the fix is the owner's to approve.

Not verified, or weaker than it looks:

- **The gate has not been run.** No run with 110 students, none with 250, none with the restart in the middle.
- The load generator is not a browser and not SEB: it computes the SEB headers itself, sends no static-file requests, and reaches the apps through a test Caddy over plain HTTP on the server itself. Campus network, TLS and real clients are outside it.
- The generator, the loaded containers and production share four cores; numbers of a large run are for this server as it is, with LokaKode idle.
- The size of the "all classes" CSV in memory is not measured; the test only sees whether the download succeeds and how long it takes.
- The Portal branch was never run: the Portal's test container needs `LOKAUJIAN_URL` in `apps/portal/.env`, and adding that line was not allowed in this session. It is a public address, not a secret.
- Arabic wording (now also the judge notice) is the agent's and was read by no Arabic speaker. Seen by the agent in three screenshots: in the review, an answer whose first line is Arabic draws a following Latin line right-to-left, with its full stop at the wrong end.
- The confirmation pages of extra time and reopening are not among the screenshots (they are answers to a form, not addresses).
- `PROGRESS.md` is committed after the deploy, so smoke's commit check reports a different commit for `lokaujian` too until the next deploy.

Mistakes of this session:

- A copy of `apps/lokaujian/.env` was made next to it before the new variable was added (`.env.sebelum-u6`). It was not ignored by Git and not excluded from the image build. It was deleted within the same minute, before any build or commit.
- The first start of `infra/uji-beban.sh` stopped silently after its first line (a `[ … ] && echo` inside an assignment under `set -e`); corrected.
- The load generator spun on one core while waiting; corrected before the 25-student run (average 3 %).
- The plan promised small commits per topic; the load test, the warning and the restore test went into one commit because they share hunks of `KONTRAK.md` and `infra/lib-tes.sh` and were tested as one.

Changed on the server outside Git: `apps/lokaujian/.env` gained `JUDGE_QUEUE_REDIS_URL` (the value of `REDIS_URL` with DB 0; never printed). Nothing in any other `.env`, nothing in MySQL's settings, no `app_access` row, no flag of any host.

Waiting for the owner:

1. **A time for the 110-student run** (about 25 minutes; then 250 students, then 110 with a restart). The script refuses to run while LokaKode is in use.
2. Whether `LOKAUJIAN_URL` may be added to `apps/portal/.env` so the Portal branch can be tested (the running Portal is not restarted by it).
3. Retention of answers and the privacy text; which accounts keep `lokaujian` access; when the host opens and the Portal branch is merged and deployed; what happens to the test exams in production.
4. What to do about MySQL's connection limit.
5. His own checks (`SPEC-LOKAUJIAN.md` §15 and the lists carried into U6): the screenshots in `docs/tangkapan/lokaujian-u6/`; Excel with Arabic text and a cell beginning with `=`; one exam with automatically scored questions; reopening, correction, release with the review inside SEB; `/audit`; `/data-saya`.

### Load test results and what followed (2026-11-27, 20:42 to 2026-11-28, 00:10 WIB)

The owner answered "approve your recommendations" and later "approve all". Taken as: run the load test in the evening; add `LOKAUJIAN_URL` to `apps/portal/.env`; bring the numbers before touching MySQL — and, once they were shown, raise MySQL's connection limit. **Not taken as an answer**, because no recommendation with a value was on the table: the retention period, which accounts keep access, when the host opens.

All runs on the test schemas and test containers, 15 minutes of exam, 40 questions, LokaKode idle throughout (the guard never fired; the public landing of production LokaKode never took longer than 0.3 s). Raw output: `infra/beban-hasil/` on the server (not in Git).

| Run | Saves sent / answered `200 ok` | Lost | Other answers | Result |
|---|---|---|---|---|
| 110 students | 8,872 / 8,872 | 0 | none of 4xx, 5xx or no answer | **pass — the gate of `SPEC-LOKAUJIAN.md` §15** |
| 110 students, app container restarted in the middle | 8,969 / 8,950 | 0 | 54 × 502 during the 2-second restart (19 of them saves, all repeated and accepted), one 500 of the live view | **pass** |
| 250 students, MySQL at 151 connections | 21,811 / 21,384 | 0 | 933 × 500; one student's *Kumpulkan* answered 500 (the attempt ended by time, answers kept) | **fail** — no answer lost, but the server did not hold |
| 250 students, MySQL at 500 connections | 21,424 / 21,419 | 0 | one 502, four 409 (saves after the time was up, rightly refused) | **pass** |

- **110 students:** half of all saves answered within 0.06 s, 95 % within 0.15 s, the slowest 1.8 s. During the exam 10–13 MySQL connections, `lokaujian` and `mysql` each about half a core on average. Five pages reopened with their counters intact; six came back after 75 seconds without a connection; the scheduler marked and scored all 110; long CSV 4,400 rows in 0.7 s.
- **Logins of the first 110 run were bunched** into about 15 seconds by a mistake in the test (its clock started before the accounts were made): every login succeeded, the slowest took 10.7 s, MySQL connections peaked at 50. Corrected for the later runs; with 250 logins spread over a minute the slowest took 4.9 s.
- **250 students at the old limit:** MySQL stood at 152 connections, LokaUjian at 147 of its 150 Apache workers, load average 32. `Connection_errors_max_connections` rose from 1,369 to 2,799 over the two runs of that size (the first was cut off when the agent's session ended, and repeated).
- **250 students at 500:** MySQL peaked at 265 connections; no 500. The next limit shows instead: LokaUjian used all 150 Apache workers and the load average reached 38, so the slow end is long — 95 % of saves within 0.9 s, 1 % above 2.8 s, the slowest 17.6 s; opening the exam page took up to 10 s at the start. Nothing was lost and every page kept working. **So: 110 is comfortable; 250 holds, slowly at the moments everybody does the same thing.**
- **"All classes" CSV:** 10,000 rows (250 students) built in 1.8 s inside the 128 MB limit; its memory was not measured separately.

Done after the numbers:

- **MySQL `max_connections` 151 → 500** (`bed8f1e`): set on the running server with `SET GLOBAL` — **no restart**, nobody was disconnected — and written into `infra/compose.yml` so it stays after the container is recreated; `infra/smoke.sh` check 19 fails below 500; `KONTRAK.md` §9, §11. This also answers the failure of 2026-11-23 in LokaKode's class. The value in the running server is lost if MySQL restarts before `infra/deploy.sh infra` has recreated the container from the new file; **that recreate was not done** (it restarts MySQL) and is the owner's to order.
- **A page whose time ran out was told "sudah dikumpulkan"** when the scheduler had marked the attempt before the page asked (two of 110 pages in the restart run). `keadaan.dikumpulkan` is now true only when the student submitted; test added; `KONTRAK.md` §22.
- `infra/tes.sh lokaujian` → `OK (127 tests, 14445 assertions)`. `infra/deploy.sh lokaujian` at `bed8f1e` → `/healthz` and the scheduler's heartbeat report the commit (0 attempts in progress, host in maintenance).
- `infra/smoke.sh` → **88 pass, 4 fail**: check 19 passes (`max_connections = 500`); the four are the commit check of `auth`, `lokakode`, `portal` and the worker, still at `cb387d3` by the owner's instruction.
- **Portal branch `u6-portal`** (`LOKAUJIAN_URL` added to `apps/portal/.env`; the running Portal was not restarted): `infra/tes.sh portal` → first 8 failures — a browser test still said "only LokaKode is a link" — then, with that test brought up to date, `OK (103 tests, 14934 assertions)`. Merged with `main`, pushed, **not merged into `main`, not deployed**.

Not verified, or weaker than it looks:

- Everything said under "LokaUjian U6" about the generator still holds: no browser, no SEB client, no TLS, no campus network, and the generator shares the four cores.
- No run with a LokaKode class working at the same time. `SPEC-LOKAUJIAN.md` §14 ("an exam is not scheduled while a LokaKode class is working") therefore **stays in force**: the load test says nothing against it.
- The 250 run at the new limit was made once.
- The suites of `auth` and `lokakode` were not run in this stage; no file of theirs changed. `infra/lib-tes.sh` was exercised for `lokaujian` and `portal`; `worker_tes` still was not.
- No backup or restore test was made after the last deploy (the one of 15:26 WIB stands; no schema changed since).

What went wrong in the session itself:

- **Three Claude Code sessions were open on this repository at once** for part of the evening (the owner reconnected with `claude --continue` while older sessions were alive). One of them started a second copy of the restart run — refused by the script's own guard — and wrote the "dikumpulkan" fix; another stopped and reported instead of continuing. Nothing was lost or overwritten, but for an hour the working tree had two authors.
- Two wait loops (`while pgrep -f 'infra/…'`) matched their own command line and never ended; one cost ten minutes, one was left running in a stale session.

Still open — the stage is NOT closed and has no tag:

1. Owner's decisions: retention of answers and its privacy text; which of the 241 accounts keep `lokaujian` access; when the host opens — with it the merge and deploy of `u6-portal`; the test exams in production.
2. Owner's checks: the screenshots in `docs/tangkapan/lokaujian-u6/`; Excel with Arabic text and a cell beginning with `=`; one exam with automatically scored questions; reopening, correction, release with the review inside SEB; `/audit`; `/data-saya`.
3. `infra/deploy.sh infra` at a quiet moment, so that MySQL's limit no longer depends on a running value.
4. The closing deploy of all five components and the tag `lokaujian-u6`.

### LokaUjian U6 closed on the owner's instruction (2026-11-28)

The owner's answers to the six open points, in his words: "1 keep 2 all 3 now 4 run after do no 6 in the end. close other stale terminals". Read as:

1. **Retention: keep.** Exam answers are assessment records; nothing is removed automatically and there is no period. `SPEC-LOKAUJIAN.md` §13 and §17 point 1 say so now, and so does the Portal's privacy page, which until today did not mention LokaUjian at all: what LokaUjian stores (attempts, answers, objective score, the integrity events with IP address and browser type, "no camera, microphone or screen recording"), what it is used for, who sees it, and that answers are not deleted automatically. Indonesian, English and Arabic; the date of the page is 28 November 2026. **The wording is the agent's and was not shown to the owner before it went live; the Arabic was read by no Arabic speaker.** It can be changed without a deploy at `/admin/teks`.
2. **Access: all.** The 241 accounts keep `lokaujian` access; no row of `app_access` was touched.
3. **The host opens now.** Branch `u6-portal` is merged into `main` (`7bfb9fd`): the LokaUjian card of the Portal is a link, `/data-saya` of the Portal links to LokaUjian's export, `LOKAUJIAN_URL` is a variable of the Portal. `KONTRAK-LOKAUJIAN.md` is deleted: its last items are in `KONTRAK.md` §4, §10, §14 and §16.
4. and 6. **The closing deploy first, the recreate of MySQL after it** — "4 run after do no 6" was read in that order.
5. was not answered: **the owner's own checks are not done.** U6 is closed with them open, as U1 to U5 were closed with parts of their gates unseen.

"Close other stale terminals": at 01:10 WIB no other terminal and no other Claude Code process existed on the server; nothing was closed.

Verified before this entry: `infra/tes.sh portal` on the branch → `OK (104 tests, 15000 assertions)` (two tests had to follow the change: the browser test that said only LokaKode is a link, and the test that expected the old date on the privacy page). `infra/cek-env.sh` → LULUS.

What happens with the commit that holds this entry, in this order; the output is in the session, and this file is not changed afterwards, because a later commit would make the commit check fail for every component:

1. `infra/deploy.sh` for `auth`, `lokakode`, `portal`, `lokaujian` and `judge` — after reading that no LokaKode submission, no unfinished judge job and no LokaUjian attempt is in progress.
2. `infra/pemeliharaan.sh lokaujian off`: **`lokaujian.lokandita.com` is open from then on.**
3. `infra/smoke.sh`; the tag `lokaujian-u6` is put on this commit **only when that run passes with 0 failures**.
4. `infra/deploy.sh infra`, which recreates MySQL from `infra/compose.yml` (`max_connections = 500`), then `infra/smoke.sh` again, `infra/backup.sh` and `infra/uji-restore.sh`.

If a step fails, the steps after it are not taken and the owner is told in the session; this entry then describes an intention, not a fact, until a later entry corrects it.

**Open after U6, and to be known before the first real exam:**

- **The host is open with all 237 students allowed in, and four test exams of the test class are still published in production** (the owner did not answer what to do with them). Students of other classes do not see them; the two test students do.
- **None of the owner's checks of the plan has been done:** the 82 screenshots of Arabic and dark mode; a CSV with Arabic text and a cell beginning with `=` in Excel; one exam with automatically scored questions compared by hand; reopening, correction, release with the review inside SEB; `/audit`; `/data-saya`. From earlier stages still unseen on the deployed system: a save refused after the deadline, the integrity events list, one exam's `.seb` file refused on another, rotation, importing an exam file, a pengajar refused another class.
- **"No exam while a LokaKode class is working" stays in force** (`SPEC-LOKAUJIAN.md` §14): no load run was made with both at once.
- 250 students hold, slowly at peak moments (all 150 Apache workers of LokaUjian in use). One cohort of about 110 is comfortable.
- `SPEC-LOKAUJIAN.md` §15: before real use, one low-stakes quiz with a single class.
- The header-on-phones gate (not a stage) is still open.
- Next by `SPEC-LOKAUJIAN.md` §15: the platform stages A1 and A2, not part of LokaUjian. Not started.


### Stage A1 — roster to the core: built and rehearsed, production NOT moved (2026-11-28)

The stage is **open**: no tag, nothing deployed. Branch `a1-roster`. Decisions: `DECISIONS.md`, 2026-11-28; contract: `KONTRAK.md` §18.

Done:

- `infra/mysql-pindah-roster.sh` (`status`, `pindah`, `pasang`, `balik`); `mysql-init.sh` creates `roster` and `roster_test` for `auth_app`; `backup.sh` dumps `roster` before `lokakode`; `uji-restore.sh`, `smoke.sh` (checks 13 and 20), `deploy.sh lokakode` (`pasang` only), the test harness and `docs/RESTORE.md` follow the new layout.
- The test schemas are moved (`lokakode_test`: 6 views; `roster_test`: 6 tables). On that layout: `infra/tes.sh lokakode` → `OK (300 tests, 16794 assertions)`; `lokaujian` → `OK (127 tests, 14445 assertions)`; `judge` → `259 passed`; `auth` → `OK (111 tests, 1706 assertions)`; `portal` → `OK (104 tests, 15000 assertions)`.
- `infra/backup.sh` → `lokandita-20261128-044528`, `backup SELESAI`; `infra/uji-restore.sh` → `LULUS — 70 skema/tabel` (old layout: "backup ini dibuat SEBELUM tabel roster pindah").
- **Rehearsal of the move on a copy of that backup**, in two temporary MySQL containers without network, removed afterwards: `pindah` took 3.4 s; rows before and after 1 / 3 / 11 / 11 / 238 / 1 (years, courses, classes, teachers, enrolments, moves), the same through the old-name views and the contract views; 6 foreign keys from `lokakode` into `roster`; a dump of the new layout loaded into the second container with the same counts and 6 keys; `balik` put everything back, 0 keys into `roster`.

State of production: the six tables are still in `lokakode`; the schema `roster` exists and is empty (`mysql-init.sh` was run); `ROSTER_READER_DSN` of LokaUjian still ends in `lokakode`. All containers run the commit of the U6 closing deploy.

**Until the move is made, on this branch:** `infra/deploy.sh lokakode` stops at `pasang` (by design: it refuses while the tables are in `lokakode`), and `infra/smoke.sh` fails checks 13 and 20. The nightly backup works (shown above).

Not verified: the rehearsal containers had fresh users with random passwords, so it did not exercise the real `roster_reader` login of LokaUjian; no request of LokaKode or LokaUjian has run against the moved production layout; `uji-restore.sh`'s roster section has only run its "before the move" branch.

Open: the production move (needs the owner's `SETUJU=produksi`), deploy, smoke, merge into `main`, tag `tahap-a1`.

### Stage A1 — the roster is in the core in production (2026-11-28, 12:00–12:15 WIB)

On the owner's "do it now" to the plan shown in the session. LokaKode and LokaUjian in maintenance from 12:05 WIB; Portal and Akun stayed open.

1. Before: every `judge_jobs` row `DONE`, none made in the last 15 minutes, 0 attempts in progress. Rows 1 / 3 / 11 / 11 / 238 / 1 (years, courses, classes, teachers, enrolments, moves); `CHECKSUM TABLE` of the six kept.
2. `infra/backup.sh` → `backup SELESAI` (05:05 UTC, the last backup of the old layout); `infra/uji-restore.sh` → `LULUS — 70 skema/tabel`.
3. `SETUJU=produksi infra/mysql-pindah-roster.sh pindah lokakode` → exit 0; `status`: `di lokakode: 0 tabel dan 6 view; di roster: 6 tabel`. Same six row counts in `roster` and through the views; **the six checksums equal those taken before the move**; 6 foreign keys from `lokakode` into `roster`.
4. `ROSTER_READER_DSN` in `apps/lokaujian/.env` now ends in `roster`; `infra/cek-env.sh` → LULUS.
5. `infra/deploy.sh lokakode` and `lokaujian` at `ee5ace5` → both verified by `/healthz`, the scheduler by its heartbeat.
6. `infra/smoke.sh` → **96 pass, 3 fail**: checks 13 and 20 (roster) pass in full — `roster_reader` reads the five views (1, 3, 11, 11, 238 rows) and is refused the tables; the layout, the 6 keys and the rights are as the contract says. The three failures were the commit check of `auth`, `portal` and the worker, not yet deployed from this branch.
7. As `lokakode_app`, from inside the LokaKode container: a join of the `classes` view with `meeting_class_windows` (14 rows), 238 enrolments through the view, an `UPDATE` and an `INSERT` through the views accepted (no value changed, rolled back), `CREATE TABLE` in `roster` refused (1142).

**Not verified: no page was opened while logged in** — the agent has no account. The owner must open, in LokaKode, a class page, the participants list and the class-move screen; in LokaUjian, the class list and one exam's participants.

What happens with the commit that holds this entry, in this order; the output is in the session, and this file is not changed afterwards, because a later commit would make the commit check fail:

1. Merge `a1-roster` into `main`, push; `infra/deploy.sh` for `auth`, `lokakode`, `portal`, `lokaujian`, `judge`.
2. `infra/smoke.sh`; the tag `tahap-a1` only when it passes with 0 failures; then maintenance off for LokaKode and LokaUjian.
3. `infra/backup.sh` and `infra/uji-restore.sh` — the first backup of the new layout, and the first run of the roster section of the restore test.

If a step fails, the steps after it are not taken and the owner is told in the session; this entry then describes an intention until a later entry corrects it.

Open after A1: backups from before 05:10 UTC of 2026-11-28 fit only the old layout (`docs/RESTORE.md`, "The roster lives in two schemas"). `lokakode_app` still writes the roster until A2. Everything listed as open after U6 is still open. Next: A2 (class management screens move to Akun Lokandita). Not started.

### Gate result for stage A1 (2026-11-28, about 16:25 WIB)

The owner sent the gate report as its template, with the three blanks (`<your note on 235 vs 238>`, `<n> pass`, `<anything that looked wrong>`) not filled in, and the order "Tag roster-a1 and update PROGRESS.md". What the agent measured itself is below; what it could not see is marked.

Measured by the agent (counts and states only; no row of a person was printed):

- **235 vs 238 enrolments.** 235 is the import of 2026-11-20 (234 rows made that day and 1 of 2026-11-15, reactivated by the import). The other 3 were made afterwards: 1 on 2026-11-25 in class 11 (TS-PY, now 29 where the file had 28), and 2 on 2026-11-26 and 2026-11-27 in class 12, a DDP101 class created on 2026-11-26 that has only those two and is the class of all four LokaUjian sessions. They belong to 2 `mahasiswa` accounts created on 2026-11-24 and 2026-11-27, after the import; the first holds two of the three (class 11 and class 12). So 238 enrolments, 237 people, 237 active `mahasiswa` accounts (235 + 2), all `AKTIF`, none without an account. Classes: 11 = the 9 imported, class 12, and the archived, empty class 1. The agent takes the two accounts to be the owner's test students but did not read their names. **For the owner: one of them counts as a participant of the real TS-PY class (class 11).**
- `infra/smoke.sh` → **`smoke: 99 lulus, 0 gagal, 0 belum aktif` / `smoke: LULUS`**; check 0: `auth`, `lokakode`, `portal`, `lokaujian` answer with commit `c9b5eac`.
- Backups after the move (05:10 UTC): `lokandita-20261128-051230` and `lokandita-20261128-092057`. The newer one: `SHA256SUMS` all `OK`; the dump holds `identity`, `roster`, `lokakode`, `portal`, `lokaujian`, with `roster` before `lokakode`. `infra/uji-restore.sh` on it → **`uji-restore: LULUS — 75 skema/tabel pulih dan sama dengan yang hidup`**; roster rows restored / live 11 / 11 classes, 11 / 11 teachers, 238 / 238 enrolments, 1 / 1 moves, "6 kunci asing lintas skema ikut pulih". This is a run of the roster section on the new layout. `audit_log` of LokaUjian 25 / 28: three audit rows were written in the minutes after the backup; the script passes that.
- The four exams of the U6 test: all four `arsip`; their four sessions (all class 12) ended on 2026-11-26 and 2026-11-27; 5 attempts, 0 not handed in, 0 reopened.
- `infra/pemeliharaan.sh status` → all four hosts `DIBUKA`.

**Not seen by the agent — the owner's statement, as the unedited sentences of the template:** in the browser, the LokaKode class page, participants and class move work, and the test student sees the same meetings and scores; the LokaUjian class list and exam participants are the same as before. The agent has no account and opened no page while logged in. **"Anything that looked wrong" was left blank by the owner**; this entry does not read that as "nothing".

The tag `roster-a1` is on the commit that holds this entry. That commit differs from the deployed `c9b5eac` only in this file and was **not** redeployed, so as not to restart the containers for a text change; `infra/smoke.sh` check 0 fails until the next deploy. `tahap-a1` stays on `c9b5eac`.

### Stage A2, step 1 — roster screens built in Akun Lokandita; LokaKode untouched (2026-11-28)

The stage is **open**: no tag. Branch `a2-roster`. Plan and the owner's four decisions ("approve all"): `DECISIONS.md`, 2026-11-28; contract: `KONTRAK.md` §18 "Management screens".

Done in this step:

- LokaAuth has the roster screens under `/admin/` — overview, academic years with the new-year tool, courses, classes, pengajar, enrolment with preview from typed numbers or CSV, withdraw, move, graduate and undo — superadmin only, in `id`, `en` and `ar` (`app/Language/*/Roster.php`, 189 keys each, taken from LokaKode's wording). Code: `app/Libraries/Roster.php`, `RosterDaftar.php`, `app/Controllers/Admin/Roster/`, `app/Views/admin/roster/`.
- Audit rows of these screens go to `identity.audit_log` with `app = 'roster'`, in the transaction of the change; `/admin/audit` has `roster` in its app filter.
- New variable `ROSTER_SCHEMA` (LokaAuth): `.env.example`, `apps/auth/.env` on the server, `KONTRAK.md` §10, `infra/lib-tes.sh`. `infra/cek-env.sh` → `cek-env: LULUS`.
- `infra/smoke.sh` check 21; `infra/tes.sh auth` builds `roster_test` in the production layout.
- **LokaKode, LokaUjian and the Portal are not changed.** LokaKode's screens still work and `lokakode_app` still writes the roster; both apps write until step 2.

Tests, all against real containers, on this tree:

- `infra/tes.sh auth` → `OK (128 tests, 3045 assertions)`; 17 of them are new (`tests/Roster/`): every roster route refuses a pengampu, a pengajar and a mahasiswa with 403 for their own class and for another, and the test fails if `Routes.php` has a roster route it does not list; cross-class cases (an enrolment id of class B sent to class A, a move to another course, year or archived class, removing a pengajar through another class) change nothing; a class with a real LokaKode announcement cannot be deleted.
- `infra/tes.sh lokaujian` → `OK (127 tests, 14445 assertions)`; `portal` → `OK (104 tests, 15000 assertions)`; `lokakode` → `OK (300 tests, 16769 assertions)`. The judge suite was not run: nothing it uses changed.
- A bug found by the tests and fixed before any deploy: a class delete refused by a foreign key was answered with "Kelas dihapus" (`DECISIONS.md`).

Not verified: no page was opened in a browser — the agent has no account. Arabic right-to-left and dark mode use the shared layout and styles and were only checked as "the page answers 200 in `ar` with `dir="rtl"` and no untranslated key"; **the owner must look at them.** The Arabic of the eleven new sentences was written by the agent and not read by an Arabic speaker.

What happens with the commit that holds this entry: `infra/deploy.sh auth` only, then `infra/smoke.sh`. Check 0 will fail for `lokakode`, `portal` and `lokaujian`, which stay on the A1 commit on purpose until step 2. The output is in the session and is recorded in the next entry.

Open — step 2, after the owner says the screens are proven: remove LokaKode's management screens and redirect their addresses, end `lokakode_app`'s write rights, copy LokaKode's old roster audit rows, move `data:pengajar` and `data:pendaftaran`, stop the curriculum seed creating courses and classes, roster stamp in the leaderboard cache key, test fixtures through `auth_app`, `SPEC.md` §3 and §5, `SPEC-LOKAUJIAN.md` §2 and §4, backup, deploy of all apps, smoke, gate, tag.

### Stage A2, step 2 — only Akun Lokandita writes the roster; deployed (2026-11-28)

The stage is built and deployed; **the owner's gate is open and there is no tag yet.** Branch `a2-roster`, deployed commit `217b141`.

Step 1 on production first: `infra/deploy.sh auth` at `0bb415c` passed (the first attempt had been refused by the session's permission system, so the owner at first found no menu). `infra/smoke.sh` then: 101 pass, 5 fail — all five the commit check of components left on the A1 commit on purpose (`lokakode`, `portal`, `lokaujian`, judge worker, LokaUjian scheduler; the entry above named only three). The owner tried the screens on class DDP101 7E and wrote "sudah dilakukan semua". What the audit log shows of that trial: pengajar removed and assigned twice, one enrolment moved to 7A and back, one empty class deleted — seven rows with `app = roster`. Enrolling from a list, withdrawing and graduating were not in the trial.

Done in step 2 (`DECISIONS.md`, 2026-11-28; `KONTRAK.md` §18):

- LokaKode: the management screens for academic years, courses, classes, pengajar and enrolment are removed. Their old addresses redirect a superadmin to the same page under `auth.lokandita.com/admin/`; an old `POST` changes nothing. `/mata-kuliah`, `/mata-kuliah/{id}` and `/kelas/{id}` are read pages; password reset stays.
- `lokakode_app` has `SELECT` only on the six roster tables (`infra/mysql-pindah-roster.sh pasang`, run by the deploy of `lokakode`).
- `infra/mysql-salin-audit-roster.sh`: LokaKode's old roster audit rows copied to `identity.audit_log` with `app = 'roster'`; originals kept. `/admin/audit` sorts by time.
- `data:pengajar` and `data:pendaftaran` now run in `auth`; `benih:kurikulum` no longer creates courses or classes.
- Leaderboard cache key carries a stamp of the enrolments.
- Test code of LokaKode, LokaUjian and the judge writes roster rows as `auth_app`.

Tests on the committed tree, real containers: `auth` → `OK (132 tests, 3110 assertions)`; `lokakode` → `OK (269 tests, 16508 assertions)` (300 before: four files of the removed screens went, their cases live in `apps/auth/tests/Roster/`); `lokaujian` → `OK (127 tests, 14445 assertions)`; `portal` → `OK (104 tests, 15000 assertions)`; `judge` → `259 passed`. One failure on the way: the new test expected MySQL error 1142 for a write through an old-name view and got 1356; both are refusals for lack of rights, the test and smoke accept either.

Production, 2026-11-28 about 16:04–16:10 UTC, on the owner's instruction in the session: 0 unfinished judge jobs, 0 sessions active in 10 minutes, 0 exam attempts running; `infra/backup.sh` → `backup SELESAI`; `infra/cek-env.sh` → `cek-env: LULUS`; `infra/deploy.sh` for `auth`, `lokakode`, `portal`, `lokaujian`, `judge` each `LULUS` at `217b141`. Audit copy: before 290 roster rows in `lokakode.audit_log`, 0 copied, `identity.audit_log` 3213 rows; after 290 copied, `identity.audit_log` 3503 rows (297 with `app = roster`), `lokakode.audit_log` unchanged at 1090. `infra/smoke.sh` → **`smoke: 109 lulus, 0 gagal`**, including: `lokakode_app` reads six tables and is refused eight writes (`1356` seven times through the old names, `1142` directly). Real requests without a cookie: `GET https://lokakode.lokandita.com/tahun-ajaran` and `/kelas/12/ubah` → 302 to the login page.

Not verified by the agent (no account, no browser): the redirect of a logged-in superadmin from an old LokaKode address to Akun Lokandita, the read-only class and course pages as a superadmin and as a dosen, the copied history on `/admin/audit`, Arabic right-to-left and dark mode. The Arabic of the new sentences (eleven in Akun, four in LokaKode) was written by the agent and not read by an Arabic speaker.

For LokaKelas: a new reader needs no new grant or view (`ROSTER_READER_DSN`). Two things it must know: an empty class can be deleted in Akun although a reader has stored something for it (a reader has no foreign key); and a reader that caches by class must key its cache on the enrolments' state or keep it short, as LokaKode's leaderboard now does.

Open: the owner's gate; then tag `tahap-a2`, merge of `a2-roster`, and "A2 done" in `LOKAKELAS-PLAN.md`. Unused language keys of the removed LokaKode screens are still in `Kode.php`.
