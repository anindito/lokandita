# Progress

Current stage: **9b — not started** (next session). Stage 9a — student interface and scoring — finished 2026-11-17, tag `tahap-9a`, after the owner's check; see "Result of the owner's check for 9a" at the end of "Stage 9a". Stage 9 was split by the owner on 2026-11-17: 9b (leaderboard, analytics, exports, late policy, three languages and RTL polish) is the next session, and the gate of `SPEC.md` §18 for stage 9 is taken after 9b.

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
- Not covered by this check, still open: everything under "Not verified, or weaker than it looks" above except the two items the owner's eyes now cover (pages seen by a person; Arabic opened). The Arabic texts are still unreviewed by an Arabic reader. The gate of `SPEC.md` §18 for stage 9 comes after 9b.
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
