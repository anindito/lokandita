# Progress

Next stage: **7 — Judge worker** — not started.

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

