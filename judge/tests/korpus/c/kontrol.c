// KONSEP: IO_DASAR VARIABEL_TIPE ARITMETIKA PERCABANGAN PERULANGAN NESTED_LOOP BREAK_CONTINUE
#include <stdio.h>
int main(void) {
    int n;
    scanf("%d", &n);
    for (int i = 0; i < n; i++) {
        int j = 0;
        while (j < n) {
            if (j > i) break;
            j++;
        }
        if (i == 3) continue;
        printf("%d\n", i);
    }
    return 0;
}
