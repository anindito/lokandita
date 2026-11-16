# KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN PERULANGAN NESTED_LOOP BREAK_CONTINUE
n = int(input())
for i in range(n):
    j = 0
    while j < n:
        if j > i and not j == 3:
            break
        j += 1
    if i == 2:
        continue
    print(i if i % 2 == 0 else -i)
