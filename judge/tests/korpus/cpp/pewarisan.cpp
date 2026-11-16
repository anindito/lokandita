// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA CLASS_OBJECT CONSTRUCTOR_DESTRUCTOR ENCAPSULATION INHERITANCE POLYMORPHISM ABSTRACT_CLASS INTERFACE POINTER MEMORI_DINAMIS
#include <iostream>
class Bentuk {
public:
    virtual double luas() const = 0;
    virtual ~Bentuk() {}
};
class Persegi : public Bentuk {
protected:
    double sisi;
public:
    Persegi(double s) : sisi(s) {}
    double luas() const override { return sisi * sisi; }
};
int main() {
    double s;
    std::cin >> s;
    Bentuk *b = new Persegi(s);
    std::cout << b->luas() << std::endl;
    delete b;
    return 0;
}
