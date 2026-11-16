// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERULANGAN ARRAY_1D ARRAY_2D POINTER MEMORI_DINAMIS STRUCT
#include <stdio.h>
#include <stdlib.h>
struct Titik { int x, y; };
typedef struct Titik Titik;
int main(void) {
    int n, m[2][2];
    scanf("%d", &n);
    int *a = malloc(n * sizeof(int));
    for (int i = 0; i < n; i++) scanf("%d", &a[i]);
    Titik t;
    t.x = a[0];
    m[0][0] = t.x;
    int *p = &m[0][0];
    printf("%d\n", *p);
    free(a);
    return 0;
}
