// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN FUNGSI REKURSI OVERLOAD_FUNGSI
#include <iostream>
using namespace std;
int faktorial(int n) { if (n <= 1) return 1; return n * faktorial(n - 1); }
bool genap(int n);
bool ganjil(int n) { if (n == 0) return false; return genap(n - 1); }
bool genap(int n) { if (n == 0) return true; return ganjil(n - 1); }
double luas(double r) { return 3.14 * r * r; }
double luas(double p, double l) { return p * l; }
int tambah(int a, int b = 1) { return a + b; }
int main() {
    int n;
    cin >> n;
    cout << faktorial(n) << genap(n) << luas(1.0) << luas(1.0, 2.0) << tambah(n) << endl;
    return 0;
}
