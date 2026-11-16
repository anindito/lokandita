// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERULANGAN NESTED_LOOP ARRAY_1D ARRAY_2D
#include <iostream>
int main() {
    int a[100], m[3][3];
    int n;
    std::cin >> n;
    for (int i = 0; i < n; i++) std::cin >> a[i];
    for (int i = 0; i < 3; i++)
        for (int j = 0; j < 3; j++) m[i][j] = i * j;
    std::cout << a[0] << m[1][1] << std::endl;
    return 0;
}
