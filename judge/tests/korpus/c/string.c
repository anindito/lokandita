// KONSEP: IO_DASAR VARIABEL_TIPE STRING
#include <stdio.h>
#include <string.h>
int main(void) {
    char nama[40];
    scanf("%39s", nama);
    printf("%s %d\n", nama, strlen(nama) > 0);
    return 0;
}
