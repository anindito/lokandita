// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA FUNGSI PARAMETER_REF REFERENSI
#include <iostream>
using namespace std;
int kuadrat(int x);
void tukar(int &a, int &b) { int t = a; a = b; b = t; }
void isi(int *p) { *p = 7; }
int kuadrat(int x) { return x * x; }
int main() {
    int a, b;
    cin >> a >> b;
    int &alias = a;
    tukar(alias, b);
    isi(&b);
    cout << kuadrat(a) << " " << b << endl;
    return 0;
}
