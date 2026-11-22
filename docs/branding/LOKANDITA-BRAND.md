# Lokandita — brand architecture

Names and scope of every app in the Lokandita family, as settled on 2026-11-21. Written so it can be handed to a new chat that has not seen the naming conversation.

Each item is marked **Decided** (the owner's decision) or **Proposed** (Claude's suggestion, not yet accepted).

---

## 1. The lineup (Decided)

Lokandita is the single parent brand. The apps are named `Loka` + one Indonesian noun.

| Name | What it is | Host | State |
|---|---|---|---|
| **Lokandita** | Portal, shared environment, accounts and authorization, membership, class rosters | `lokandita.com` (identity service on `auth.lokandita.com`) | Live |
| **LokaKode** | Programming practice with an automatic judge | `lokakode.lokandita.com` | Live |
| **LokaUjian** | Exams and quizzes | `lokaujian.lokandita.com` | Not started |
| **LokaKelas** | Class material management and distribution | `lokakelas.lokandita.com` (by pattern, not yet confirmed) | New, not started |
| **LokaSiber** | CTF and Forensik, with CTFd as the main platform | `lokasiber.lokandita.com` | Later |
| **LokaRetas** | Cyber range | `lokaretas.lokandita.com` (by pattern, not yet confirmed) | Future |
| **LokaData** | Data analytics learning platform | `lokadata.lokandita.com` (by pattern, not yet confirmed) | Future |

Further decisions:

- **LokaAuth is retired as a brand.** Login and authorization are simply part of Lokandita.
- **Membership lives in Lokandita** (the portal), not in the individual apps.
- **Class rosters belong to the Lokandita core.** Physically, the academic tables (academic years, courses, classes, teacher assignments, enrollments) stay in the `lokakode` schema for now and move to the core after LokaUjian is finished. Until then other apps read them through read-only views (decision D1 in `LOKAUJIAN-START.md`).
- **Forensik is not a separate app.** It sits inside LokaSiber together with CTF.
- **The cyber range is a separate app** (LokaRetas), not a mode of LokaSiber.

---

## 2. What retiring "LokaAuth" means in practice (Proposed)

- **User-facing text changes.** Students and lecturers see "Akun Lokandita". The word "LokaAuth" disappears from interface strings, emails and the privacy text.
- **Internal identifiers stay.** `apps/auth`, `packages/lokandita-auth`, `auth.lokandita.com`, environment variables and service tokens keep their names. Renaming them on a live system adds risk and gives users nothing.
- **Documents catch up gradually.** `SPEC.md`, `KONTRAK.md`, `CLAUDE.md` and `LOKAUJIAN-START.md` say "the identity service (`apps/auth`)" from now on; old mentions are corrected when each file is next edited.
- **No code merge is implied.** The portal and the identity service can remain separate apps under one brand.

---

## 3. Open items

1. **Which schema in the Lokandita core receives the academic tables.** To be decided when the roster move is planned, after LokaUjian stage U6.
2. **LokaKelas scope beyond materials.** Announcements, assignment submission and a grade recap across apps are proposed, not decided.
3. **How CTFd uses Lokandita accounts.** CTFd is separate software with its own user accounts; single sign-on with the Lokandita login is not designed yet.
4. **Where LokaRetas runs.** Proposed: its own server. A range needs isolated multi-machine networks per team, and the current VPS (4 vCPU, 8 GB RAM) already carries LokaKode, its judge and soon LokaUjian. Deliberately attackable networks should also not share a host with the identity database.
5. **Name clearance.** A web search on 2026-11-21 for LokaKelas, LokaSiber, LokaUjian and LokaRetas surfaced no existing products. LokaData was not searched. PDKI has not been checked.

---

## 4. Naming rules (Proposed)

- **Display form** is CamelCase (`LokaKode`); hosts are lowercase (`lokakode.lokandita.com`).
- **A new Loka app only when it needs its own engine** (judge sandbox, CTFd, range infrastructure, notebooks). Anything else is a feature of an existing app or of the portal.
- **Subject apps versus function apps.** LokaKode, LokaSiber, LokaRetas and LokaData are hands-on practice environments for a subject. LokaUjian and LokaKelas are generic across subjects.
