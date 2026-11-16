// KONSEP: IO_DASAR VARIABEL_TIPE
// Gerbang tahap 8 (SPEC.md §18): mencetak literal string di antara dua double bukan STRING.
#include <iostream>
using namespace std;
int main() {
    double x, y;
    cin >> x >> y;
    cout << x << " " << y << endl;
    return 0;
}
