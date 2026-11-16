// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN PERULANGAN ARRAY_1D FUNGSI CLASS_OBJECT ENCAPSULATION MODERN_CPP TEMPLATE_FUNGSI TEMPLATE_CLASS STL_CONTAINER STL_ALGORITMA SMART_POINTER COPY_SEMANTICS POINTER
#include <iostream>
#include <vector>
#include <algorithm>
#include <memory>
template <typename T> T maks(T a, T b) { return a > b ? a : b; }
template <class T> class Kotak {
    T isi;
public:
    T ambil() const { return isi; }
};
int main() {
    int n;
    std::cin >> n;
    std::vector<int> v(n);
    for (auto &x : v) std::cin >> x;
    std::sort(v.begin(), v.end());
    std::unique_ptr<int> p = std::make_unique<int>(maks(v[0], 3));
    std::unique_ptr<int> q = std::move(p);
    int z{5};
    int *kosong = nullptr;
    std::cout << *q + z << (kosong == nullptr) << std::endl;
    return 0;
}
