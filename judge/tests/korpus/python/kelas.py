# KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA CLASS_OBJECT CONSTRUCTOR_DESTRUCTOR ENCAPSULATION KOMPOSISI_AGREGASI OPERATOR_OVERLOAD
class Mesin:
    def __init__(self, daya):
        self.daya = daya


class Mobil:
    def __init__(self, harga):
        self.__harga = harga
        self.mesin = Mesin(100)

    @property
    def harga(self):
        return self.__harga

    def __add__(self, lain):
        return Mobil(self.__harga + lain.harga)


h = float(input())
a = Mobil(h)
print((a + a).harga)
