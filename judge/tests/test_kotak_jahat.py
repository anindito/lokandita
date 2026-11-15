"""Program jahat sungguhan di kontainer sungguhan — uji terima SPEC.md §10.

Tes ini tidak pernah dilewati. Setiap tes juga memeriksa (lewat conftest) bahwa
tidak ada kontainer judge yang tertinggal.
"""

from __future__ import annotations

import subprocess
import time

import pytest

from lokandita_judge import kotak

HALO_C = '#include <stdio.h>\nint main(){int a,b;if(scanf("%d %d",&a,&b)!=2)return 1;printf("%d\\n",a+b);return 0;}\n'


def jumlah_proses_host() -> int:
    return int(subprocess.run("ps -e --no-headers | wc -l", shell=True, capture_output=True, text=True).stdout)


# ---- 1. while(1); dimatikan tepat waktu dan kotak pasir tetap sehat -------------

def test_putaran_tanpa_akhir_dimatikan_tepat_waktu_lalu_program_lain_tetap_jalan(jalan):
    mulai = time.time()
    h = jalan("c", "int main(){while(1);}")
    lama = time.time() - mulai
    assert h.status == "TLE"
    assert 1900 <= h.cpu_ms <= 3100, h.cpu_ms  # batas 2 s CPU
    assert lama < 8, f"butuh {lama:.1f} s untuk mematikan while(1)"
    assert jalan("c", HALO_C, b"2 3\n").stdout == b"5\n"


def test_putaran_tanpa_akhir_python_dimatikan(jalan):
    h = jalan("python", "while True:\n    pass\n")
    assert h.status == "TLE"
    assert 4900 <= h.cpu_ms <= 6100, h.cpu_ms  # batas 5 s CPU


def test_program_yang_tidur_dimatikan_oleh_batas_waktu_dinding(jalan):
    mulai = time.time()
    h = jalan("c", "#include <unistd.h>\nint main(){sleep(1000);return 0;}")
    lama = time.time() - mulai
    assert h.status == "TLE"
    assert h.cpu_ms < 200  # hampir tidak memakai CPU: yang bekerja batas dinding 2×2+1 = 5 s
    assert 4.5 <= lama < 9, lama


def test_mengabaikan_sigxcpu_tetap_dimatikan(jalan):
    h = jalan("c", "#include <signal.h>\nint main(){signal(SIGXCPU,SIG_IGN);signal(SIGTERM,SIG_IGN);while(1);}")
    assert h.status == "TLE"
    assert h.cpu_ms <= 3200


# ---- 2. bom fork terkurung -----------------------------------------------------------

def test_bom_fork_terkurung(jalan):
    sebelum = jumlah_proses_host()
    mulai = time.time()
    h = jalan("c", "#include <unistd.h>\nint main(){while(1)fork();}")
    assert h.status in ("TLE", "RE")
    assert time.time() - mulai < 12
    assert jumlah_proses_host() - sebelum < 20  # tidak ada proses yang lolos ke host


def test_paling_banyak_64_proses(jalan):
    sumber = r'''
#include <stdio.h>
#include <unistd.h>
#include <sys/wait.h>
int main(){int n=0;for(int i=0;i<500;i++){pid_t p=fork();if(p==0){pause();_exit(0);}if(p>0)n++;}
printf("%d\n",n);fflush(stdout);return 0;}
'''
    h = jalan("c", sumber)
    assert h.status == "OK", h
    assert 0 < int(h.stdout) < 64  # pelari dan program sendiri ikut dihitung


def test_bom_fork_python_terkurung(jalan):
    h = jalan("python", "import os\nwhile True:\n    try:\n        os.fork()\n    except OSError:\n        pass\n")
    assert h.status in ("TLE", "RE")


# ---- 3. jaringan ---------------------------------------------------------------------

def test_soket_jaringan_ditolak(jalan):
    sumber = r'''
#include <stdio.h>
#include <errno.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
int main(){
  int s=socket(AF_INET,SOCK_STREAM,0); printf("inet=%d errno=%d\n",s,errno);
  int u=socket(AF_INET,SOCK_DGRAM,0); printf("udp=%d\n",u);
  int s6=socket(AF_INET6,SOCK_STREAM,0); printf("inet6=%d\n",s6);
  int r=socket(AF_INET,SOCK_RAW,1); printf("raw=%d\n",r);
  int p=socket(AF_PACKET,SOCK_RAW,0); printf("packet=%d\n",p);
  int n=socket(AF_NETLINK,SOCK_RAW,0); printf("netlink=%d\n",n);
  return 0;}
'''
    h = jalan("c", sumber)
    assert h.status == "OK"
    assert h.stdout == b"inet=-1 errno=1\nudp=-1\ninet6=-1\nraw=-1\npacket=-1\nnetlink=-1\n"


def test_python_tidak_bisa_menghubungi_jaringan(jalan):
    sumber = r'''
import socket, urllib.request
for nama, aksi in [
    ("connect", lambda: socket.create_connection(("1.1.1.1", 53), timeout=2)),
    ("dns", lambda: socket.getaddrinfo("lokandita.com", 443)),
    ("http", lambda: urllib.request.urlopen("http://1.1.1.1/", timeout=2)),
    ("loopback", lambda: socket.create_connection(("127.0.0.1", 3306), timeout=2)),
]:
    try:
        aksi(); print(nama, "BERHASIL")
    except Exception as e:
        print(nama, "gagal")
'''
    h = jalan("python", sumber)
    assert h.status == "OK", h.stderr
    assert h.stdout == b"connect gagal\ndns gagal\nhttp gagal\nloopback gagal\n"


def test_kontainer_hanya_punya_antarmuka_loopback(jalan):
    h = jalan("python", "import os\nprint(sorted(os.listdir('/sys/class/net')))\n")
    assert h.stdout == b"['lo']\n"


# ---- 4. /etc/passwd ------------------------------------------------------------------

def test_etc_passwd_tidak_terbaca_langsung_maupun_lewat_symlink(jalan):
    sumber = r'''
#include <stdio.h>
#include <unistd.h>
#include <fcntl.h>
int main(){
  FILE*f=fopen("/etc/passwd","r"); printf("langsung=%s\n",f?"TERBACA":"ditolak");
  int s=symlink("/etc/passwd","/box/x"); printf("symlink=%d\n",s);
  f=fopen("/box/x","r"); printf("lewat_symlink=%s\n",f?"TERBACA":"ditolak");
  int l=link("/etc/passwd","/box/y"); printf("hardlink=%s\n",l==0?"TERBUAT":"ditolak");
  f=fopen("/etc/shadow","r"); printf("shadow=%s\n",f?"TERBACA":"ditolak");
  f=fopen("/proc/1/environ","r"); printf("environ_pelari=%s\n",f?"TERBACA":"ditolak");
  return 0;}
'''
    h = jalan("c", sumber)
    assert h.status == "OK"
    assert h.stdout == (b"langsung=ditolak\nsymlink=0\nlewat_symlink=ditolak\nhardlink=ditolak\n"
                        b"shadow=ditolak\nenviron_pelari=ditolak\n")


def test_berkas_host_tidak_terlihat(jalan):
    sumber = r'''
import os
for p in ["/srv", "/srv/lokandita", "/srv/lokandita-data", "/home/deploy", "/var/run/docker.sock", "/run/docker.sock"]:
    print(p, os.path.exists(p))
print(sorted(os.listdir("/src")))
'''
    h = jalan("python", sumber)
    assert h.stdout == (b"/srv True\n/srv/lokandita False\n/srv/lokandita-data False\n/home/deploy False\n"
                        b"/var/run/docker.sock False\n/run/docker.sock False\n['main.py']\n")


# ---- 5. menulis 100 MB gagal di batas ukuran berkas -------------------------------------

def test_menulis_100_mb_gagal_di_batas_8_mb(jalan):
    sumber = r'''
#include <stdio.h>
#include <string.h>
#include <signal.h>
#include <errno.h>
#include <fcntl.h>
#include <unistd.h>
int main(){ signal(SIGXFSZ,SIG_IGN); static char b[1<<20]; memset(b,'a',sizeof b);
  int f=open("/box/besar",O_WRONLY|O_CREAT,0644); long total=0; int e=0;
  for(int i=0;i<100;i++){ ssize_t n=write(f,b,sizeof b); if(n<0){e=errno;break;} total+=n; if(n<(ssize_t)sizeof b){e=-1;break;} }
  printf("%ld %s\n",total,e==EFBIG?"EFBIG":"lain"); return 0;}
'''
    h = jalan("c", sumber)
    assert h.status == "OK", h
    assert h.stdout == b"8388608 EFBIG\n"


def test_menulis_100_mb_tanpa_menangani_sinyal_adalah_re(jalan):
    sumber = ('#include <stdio.h>\nint main(){FILE*f=fopen("/box/besar","w");static char b[1<<20];'
              "for(int i=0;i<100;i++)fwrite(b,1,sizeof b,f);fclose(f);puts(\"selesai\");return 0;}")
    h = jalan("c", sumber)
    assert h.status == "RE" and h.sinyal == kotak.SIGXFSZ
    assert b"selesai" not in h.stdout


def test_box_penuh_di_64_mb(jalan):
    sumber = r'''
import os
total = 0
try:
    for i in range(20):
        with open(f"/box/f{i}", "wb") as f:
            f.write(b"a" * (7 * 1024 * 1024)); total += 7
except OSError as e:
    print("gagal setelah", total, "MB")
'''
    h = jalan("python", sumber, memori_mb=256)
    # tmpfs dihitung sebagai memori kontainer: yang kena lebih dulu boleh batas /box atau batas memori.
    assert (h.status == "OK" and b"gagal setelah" in h.stdout and int(h.stdout.split()[2]) <= 64) or h.status == "MLE", h


# ---- 6. banjir keluaran -----------------------------------------------------------------

def test_banjir_stdout_dihentikan_di_1_mb(jalan):
    mulai = time.time()
    h = jalan("c", '#include <stdio.h>\nint main(){while(1)puts("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");}')
    assert h.status == "OLE"
    assert len(h.stdout) == kotak.BATAS_KELUARAN
    assert time.time() - mulai < 6


def test_banjir_stderr_dihentikan_di_1_mb(jalan):
    h = jalan("python", "import sys\nwhile True:\n    sys.stderr.write('e' * 1000)\n")
    assert h.status == "OLE"
    assert len(h.stderr) <= kotak.BATAS_KELUARAN + 256


def test_keluaran_tepat_di_bawah_batas_diterima(jalan):
    h = jalan("python", "import sys\nsys.stdout.write('a' * (1024 * 1024))\n")
    assert h.status == "OK" and len(h.stdout) == 1024 * 1024


# ---- 7. kehabisan memori ---------------------------------------------------------------

def test_kehabisan_memori_c_terkurung(jalan):
    sumber = ("#include <stdlib.h>\n#include <string.h>\n"
              "int main(){for(;;){char*p=malloc(1<<20);if(!p)return 3;memset(p,1,1<<20);}}")
    h = jalan("c", sumber)
    assert h.status == "MLE", h
    assert h.memori_kb <= 256 * 1024 + 1024


def test_kehabisan_memori_python_terkurung(jalan):
    h = jalan("python", "x = []\nwhile True:\n    x.append(bytearray(10**7))\n")
    assert h.status == "MLE", h


def test_swap_tidak_bisa_dipakai_untuk_melewati_batas_memori(jalan):
    # --memory-swap sama dengan --memory: 300 MB tidak bisa "ditampung" di swap.
    # volatile dan pembacaan ulang: tanpa itu gcc -O2 membuang alokasinya sama sekali.
    def sentuh(mb: int) -> str:
        return ("#include <stdlib.h>\n#include <stdio.h>\n"
                f"int main(){{size_t n={mb}u<<20;volatile char*p=malloc(n);if(!p)return 3;"
                "for(size_t i=0;i<n;i+=4096)p[i]=(char)i;unsigned long s=0;"
                "for(size_t i=0;i<n;i+=4096)s+=p[i];printf(\"lolos %lu\\n\",s);return 0;}")

    lebih = jalan("c", sentuh(300))
    assert lebih.status == "MLE" and b"lolos" not in lebih.stdout
    assert lebih.memori_kb <= 256 * 1024
    kurang = jalan("c", sentuh(200))
    assert kurang.status == "OK" and kurang.stdout.startswith(b"lolos")
    assert 200 * 1024 <= kurang.memori_kb <= 256 * 1024


# ---- 8. pengawas kompilasi 10 detik ----------------------------------------------------

def test_pengawas_kompilasi_bekerja_pada_10_detik():
    # 3000 fungsi inline yang digabung ke main: gcc -O2 butuh jauh lebih dari 10 detik.
    sumber = "".join(
        f"static inline unsigned f{i}(unsigned x){{for(int i=0;i<64;i++){{x=x*1664525u+1013904223u+{i}u;"
        f"if(x&1)x^=x>>3;else x+=x<<2;}}return x;}}\n" for i in range(3000))
    sumber += "int main(void){unsigned x=1;\n" + "".join(f"x=f{i}(x);" for i in range(3000)) + "return x&1;}\n"
    d = kotak.siapkan_dir_job()
    try:
        mulai = time.time()
        k = kotak.kompilasi("c", sumber, d)
        lama = time.time() - mulai
    finally:
        kotak.buang_dir_job(d)
    assert not k.ok and k.alasan == "waktu_habis"
    assert 9.5 <= lama < 14, f"pengawas bekerja setelah {lama:.1f} s"


def test_kompilasi_yang_menghabiskan_memori_terkurung():
    sumber = ("template<int N,class T> struct A{ enum{v=A<N-1,T[1]>::v+A<N-1,T[2]>::v}; };\n"
              "template<class T> struct A<0,T>{enum{v=1};};\nint main(){return A<40,int>::v;}\n")
    d = kotak.siapkan_dir_job()
    try:
        k = kotak.kompilasi("cpp", sumber, d)
    finally:
        kotak.buang_dir_job(d)
    assert not k.ok and k.alasan == "memori_habis"


def test_kompilasi_tidak_bisa_menyertakan_berkas_host_atau_perangkat():
    d = kotak.siapkan_dir_job()
    try:
        k = kotak.kompilasi("c", '#include "/etc/passwd"\nint main(){return 0;}\n', d)
    finally:
        kotak.buang_dir_job(d)
    assert not k.ok and k.alasan == ""
    assert "root:" not in k.log


# ---- pagar lain yang tidak boleh dilonggarkan (CLAUDE.md) --------------------------------

def test_root_hanya_baca_dan_sumber_tidak_bisa_diubah(jalan):
    sumber = r'''
import os
for p in ["/tmp/x", "/x", "/usr/x", "/src/x", "/src/main.py", "/etc/x", "/dev/shm/x", "/var/tmp/x"]:
    try:
        open(p, "w").close(); print(p, "TERTULIS")
    except OSError:
        print(p, "ditolak")
open("/box/x", "w").close(); print("/box/x", "tertulis")
'''
    h = jalan("python", sumber)
    assert h.status == "OK", h.stderr
    assert b"TERTULIS" not in h.stdout
    assert h.stdout.endswith(b"/box/x tertulis\n")


def test_bukan_root_tanpa_capability_tanpa_hak_baru_dengan_seccomp(jalan):
    sumber = r'''
import os
s = dict(b.split(":\t", 1) for b in open("/proc/self/status").read().splitlines() if ":\t" in b)
print(os.getuid(), os.getgid(), s["CapEff"], s["CapPrm"], s["CapBnd"], s["NoNewPrivs"], s["Seccomp"])
'''
    h = jalan("python", sumber)
    assert h.stdout == b"65534 65534 0000000000000000 0000000000000000 0000000000000000 1 2\n"


def test_tidak_bisa_menjadi_root(jalan):
    sumber = ('#include <stdio.h>\n#include <unistd.h>\n'
              'int main(){printf("%d %d\\n",setuid(0),setgid(0));printf("%d\\n",geteuid());return 0;}')
    assert jalan("c", sumber).stdout == b"-1 -1\n65534\n"


def test_syscall_berbahaya_ditolak_seccomp(jalan):
    sumber = r'''
#define _GNU_SOURCE
#include <stdio.h>
#include <errno.h>
#include <sched.h>
#include <unistd.h>
#include <sys/ptrace.h>
#include <sys/mount.h>
#include <sys/syscall.h>
int main(){
  printf("ptrace=%ld\n",ptrace(PTRACE_TRACEME,0,0,0));
  printf("unshare=%d\n",unshare(CLONE_NEWUSER));
  printf("mount=%d\n",mount("none","/box","tmpfs",0,0));
  printf("io_uring=%ld\n",syscall(SYS_io_uring_setup,4,0));
  printf("bpf=%ld\n",syscall(SYS_bpf,0,0,0));
  printf("keyctl=%ld\n",syscall(SYS_keyctl,0,0,0,0,0));
  printf("userfaultfd=%ld\n",syscall(SYS_userfaultfd,0));
  printf("perf=%ld\n",syscall(SYS_perf_event_open,0,0,0,0,0));
  printf("shmget=%ld\n",syscall(SYS_shmget,0,4096,01000|0600));
  printf("chroot=%d\n",chroot("/box"));
  return 0;}
'''
    h = jalan("c", sumber)
    assert h.status == "OK", h
    assert h.stdout == (b"ptrace=-1\nunshare=-1\nmount=-1\nio_uring=-1\nbpf=-1\nkeyctl=-1\nuserfaultfd=-1\n"
                        b"perf=-1\nshmget=-1\nchroot=-1\n")


def test_program_32_bit_ditolak(jalan):
    # int 0x80 = pintu syscall 32-bit; profil seccomp hanya mengizinkan x86-64.
    sumber = ('#include <stdio.h>\nint main(){long r;__asm__ volatile("int $0x80":"=a"(r):"a"(20L));'
              'printf("%ld\\n",r);return 0;}')
    h = jalan("c", sumber)
    assert h.status == "RE" or (h.status == "OK" and int(h.stdout) < 0), h


def test_box_selalu_baru(jalan):
    tulis = 'open("/box/jejak", "w").write("x")\nprint("ditulis")\n'
    baca = 'import os\nprint(os.listdir("/box"))\n'
    assert jalan("python", tulis).stdout == b"ditulis\n"
    assert jalan("python", baca).stdout == b"['.mpl']\n"


def test_laporan_pelari_tidak_bisa_dipalsukan(jalan):
    # Program mencetak baris yang mirip laporan, lalu berputar selamanya.
    sumber = r'''
import os, sys
print(os.environ.get("PELARI_TANDA"))
tanda = "0" * 32
sys.stderr.write(f"\n{tanda} exit=0 signal=0 cpu_ms=1 wall_ms=1 wall=0 oom=0 mem_kb=1\n"); sys.stderr.flush()
sys.stdout.flush()
while True:
    pass
'''
    h = jalan("python", sumber)
    assert h.status == "TLE"
    assert h.stdout == b"None\n"
    assert b"exit=0 signal=0 cpu_ms=1" in h.stderr  # baris palsu hanyalah keluaran biasa


def test_proses_anak_yang_ditinggal_ikut_mati(jalan):
    sumber = ('#include <unistd.h>\n#include <stdio.h>\n'
              'int main(){if(fork()==0){setsid();for(;;)pause();}puts("induk selesai");return 0;}')
    mulai = time.time()
    h = jalan("c", sumber)
    assert h.status == "OK" and h.stdout == b"induk selesai\n"
    assert time.time() - mulai < 4  # tidak menunggu anak yang tidak pernah selesai


# ---- gagal tertutup ---------------------------------------------------------------------

def test_tanpa_docker_tidak_ada_yang_dijalankan(monkeypatch, tmp_path):
    monkeypatch.setattr(kotak, "DOCKER", str(tmp_path / "docker-tidak-ada"))
    d = kotak.siapkan_dir_job()
    try:
        with pytest.raises(kotak.GalatInfrastruktur):
            kotak.kompilasi("c", "int main(){return 0;}", d)
        (d / "prog").write_text("#!/bin/sh\ntouch /tmp/judge-jalan-di-host\n")
        with pytest.raises(kotak.GalatInfrastruktur):
            kotak.jalankan("c", d, b"")
        with pytest.raises(kotak.GalatInfrastruktur):
            kotak.periksa_host()
    finally:
        kotak.buang_dir_job(d)
    monkeypatch.undo()


def test_docker_yang_gagal_adalah_galat_infrastruktur_bukan_vonis(monkeypatch, tmp_path):
    palsu = tmp_path / "docker"
    palsu.write_text("#!/bin/sh\necho 'Cannot connect to the Docker daemon' >&2\nexit 125\n")
    palsu.chmod(0o755)
    monkeypatch.setattr(kotak, "DOCKER", str(palsu))
    d = kotak.siapkan_dir_job()
    try:
        with pytest.raises(kotak.GalatInfrastruktur) as info:
            kotak.kompilasi("c", "int main(){return 0;}", d)
        assert "Cannot connect" in str(info.value)
    finally:
        kotak.buang_dir_job(d)
    monkeypatch.undo()


def test_image_yang_tidak_ada_adalah_galat_infrastruktur(monkeypatch):
    from lokandita_judge import citra
    monkeypatch.setattr(citra, "tag", lambda: "tidakadatag00")
    d = kotak.siapkan_dir_job()
    try:
        with pytest.raises(kotak.GalatInfrastruktur):
            kotak.kompilasi("c", "int main(){return 0;}", d)
        with pytest.raises(kotak.GalatInfrastruktur):
            kotak.periksa_host()
    finally:
        kotak.buang_dir_job(d)
    monkeypatch.undo()


def test_host_ini_memenuhi_syarat():
    kotak.periksa_host()


def test_bendera_kontainer_memuat_semua_batas():
    b = kotak.bendera_kontainer("n", kotak.DIR_JOB / "x", "t" * 32, 256, 10)
    teks = " ".join(b)
    for wajib in ["--network none", "--read-only", "--memory 256m", "--memory-swap 256m", "--cpus 1",
                  "--pids-limit 64", "--cap-drop ALL", "no-new-privileges", "seccomp=", "--user 65534:65534",
                  "fsize=8388608", "nofile=64", "core=0", "/box:rw,size=64m", "noexec", ":/src:ro",
                  "--label lokandita.judge=true"]:
        assert wajib in teks, wajib
    for terlarang in ["--privileged", "--cap-add", "seccomp=unconfined", "--pid ", "--pid=", "--network host", "docker.sock"]:
        assert terlarang not in teks
