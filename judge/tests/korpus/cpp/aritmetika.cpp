// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA
#include <iostream>
int main() {
    int a, b;
    std::cin >> a >> b;
    int jumlah = a + b * 2 - a % 3;
    std::cout << "Jumlah: " << jumlah << "\n";
    std::cout << "Selisih = " << a - b << '\n';
    return 0;
}
