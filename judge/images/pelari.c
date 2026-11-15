/*
 * pelari — pengawas kecil yang menjadi proses pertama (PID 1) di setiap
 * kontainer judge.
 *
 *   pelari <batas_cpu_ms> <batas_wall_ms> <program> [argumen…]
 *
 * Menjalankan program dengan stdin/stdout/stderr kontainer apa adanya, lalu
 * menulis SATU baris laporan ke stderr:
 *
 *   <tanda> exit=<n> signal=<n> cpu_ms=<n> wall_ms=<n> wall=<0|1> oom=<n> mem_kb=<n>
 *
 * Batas CPU dipaksakan kernel (RLIMIT_CPU: SIGXCPU pada batas, SIGKILL satu
 * detik kemudian). Batas waktu dinding dipaksakan pelari: saat terlampaui
 * semua proses dimatikan dan laporan memuat wall=1.
 *
 * Waktu diukur di sini supaya lama menyalakan kontainer tidak dibebankan
 * kepada mahasiswa (keputusan pemilik, tahap 7).
 *
 * <tanda> adalah nilai acak dari variabel PELARI_TANDA. Program mahasiswa
 * tidak boleh bisa memalsukan baris laporan, jadi tanda itu:
 *   - dihapus dari lingkungan sebelum program dijalankan;
 *   - ditimpa di memori /proc/1/environ;
 *   - dan pelari menandai dirinya tidak-dumpable, sehingga isi /proc/1 tidak
 *     terbaca oleh proses lain walau uid-nya sama.
 *
 * Pelari TIDAK menjadi pagar keamanan: batas memori, proses, jaringan dan
 * waktu dinding dipaksakan dari luar oleh Docker dan worker. Jika pelari mati
 * atau tidak sempat melapor, worker menganggap batasnya terlampaui.
 */
#define _GNU_SOURCE
#include <errno.h>
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/prctl.h>
#include <sys/resource.h>
#include <sys/time.h>
#include <sys/wait.h>
#include <time.h>
#include <unistd.h>

static volatile sig_atomic_t wall_habis = 0;

static void saat_wall_habis(int sinyal)
{
	(void)sinyal;
	wall_habis = 1;
	kill(-1, SIGKILL); /* semua proses di kontainer kecuali pelari (PID 1) */
}

static long long ms_sekarang(void)
{
	struct timespec t;
	clock_gettime(CLOCK_MONOTONIC, &t);
	return (long long)t.tv_sec * 1000 + t.tv_nsec / 1000000;
}

/* Membaca "<kunci> <angka>" dari berkas cgroup; -1 bila tidak ada. */
static long long baca_cgroup(const char *berkas, const char *kunci)
{
	char baris[256];
	long long nilai = -1;
	FILE *f = fopen(berkas, "r");
	if (!f)
		return -1;
	while (fgets(baris, sizeof baris, f)) {
		if (!kunci) {
			nilai = atoll(baris);
			break;
		}
		size_t n = strlen(kunci);
		if (strncmp(baris, kunci, n) == 0 && baris[n] == ' ') {
			nilai = atoll(baris + n + 1);
			break;
		}
	}
	fclose(f);
	return nilai;
}

int main(int argc, char **argv)
{
	char tanda[80] = "";

	if (argc < 4) {
		fprintf(stderr, "pelari: pemakaian: pelari <batas_cpu_ms> <batas_wall_ms> <program> [argumen]\n");
		return 2;
	}
	long batas_ms = atol(argv[1]);
	long batas_wall_ms = atol(argv[2]);
	if (batas_ms <= 0 || batas_wall_ms <= 0) {
		fprintf(stderr, "pelari: batas harus positif\n");
		return 2;
	}

	char *t = getenv("PELARI_TANDA");
	if (!t || strlen(t) < 16 || strlen(t) >= sizeof tanda) {
		fprintf(stderr, "pelari: PELARI_TANDA tidak ada atau panjangnya salah\n");
		return 2;
	}
	strcpy(tanda, t);
	memset(t, 'x', strlen(t)); /* menimpa isi /proc/1/environ */
	unsetenv("PELARI_TANDA");
	if (prctl(PR_SET_DUMPABLE, 0) != 0) {
		fprintf(stderr, "pelari: PR_SET_DUMPABLE gagal\n");
		return 2;
	}

	if (chdir("/box") != 0) {
		fprintf(stderr, "pelari: /box tidak ada\n");
		return 2;
	}

	long long mulai = ms_sekarang();
	pid_t anak = fork();
	if (anak < 0) {
		fprintf(stderr, "pelari: fork gagal\n");
		return 2;
	}
	if (anak == 0) {
		/* Kernel mengirim SIGXCPU saat batas CPU tercapai dan SIGKILL satu
		 * detik kemudian, walau pelari atau worker tidak sempat bertindak. */
		struct rlimit cpu;
		cpu.rlim_cur = (rlim_t)((batas_ms + 999) / 1000);
		cpu.rlim_max = cpu.rlim_cur + 1;
		setrlimit(RLIMIT_CPU, &cpu);
		execvp(argv[3], argv + 3);
		fprintf(stderr, "pelari: tidak bisa menjalankan %s: %s\n", argv[3], strerror(errno));
		_exit(127);
	}

	struct sigaction aksi;
	memset(&aksi, 0, sizeof aksi);
	aksi.sa_handler = saat_wall_habis;
	sigaction(SIGALRM, &aksi, NULL);
	struct itimerval jam;
	memset(&jam, 0, sizeof jam);
	jam.it_value.tv_sec = batas_wall_ms / 1000;
	jam.it_value.tv_usec = (batas_wall_ms % 1000) * 1000;
	setitimer(ITIMER_REAL, &jam, NULL);

	/* Sebagai PID 1, pelari juga memungut proses yatim sampai program utama selesai. */
	int status = 0;
	struct rusage pakai;
	memset(&pakai, 0, sizeof pakai);
	for (;;) {
		struct rusage r;
		int s;
		pid_t p = wait4(-1, &s, 0, &r);
		if (p == anak) {
			status = s;
			pakai = r;
			break;
		}
		if (p < 0 && errno != EINTR)
			break;
	}
	long long wall_ms = ms_sekarang() - mulai;
	memset(&jam, 0, sizeof jam);
	setitimer(ITIMER_REAL, &jam, NULL);

	/* Tidak ada proses yang boleh hidup lebih lama daripada program utama. */
	kill(-1, SIGKILL);

	long long cpu_ms = (long long)pakai.ru_utime.tv_sec * 1000 + pakai.ru_utime.tv_usec / 1000
		+ (long long)pakai.ru_stime.tv_sec * 1000 + pakai.ru_stime.tv_usec / 1000;
	long long oom = baca_cgroup("/sys/fs/cgroup/memory.events", "oom_kill");
	long long puncak = baca_cgroup("/sys/fs/cgroup/memory.peak", NULL);

	char laporan[256];
	int n = snprintf(laporan, sizeof laporan, "\n%s exit=%d signal=%d cpu_ms=%lld wall_ms=%lld wall=%d oom=%lld mem_kb=%lld\n",
		tanda,
		WIFEXITED(status) ? WEXITSTATUS(status) : -1,
		WIFSIGNALED(status) ? WTERMSIG(status) : 0,
		cpu_ms, wall_ms, wall_habis ? 1 : 0, oom < 0 ? 0 : oom, puncak < 0 ? -1 : puncak / 1024);
	if (write(2, laporan, (size_t)n) != n)
		return 3;
	return 0;
}
