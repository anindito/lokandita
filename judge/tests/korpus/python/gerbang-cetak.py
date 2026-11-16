# KONSEP: IO_DASAR VARIABEL_TIPE
# print("x", y) bukan STRING (SPEC.md §9.1); int(input()) dan input().split() adalah cara membaca.
a = int(input())
b, c = map(float, input().split())
print("Nilai:", a, b, c)
print(f"a = {a}")
