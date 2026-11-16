# KONSEP: IO_DASAR VARIABEL_TIPE KONVERSI_TIPE FORMAT_OUTPUT
x = float(input())
bulat = int(x)
teks = str(bulat)
print(f"{x:.2f}")
print("%.3f" % x)
print("{:8.2f}".format(x))
print(format(x, ".1f"), teks)
