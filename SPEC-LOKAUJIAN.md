# SPEC — LokaUjian

Product specification of **LokaUjian**, the exam and quiz tool of Lokandita. It continues `SPEC.md`: stack, roles, LokaAuth, the account menu, security headers and languages are as written there and are not repeated. Wire-level details shared between components live in `KONTRAK.md`; working rules for the coding agent live in `CLAUDE.md` and apply unchanged. Where this file and `KONTRAK.md` disagree, `KONTRAK.md` wins and the disagreement must be reported.

Written 2026-11-24 from `LOKAUJIAN-START.md`. Decisions D1–D12 of that file are settled and applied here (§16). Points this file had to settle beyond them are listed in §17 for the owner to confirm.

Domain words are kept in Indonesian because they are the names users see: *ujian* (exam), *kuis* (quiz), *soal* (question), *kelompok soal* (question group), *bank soal* (question bank), *sesi* (one exam given to one class), *pengerjaan* (one student's attempt), *jawaban* (answer), *kunci* (correct answer).

---

## 1. Product

A plain testing tool. A lecturer writes questions, assembles an exam or quiz, gives it to classes, and downloads what students answered.

| Host | App | Purpose |
|---|---|---|
| `lokaujian.lokandita.com` | **LokaUjian** | Exams and quizzes inside Safe Exam Browser |

Requirements of the owner:

1. Four question types: true/false, multiple choice, short answer, long answer (§5).
2. Question groups with a shared text and/or picture (§5.2).
3. A student can start an exam or quiz only inside Safe Exam Browser (§9.1).
4. Two timing modes: per student, and lecturer-controlled (§7).
5. CSV download of student answers (§12).
6. No copy or paste in any text box of the exam (§9.2).
7. No plagiarism check.

**Not built:** plagiarism or similarity checking, code execution, AI-generated questions inside the app, course final grades, proctoring by camera, automatic checking of short answers, a practice mode outside SEB, question types beyond the four, negative marking, marking of short and long answers inside the app, importing marks back.

Two limits accepted knowingly:

- **SEB works on Windows only for this platform** (SEB 3.10.x, Windows x64). Students need a Windows machine or a campus lab. The instructions page says so.
- **Copy and paste blocking is a deterrent.** Inside SEB it is effective. It does not stop a student reading from a phone.

## 2. Place in the platform

| Topic | Choice |
|---|---|
| Code | `apps/lokaujian`, CodeIgniter 4 on PHP 8.3, same layout as the other apps |
| Database | Schema `lokaujian`, MySQL user `lokaujian_app`; tests on `lokaujian_test` |
| Login, session check, account menu | `packages/lokandita-auth`, exactly as LokaKode uses it. `app_access` value `lokaujian`, service token `SERVICE_TOKEN_LOKAUJIAN` |
| Classes and enrollment | Read from the **roster views** (§4). Never copied, never written |
| SEB | One implementation in the shared package, used by LokaKode and LokaUjian (§9.1) |
| Images | The Portal's safe pipeline (`KONTRAK.md` §16), adapted in §5.3 |
| Judge worker | Not used. LokaUjian runs no code |
| Background work | One small scheduler container, `lokaujian-jadwal` (§7.5) |
| Front end | Server-rendered views, Tailwind, Alpine.js. No Monaco, no Chart.js. All assets self-hosted |
| Progress in the browser | Polling. No SSE, no WebSocket |

LokaUjian does not create accounts, does not manage classes and does not reset passwords; those stay where they are (LokaAuth, LokaKode). An unknown student or class is never created on the fly.

## 3. Roles

Global roles are those of LokaAuth. Scoped roles are derived from the roster views on every request, never from a role column and never cached beyond the request:

| Scoped role | Derived from |
|---|---|
| Pengampu of a course | `roster_courses.owner_id` (also acts as pengajar of every class of the course) |
| Pengajar of a class | `roster_class_teachers` |
| Mahasiswa of a class | `roster_enrollments` with status `AKTIF`, class not archived, academic year `active` |

Permission matrix (one policy layer, applied in queries, never only in views):

| Action | Superadmin | Pengampu | Pengajar | Mahasiswa |
|---|---|---|---|---|
| Question bank of a course: see, create, edit, archive | yes | own course | no | no |
| Upload images | yes | own course | no | no |
| Exams: create, edit, publish, archive, JSON import and export | yes | own course | no | no |
| Preview a published exam as a student sees it | yes | own course | exams given to own classes | no |
| Give an exam to a class, set its schedule | yes | own course | no | no |
| Run a session: open, start, set end, end | yes | own course | own classes | no |
| Live view, extra time, reopen for one student | yes | own course | own classes | no |
| Correct a key or points after the exam (§11) | yes | own course | no | no |
| Release or withdraw results | yes | own course | no | no |
| CSV download, integrity events | yes | own course | own classes | no |
| SEB configuration of a course | yes | own course | no | no |
| Start and work on an exam | no | no | no | own class, inside SEB |
| Audit log | yes | no | no | no |

- A lecturer never sees another lecturer's class. A pengajar of class A requesting class B, or a pengampu requesting another course, gets *Halaman tidak ditemukan* (404), indistinguishable from an id that does not exist. A mahasiswa on a staff route gets 403.
- A student never sees an exam of a class they are not `AKTIF` in, and never another student's attempt.
- Every endpoint has an authorisation test with a cross-class (IDOR) case, against real containers.
- In an archived academic year staff can read and export but change nothing.

## 4. Roster

The academic structure (academic years, courses, classes, teacher assignments, enrollments) is described in `SPEC.md` §5 and managed on LokaKode's screens. LokaUjian reads it through **five read-only views** with neutral names and fixed columns (`roster_academic_years`, `roster_courses`, `roster_classes`, `roster_class_teachers`, `roster_enrollments`), through its own read-only MySQL user. The views are the contract; their columns are in `KONTRAK.md`.

- LokaUjian queries only the views, never LokaKode's tables, and has no write privilege on anything outside its own schema.
- A class id, course id or user id stored by LokaUjian is the id of the roster and of LokaAuth. There is no foreign key across schemas and no join across them: the policy layer first reads which classes the user may see, then every query on LokaUjian's tables is bound to those ids.
- Names and nomor induk come from `user_directory` of LokaAuth, as in LokaKode. They are not copied.
- After LokaUjian is finished the tables move to the Lokandita core (stages A1 and A2, outside this specification). The move redefines the views; LokaUjian's code does not change.

## 5. Questions

### 5.1 Types

| Type | Code | Student gives | Scored |
|---|---|---|---|
| Benar/Salah | `benar_salah` | one of *Benar*, *Salah* | automatically |
| Pilihan ganda | `pilihan_ganda` | one option of 2–6. **Exactly one option is correct**; there is no "choose all that apply" | automatically |
| Isian singkat | `isian` | one line of text, at most 200 characters | by the lecturer, from the CSV |
| Uraian | `uraian` | text, at most 20,000 characters | by the lecturer, from the CSV |

- Every question has **points** (0.25–100, steps of 0.25, default 1), stored for all four types.
- There is no list of accepted answers for `isian` and no key for `uraian`.
- Question text, options and group text are Markdown; raw HTML is shown as text, never executed. A question is written in one language by its author; it is not translated. Text direction follows the content (`dir="auto"`), so an Arabic question reads right to left under any interface language.
- A question may carry one picture (§5.3).

### 5.2 Groups

A *kelompok soal* has a shared text and/or one picture (a reading passage, a diagram, a case) and 1–20 questions in a fixed order. The shared part stays visible with each of its questions. A group is added to an exam as a whole; its questions stay together and in order under every shuffle (§6.2).

### 5.3 Pictures

Uploaded by the pengampu or a superadmin, through the same checks as the Portal photo: type decided from the content (JPEG, PNG or WebP; extension and browser MIME type ignored), at most 5 MB and 30,000,000 pixels, decodable. There is no minimum size.

- The upload is never stored. The server decodes it and writes one new file from the pixels, so it carries no metadata and nothing appended: PNG for a PNG upload, JPEG otherwise, longest side at most 2000 px. The name is random and never derived from the upload.
- An alternative text is required with every picture.
- Pictures are **not** public static files. They are answered by the app after authorisation: to staff who may see the course, and to a student only while that student has an attempt in progress, or a released review (§11), of an exam that contains the picture.
- Pictures live in a volume that survives deploys and is part of backup and restore.

### 5.4 Bank

- One bank per course. It lists questions and groups with type, points, first words, the exams that use them, and search and filter by type.
- **Preview** shows a question, a group or a whole exam with the same view partial the student page uses, marked *Pratinjau*, in an ordinary browser. A preview creates no attempt and records nothing.
- A question used by no published exam can be edited freely or deleted. One that a published exam uses can still be edited in the bank: the published exam keeps its own frozen copy (§6.1) and does not change. A question or group that any exam uses cannot be deleted, only archived (hidden from the bank, kept for the exams).

## 6. Exams

### 6.1 Model and lifecycle

An exam belongs to one course and has: title, kind (`ujian` or `kuis` — a label only; behaviour is identical), instructions shown before the start, an ordered list of questions and groups from the bank, and the settings of §6.2.

| Status | Meaning |
|---|---|
| `draf` | Being assembled. Invisible to students and to pengajar |
| `terbit` | Published. The content is **frozen**: publishing copies every question, option, key, points value and picture reference into the exam. Later edits in the bank never reach it. It can now be given to classes |
| `arsip` | Hidden from lists; data kept |

- Publishing is refused, with a message that lists exactly what is missing, when: there is no question; a `pilihan_ganda` does not have exactly one correct option; a `benar_salah` has no key; the mode is `per_mahasiswa` and there is no duration.
- A published exam that no student has started can be withdrawn to `draf`. Once one attempt exists, text, options, order and settings are locked; only the corrections of §11 remain.
- An exam without attempts can be deleted. An exam with attempts is never deleted, only archived.
- Total points, and the split between automatically scored points and points the lecturer will mark, are shown wherever the exam is listed.

### 6.2 Settings

| Setting | Values |
|---|---|
| Timing mode | `per_mahasiswa` or `dikendalikan_dosen` (§7) |
| Duration | 5–600 minutes. Required for `per_mahasiswa`; for `dikendalikan_dosen` an optional planned length |
| Question order | fixed, or **shuffled per student**. Units of the shuffle are groups and questions outside a group; inside a group the order never changes |
| Option order | fixed, or shuffled per student, for `pilihan_ganda`. *Benar* / *Salah* are never shuffled |
| Result release | set per class when releasing (§11); nothing is shown by default |

A student's order is fixed once, when the attempt is created, and stored; resuming never reshuffles.

### 6.3 Import and export as JSON

An exam can be exported to one JSON file and imported from one (format: `KONTRAK.md`). The file holds the title, kind, instructions, settings, every question and group with keys and points, and the pictures. It never holds classes, schedules, students or answers.

- Ways in and out: the web page of the course (superadmin and the pengampu), and `php spark ujian:impor <file> --oleh <nomor induk>` / `php spark ujian:ekspor <exam id>`.
- An import always creates a **new exam in `draf`**. A question whose content already exists in the course's bank is reused, not duplicated; so is a picture. Exporting an exam, importing the file and exporting again gives the same file.
- The importer is strict (unknown keys refused, types checked) and its messages say what to change, with the position in the file. A mistake anywhere refuses the whole file; nothing is stored.
- Pictures in the file go through the checks of §5.3.
- The export contains every key. It is a staff action, audited, and the page says so before the download.
- Every import and export is audited: who, file name, counts.

## 7. Sessions and time

A **sesi** is one published exam given to one class: at most one per exam per class. Students of the class see the exam in their list from that moment (title, kind, mode, duration, schedule — nothing of its content).

**All time is the server's.** The browser's clock is never used for a decision. The page shows a countdown computed from the remaining seconds the server sends, and corrects it with every answer from the server.

### 7.1 Per student (`per_mahasiswa`)

- The session has `open_at` and `close_at` (Asia/Jakarta on screen). A student may **start** while it is open. Staff who run the session may also open or close it by hand, which overrides the schedule.
- The clock starts when the student presses *Mulai* and runs for the exam's duration: `deadline = started + duration + extra time`. A student who starts just before `close_at` still gets the full duration.
- The session is over when it is closed for new starts and no attempt is still running.

### 7.2 Lecturer-controlled (`dikendalikan_dosen`)

- Nothing is scheduled. Before the start, a student inside SEB who opens the exam sees a waiting page (*Menunggu dosen memulai ujian*) that turns into the exam by itself.
- **Mulai** by the lecturer starts the clock for the whole class at once. With it, or at any time later, the lecturer may set or change the **end time** (as "N minutes from now" or a clock time; the planned length is offered as the default). Students see the countdown to it; without an end time they see the time elapsed.
- **Akhiri** ends the session for everyone. It is announced: the end is set 30 seconds ahead, every student page shows *Ujian diakhiri dosen* with the countdown, and at the end all answers are in.
- A student who arrives late joins with the time that is left. `deadline = end of the session + extra time`, so a student with extra time continues after the others have ended.
- A session that was started and never ended ends by itself 8 hours after the start.

### 7.3 Extra time and reopening

- **Extra time** (D7): staff who run the session may give one student, or several selected students, 1–240 more minutes, before or during the exam, any number of times. Grants only add; none can be taken back. The student's countdown follows within seconds.
- **Reopen for one student**: for a student whose attempt has ended, or who has none after the session is over. The lecturer gives a number of minutes (1–600) and a reason. The student continues the **same** attempt with every answer kept, or starts one; `deadline = moment of reopening + minutes`. The session itself stays as it is for everyone else.
- Both need a confirmation that names the student and the minutes, and both are audited.

### 7.4 One attempt

- One attempt per student per exam, enforced by the database. A second *Mulai* returns the existing attempt.
- A student may answer exactly when their attempt has not been submitted and the server's time is before its deadline. That one rule decides every save; it is written once.
- A save that reaches the server up to **10 seconds** after the deadline is still accepted, to take in the last autosave in flight. The grace is not shown and not configurable. Anything later is refused and the student is told the time is up.
- A student who is moved to another class keeps the attempt where it was started.

### 7.5 Automatic submission and the scheduler

- An attempt whose deadline has passed **is** ended, whatever its stored status says: every read and every write derives it from the time. No answer can be lost or added because a background job was late.
- The scheduler (`lokaujian-jadwal`, the same image, no HTTP) only brings the stored state up to date, at least every 20 seconds: marks ended attempts as submitted (`waktu_habis` or `diakhiri_dosen`), scores them (§11), opens and closes scheduled sessions, ends lecturer-controlled sessions at their end time or after 8 hours.
- It writes a heartbeat that `infra/deploy.sh` and `infra/smoke.sh` read. When it is down the lecturer's live view says so; the pages that read results bring the state up to date themselves.

## 8. Student interface

**Home.** The exams of the student's classes: upcoming, in progress (with *Lanjutkan*), finished, and released results. A student with no class sees an empty list with a plain sentence. Outside SEB the list is shown with a notice that exams open only in Safe Exam Browser and a link to the instructions (§9.1).

**Before the start.** Title, kind, number of questions, duration or mode, the instructions, the rules (one attempt, answers saved automatically, the clock does not pause), then *Mulai*.

**Exam page.** One page, loaded once after the start with the whole exam; one question, or one group with its current question, on screen at a time.

- Header: title, countdown, save state, the student's name and nomor induk. The countdown changes colour and is announced to screen readers at 10, 5 and 1 minutes.
- **Free movement** (D6): *Sebelumnya* / *Berikutnya*, and a **question map** with one numbered button per question showing unanswered, answered, and *ragu-ragu* (a mark the student sets on any question).
- A `pilihan_ganda` choice can be cleared (*Hapus pilihan*). Text boxes show the characters used and the limit.
- A semi-transparent watermark with nomor induk, name and time covers the question area. Question text cannot be selected; printing is blocked.
- No score, no key and no hint of correctness appears anywhere on this page.

**Autosave.**

- A choice is saved at once. Text is saved 2 seconds after typing stops and at least every 10 seconds while typing, when the box loses focus, and before moving to another question.
- The header always shows one of *Tersimpan hh:mm:ss*, *Menyimpan…*, or *Belum tersimpan — mencoba lagi*. Only the server's confirmation produces *Tersimpan*.
- When the connection is lost the student keeps working: answers are held in the page, retried until they are accepted, and a banner says so and asks not to close the page. Nothing typed is discarded by the page while the time is running.
- Saves carry a counter per question; the server never lets an older save overwrite a newer one.

**Resume.** Closing the browser, a crash or a dropped connection ends nothing. The student logs in again, inside SEB, on the same or another computer, and continues the same attempt with every saved answer, the same order and the time that is left. **The clock does not pause.** Only one page works on an attempt at a time: the newest takes over, the older one is told so at its next save and stops (integrity event, §9.3).

**Submitting.** *Selesai* leads to a **review page**: how many answered, the numbers of the unanswered and *ragu-ragu* questions as links back, then *Kumpulkan* with a confirmation that states how many are unanswered. After submission, and after an automatic one, the student sees only that the answers were received, when, and how many were answered. A submitted attempt cannot be changed by the student.

**Languages.** Interface in Indonesian (default), English and Arabic (mirrored). Question content is shown as written. Latin digits by default. Dark mode.

## 9. Integrity

### 9.1 Safe Exam Browser

As `SPEC.md` §13.1, with one implementation for both apps in the shared package (`KONTRAK.md`):

- Enforced for **mahasiswa only**, after authentication, by role and never by URL. Staff use ordinary browsers. The User-Agent is never trusted; both SEB headers are verified cryptographically.
- **There is no practice mode and no exam outside SEB** (D11). Every student route requires SEB, with these exceptions: the home list (read-only), the SEB instructions and download, the account routes (logging out always works), and the export of one's own data. Starting, the waiting page, the exam page, every answer endpoint and the released review are inside SEB.
- To start or work on an exam the request must match the SEB configuration **of that exam's course**, not merely of any course of the student.
- LokaUjian has its own configuration per course (start address, allowed addresses), so its `.seb` file and Browser Exam Keys are separate from LokaKode's. The pengampu panel is the same: create or rotate (quit password ≥ 12 characters), download the file, register Browser Exam Keys. Rotation invalidates old keys at once; rotating while a session is running is refused.
- Heartbeat every 25 s, lease 30 s. A lost or invalid header stops the exam page within 30 s; saved answers stay, the clock keeps running, and the student continues after reopening the exam in SEB.
- `SEB_ENFORCEMENT=disabled` exists for the owner's testing only: it skips the check, shows the red banner "MODE UJI: penegakan SEB dinonaktifkan" on every student page, is logged at start, and makes the smoke test print a warning.
- Moving the verification into the shared package (stage U0) must not change LokaKode's behaviour in any way.

### 9.2 Copy and paste

- **Paste is blocked in every text box of the exam** on every path: paste event, Ctrl/Cmd+V, Shift+Insert, middle click, drag and drop, context menu. **Copy and cut are blocked** in those boxes and on the question text. A short message appears and an integrity event is recorded.
- Spell check, autocomplete and autocorrect are off in those boxes.
- The per-user `paste_allowed` flag of LokaAuth (D12) exempts a user from the paste block; nothing is recorded for them. It is set only by a superadmin, as today.
- This applies to the student's exam page. Lecturers' authoring forms are ordinary forms.

### 9.3 Events

Recorded per student and attempt, with time, IP address and user agent:

| Event | When |
|---|---|
| `seb_rejected`, `seb_terminated` | as in LokaKode |
| `paste_blocked` | a blocked paste, copy or cut, with the path |
| `focus_lost` | the exam page lost focus or was hidden, with the duration when it returns |
| `display_changed` | the number of displays or the screen size changed |
| `reconnected` | the page was unreachable for more than 60 seconds and came back, with the length of the gap |
| `page_taken_over` | the attempt was opened in another page or on another computer |

Events report; lecturers decide. They are shown per student in the live view and in a list per session with a CSV export, to whoever may see the class.

## 10. Lecturer control

**Session page (live view)**, for whoever may run the session, refreshed every 5 seconds:

- The state of the session, the server's time, the end time, and whether the scheduler is alive.
- One row per `AKTIF` student of the class: not arrived, waiting, working (connected, or disconnected since hh:mm), submitted (by the student, by time, by the lecturer); questions answered out of total; time of the last save; time left; extra minutes; number of integrity events.
- Totals at the top: arrived, working, disconnected, submitted.
- Actions: open or close (`per_mahasiswa`); **Mulai**, set or change the end, **Akhiri** (`dikendalikan_dosen`); extra time for selected students; reopen for one student. Ending and reopening ask for confirmation.
- No answer and no score is shown here while the session runs.

A lecturer must be able to act when things go wrong: a dead laptop, SEB that will not start, a power cut. Extra time, reopening and ending by hand work at any moment of a session and are always audited.

## 11. Scoring and results

- `benar_salah` and `pilihan_ganda`: full points for the correct answer, 0 otherwise, 0 for no answer. `isian` and `uraian` have no score in the app (D3, D4).
- An attempt's **objective score** is the points earned on automatically scored questions, shown always together with the points available on them. It is computed when the attempt ends and is always recomputed in full, never adjusted.
- There is no total grade, no percentage of the whole exam and no pass mark: the lecturer combines the objective score with their own marking outside the app.
- **Correction after the exam.** The pengampu or a superadmin may change the key or the points of a question of a published exam. Every attempt is then rescored; the change, the old and new values and the number of attempts whose score changed are audited. Text and options are not changed after the first attempt. A question cannot be annulled in the app; that is done in the lecturer's own marking.

**What students see afterwards** (D8) — nothing by default. The pengampu or a superadmin releases results **per class**, at one of two levels, and can withdraw the release:

| Level | The student sees |
|---|---|
| `nilai` | the objective score out of the objective points, with the sentence that short and long answers are marked by the lecturer outside the app |
| `nilai_dan_kunci` | that, and a review inside SEB: each question, the student's own answer, and for automatically scored questions the correct answer |

Releasing `nilai_dan_kunci` while another class of the same exam has not finished shows a warning that names the class and needs a second confirmation. Release and withdrawal are audited.

## 12. CSV export

For one session, or for all sessions of an exam the requester may see. Two files (D9):

| File | Rows | Columns |
|---|---|---|
| **Long** | one per student per question, for every student with an attempt | class, nomor induk, name, question number (exam order), question number as that student saw it, question id, group number, type, question text (plain, first 200 characters), answer, key, correct (1 / 0 / empty), maximum points, points (empty for `isian` and `uraian`), time of the last save |
| **Wide** | one per `AKTIF` student of the class, including those who never started | class, nomor induk, name, status, started, submitted, how it was submitted, extra minutes, questions answered, objective score, objective points available, points to be marked by hand, number of integrity events, then one column per question in exam order holding the answer |

- A `pilihan_ganda` answer is written as the option's letter in exam order and its text; `benar_salah` as *Benar* or *Salah*; text answers in full with their line breaks.
- UTF-8 **with BOM**, CRLF line ends, every field quoted where needed, so Excel shows Indonesian and Arabic text correctly. The separator is chosen at download: semicolon (default, for Excel with Indonesian regional settings) or comma.
- A cell that begins with `=`, `+`, `-`, `@` or a tab is written with a leading apostrophe, so that a student's answer can never run as a formula in the lecturer's spreadsheet.
- Times are Asia/Jakarta. The file name carries the course code, exam, class and date.
- An export can be taken while a session runs; it then says so in its first status column. Every export is audited: who, which sessions, row count.

## 13. Audit and personal data

- Append-only audit log in LokaUjian's schema, written in the same transaction as the change: exam created, published, withdrawn, archived; import and export of JSON; session created, opened, closed, started, end set, ended; extra time; reopening; key or points corrected; results released or withdrawn; CSV export; SEB configuration created or rotated, keys registered. The superadmin has a filterable page. No endpoint changes or deletes an audit row.
- No permanent delete of an exam with attempts, an attempt, an answer or an integrity event.
- **Export of a user's own data**, as in the other apps and outside SEB: a student's attempts (times, how submitted, extra time), their own answers by question number, their integrity events, and scores only where released. Never the questions, the keys or anything of another person.
- Exam answers are personal data and may be assessment records. **The retention period is the owner's decision and is open** (§17). Until it is set and stated in the Portal's privacy text, nothing is removed automatically and LokaUjian must not be used for a real exam.

## 14. Non-functional

`SPEC.md` §16 applies. In addition:

- **Target load:** a whole cohort starts in the same minute. 110 students working at once and autosaving, with no lost answer, is the gate (U6); the design must hold for 250.
- An accepted save is durable: the server confirms only after the answer is committed to MySQL. Redis holds nothing that would be missed if it were emptied.
- The exam page stays usable through a server restart or deploy: saves are retried, the countdown continues, nothing is lost. A deploy during a running session still needs the owner's explicit word.
- The server also runs LokaKode and its judge. Until the load test of U6 says otherwise, an exam is not scheduled while a LokaKode class is working; the session page shows a warning when judge jobs are queued.
- Rate limits protect the save endpoint per attempt without ever refusing a normal typist.
- Backup and the documented restore test cover the `lokaujian` schema and the picture volume.
- Maintenance page until the owner opens the host (`infra/pemeliharaan.sh lokaujian on|off`).
- Tests follow `CLAUDE.md`: real MySQL, Redis, LokaAuth and HTTP; the exam page (autosave, countdown, resume, paste block, behaviour without a connection) is tested in the real headless browser and those tests fail, not skip, when it is missing. No test can show that a real SEB client is accepted; that is the owner's gate in U4.

## 15. Stages

One stage per working session, a plan first, and a gate done by the owner in a browser. Tags are `lokaujian-u<n>`. Every stage ends with `infra/smoke.sh` passing and an entry in `PROGRESS.md`.

| # | Stage | Gate |
|---|---|---|
| U0 | **Contract and groundwork.** `KONTRAK.md` gains LokaUjian: host, schema, users, service token, environment variables, the roster views (§4), the shared SEB package. SEB verification moves out of LokaKode into the shared package with no change in LokaKode's behaviour, proven by LokaKode's existing tests and by a real request before and after. Empty app behind the maintenance page; added to Caddy, deploy, smoke, env check, backup | LokaKode's SEB gate still passes after the move; `lokaujian.lokandita.com` shows the maintenance page over HTTPS; smoke test passes with the new checks |
| U1 | **Access** (§3, §4). Login through LokaAuth, account menu, policy layer over the roster views, the lists of courses and classes a lecturer may see, the student's empty home | A pengajar sees only their own classes; changing a class number in the URL gives *Halaman tidak ditemukan*; a student sees an empty list |
| U2 | **Question authoring** (§5, §6.3). Bank per course; the four types; groups with shared text and picture; picture upload; points; preview; JSON import and export | The owner writes one question of each type and one group with a picture, previews them, exports and re-imports the file without change |
| U3 | **Exam setup and delivery** (§6, §7, §8, §9.2). Assemble and publish an exam, give it to classes, both timing modes with the minimum controls to run them, the scheduler, the student's exam page, autosave, countdown, resume, automatic submission, review page, paste and copy blocking. Runs with `SEB_ENFORCEMENT=disabled` until U4 | A test student completes an exam in each timing mode; closing the browser mid-exam and returning keeps answers and remaining time; submitting after the deadline is refused; Ctrl+V is refused in every text box |
| U4 | **SEB enforcement and integrity events** (§9.1, §9.3). Configuration panel, start refused outside SEB, heartbeat, the events of §9.3 | With real SEB 3.10.x on Windows a student starts and finishes an exam; the same student in ordinary Chrome is refused; a lecturer in Chrome still gets in |
| U5 | **Lecturer control, scoring and export** (§7.3, §10, §11, §12, §13). Live view; extra time; reopening; scoring and correction; CSV downloads; result release; audit page; own-data export | The owner runs a lecturer-controlled session with two test students, ends it, and opens both CSV files in Excel with correct characters and every answer present |
| U6 | **Readiness** (§14). Arabic and dark-mode review by a person; load test at full cohort size; backup and restore including pictures; retention period in the privacy text; Portal card becomes a link; maintenance page off | A simulated run with 110 concurrent students autosaving shows no lost answers; restore test passes; smoke test passes |

Before real use, one low-stakes quiz with a single class.

After U6 come the platform stages **A1** (roster tables move to the Lokandita core) and **A2** (class management screens move to Akun Lokandita), described in `LOKAUJIAN-START.md` §5. They are not part of LokaUjian and must leave it unchanged.

## 16. Decisions applied

| # | Settled as | Where |
|---|---|---|
| D1 | Roster stays in LokaKode's schema for now; LokaUjian reads read-only views with a read-only user | §4 |
| D2 | Multiple choice has one correct answer | §5.1 |
| D3 | True/false and multiple choice scored automatically; short and long answers marked by the lecturer from the CSV; points stored for all | §5.1, §11 |
| D4 | No automatic check of short answers | §5.1 |
| D5 | Question order and option order fixed or shuffled per student, per exam; groups stay together and in order | §6.2 |
| D6 | Free movement, question map, review page before submitting | §8 |
| D7 | Per-student extra time, before or during the exam | §7.3 |
| D8 | Students see nothing by default; the score, or the score with correct answers, may be released | §11 |
| D9 | CSV in long and wide format, UTF-8 with BOM | §12 |
| D10 | In-app editor plus JSON import and export | §5.4, §6.3 |
| D11 | No practice mode without SEB; `SEB_ENFORCEMENT=disabled` for the owner's testing only | §9.1 |
| D12 | The `paste_allowed` flag of LokaAuth is honoured | §9.2 |

## 17. Points settled in this file, for the owner to confirm

`LOKAUJIAN-START.md` does not answer these. Each is written into the sections above as the simplest behaviour that satisfies the requirements; each is the owner's to overrule before the stage named.

| # | Point | Written as | Before |
|---|---|---|---|
| 1 | Retention of answers | **Open.** Nothing is removed automatically; no real exam before it is decided and in the privacy text (§13) | U6 |
| 2 | Who releases results, and for whom | Pengampu or superadmin, per class, not per exam (§11) | U5 |
| 3 | What a student can open outside SEB | Only the read-only list of their exams; the released review with correct answers is inside SEB (§8, §9.1) | U3 |
| 4 | `per_mahasiswa`: a student who starts shortly before the window closes | Gets the full duration (§7.1) | U3 |
| 5 | Ending by the lecturer | Announced 30 seconds ahead; saves accepted up to 10 seconds after any deadline (§7.2, §7.4) | U3 |
| 6 | Reopening for one student | Same attempt, answers kept, N minutes counted from the moment of reopening; also for a student who never started (§7.3) | U5 |
| 7 | Extra time | Can only be added, never taken back (§7.3) | U5 |
| 8 | A wrong key found after the exam | Key and points can be corrected with a full rescore; no annulling of a question in the app (§11) | U5 |
| 9 | What a pengajar may do | Run sessions, extra time, reopening, CSV and events for own classes; no bank, no editing, no release (§3) | U1 |
| 10 | Pictures | One per group and one per question; none on options (§5.1, §5.2) | U2 |
| 11 | SEB configuration | Per course, separate from LokaKode's: students get a second `.seb` file and the pengampu registers Browser Exam Keys again (§9.1) | U4 |
| 12 | Two pages on one attempt | The newest takes over, the older stops, an event is recorded (§8) | U3 |
| 13 | CSV separator | Semicolon by default, comma on request (§12) | U5 |
| 14 | `kuis` and `ujian` | A label only; no difference in behaviour (§6.1) | U3 |
| 15 | Marking | No negative marking, no partial points, no total grade in the app (§11) | U5 |
