// KONSEP: IO_DASAR VARIABEL_TIPE STRING
#include <iostream>
#include <string>
using namespace std;
int main() {
    string nama;
    getline(cin, nama);
    cout << "Halo, " << nama << "!" << endl;
    cout << nama.length() << " " << nama[0] << " " << nama.substr(1) << endl;
    string salam = "Halo " + nama;
    cout << salam << endl;
    return 0;
}
