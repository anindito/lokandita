#!/usr/bin/env python3
"""Membuat judge/seccomp/judge.json dari profil bawaan Docker.

Sumber: moby-default.json = github.com/moby/profiles, tag seccomp/v0.3.0
(sha256 di bawah). Profil judge adalah profil itu, dipersempit:

  - hanya arsitektur x86_64 (program 32-bit ditolak kernel);
  - semua aturan yang bergantung pada capability dibuang: kontainer judge
    tidak punya capability apa pun, jadi aturan itu tidak pernah berlaku;
  - `socket` hanya untuk AF_UNIX; tidak ada soket jaringan sama sekali,
    juga tidak ke loopback kontainer sendiri;
  - syscall di DIBUANG dihapus: tidak dibutuhkan gcc, program C/C++ biasa,
    maupun Python dengan NumPy dan Matplotlib (dibuktikan tes), dan
    masing-masing pernah menjadi jalan keluar dari kontainer atau cara
    mengganggu host.

Semua yang tidak disebut dijawab EPERM. Jalankan ulang skrip ini setelah
mengubah daftar; tes membandingkan judge.json dengan keluaran skrip.
"""

from __future__ import annotations

import hashlib
import json
import sys
from pathlib import Path

DI_SINI = Path(__file__).resolve().parent
SHA256_SUMBER = "6416b47770785a41ac59073cdc77d9fe98517df2799dc83ef207e622de3053f6"

DIBUANG = {
    # melihat atau mengubah proses lain
    "ptrace", "process_vm_readv", "process_vm_writev", "kcmp", "pidfd_getfd", "process_madvise",
    # antarmuka kernel yang luas dan sering berlubang
    "io_uring_setup", "io_uring_enter", "io_uring_register", "userfaultfd", "vmsplice",
    "add_key", "request_key", "keyctl", "modify_ldt",
    # namespace dan mount (tanpa capability memang gagal; di sini ditolak lebih awal)
    "unshare", "setns", "mount", "umount", "umount2", "pivot_root", "chroot",
    "name_to_handle_at", "open_by_handle_at",
    # IPC System V dan antrean pesan POSIX: bertahan di luar umur proses
    "shmget", "shmat", "shmdt", "shmctl", "semget", "semop", "semtimedop", "semctl",
    "msgget", "msgsnd", "msgrcv", "msgctl", "ipc",
    "mq_open", "mq_unlink", "mq_timedsend", "mq_timedreceive", "mq_notify", "mq_getsetattr",
    "mq_timedsend_time64", "mq_timedreceive_time64",
    # jaringan selain `socket` AF_UNIX tidak ada gunanya tanpa soket jaringan,
    # tetapi yang menyentuh host tetap dibuang
    "sethostname", "setdomainname",
    # waktu, penjadwalan lintas proses, memori NUMA
    "adjtimex", "clock_adjtime", "clock_adjtime64", "settimeofday", "stime",
    "mbind", "set_mempolicy", "get_mempolicy", "migrate_pages", "move_pages",
    "ioprio_set", "sched_setattr", "sched_setscheduler", "sched_setparam", "sched_setaffinity",
    # lain-lain yang tidak dibutuhkan program latihan
    "personality", "acct", "swapon", "swapoff", "quotactl", "quotactl_fd", "syslog", "vhangup",
    "fanotify_init", "fanotify_mark", "perf_event_open", "bpf", "kexec_load", "kexec_file_load",
    "reboot", "init_module", "finit_module", "delete_module", "iopl", "ioperm",
    "landlock_create_ruleset", "landlock_add_rule", "landlock_restrict_self",
    "memfd_secret", "seccomp",
}

AF_UNIX = 1
ENOSYS = 38


def buat() -> dict:
    mentah = (DI_SINI / "moby-default.json").read_bytes()
    if hashlib.sha256(mentah).hexdigest() != SHA256_SUMBER:
        sys.exit("moby-default.json bukan berkas yang diharapkan (sha256 berbeda)")
    sumber = json.loads(mentah)

    umum = sumber["syscalls"][0]
    assert umum["action"] == "SCMP_ACT_ALLOW" and "includes" not in umum and not umum.get("args")
    boleh = sorted(set(umum["names"]) - DIBUANG - {"socket", "clone", "clone3"})

    # Aturan `clone` bawaan untuk kontainer tanpa CAP_SYS_ADMIN: boleh, kecuali
    # dengan bendera yang membuat namespace baru.
    clone = next(
        a for a in sumber["syscalls"]
        if a["names"] == ["clone"] and a.get("args") and "CAP_SYS_ADMIN" in a.get("excludes", {}).get("caps", [])
        and "s390" not in a.get("includes", {}).get("arches", [])
    )

    return {
        "defaultAction": "SCMP_ACT_ERRNO",
        "defaultErrnoRet": 1,
        "architectures": ["SCMP_ARCH_X86_64"],
        "syscalls": [
            {"names": boleh, "action": "SCMP_ACT_ALLOW"},
            {"names": ["arch_prctl"], "action": "SCMP_ACT_ALLOW"},
            {
                "names": ["socket"],
                "action": "SCMP_ACT_ALLOW",
                "args": [{"index": 0, "value": AF_UNIX, "op": "SCMP_CMP_EQ"}],
            },
            {"names": ["clone"], "action": "SCMP_ACT_ALLOW", "args": clone["args"]},
            # glibc mencoba clone3 lebih dulu; ENOSYS membuatnya kembali ke clone,
            # yang benderanya bisa diperiksa.
            {"names": ["clone3"], "action": "SCMP_ACT_ERRNO", "errnoRet": ENOSYS},
        ],
    }


def teks() -> str:
    return json.dumps(buat(), indent=1, sort_keys=True) + "\n"


if __name__ == "__main__":
    (DI_SINI / "judge.json").write_text(teks(), encoding="utf-8")
    profil = buat()
    print(f"judge.json: {len(profil['syscalls'][0]['names'])} syscall diizinkan tanpa syarat")
