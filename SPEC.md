# SPEC — Lokandita v2

Product specification. Wire-level details shared between components live in `KONTRAK.md`; working rules for the coding agent live in `CLAUDE.md`. Where this file and `KONTRAK.md` disagree, `KONTRAK.md` wins and the disagreement must be reported.

Domain words are kept in Indonesian because they are the names users see: *mata kuliah* (course), *pertemuan* (meeting), *kelas* (class), *pengampu* (course owner), *pengajar* (class teacher), *soal* (problem), *nomor induk* (NIM/NIP).

---

## 1. Products

| Host | App | Purpose |
|---|---|---|
| `lokandita.com`, `www.lokandita.com` | **Portal** | Public landing and entry to all apps |
| `auth.lokandita.com` | **LokaAuth** | Central identity, sessions, user management |
| `lokakode.lokandita.com` | **LokaKode** | Programming practice with automatic judging |
| `lokaujian.`, `lokasiber.` | future apps | Not built now; nothing may block adding them |

Contact address everywhere: `lokanditax@gmail.com` (one config value, never hardcoded).

No self-registration anywhere. Accounts are created by a superadmin.

## 2. Stack

| Layer | Choice |
|---|---|
| Web apps | **CodeIgniter 4** on **PHP 8.3**, one CI4 project per app |
| Shared PHP code | Composer path package `packages/lokandita-auth` |
| Database | **MySQL 8.4**, one schema per app |
| Cache / queue | Redis 7 |
| Judge worker | **Python 3.12** service on the host (systemd), not PHP |
| Front end | Server-rendered CI4 views, Tailwind CSS, Alpine.js, Monaco editor, Chart.js. All assets self-hosted; no CDN |
| Edge | Caddy 2 in Docker, automatic TLS |
| Spreadsheets | PhpSpreadsheet |
| TOTP | spomky-labs/otphp |

PHP is not installed on the host. Every PHP command runs inside a container.

Execution progress in the browser uses **polling** (1 s), not SSE or WebSocket.

## 3. Roles

Global role, stored in LokaAuth: `superadmin`, `dosen`, `mahasiswa`.

Scoped roles, stored in LokaKode and derived from relations, never from a role column:

| Scoped role | Derived from |
|---|---|
| Pengampu of a course | `courses.owner_id` (exactly one per course; also acts as pengajar) |
| Pengajar of a class | `class_teachers` (many classes, across courses) |
| Mahasiswa of a class | `enrollments` with status `AKTIF` |

Permission matrix (enforced in one policy layer, at query level, never only in the UI):

| Action | Superadmin | Pengampu | Pengajar | Mahasiswa |
|---|---|---|---|---|
| Create / edit / disable users | yes | no | no | no |
| Reset a user's password | anyone | students in own course | students in own classes | no |
| Change own password | yes | yes | yes | yes |
| Academic years, courses | yes | no | no | no |
| Classes, enrollment in a course | yes | own course | view own classes | no |
| Assign pengajar to class | yes | own course | no | no |
| Meetings, concepts, composition, target | yes | own course | no | no |
| Upload problem JSON | yes | no | no | no |
| Approve / reject / edit problems | yes | own course | no | no |
| Open / close meeting per class, pre-generate | yes | own course | own classes | no |
| See submissions and source code | yes | own course | own classes | own |
| Grade distribution, analytics, export | yes | own course | own classes | no |
| Leaderboard | all | class + course | own classes | own class only |
| Audit log | yes | no | no | no |

Every new endpoint needs an IDOR test: a pengajar of class A requesting class B gets 403/404.

---

## 4. LokaAuth

### 4.1 Accounts
- Login with **nomor induk** and password. Email is optional and nullable.
- Created singly or in bulk (CSV/XLSX: `nomor_induk, nama, email, global_role, apps`). Bulk import shows a preview: new, already existing, invalid with reasons.
- Initial password: random per user, or one shared value chosen by the superadmin. Always with `must_change_password`. Credentials can be downloaded once.
- Status: `active`, `suspended`, `disabled`. **No permanent delete** — grades in other apps reference user IDs.
- `app_access` decides which apps a user may enter.
- Bulk actions on selected rows: grant/revoke app access, change status, reset password. Confirmation states the row count.
- User list: search, filter by role/status, pagination, CSV/XLSX export. Must stay usable at 5,000 rows.

### 4.2 Passwords and lockout
- Argon2id via `password_hash`.
- Minimum 10 characters, rejected if in a common-password list or equal to one of the last 3.
- First login and every reset force a password change before anything else.
- Changing password revokes all other sessions of that user.
- 5 failures in 15 minutes locks the account: a student account for 5 minutes every time (owner's decision 2026-11-24), any other account progressively (1, 5, 15, then 60 minutes). Lock state is stored in the database (`locked_until`), not only in Redis. A separate per-IP limit protects against spraying.
- Error text for wrong password, locked account and wrong TOTP is identical to the user; the real reason is logged.

### 4.3 MFA
- TOTP is **mandatory for superadmin**, enrolled at first login, with 8 single-use backup codes.
- Secret encrypted at rest.
- Not enabled for other roles, but the schema must allow enabling it per user later without a migration.
- Superadmin can clear another user's MFA; this is audited.

### 4.4 Sessions
- Database-backed opaque sessions shared across all subdomains (see `KONTRAK.md` §2). No JWT.
- Lifetime 12 hours, sliding.
- Warning 2 minutes before expiry with a "Tetap masuk" button.
- Idle logout after 30 minutes: user setting, **forced on for mahasiswa** (shared lab computers).
- Logout revokes the session server-side and ends it in every app.

### 4.5 Account menu (identical in all three apps)
Top-right on every authenticated page: initials, name, nomor induk. Opens a menu with name and role, *Ganti kata sandi*, language, idle-logout toggle, and **Keluar** as the last item, red, with a text label. Keyboard accessible, closes with Escape. The view partial lives in the shared package so it cannot diverge.

Error toasts everywhere have a close button and disappear by themselves.

### 4.6 Login redirect
`/login?next=<url>`. `next` is accepted only if it is `https` and its host exactly equals one of the configured app hosts. Otherwise the user goes to the Portal.

### 4.7 Audit
Append-only log: actor, action, target, app, time, IP, user agent. Covers user creation and changes, password resets, MFA changes, logins, internal API calls and denials. Superadmin has a filterable audit page. There is no endpoint that deletes audit rows.

---

## 5. LokaKode — academic structure

- **Tahun ajaran**: label, start, end, `active` or `archived`. Exactly one active at a time.
- **Mata kuliah**: code, name, one pengampu.
- **Kelas**: belongs to a course and an academic year; name, optional capacity; can be archived. Classes with enrollments or grades cannot be deleted; the refusal states how many records depend on it.
- **Enrollment**: one per student per course per academic year. Status `AKTIF`, `LULUS`, `MENGUNDURKAN_DIRI`, `DIPINDAHKAN`. Only `AKTIF` appears in leaderboards, analytics and pre-generation.
- Enrollment by single entry or CSV of nomor induk, with preview: matched, not found in LokaAuth, already enrolled elsewhere in the same course. LokaKode never creates accounts; unknown nomor induk is reported with a link to LokaAuth.
- **Pindah kelas** within a course keeps all instances, submissions and scores, and recomputes per-class aggregates and leaderboards.
- **Kelulusan**: mark one, many, or a whole class as `LULUS` with a date. Graduated students can no longer submit in that class; their data stays readable. Reversible, audited.
- **New academic year tool**: copies classes and teacher assignments from the previous year without enrollments, shows a summary first, then archives the old year.
- Overview page: active year, class count, active students, classes without a pengajar flagged.

## 6. LokaKode — meetings

- A course has ordered **pertemuan**. Each meeting has exactly **one programming language**: `c`, `cpp` or `python`.
- Per meeting, set by the pengampu: title, language, `target_points` (default 60), minimum composition (default below), allowed concepts, starter template, compiler flags, autocomplete on/off, leaderboard anonymisation, practice-after-close on/off.
- Per meeting **per class** (`meeting_class_windows`): `open_at`, `close_at` in Asia/Jakarta, manual open/close override by the pengajar, `late_policy` (`reject` or `multiplier`, default factor 0.5), `post_close_visibility` (`hidden` or `review_only`).
- Publishing a meeting is refused when the minimum composition is not met, or when available points are below 2 × target. A warning is shown below 3 × target. The message lists exactly what is missing.
- Minimum composition is **per meeting**, default 8 / 8 / 6 / 6 / 4 (very easy → hard). The pengampu may lower it for a meeting as long as the 2 × target rule still holds.

**Exam meetings** (owner's decision 2026-11-23). A meeting has a type, `latihan` (default) or `ujian`, set by the pengampu while the meeting is unpublished and has no submissions. For an exam:
- The score is the points earned out of **all** points available: the target always equals the total points of the approved problems attached. Publishing needs at least one approved problem; the minimum composition and the 2 × target rule do not apply.
- Problems are visible to a student only while the window of their class is open. Before it opens and after it closes the problem page and every problem endpoint answer as for a problem that does not exist, and no list, notification, announcement, clarification or export shows a title, statement or slug of an exam problem. `post_close_visibility` is ignored.
- Late submissions are refused and there is no practice after close, whatever the window settings say.
- The window is not scheduled. The pengajar opens and closes it by hand on the day, or opens it **for N minutes** (default 150, 10–600): the server closes it at that time and students see a countdown.
- **Until the pengampu (or a superadmin) releases the scores for a class**, its students see no score, no points earned, no verdict, no per-test result and no leaderboard. After Submit a student is told only that the submission was received, or that it failed to compile (with the compiler log). Run on sample tests and custom input works as in practice.
- After release a student sees the total and the points per problem **number**. The problems stay hidden, and students never get a leaderboard for an exam. A release can be withdrawn. Release and withdrawal are audited.
- Staff pages (leaderboard, analytics, submissions, exports) are unchanged.

## 7. LokaKode — scoring

Five difficulty levels:

| Level | Code | Points |
|---|---|---|
| Mudah Sekali | `MUDAH_SEKALI` | 3 |
| Mudah | `MUDAH` | 5 |
| Mudah-Sedang | `MUDAH_SEDANG` | 8 |
| Sedang | `SEDANG` | 10 |
| Sulit | `SULIT` | 15 |

Per problem:
```
score = points × (hidden tests passed / hidden tests total)      rounded to 2 decimals
```
- Sample tests are never scored.
- Compile error → 0, full compiler log shown.
- RE / TLE / MLE / OLE fails that test only; the others still run.
- If required concepts are missing from the student's code (§9.2): verdict `CONCEPT_NOT_MET`, score × 0.5, with a specific message.
- Late submission under `multiplier` policy: score × factor.
- Stored score = **best** over all submissions. Unlimited attempts, no penalty. Attempt count is recorded.
- Rate limits: Submit once per 10 s per problem; Run once per 3 s.

Per meeting:
```
counted_points = Σ best scores, with only the 4 best MUDAH_SEKALI problems counted
meeting_score  = min(counted_points / target_points, 1) × 100        hard cap 100
surplus        = total points earned − min(counted_points, target_points)
```
- The 4 counted very-easy problems are re-selected on every new submission, never by order of attempt.
- Surplus never raises the score. It is used only for ranking and analytics.
- **Exam meetings (§6):** every problem counts — the very-easy quota does not apply — and `meeting_score = total points earned / total points available × 100`; surplus is always 0.
- The student always sees: points toward target, very-easy quota used ("Mudah sekali: 3/4 terhitung"), points still needed.
- **There is no course final grade and no letter grade.** Lecturers aggregate outside the system from exports.

Output comparison per problem: `exact`, `trimmed` (default: ignore trailing whitespace per line and trailing newlines), `token`, `float` (ε default 1e-6).

## 8. LokaKode — problems

### 8.1 Model
- `problems` is a bank identity; content lives in `problem_versions`. Editing an approved problem creates a new version; old scores stay tied to the old version. A meeting references specific versions.
- A version has: difficulty, statement per language (`id` required, `en`, `ar` optional), input/output format, constraints, comparison mode, time limit, sample count (2–3), hidden count (≥ 10), **generator** (Python), **reference solution** (in the meeting language), required concepts, required concepts in submission, status.
- Status: `PENDING_REVIEW` → `APPROVED` or `REJECTED`. Nothing reaches students without approval by the pengampu.
- Statements support `{{param}}` templating.
- Missing translation falls back to Indonesian with a badge. Machine translations carry a badge the pengampu can clear.

### 8.2 Per-student randomisation
Every student sees the same text but different numbers and tests.
- `seed = HMAC_SHA256(INSTANCE_SECRET, user_id | problem_version_id)`.
- Generator contract: reads env `SEED` and `INDEX`, prints exactly one test input to stdout, deterministic, no clock, no files, no network.
- A `problem_instance` is created once per student per meeting problem: inputs from the generator, expected outputs from running the reference solution in the sandbox.
- Re-opening a problem never regenerates it.
- **Pre-generation**: when a class window opens, and on demand from the pengajar panel with a progress indicator, instances for all `AKTIF` students are created on the low-priority queue. All tests of one problem run in a single container.
- The "preparing your problem" screen shows an honest wait estimate.

### 8.3 Import
In-app AI generation is not built. Problems are produced outside (by Claude Code on the server) and imported as JSON (`KONTRAK.md` §7).
- CLI: `php spark soal:impor <file>` and `php spark soal:ekspor <course> <meeting>`.
- Web upload: **superadmin only**, `.json`, ≤ 8 MB, asynchronous with a progress page. Pengampu and pengajar get 403 on page and endpoint.
- Each problem is validated by the judge worker before being stored:
  1. generator determinism (same `SEED`/`INDEX` three times → identical output);
  2. reference solution compiles and runs over all tests of 20 random seeds without TLE or crash;
  3. concept check against the meeting's allowed concepts (§9.1);
  4. schema.
- The report lists, per problem, accepted or rejected with reason, source line and column, and JSON line.
- Idempotent by `slug` within a meeting. Changed content creates a new version.
- The importer is strict about types and codes but its messages must say what to change (for example `meeting must be the integer 4, got "4 — Percabangan"`).
- Every import is audited: who, file name, counts.

### 8.4 Calibration
Per problem: pass rate, average tests passed, average attempts, median time. A hint is shown when the label looks wrong (for example a `MUDAH` problem below 30 % pass rate).

## 9. LokaKode — concepts

Catalogue and per-meeting mapping: `docs/PETA-KONSEP.md`. Concepts are data, not a hardcoded enum. `allowed_concepts` is cumulative within a course; inherited concepts are shown ticked and locked.

Analysis uses real parsers in the judge worker: tree-sitter for C and C++, the `ast` module for Python. Never string matching.

### 9.1 Restriction (reference solution)
A reference solution using a concept outside the meeting's allowed set is rejected, naming the construct, line and column. Applies to imported and manually edited problems. **Student code is never restricted.**

Detection rules that must hold:
- Declaring variables is `VARIABEL_TIPE`. It is allowed from meeting 1 of every course.
- `STRING` means real string operations: `std::string` or `char[]` variables, `getline`, `length`/`size`, `substr`, indexing a string, concatenation; in Python, string methods, slicing, indexing. **Printing a string literal is not `STRING`**, including `cout << a << " " << b` with operands of any type and `print("x", y)`.
- `if`, `else`, `switch`, ternary, `&&`, `||`, `!` → `PERCABANGAN`. `for`, `while`, `do` → `PERULANGAN`.
- A user-defined function other than `main` → `FUNGSI`.

### 9.2 Requirement (student code)
`required_concepts_in_submission` on a problem version. After hidden tests pass, the student's source is analysed; a missing concept gives `CONCEPT_NOT_MET` (§7) with a message such as "Keluaran benar, tetapi soal ini mensyaratkan pewarisan kelas." Analysis runs with a time and memory limit, outside the worker's main process.

## 10. LokaKode — judge

Architecture and message format: `KONTRAK.md` §6.

Every program runs in a fresh container, destroyed afterwards:

| Limit | Value |
|---|---|
| Network | none |
| Root filesystem | read-only; `/box` tmpfs 64 MB |
| Memory | 256 MB including swap (configurable per problem) |
| CPU | 1 |
| Processes | 64 |
| Capabilities | all dropped, `no-new-privileges`, seccomp profile |
| User | non-root |
| File size / descriptors / core | 8 MB / 64 / 0 |
| stdout, stderr | 1 MB each; exceeding → `OLE` |
| Compile | 10 s |
| Run per test | 2 s C/C++, 5 s Python (configurable per problem) |

- Compile once per submission; each test gets a fresh `/box`.
- Images built in this repo and pinned: `judge-c`, `judge-cpp` (GCC 13, `-O2 -std=c17` / `-std=c++17`), `judge-python` (3.12 with NumPy and Matplotlib pinned, `MPLBACKEND=Agg`, `HOME=/box`).
- If Docker or a required kernel feature is unavailable, execution fails closed. Code never runs on the host.
- **Run**: sample tests or custom stdin, not scored, not counted. **Submit**: all hidden tests, scored.
- Two queues: normal (Run, Submit, import validation) and low (pre-generation).
- Jobs are idempotent; infrastructure failure retries twice, then `FAILED` with a clear message to the student. A worker restart recovers pending jobs from the database.
- A reaper removes expired judge containers by label. It never touches other containers.
- Submit results expose verdict, score, duration and compile log only. **Hidden test input and expected output are never sent to the browser.**

Acceptance tests (automated, and run by the owner on this host):
`while(1);` killed on time and the worker stays healthy; fork bomb contained; network call fails; `/etc/passwd` unreadable directly and through a symlink in `/box`; 100 MB write fails at the file-size limit; stdout flood stopped at 1 MB; memory exhaustion contained; compile watchdog fires at 10 s; 200 simultaneous jobs all complete; no judge container left afterwards.

## 11. LokaKode — student interface

**Meeting page (problem bank).** Cards with number, title, difficulty badge (five distinct colours, not one gradient), points, status, points earned. Header with progress to target, very-easy quota, time left. Free order; filter and sort.

**Problem page, three panels.** Left: statement, formats, constraints, samples, language toggle. Top right: Monaco editor. Bottom right: tabs *Output*, *Test Cases*, *Input Kustom*, *Riwayat*.
- Panels resizable, sizes saved per user. Below 1024 px they become stacked tabs.
- Editor starts with the language starter template (editable per meeting); **Reset** restores it.
- **Paste into the editor is blocked** on every path: paste event, Ctrl/Cmd+V, Shift+Insert, middle click, drag and drop, context menu. A short message appears and an integrity event is recorded. A per-user `paste_allowed` flag, set only by superadmin and audited, exempts users who need assistive tools.
- **Copying the statement is blocked**: no selection, Ctrl/Cmd+C/X/A and context menu disabled, print blocked and hidden in print CSS. A semi-transparent dynamic watermark with nomor induk, name and time covers the statement.
- Draft autosave every 5 s to the server; restored after a crash or closed tab.
- Shortcuts: Ctrl/Cmd+Enter = Run, Ctrl/Cmd+Shift+Enter = Submit.
- Only the open problem's content is sent to the browser.

**Languages.** Interface and problems in Indonesian (default), English, Arabic. Arabic mirrors the layout (RTL) but the editor, program output, compile logs and code blocks are always LTR. Latin digits by default. All user-facing strings go through CI4 language files from the first commit.

## 12. LokaKode — leaderboard and analytics

- Leaderboard per meeting per class, and per meeting per course. Students never see a leaderboard of an exam meeting (§6).
- Order: meeting score, then surplus (higher first), then time the final score was reached (earlier first), then submission count (fewer first).
- Columns show score and total points. Optional anonymisation per meeting. Cached, refreshed when a submission is judged.
- Pengajar: grade distribution per class (histogram, mean, median, standard deviation), per-problem statistics, students at risk (no submission, repeated low scores), any submission's source and per-test result.
- Pengampu additionally: comparison between classes of the course.
- Export CSV and XLSX of meeting scores per student, laid out for easy aggregation.

## 13. LokaKode — integrity

### 13.1 Safe Exam Browser
- Enforced for **mahasiswa only**, after authentication, by role and never by URL. Staff use ordinary browsers.
- Server validates both headers cryptographically (`KONTRAK.md` §8). The User-Agent is never trusted.
- Public routes stay open: landing, policies, SEB instructions, `.seb` download, static assets, and LokaAuth login.
- Heartbeat every 25 s; server lease 30 s. A lost or invalid header ends the LokaKode session within 30 s; the last draft is kept and accepted submissions stay valid.
- Pengampu panel: create or rotate a configuration per course (quit password ≥ 12 characters), download the `.seb` file, register Browser Exam Keys (one per line). The allowed-URL list is generated by the server and always contains the app origin and the LokaAuth origin.
- Rotation invalidates old keys.
- `SEB_ENFORCEMENT=disabled` skips the check and shows a large red banner "MODE UJI: penegakan SEB dinonaktifkan" on every student page, and logs it at start.
- Supported client: SEB 3.10.x for Windows x64. This limitation is stated on the instructions page.
- Rejections and terminations are integrity events.

### 13.2 Other signals
- Code similarity per problem version within a class: winnowing over tokens with identifiers normalised, comments ignored; side-by-side view; pairs ≥ 30 % listed. It reports; lecturers decide.
  **Not built: dropped by the owner's decision of 2026-11-22 (stage 10b, `DECISIONS.md`).** The terms page therefore does not mention it.
- Typing anomaly: ≥ 300 characters added at ≥ 150 characters per second.
- Integrity events per class, viewable and exportable by pengajar and pengampu.

## 14. LokaKode — communication

- In-app notifications: meeting opened, 24 h before close, password reset.
- Per-problem clarification: student asks, pengajar answers privately or publishes to the class.
- Class announcements.

## 15. Portal

Public: landing for Lokandita, app cards (LokaKode active; LokaUjian and LokaSiber marked "segera" and not clickable), about, contact, privacy policy, terms. A "Masuk" button top right. Static, light, no authenticated API calls, SEO on. Three languages.

LokaKode also has a public landing at `lokakode.lokandita.com/`: features, how it works, supported languages, SEB requirement with download links, FAQ, contact. No real problems or student data on any public page.

After login the Portal shows the user's name and role and the apps allowed by `app_access`. App routes are `noindex, nofollow`.

## 16. Non-functional

- Target load: 500 users, 200 simultaneous submissions queued without loss.
- Security headers on every app: strict CSP with per-request nonce, HSTS, `nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: same-origin`. (`no-referrer` breaks POST origin checks and must not be used.)
- CI4 CSRF protection on every state-changing form.
- Structured logs. Every internal-call failure is logged with a reason, without secrets.
- Daily backup of all databases and configuration with 14-day retention, plus a documented restore test.
- Personal data handled per UU 27/2022: minimal data, encryption in transit and at rest for secrets, export of a user's own data. Student data files are never committed to Git.
- Accessibility WCAG 2.1 AA, full keyboard use, dark mode.

## 17. Seed data

- Courses: `DDP101` Dasar-Dasar Pemrograman (C++), `PBO301` Pemrograman Berorientasi Obyek (C++), `TS-PY` Pemrograman untuk Teknik Sipil (Python); 16 meetings each with concepts from `docs/PETA-KONSEP.md`.
- All three are owned by pengampu `197805192024211003`.
- Classes: DDP101 7A–7D, PBO301 6A–6D, TS-PY TS-6. Teaching assignments and enrollments come from CSV files in `/srv/lokandita-data/` (outside the repository).
- Students start with a shared initial password chosen at import time, with forced change.
- Meetings for UTS, UAS and those marked not auto-gradable exist but are disabled for practice.

## 18. Stages

One stage per working session. A stage is finished only when its gate passes.

| # | Stage | Gate |
|---|---|---|
| 4 | Skeleton and infrastructure: monorepo, Compose, MySQL, Redis, Caddy with maintenance pages, deploy / smoke / backup / env-check scripts | Maintenance page over HTTPS on all three hosts; smoke test passes |
| 5 | LokaAuth and the shared package | Superadmin logs in with TOTP, creates a dosen and a mahasiswa, both log in, **Keluar works**, old session is refused |
| 6 | LokaKode academic core (§5) and policy layer | A pengajar cannot open another pengajar's class by changing the URL |
| 7 | Judge worker (§10) | All acceptance tests pass on this host |
| 8 | Problems, concepts, importer (§8, §9) | A sample file imports; a deliberately wrong problem is rejected with a line number; `cout << x << " " << y` with doubles is accepted |
| 9 | Student interface, scoring, leaderboard, analytics (§6, §7, §11, §12) | A test student completes one meeting end to end; hidden tests show status only |
| 10 | SEB and integrity, communication (§13, §14) | Verified with real SEB 3.10.x on Windows from login to submit |
| 11 | Portal and public pages (§15) | Login from Portal reaches LokaKode without a second login |
| 12 | Production data (§17) | Account and class counts match the source files |
| 13 | Problem production | Each meeting reaches its minimum composition through the importer |
