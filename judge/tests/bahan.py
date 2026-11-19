"""Sumber program yang dipakai lebih dari satu berkas tes."""


def kompilasi_berat(n: int) -> str:
    """n fungsi inline yang digabung ke main: gcc -O2 butuh jauh lebih dari 10 detik CPU."""
    sumber = "".join(
        f"static inline unsigned f{i}(unsigned x){{for(int i=0;i<64;i++){{x=x*1664525u+1013904223u+{i}u;"
        f"if(x&1)x^=x>>3;else x+=x<<2;}}return x;}}\n" for i in range(n))
    return sumber + "int main(void){unsigned x=1;\n" + "".join(f"x=f{i}(x);" for i in range(n)) + "return x&1;}\n"


# Uji terima SPEC.md §10 (langsung ke kotak pasir; di atas batas 256 KB sebuah kiriman).
KOMPILASI_BERAT = kompilasi_berat(3000)
# Program yang sama untuk jalur kiriman mahasiswa, yang dibatasi 256 KB: ditulis dengan
# makro, sehingga sesudah praproses kompiler mendapat 3000 fungsi yang sama (sekitar 40 KB).
KOMPILASI_BERAT_KIRIMAN = (
    "#define F(i) static inline unsigned f##i(unsigned x){for(int j=0;j<64;j++){x=x*1664525u+1013904223u+i##u;"
    "if(x&1)x^=x>>3;else x+=x<<2;}return x;}\n#define C(i) x=f##i(x);\n"
    + "".join(f"F({i})\n" for i in range(3000))
    + "int main(void){unsigned x=1;\n" + "".join(f"C({i})" for i in range(3000)) + "\nreturn x&1;}\n")
