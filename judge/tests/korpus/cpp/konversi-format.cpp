// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA KONVERSI_TIPE FORMAT_OUTPUT
#include <iostream>
#include <iomanip>
#include <cstdio>
using namespace std;
int main() {
    int a, b;
    cin >> a >> b;
    double rata = (double) (a + b) / 2;
    double lain = static_cast<double>(a) / b;
    int bulat = int(rata);
    cout << fixed << setprecision(2) << rata << " " << setw(8) << lain << endl;
    printf("%.3f %5d\n", rata, bulat);
    printf("%d\n", bulat);
    return 0;
}
