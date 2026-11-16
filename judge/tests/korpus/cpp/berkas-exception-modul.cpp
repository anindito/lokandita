// KONSEP: IO_DASAR VARIABEL_TIPE PERCABANGAN FILE_IO EXCEPTION MODUL STRING
#include <iostream>
#include <fstream>
#include <stdexcept>
#include "utilitas.h"
namespace alat { }
int main() {
    std::ofstream keluar("/box/data.txt");
    keluar << 42;
    keluar.close();
    std::ifstream masuk("/box/data.txt");
    int n;
    masuk >> n;
    try {
        if (n < 0) throw std::runtime_error("negatif");
    } catch (const std::exception &e) {
        std::cout << e.what();
    }
    std::string s;
    std::cout << n << std::endl;
    return 0;
}
