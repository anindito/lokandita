// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERULANGAN
#include <iostream>
int main() {
    int n, jumlah = 0;
    std::cin >> n;
    for (int i = 1; i <= n; i++) jumlah += i;
    int k = n;
    while (k > 0) k--;
    do { k++; } while (k < 3);
    std::cout << jumlah << std::endl;
    return 0;
}
