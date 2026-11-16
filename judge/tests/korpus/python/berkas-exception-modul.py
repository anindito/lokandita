# KONSEP: IO_DASAR VARIABEL_TIPE PERCABANGAN FUNGSI FILE_IO EXCEPTION MODUL
import random


def utama():
    with open("/box/data.txt", "w") as f:
        f.write("5\n")
    try:
        n = int(input())
        if n < 0:
            raise ValueError("negatif")
    except ValueError:
        n = 0
    print(n)


if __name__ == "__main__":
    utama()
