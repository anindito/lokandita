// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN PERULANGAN NESTED_LOOP BREAK_CONTINUE
#include <iostream>
int main() {
    int n;
    std::cin >> n;
    for (int i = 0; i < n; i++) {
        for (int j = 0; j < n; j++) {
            if (j > i) break;
            if (j == 2) continue;
            std::cout << "*";
        }
        std::cout << "\n";
    }
    return 0;
}
