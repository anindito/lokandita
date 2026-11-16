// KONSEP: IO_DASAR VARIABEL_TIPE PERCABANGAN
// Operator logika tanpa if tetap PERCABANGAN (SPEC.md §9.1).
#include <iostream>
int main() {
    int a, b;
    std::cin >> a >> b;
    bool keduanya = a > 0 || !(b > 0);
    std::cout << keduanya << std::endl;
    return 0;
}
