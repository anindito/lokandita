// KONSEP: IO_DASAR VARIABEL_TIPE STRING
// char[] adalah string (SPEC.md §9.1), bukan pelajaran array.
#include <iostream>
#include <cstring>
int main() {
    char kata[50];
    std::cin >> kata;
    std::cout << strlen(kata) << std::endl;
    return 0;
}
