# Working rules for this repository

Lokandita v2: three CodeIgniter 4 apps (LokaAuth, LokaKode, Portal), a Python judge worker, one server. Read `SPEC.md` and `KONTRAK.md` before any work. `PROGRESS.md` says which stage is current.

The owner is a lecturer who operates this server alone. He reads diffs in VS Code and runs the manual gate of each stage himself.

## Why these rules exist

The first version of this system was built quickly and reported hundreds of passing tests while core features did not work in production: logout failed for days because tests used mocks, a fix was reported as done but never reached the repository, variables were written to an env file the containers did not read, and internal calls were rejected silently. Every rule below prevents one of those.

## How to work

1. **One stage per session** (`SPEC.md` §18). Do not start the next stage.
2. **Plan first.** Present the plan for the stage and wait for approval before writing code.
3. **Ask when a decision is the owner's.** Product behaviour not covered by `SPEC.md` is his decision. Small technical choices are yours: make them and record them in `DECISIONS.md`.
4. **Keep the contract true.** Anything two components share goes into `KONTRAK.md` in the same commit as the code. Never let code and contract disagree.
5. **Commit small, push often.** End each stage with a commit, a tag `tahap-N`, and an entry in `PROGRESS.md`.

## Evidence

- Never say something works without showing the command and its output.
- "Tests pass" is not evidence that a feature works. After deploying, run `infra/smoke.sh` and the specific real request for the feature, and show the result.
- If something could not be verified, say so plainly and say what the owner must check.
- Report failures and skipped steps as prominently as successes.

## Tests

- Anything in `KONTRAK.md` is tested against real containers: real MySQL, real Redis, real HTTP between app containers. No mocks for the LokaAuth client, the session check, or the judge.
- Judge isolation tests run real hostile programs in real containers and are never skipped. If Docker is unavailable the suite fails.
- Each new endpoint has an authorisation test, including a cross-class (IDOR) case.
- Tests use dedicated `*_test` schemas and refuse to run against any other schema.

## Environment and deploy

- PHP is not installed on the host. Run Composer, `spark` and PHPUnit inside containers.
- One `.env` per component (`KONTRAK.md` §10). A new variable is added to `.env.example`, to startup validation, and to `KONTRAK.md` together. Run `infra/cek-env.sh` before deploy.
- Deploy only through `infra/deploy.sh`, which verifies the running commit. Do not rely on `docker compose up --build` picking up changes.
- Never print secrets, tokens or passwords into logs, commits, or chat output. Refer to them by variable name.

## Boundaries

- Work only inside `/srv/lokandita`. Do not change SSH, firewall, users, or anything under `/etc` without asking; say exactly what and why.
- `/srv/lokandita-data` holds personal data of real students. Read it only for the import stage. Never commit it, never copy it into the repository, never paste rows into chat.
- Judge containers must never gain network access, a writable root, or extra capabilities for convenience. If a limit blocks legitimate work, raise it with a measured reason; do not remove it.
- Destructive database actions (drop, truncate, mass update) on non-test schemas need explicit approval each time.
- No permanent delete of users, classes with data, or audit rows — by design.

## Code

- All user-facing text goes through CI4 language files (`id`, `en`, `ar`) from the first commit. Indonesian is the default and is written naturally, not translated word for word.
- Authorisation lives in one policy layer and is applied in queries, not only in views.
- The account menu, session check and LokaAuth client exist once, in `packages/lokandita-auth`.
- Self-host all front-end assets. The CSP forbids CDNs and inline scripts without a nonce.
- Store times in UTC; display in Asia/Jakarta.
- Prefer boring, readable code. The owner maintains this himself.
