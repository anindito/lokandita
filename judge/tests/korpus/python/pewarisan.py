# KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA CLASS_OBJECT CONSTRUCTOR_DESTRUCTOR INHERITANCE POLYMORPHISM ABSTRACT_CLASS INTERFACE
from abc import ABC, abstractmethod


class Bentuk(ABC):
    @abstractmethod
    def luas(self):
        pass


class Persegi(Bentuk):
    def __init__(self, sisi):
        self.sisi = sisi

    def luas(self):
        return self.sisi * self.sisi


s = float(input())
print(Persegi(s).luas())
