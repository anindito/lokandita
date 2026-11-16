// KONSEP: IO_DASAR VARIABEL_TIPE
// &x pada scanf adalah cara membaca, bukan pelajaran pointer; "%d %d" bukan STRING atau FORMAT_OUTPUT.
#include <stdio.h>
int main(void) {
    int a, b;
    scanf("%d %d", &a, &b);
    printf("%d %d\n", a, b);
    return 0;
}
