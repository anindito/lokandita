// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA CLASS_OBJECT CONSTRUCTOR_DESTRUCTOR COPY_SEMANTICS ENCAPSULATION KOMPOSISI_AGREGASI OPERATOR_OVERLOAD PARAMETER_REF
#include <iostream>
class Mesin {
public:
    int daya;
};
class Mobil {
private:
    double harga;
    Mesin mesin;
public:
    Mobil(double h) : harga(h) {}
    Mobil(const Mobil &lain) : harga(lain.harga) {}
    ~Mobil() {}
    Mobil &operator=(const Mobil &lain) { harga = lain.harga; return *this; }
    Mobil operator+(const Mobil &lain) const { return Mobil(harga + lain.harga); }
    double getHarga() const { return harga; }
};
int main() {
    double h;
    std::cin >> h;
    Mobil a(h);
    Mobil b = a + a;
    std::cout << b.getHarga() << std::endl;
    return 0;
}
