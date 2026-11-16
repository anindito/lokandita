# KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERULANGAN PY_LIST PY_DICT_SET PY_COMPREHENSION
data = list(map(int, input().split()))
data.append(4)
pertama = data[0]
bagian = data[1:]
kuadrat = [x * x for x in data]
umur = {"ani": 20}
umur["budi"] = 21
titik = (1, 2)
unik = set(data)
for kunci, nilai in umur.items():
    print(kunci, nilai)
print(pertama, bagian, kuadrat, titik, unik)
