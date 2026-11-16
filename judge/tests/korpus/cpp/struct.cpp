// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA STRUCT
#include <iostream>
struct Titik { int x; int y; };
typedef long long besar;
enum Warna { MERAH, HIJAU };
union Angka { int i; float f; };
int main() {
    Titik t;
    std::cin >> t.x >> t.y;
    besar jumlah = t.x + t.y;
    std::cout << jumlah << std::endl;
    return 0;
}
