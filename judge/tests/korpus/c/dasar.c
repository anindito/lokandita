// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA KONVERSI_TIPE FORMAT_OUTPUT
#include <stdio.h>
int main(void) {
    int a, b;
    scanf("%d %d", &a, &b);
    double rata = (double) (a + b) / 2;
    printf("%.2f\n", rata);
    return 0;
}
