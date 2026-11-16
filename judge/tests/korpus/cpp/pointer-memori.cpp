// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERULANGAN ARRAY_1D POINTER MEMORI_DINAMIS
#include <iostream>
int main() {
    int n;
    std::cin >> n;
    int *data = new int[n];
    for (int i = 0; i < n; i++) std::cin >> data[i];
    int x = 5;
    int *p = &x;
    std::cout << *p + data[0] << std::endl;
    delete[] data;
    return 0;
}
