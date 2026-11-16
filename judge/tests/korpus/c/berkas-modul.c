// KONSEP: IO_DASAR VARIABEL_TIPE PERCABANGAN POINTER FILE_IO MODUL
#include <stdio.h>
#include "alat.h"
int main(void) {
    FILE *f = fopen("/box/a.txt", "w");
    if (f == NULL) return 1;
    fprintf(f, "%d\n", 5);
    fclose(f);
    printf("selesai\n");
    return 0;
}
