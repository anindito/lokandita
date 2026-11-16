/*
 * kelompok — menjalankan SATU program berkali-kali di dalam satu kontainer
 * judge (SPEC.md §8.2: semua tes sebuah soal berjalan di satu kontainer).
 *
 * Dipakai HANYA untuk generator dan solusi referensi sebuah soal, yaitu kode
 * yang ditulis dosen. Kode mahasiswa tetap satu kontainer baru per tes
 * (SPEC.md §10). Kontainernya sama terbatasnya dengan kontainer judge lain;
 * kelompok tidak melonggarkan apa pun, dan bukan pagar keamanan.
 *
 *   pelari <cpu total> <wall total> kelompok gen <n> <cpu_ms> <wall_ms> <batas> <henti> <seed,seed,…> -- <program…>
 *   pelari <cpu total> <wall total> kelompok uji <n> <cpu_ms> <wall_ms> <batas> <henti> -            -- <program…>
 *
 *   gen  untuk tiap seed, untuk INDEX 0..n-1: program dijalankan dengan
 *        lingkungan SEED dan INDEX, tanpa masukan
 *   uji  untuk i 0..n-1: program dijalankan dengan stdin dari /src/in/<i>
 *
 * Sebelum tiap kali jalan isi /box dikosongkan; sesudahnya semua proses yang
 * tersisa dimatikan. Tiap kali jalan dibatasi: waktu CPU (RLIMIT_CPU), waktu
 * dinding, dan <batas> byte stdout.
 *
 * Untuk tiap kali jalan, satu bingkai ditulis ke stdout kontainer:
 *
 *   K <nomor> <exit> <sinyal> <cpu_ms> <wall_ms> <bendera> <panjang stdout> <panjang stderr>\n
 *   <stdout mentah><stderr mentah>
 *
 * bendera: 1 = waktu dinding habis, 2 = stdout melewati batas.
 * <henti> = 1: berhenti pada kali jalan pertama yang gagal.
 * Program yang dijalankan tidak memegang stdout kontainer (stdout-nya pipa),
 * jadi ia tidak bisa menulis bingkai palsu.
 */
#define _GNU_SOURCE
#include <dirent.h>
#include <errno.h>
#include <fcntl.h>
#include <poll.h>
#include <signal.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/resource.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <sys/wait.h>
#include <time.h>
#include <unistd.h>

#define BATAS_STDERR 2048

static long long ms_sekarang(void)
{
	struct timespec t;
	clock_gettime(CLOCK_MONOTONIC, &t);
	return (long long)t.tv_sec * 1000 + t.tv_nsec / 1000000;
}

/* Menghapus seluruh isi direktori (bukan direktorinya sendiri). */
static void kosongkan(const char *jalur)
{
	DIR *d = opendir(jalur);
	if (!d)
		return;
	struct dirent *e;
	while ((e = readdir(d)) != NULL) {
		if (strcmp(e->d_name, ".") == 0 || strcmp(e->d_name, "..") == 0)
			continue;
		char anak[4096];
		if (snprintf(anak, sizeof anak, "%s/%s", jalur, e->d_name) >= (int)sizeof anak)
			continue;
		struct stat s;
		if (lstat(anak, &s) != 0)
			continue;
		if (S_ISDIR(s.st_mode)) {
			chmod(anak, 0700);
			kosongkan(anak);
			rmdir(anak);
		} else {
			unlink(anak);
		}
	}
	closedir(d);
}

static int tulis_semua(int fd, const char *data, size_t n)
{
	while (n > 0) {
		ssize_t t = write(fd, data, n);
		if (t < 0) {
			if (errno == EINTR)
				continue;
			return -1;
		}
		data += t;
		n -= (size_t)t;
	}
	return 0;
}

/* Membaca yang tersedia dari pipa tak-memblokir ke penampung; yang melewati batas dibuang. */
static void sedot(int *fd, char *buf, size_t batas, size_t *n, int *lewat)
{
	char sementara[65536];
	for (;;) {
		ssize_t t = read(*fd, sementara, sizeof sementara);
		if (t > 0) {
			size_t muat = batas - *n;
			if ((size_t)t > muat) {
				*lewat = 1;
				t = (ssize_t)muat;
			}
			memcpy(buf + *n, sementara, (size_t)t);
			*n += (size_t)t;
			continue;
		}
		if (t == 0) {
			close(*fd);
			*fd = -1;
		}
		return; /* EAGAIN atau galat: coba lagi nanti */
	}
}

int main(int argc, char **argv)
{
	if (argc < 10 || strcmp(argv[8], "--") != 0) {
		fprintf(stderr, "kelompok: pemakaian: kelompok <gen|uji> <n> <cpu_ms> <wall_ms> <batas> <henti> <seed,…|-> -- <program…>\n");
		return 2;
	}
	int gen = strcmp(argv[1], "gen") == 0;
	long n = atol(argv[2]), cpu_ms = atol(argv[3]), wall_ms = atol(argv[4]), batas = atol(argv[5]);
	int henti = atoi(argv[6]);
	char *daftar_seed = argv[7];
	char **program = argv + 9;
	if ((!gen && strcmp(argv[1], "uji") != 0) || n <= 0 || n > 100000 || cpu_ms <= 0 || wall_ms <= 0 || batas <= 0
		|| batas > 64 * 1024 * 1024) {
		fprintf(stderr, "kelompok: argumen tidak sah\n");
		return 2;
	}
	char *keluar = malloc((size_t)batas), *galat = malloc(BATAS_STDERR);
	if (!keluar || !galat)
		return 2;

	long nomor = 0;
	char *simpan = NULL;
	char *seed = gen ? strtok_r(daftar_seed, ",", &simpan) : "";
	for (; seed != NULL; seed = gen ? strtok_r(NULL, ",", &simpan) : NULL) {
		for (long i = 0; i < n; i++, nomor++) {
			kosongkan("/box");
			int pk[2], pg[2];
			if (pipe2(pk, O_CLOEXEC) != 0 || pipe2(pg, O_CLOEXEC) != 0)
				return 3;

			long long mulai = ms_sekarang();
			pid_t anak = fork();
			if (anak < 0)
				return 3;
			if (anak == 0) {
				char angka[32];
				int masuk;
				if (gen) {
					setenv("SEED", seed, 1);
					snprintf(angka, sizeof angka, "%ld", i);
					setenv("INDEX", angka, 1);
					masuk = open("/dev/null", O_RDONLY);
				} else {
					char berkas[64];
					snprintf(berkas, sizeof berkas, "/src/in/%ld", i);
					masuk = open(berkas, O_RDONLY);
				}
				if (masuk < 0 || dup2(masuk, 0) < 0 || dup2(pk[1], 1) < 0 || dup2(pg[1], 2) < 0)
					_exit(126);
				if (chdir("/box") != 0)
					_exit(126);
				struct rlimit cpu;
				cpu.rlim_cur = (rlim_t)((cpu_ms + 999) / 1000);
				cpu.rlim_max = cpu.rlim_cur + 1;
				setrlimit(RLIMIT_CPU, &cpu);
				execvp(program[0], program);
				_exit(127);
			}
			close(pk[1]);
			close(pg[1]);
			fcntl(pk[0], F_SETFL, O_NONBLOCK);
			fcntl(pg[0], F_SETFL, O_NONBLOCK);

			size_t nk = 0, ng = 0;
			int lewat = 0, lewat_galat = 0, wall = 0, status = 0, selesai = 0;
			struct rusage pakai;
			memset(&pakai, 0, sizeof pakai);
			while (!selesai) {
				struct pollfd p[2] = { { pk[0], POLLIN, 0 }, { pg[0], POLLIN, 0 } };
				poll(p, 2, 20);
				if (pk[0] >= 0)
					sedot(&pk[0], keluar, (size_t)batas, &nk, &lewat);
				if (pg[0] >= 0)
					sedot(&pg[0], galat, BATAS_STDERR, &ng, &lewat_galat);
				if (!wall && (lewat || ms_sekarang() - mulai > wall_ms)) {
					wall = !lewat;
					kill(anak, SIGKILL);
				}
				pid_t p_selesai = wait4(anak, &status, WNOHANG, &pakai);
				if (p_selesai == anak)
					selesai = 1;
			}
			long long lama = ms_sekarang() - mulai;
			/* Tidak ada proses yang boleh hidup lebih lama daripada kali jalan ini
			 * (kill -1 tidak mengenai pelari, PID 1, dan tidak mengenai kelompok sendiri). */
			kill(-1, SIGKILL);
			while (waitpid(-1, NULL, WNOHANG) > 0)
				;
			if (pk[0] >= 0) {
				sedot(&pk[0], keluar, (size_t)batas, &nk, &lewat);
				if (pk[0] >= 0)
					close(pk[0]);
			}
			if (pg[0] >= 0) {
				sedot(&pg[0], galat, BATAS_STDERR, &ng, &lewat_galat);
				if (pg[0] >= 0)
					close(pg[0]);
			}

			long long cpu = (long long)pakai.ru_utime.tv_sec * 1000 + pakai.ru_utime.tv_usec / 1000
				+ (long long)pakai.ru_stime.tv_sec * 1000 + pakai.ru_stime.tv_usec / 1000;
			int kode = WIFEXITED(status) ? WEXITSTATUS(status) : -1;
			int sinyal = WIFSIGNALED(status) ? WTERMSIG(status) : 0;
			char kepala[160];
			int nkepala = snprintf(kepala, sizeof kepala, "K %ld %d %d %lld %lld %d %zu %zu\n",
				nomor, kode, sinyal, cpu, lama, (wall ? 1 : 0) | (lewat ? 2 : 0), nk, ng);
			if (tulis_semua(1, kepala, (size_t)nkepala) != 0 || tulis_semua(1, keluar, nk) != 0
				|| tulis_semua(1, galat, ng) != 0)
				return 3;
			if (henti && (kode != 0 || sinyal != 0 || wall || lewat || cpu > cpu_ms))
				return 0;
		}
	}
	return 0;
}
