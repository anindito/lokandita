// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN
#include <iostream>
using namespace std;
int main() {
    int n;
    cin >> n;
    if (n % 2 == 0 && n > 0) {
        cout << "genap" << endl;
    } else {
        cout << "ganjil" << endl;
    }
    switch (n) {
        case 1: cout << "satu"; break;
        default: cout << "lain"; break;
    }
    cout << (n > 5 ? "besar" : "kecil") << endl;
    return 0;
}
