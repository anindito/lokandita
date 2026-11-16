// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN FUNGSI PARAMETER_REF REKURSI
#include <stdio.h>
void tukar(int *a, int *b) { int t = *a; *a = *b; *b = t; }
int fib(int n) { return n < 2 ? n : fib(n - 1) + fib(n - 2); }
int main(void) {
    int a, b;
    scanf("%d %d", &a, &b);
    tukar(&a, &b);
    printf("%d %d\n", fib(a), b);
    return 0;
}
