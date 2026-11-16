# KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN FUNGSI REKURSI
def faktorial(n):
    if n <= 1:
        return 1
    return n * faktorial(n - 1)


def bagi(a, b):
    return a // b, a % b


tambah = lambda a, b: a + b
n = int(input())
hasil, sisa = bagi(n, 2)
print(faktorial(n), hasil, sisa, tambah(n, 1))
