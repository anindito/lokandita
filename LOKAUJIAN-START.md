# LokaUjian — start guide

How to begin building **LokaUjian**, the exam and quiz tool of the Lokandita platform, once LokaKode is finished. Written so it can be handed to a new chat that has not seen the earlier conversations.

**To use this in a new chat:** open a chat in the Lokandita project and say:
*"Read LOKAUJIAN-START.md. All decisions in section 4 are settled. Write SPEC-LOKAUJIAN.md and the KONTRAK.md additions described in section 6."*

**Revised 2026-11-21:** decision D1 is settled (classes stay in LokaKode until LokaUjian is finished, then move to the Lokandita core), and a naming note was added to section 1. App names and scope are in `LOKANDITA-BRAND.md`.

**Revised 2026-11-22:** decisions D2–D12 are settled (section 4). The destination of the roster move is settled: the tables go to the Lokandita core and the class management screens go to Akun Lokandita (section 5 and step 6).

---

## 1. Where things stand

Lokandita is a set of web apps on one server, sharing one login.

| Host | App | State |
|---|---|---|
| `lokandita.com` | Portal | Live |
| `auth.lokandita.com` | LokaAuth: accounts, sessions, user management | Live |
| `lokakode.lokandita.com` | LokaKode: programming practice with an automatic judge | Live; SEB stage and problem production finishing |
| `lokaujian.lokandita.com` | **LokaUjian** | Not started — this document |
| `lokasiber.lokandita.com` | LokaSiber | Later |

**Naming:** "LokaAuth" is retired as a brand; to users the login is part of Lokandita ("Akun Lokandita"). In this document it still names the identity service in `apps/auth`, matching the repository documents. The full lineup, including LokaKelas and LokaRetas, is in `LOKANDITA-BRAND.md`.

**Server:** Contabo VPS, Ubuntu 24.04, 4 vCPU, 8 GB RAM, IP `194.233.73.224`, user `deploy`, key-only SSH. Wildcard DNS already points every subdomain to it.

**Repository:** `/srv/lokandita` on the server, private on GitHub. Monorepo:

```
apps/auth  apps/lokakode  apps/portal     CodeIgniter 4 (PHP 8.3), one project per app
packages/lokandita-auth                  shared session check, LokaAuth client, account menu
judge/                                   Python worker (LokaKode only)
infra/                                   compose, Caddyfile, deploy.sh, smoke.sh, backup.sh
SPEC.md  KONTRAK.md  CLAUDE.md  PROGRESS.md  DECISIONS.md
```

**Stack:** CodeIgniter 4, MySQL 8.4 (one schema per app), Redis, Caddy, Docker. Server-rendered views with Tailwind and Alpine.js. All assets self-hosted, strict CSP. Three interface languages: Indonesian (default), English, Arabic (RTL).

**Already in place and to be reused, not rebuilt:**
- Login, sessions across subdomains, password rules, MFA for superadmin, account menu with *Keluar*.
- `app_access` in LokaAuth already has the value `lokaujian`; `SERVICE_TOKEN_LOKAUJIAN` is reserved.
- Academic structure in LokaKode: academic years, courses, classes, teacher assignments, enrollments. It stays there until LokaUjian is finished, then moves to the Lokandita core (D1).
- 235 students, 2 lecturers, 9 classes loaded.
- Safe Exam Browser verification (built in LokaKode stage 10).
- Safe image upload (built for the Portal hero photo).
- Deploy, smoke test, backup and restore scripts.

**How work is done:** Claude Code runs on the server inside VS Code (Remote-SSH). One stage per session. The agent presents a plan, the owner approves, the agent builds and deploys, then the owner performs a manual gate in the browser before the stage is tagged. `CLAUDE.md` in the repository holds the rules; they apply to LokaUjian unchanged.

---

## 2. What LokaUjian is

A plain testing tool. A lecturer writes questions, assembles an exam or quiz, gives it to classes, and downloads what students answered.

### Requirements from the owner

1. **Question types:** true/false, multiple choice, short answer, long answer.
2. **Question groups:** several questions can belong to one group, and the group carries a shared text and/or picture that applies to all of them (a reading passage, a diagram, a case).
3. **SEB is mandatory:** a student can start an exam or quiz only inside Safe Exam Browser.
4. **Two timing modes:**
   - *per student* — the clock starts when the student starts and runs for a set duration;
   - *lecturer-controlled* — the lecturer starts and ends the exam for everyone.
5. **CSV download** of student answers for the lecturer.
6. **No copy or paste in any text box**, to deter cheating.
7. **No plagiarism check.**

### Implied by those requirements

These are not extras; the requirements do not work without them.

- **Server-side time.** Deadlines are decided by the server clock, never the browser's. The page shows a countdown, but a submission after the deadline is refused by the server.
- **Autosave.** Every answer is saved to the server within seconds of being typed or chosen. A crash, a closed lid or a dropped connection loses nothing.
- **Resume.** A student who reconnects continues the same attempt with the time that is left; the clock does not pause.
- **Automatic submission** when time ends or the lecturer ends the exam.
- **One attempt per student per exam** unless the lecturer reopens it for that student.
- **Roles and scope** as in LokaKode: pengampu owns a course's exams; pengajar runs them for their own classes; a lecturer never sees another lecturer's class. Every endpoint gets a cross-class access test.
- **Results hidden from students by default.** Whether and when a student sees scores or correct answers is a setting per exam.
- **Live status for the lecturer** during a session: who has started, who is connected, who has submitted. Without it the lecturer-controlled mode cannot be run responsibly.
- **Images** uploaded by lecturers go through the same safe pipeline as the Portal photo: type decided from content, re-encoded on the server, metadata stripped, random names, never executed.
- **Audit log** of exam creation, start, end, reopening and exports.

### Out of scope

Plagiarism or similarity checking, code execution, AI-generated questions inside the app, course final grades, proctoring by camera, automatic checking of short answers, a practice mode outside SEB, question types beyond the four above.

---

## 3. How it fits the platform

| Topic | Approach |
|---|---|
| Code location | `apps/lokaujian` in the same monorepo |
| Database | New schema `lokaujian` with its own MySQL user |
| Login | `packages/lokandita-auth`, exactly as LokaKode uses it |
| Classes and enrollment | **Read from LokaKode through read-only views, not duplicated.** The tables move to the Lokandita core after LokaUjian is finished, and the management screens move to Akun Lokandita (see decision D1) |
| SEB | The verification code moves from LokaKode into a shared package so both apps use one implementation (see section 6) |
| Judge worker | Not used. LokaUjian has no code execution |
| Background work | Only a small scheduler: auto-submit at deadlines, closing lecturer-ended sessions |

---

## 4. Decisions (all settled)

All twelve are settled by the owner. The specification applies them as written; changing them later costs more.

| # | Decision | Settled as |
|---|---|---|
| D1 | Where classes and enrollments live | **Settled 2026-11-21, destination added 2026-11-22.** The academic tables stay in LokaKode's schema for now and move to the Lokandita core after LokaUjian is finished; the class management screens then move to Akun Lokandita. LokaUjian reads the roster through read-only database views and a read-only MySQL user, the same pattern LokaAuth uses for identity, and never queries LokaKode's tables directly. The views are the contract: neutral names and fixed columns in `KONTRAK.md`, so the later move changes the view definitions and not LokaUjian. One source of truth, no double entry |
| D2 | Multiple choice: one correct answer, or several | **One correct answer.** No "choose all that apply" variant |
| D3 | Scoring | True/false and multiple choice are scored automatically. Short and long answers are **not** scored in the app; the lecturer marks them from the CSV. Points per question are stored either way |
| D4 | Short answer auto-check | **None.** No list of accepted answers. Every short answer is marked by the lecturer |
| D5 | Question order | Per exam: fixed, or shuffled per student. Options within a multiple-choice question likewise. Questions inside a group stay together and in order |
| D6 | Navigation | Free movement between questions with a question map, and a review page before submitting |
| D7 | Extra time | A per-student time extension the lecturer can grant before or during the exam |
| D8 | What students see afterwards | Nothing by default. Per exam the lecturer may release the score, or the score with the correct answers |
| D9 | CSV layout | One row per student per question (long format), plus a second file with one row per student (wide format). UTF-8 with BOM so Excel opens it correctly |
| D10 | Question entry | In-app editor, plus import and export of an exam as a JSON file so questions can be prepared outside and reused |
| D11 | Practice mode without SEB | **None.** Every start requires SEB. A `SEB_ENFORCEMENT=disabled` switch with a red banner exists for the owner's testing only |
| D12 | Paste-block exemption | Honour the existing per-user `paste_allowed` flag in LokaAuth for students who need assistive tools |

Two limits to accept knowingly:

- **SEB works on Windows only for this platform.** The macOS and iPadOS clients do not send the verification headers the server relies on, and there is no SEB for Linux or Android. Students need a Windows machine or a campus lab.
- **Copy and paste blocking is a deterrent.** Inside SEB it is effective. It does not stop a student reading from a phone.

---

## 5. Stages

One stage per Claude Code session. A stage is finished only when its gate, done by the owner in a browser, passes.

| # | Stage | Gate |
|---|---|---|
| U0 | **Contract and groundwork.** Add LokaUjian to `KONTRAK.md`: host, schema, service token, environment variables, the academic read-only views (D1), the shared SEB package. Move SEB verification out of LokaKode into the shared package with no change in LokaKode's behaviour. Empty app skeleton behind a maintenance page; added to Caddy, deploy, smoke and backup | LokaKode's SEB gate still passes after the move; `lokaujian.lokandita.com` shows the maintenance page over HTTPS; smoke test passes with the new checks |
| U1 | **Access.** Login through LokaAuth, account menu, role and class scope read from the academic views | A pengajar sees only their own classes; changing a class number in the URL gives *Halaman tidak ditemukan*; a student sees an empty list |
| U2 | **Question authoring.** Question bank per course; the four types; groups with shared text and picture; image upload; points per question; preview as a student would see it; JSON import and export | The owner writes one question of each type and one group with a picture, previews them, exports and re-imports the file without change |
| U3 | **Exam setup and delivery.** Assemble an exam, assign it to classes, both timing modes, the student's exam page, autosave, countdown, resume, auto-submit, review page, paste and copy blocking in every text box | A test student completes an exam in each timing mode; closing the browser mid-exam and returning keeps answers and remaining time; submitting after the deadline is refused; Ctrl+V is refused in every text box |
| U4 | **SEB enforcement and integrity events.** Start refused outside SEB for students; heartbeat; focus-loss, display-change and reconnect events recorded per student | With real SEB 3.10.x on Windows a student starts and finishes an exam; the same student in ordinary Chrome is refused; a lecturer in Chrome still gets in |
| U5 | **Lecturer control, scoring and export.** Live session view; start and end for a class; extra time; reopen for one student; automatic scoring of objective questions; CSV downloads; result release settings | The owner runs a lecturer-controlled session with two test students, ends it, and opens both CSV files in Excel with correct characters and every answer present |
| U6 | **Readiness.** Arabic and dark-mode review by a person; load test at full cohort size; backup and restore including uploaded images; maintenance page off | A simulated run with 110 concurrent students autosaving shows no lost answers; restore test passes; smoke test passes |

Before real use, run one low-stakes quiz with a single class.

**After U6: the roster move.** Once LokaUjian is finished, the academic tables move from the `lokakode` schema to the Lokandita core, and the class management screens move to Akun Lokandita. These are two platform stages with their own plan, backup and gate, not part of LokaUjian. Which schema in the core receives the tables is decided in the A1 plan. Because LokaUjian reads only the views, the move should touch LokaKode and the view definitions, and leave LokaUjian unchanged.

| # | Stage | Gate |
|---|---|---|
| A1 | **Roster data move.** List the roster tables in `lokakode` and everything that references them. Move the tables to the core with their IDs unchanged, and leave views with the old names in `lokakode` so LokaKode runs without code changes. Redefine LokaUjian's views to point at the new place. Apps read through a read-only MySQL user; only the auth app may write. Meetings, problems, class windows and scores stay in `lokakode`. Backup and restore cover the new location. Done in a maintenance window after a fresh backup | Counts match before and after (students, classes, enrolments per class, lecturer assignments); a student sees the same meetings, scores and exams as before; a lecturer sees the same classes in both apps; moving one test student between classes still works from the existing LokaKode screens; smoke test passes |
| A2 | **Roster screens in Akun Lokandita.** Rebuild the class management screens (academic year, course, class, enrolment, move class, graduation, lecturer assignment, CSV import) in the auth app, superadmin only, in three languages. The LokaKode screens are removed and their addresses lead to the new place. LokaKode loses write access to the roster. Audit entries continue without a gap | The owner creates a class, enrols and moves a test student, and assigns a lecturer from Akun Lokandita, and both LokaKode and LokaUjian show each change; the old LokaKode addresses lead to the new screens; a pengajar cannot open them; smoke test passes |

---

## 6. Launch steps

### Step 1 — Confirm LokaKode is closed

On the server:

```bash
ssh deploy@194.233.73.224
cd /srv/lokandita
git status --short | head
git tag | tail -8
infra/smoke.sh | tail -2
tail -40 PROGRESS.md
```

Wanted: a clean working tree, `tahap-10` among the tags, a passing smoke test, and no open items in `PROGRESS.md` that touch login, SEB or the academic tables. LokaKode problem production (stage 13) may continue in parallel; it does not block LokaUjian.

Then take a backup and copy it off the server:

```bash
infra/backup.sh
```

### Step 2 — Write the specification (in a chat, not on the server)

In a new chat in the Lokandita project, with this file:

1. Decisions D1–D12 are settled (section 4). Apply them; do not reopen them.
2. Have the chat write **`SPEC-LOKAUJIAN.md`**: sections 2, 3 and 5 of this file turned into a full specification with the decisions applied, in the same style as the existing `SPEC.md`.
3. Have the chat list the **additions to `KONTRAK.md`**: host and container name, schema and MySQL users, the academic read-only views with their exact columns, environment variables, the shared SEB package interface, the exam JSON format, new smoke checks.

Read both before they go into the repository.

### Step 3 — Put them into the repository

Drag `SPEC-LOKAUJIAN.md` into `/srv/lokandita` in VS Code, then:

```bash
cd /srv/lokandita
git add -A && git commit -m "lokaujian: spesifikasi" && git push
```

The `KONTRAK.md` additions are applied by Claude Code in stage U0, so that contract and code change in one commit.

### Step 4 — Start stage U0

```bash
cd /srv/lokandita
claude
```

Check the model with `/model`, then paste:

```
Read CLAUDE.md, KONTRAK.md, PROGRESS.md and SPEC-LOKAUJIAN.md. Skim SPEC.md §4, §5 and §13
for what already exists.

We are starting LokaUjian, stage U0 (contract and groundwork) from SPEC-LOKAUJIAN.md.

Rules for this stage:
- LokaKode is live with real students. Nothing in this stage may change its behaviour.
  Moving SEB verification into a shared package must be proven by LokaKode's existing tests
  and by a real request, before and after.
- LokaUjian reads classes and enrollments from LokaKode through read-only views and a
  read-only MySQL user. It never writes to the lokakode schema and never copies those tables.
  It queries only the views, never LokaKode's tables directly: the tables will move to the
  Lokandita core after LokaUjian is finished, and only the view definitions should change then.
- Every addition shared between components goes into KONTRAK.md in the same commit.
- LokaUjian stays behind its maintenance page until I open it.

Present your plan for U0 only and wait for my approval before writing anything.
```

### Step 5 — Each later stage

Fresh session each time:

```
Read CLAUDE.md, KONTRAK.md, PROGRESS.md and SPEC-LOKAUJIAN.md.
We are starting LokaUjian stage U<n> from SPEC-LOKAUJIAN.md.
Present your plan for this stage only and wait for my approval before writing anything.
```

After the agent reports, do the gate yourself, then:

```
Gate result for LokaUjian stage U<n>: <what you saw, including anything that looked wrong>.
Tag lokaujian-u<n> and update PROGRESS.md.
```

### Step 6 — After U6: move the roster (stages A1 and A2)

One session per stage. Pick a time with no class and no exam running.

```bash
ssh deploy@194.233.73.224
cd /srv/lokandita
git status --short | head
infra/backup.sh
claude
```

Check the model with `/model`, then paste for A1:

```
Read CLAUDE.md, KONTRAK.md, PROGRESS.md, SPEC.md §5 and SPEC-LOKAUJIAN.md.

Stage A1: move the class roster out of LokaKode into the Lokandita core.

Decided by the owner: academic years, courses, classes, enrolments and lecturer assignments
become platform data, read by every app and managed in Akun Lokandita. This stage moves the
data only. The management screens move in stage A2.

Rules for this stage:
- LokaKode and LokaUjian are live with real students. Their behaviour must not change.
- First show me the exact list of tables you consider roster, every table and query that
  references them, and the count of rows in each. Propose which schema in the core receives
  them. Wait for my approval.
- IDs do not change. Meetings, problems, class windows, submissions and scores stay in the
  lokakode schema.
- Keep views with the old table names in lokakode so existing code keeps working. Say
  plainly which existing writes cannot go through a view and how you handle each.
- LokaUjian reads only its contract views. Redefine those views; do not change LokaUjian.
- Apps read through a read-only MySQL user. Only the auth app may write.
- This is a destructive change on a non-test schema: rehearse it on a copy first, show me
  the rehearsal result, and ask before running it on the real schema. Take a backup
  immediately before and tell me how to roll back.
- Put the maintenance page on both apps during the move and take it off after.
- KONTRAK.md, infra/backup.sh, infra/smoke.sh and the restore test are updated in the same
  commit.
- Do not paste student rows into chat. Counts only.

Present your plan for A1 only and wait for my approval before writing anything.
```

After the A1 gate passes and is tagged, a fresh session for A2:

```
Read CLAUDE.md, KONTRAK.md, PROGRESS.md, SPEC.md §5 and SPEC-LOKAUJIAN.md.

Stage A2: move the class management screens from LokaKode to Akun Lokandita.

Rules for this stage:
- First list every roster screen and action LokaKode has today, so nothing is lost. Wait
  for my approval of that list.
- Superadmin only, three languages, light and dark, same authorisation tests as before
  including the cross-class case.
- The user-facing name is "Akun Lokandita". Internal identifiers keep their names.
- When the new screens are proven, remove the LokaKode screens, redirect their addresses,
  and take away LokaKode's write access to the roster.
- Audit entries keep one continuous history.
- No permanent deletion of classes with data.

Present your plan for A2 only and wait for my approval before writing anything.
```

---

## 7. Points that deserve attention

**Exam time is unforgiving.** In LokaKode a slow moment costs a student a few seconds. In an exam, a lost answer or a wrong deadline is a dispute. Autosave, server-side deadlines and resume are the parts to test hardest, including pulling the network cable mid-exam.

**Everyone arrives at once.** A practice session spreads out; an exam has a whole cohort pressing *Mulai* in the same minute and autosaving every few seconds for an hour. The load test in U6 exists for this. The server also runs LokaKode and its judge, so avoid scheduling an exam while a LokaKode class is running until the load test says it is safe.

**The lecturer needs a way out.** During a live exam things go wrong: a laptop dies, SEB refuses to start, the power cuts. Extra time, reopening one student's attempt, and ending a session by hand must work and must be audited.

**Lessons already paid for in LokaKode**, which `CLAUDE.md` now enforces:
- Passing tests are not proof; after each deploy make a real request and look at the page.
- Deploy only through `infra/deploy.sh`, which checks the running commit.
- One `.env` per component; a new variable goes into `.env.example`, startup validation and `KONTRAK.md` together.
- Calls between apps go through the `internal` network by the alias in `KONTRAK.md`, and a refusal is always logged with its reason.
- No permanent deletion of users, classes with data, or audit rows.

**Personal data.** Exam answers are personal data and may be assessment records. Decide a retention period before the first real exam and put it in the privacy text.
