# Progress

**Stage 13 — problem production — all eleven meetings of the task (DDP101 4–7, PBO301 1–7) are built and imported since 2026-11-23: 352 problems, all `PENDING_REVIEW`, no meeting published. Waiting for the owner's review; review sheets in `docs/soal/tinjauan/`. No tag yet: the stage closes when the owner has reviewed. Nothing is approved or published by the agent. See the table under "Stage 13" at the end of this file.**

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
| DDP101 | 4 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-4.md` | no | no |
| DDP101 | 5 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-5.md` | no | no |
| DDP101 | 6 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-6.md` | no | no |
| DDP101 | 7 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/ddp101-pertemuan-7.md` | no | no |
| PBO301 | 1 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-1.md` | no | no |
| PBO301 | 2 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-2.md` | no | no |
| PBO301 | 3 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-3.md` | no | no |
| PBO301 | 4 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-4.md` | no | no |
| PBO301 | 5 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-5.md` | no | no |
| PBO301 | 6 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-6.md` | no | no |
| PBO301 | 7 | yes | yes, 2026-11-23, 32 accepted | `docs/soal/tinjauan/pbo301-pertemuan-7.md` | no | no |

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
