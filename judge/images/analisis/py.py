"""Detektor konsep untuk Python di atas modul `ast` (SPEC.md §9).

Python tidak punya deklarasi tipe, jadi jenis sebuah nama diketahui hanya dari
penugasan sederhana di berkas yang sama (`s = "abc"`, `data = []`). Bila tidak
diketahui, indeks dan irisan dianggap operasi list. Lihat docs/DETEKSI-KONSEP.md.
"""

from __future__ import annotations

import ast

from . import Temuan

KODE = {
    "IO_DASAR", "VARIABEL_TIPE", "ARITMETIKA", "KONVERSI_TIPE", "FORMAT_OUTPUT", "STRING",
    "PERCABANGAN", "PERULANGAN", "NESTED_LOOP", "BREAK_CONTINUE",
    "PY_LIST", "PY_DICT_SET", "PY_COMPREHENSION", "FUNGSI", "REKURSI",
    "FILE_IO", "EXCEPTION", "MODUL",
    "CLASS_OBJECT", "CONSTRUCTOR_DESTRUCTOR", "ENCAPSULATION", "KOMPOSISI_AGREGASI", "INHERITANCE",
    "POLYMORPHISM", "ABSTRACT_CLASS", "INTERFACE", "OPERATOR_OVERLOAD",
    "PY_NUMPY", "PY_MATPLOTLIB", "PY_TKINTER",
}

KONVERSI = {"int", "float", "str", "bool", "complex"}
METODE_STRING = {"upper", "lower", "strip", "lstrip", "rstrip", "split", "rsplit", "splitlines", "join", "replace",
                 "find", "rfind", "startswith", "endswith", "isdigit", "isalpha", "isalnum", "isspace", "isupper",
                 "islower", "title", "capitalize", "swapcase", "zfill", "center", "ljust", "rjust", "encode"}
METODE_LIST = {"append", "extend", "insert", "pop", "remove", "sort", "reverse"}
METODE_DICT = {"items", "keys", "values", "get", "setdefault", "update", "add", "discard"}
PEMBUAT_LIST = {"list", "sorted"}
PEMBUAT_DICT = {"dict", "set", "tuple", "frozenset"}
# Modul yang impornya bukan pelajaran "modularisasi": math dan sys dipakai sejak awal.
IMPOR_BIASA = {"math", "sys", "abc"}
PUSTAKA = {"numpy": "PY_NUMPY", "matplotlib": "PY_MATPLOTLIB", "tkinter": "PY_TKINTER"}
OPERATOR_DUNDER = {
    "__add__", "__sub__", "__mul__", "__truediv__", "__floordiv__", "__mod__", "__pow__", "__neg__",
    "__radd__", "__rsub__", "__rmul__", "__iadd__", "__isub__", "__imul__",
    "__eq__", "__ne__", "__lt__", "__le__", "__gt__", "__ge__",
    "__getitem__", "__setitem__", "__len__", "__contains__", "__call__", "__iter__", "__next__", "__bool__",
}
ARITMETIKA = (ast.Add, ast.Sub, ast.Mult, ast.Div, ast.FloorDiv, ast.Mod, ast.Pow)


def nama_panggilan(n: ast.Call) -> str:
    return n.func.id if isinstance(n.func, ast.Name) else ""


def baca_masukan(n: ast.AST) -> bool:
    """Idiom membaca masukan, yang dihitung IO_DASAR dan bukan konversi atau operasi string:
    input(), int(input()), float(input()), input().split(), input().strip(), map(int, input().split())."""
    if not isinstance(n, ast.Call) or n.keywords and nama_panggilan(n) != "input":
        return False
    nama = nama_panggilan(n)
    if nama == "input":
        return True
    if nama in ("int", "float") and len(n.args) == 1:
        return baca_masukan(n.args[0])
    if nama == "map" and len(n.args) == 2 and isinstance(n.args[0], ast.Name) and n.args[0].id in ("int", "float", "str"):
        return baca_masukan(n.args[1])
    if isinstance(n.func, ast.Attribute) and n.func.attr in ("split", "strip", "rstrip") and len(n.args) <= 1:
        return baca_masukan(n.func.value)
    return False


class Analis(ast.NodeVisitor):
    def __init__(self, pohon: ast.Module, baris: list[str]):
        self.pohon = pohon
        self.baris = baris
        self.t = Temuan()
        self.jenis: dict[str, str] = {}          # nama -> 'str' | 'list' | 'dict'
        self.kelas: dict[str, ast.ClassDef] = {}
        self.fungsi: dict[str, ast.AST] = {}
        self.panggil: dict[str, set[str]] = {}
        self.dalam_loop = 0
        self.fungsi_kini: list[str] = []
        self.kelas_kini: list[ast.ClassDef] = []

    def catat(self, kode: str, n: ast.AST, konstruksi: str | None = None) -> None:
        baris = getattr(n, "lineno", 1)
        kolom = getattr(n, "col_offset", 0) + 1
        if konstruksi is None:
            konstruksi = self.baris[baris - 1].strip() if 0 < baris <= len(self.baris) else ""
        self.t.catat(kode, baris, kolom, konstruksi)

    def jalan(self) -> list[dict]:
        self.kumpulkan()
        self.visit(self.pohon)
        self.rekursi()
        return self.t.daftar()

    # ---- lintasan pertama ---------------------------------------------------------

    def jenis_nilai(self, n: ast.AST | None) -> str:
        if isinstance(n, ast.Constant) and isinstance(n.value, str) or isinstance(n, ast.JoinedStr):
            return "str"
        if isinstance(n, (ast.List, ast.ListComp)):
            return "list"
        if isinstance(n, (ast.Dict, ast.DictComp, ast.Set, ast.SetComp)):
            return "dict"
        if isinstance(n, ast.Call):
            nama = nama_panggilan(n)
            if nama in ("input", "str"):
                return "str"
            if nama in PEMBUAT_LIST:
                return "list"
            if nama in ("dict", "set"):
                return "dict"
            if isinstance(n.func, ast.Attribute):
                if n.func.attr in ("split", "rsplit", "splitlines"):
                    return "list"
                if n.func.attr in METODE_STRING:
                    return "str"
        return ""

    def kumpulkan(self) -> None:
        for n in ast.walk(self.pohon):
            if isinstance(n, ast.Assign) and len(n.targets) == 1 and isinstance(n.targets[0], ast.Name):
                jenis = self.jenis_nilai(n.value)
                if jenis:
                    self.jenis.setdefault(n.targets[0].id, jenis)
            elif isinstance(n, ast.ClassDef):
                self.kelas[n.name] = n
            elif isinstance(n, (ast.FunctionDef, ast.AsyncFunctionDef)):
                self.fungsi.setdefault(n.name, n)

    def rekursi(self) -> None:
        def mencapai(asal: str, tujuan: str, terlihat: set[str]) -> bool:
            for berikut in self.panggil.get(asal, ()):
                if berikut == tujuan:
                    return True
                if berikut not in terlihat:
                    terlihat.add(berikut)
                    if mencapai(berikut, tujuan, terlihat):
                        return True
            return False

        for nama in self.panggil:
            if nama in self.fungsi and mencapai(nama, nama, set()):
                self.catat("REKURSI", self.fungsi[nama], f"{nama}() memanggil dirinya sendiri")

    def string(self, n: ast.AST | None) -> bool:
        if isinstance(n, ast.Constant) and isinstance(n.value, str) or isinstance(n, ast.JoinedStr):
            return True
        return isinstance(n, ast.Name) and self.jenis.get(n.id) == "str"

    # ---- impor --------------------------------------------------------------------

    def _impor(self, n: ast.AST, modul: str) -> None:
        akar = modul.split(".")[0]
        if akar in PUSTAKA:
            self.catat(PUSTAKA[akar], n)
        elif akar not in IMPOR_BIASA:
            self.catat("MODUL", n)

    def visit_Import(self, n: ast.Import) -> None:
        for a in n.names:
            self._impor(n, a.name)

    def visit_ImportFrom(self, n: ast.ImportFrom) -> None:
        self._impor(n, n.module or ".")

    # ---- penugasan dan ekspresi ---------------------------------------------------

    def visit_Assign(self, n: ast.Assign) -> None:
        self.catat("VARIABEL_TIPE", n)
        if isinstance(n.value, ast.Tuple) and len(n.targets) == 1 and isinstance(n.targets[0], ast.Name):
            self.catat("PY_DICT_SET", n.value, "tuple")
        self._atribut_komposisi(n)
        self.generic_visit(n)

    def visit_AnnAssign(self, n: ast.AnnAssign) -> None:
        self.catat("VARIABEL_TIPE", n)
        self.generic_visit(n)

    def visit_AugAssign(self, n: ast.AugAssign) -> None:
        self.catat("VARIABEL_TIPE", n)
        if isinstance(n.op, ast.Add) and (self.string(n.target) or self.string(n.value)):
            self.catat("STRING", n)
        elif isinstance(n.op, ARITMETIKA):
            self.catat("ARITMETIKA", n)
        self.generic_visit(n)

    def visit_BinOp(self, n: ast.BinOp) -> None:
        if isinstance(n.op, ast.Mod) and self.string(n.left):
            self.catat("FORMAT_OUTPUT", n, "format dengan %")
        elif isinstance(n.op, (ast.Add, ast.Mult)) and (self.string(n.left) or self.string(n.right)):
            self.catat("STRING", n, "penyambungan atau pengulangan string")
        elif isinstance(n.op, ARITMETIKA):
            self.catat("ARITMETIKA", n)
        self.generic_visit(n)

    def visit_BoolOp(self, n: ast.BoolOp) -> None:
        self.catat("PERCABANGAN", n, "and" if isinstance(n.op, ast.And) else "or")
        self.generic_visit(n)

    def visit_UnaryOp(self, n: ast.UnaryOp) -> None:
        if isinstance(n.op, ast.Not):
            self.catat("PERCABANGAN", n, "not")
        self.generic_visit(n)

    def visit_IfExp(self, n: ast.IfExp) -> None:
        self.catat("PERCABANGAN", n, "… if … else …")
        self.generic_visit(n)

    def visit_JoinedStr(self, n: ast.JoinedStr, dicetak: bool = False) -> None:
        # f-string yang langsung dicetak hanyalah cara mencetak; di tempat lain ia membentuk string.
        if not dicetak:
            self.catat("STRING", n, "f-string")
        for bagian in n.values:
            if isinstance(bagian, ast.FormattedValue):
                if bagian.format_spec is not None:
                    self.catat("FORMAT_OUTPUT", n, "f-string dengan format")
                self.visit(bagian.value)

    def visit_Subscript(self, n: ast.Subscript) -> None:
        if self.string(n.value):
            self.catat("STRING", n, "indeks atau irisan string")
        elif isinstance(n.value, ast.Name) and self.jenis.get(n.value.id) == "dict":
            self.catat("PY_DICT_SET", n)
        else:
            self.catat("PY_LIST", n)
        self.generic_visit(n)

    def visit_List(self, n: ast.List) -> None:
        if isinstance(n.ctx, ast.Load):
            self.catat("PY_LIST", n, "list")
            self._tuple_di_dalam(n.elts)
        self.generic_visit(n)

    def visit_Dict(self, n: ast.Dict) -> None:
        self.catat("PY_DICT_SET", n, "dict")
        self._tuple_di_dalam(n.values)
        self.generic_visit(n)

    def visit_Set(self, n: ast.Set) -> None:
        self.catat("PY_DICT_SET", n, "set")
        self.generic_visit(n)

    def _tuple_di_dalam(self, elemen: list) -> None:
        for e in elemen:
            if isinstance(e, ast.Tuple):
                self.catat("PY_DICT_SET", e, "tuple")

    def _komprehensi(self, n: ast.AST) -> None:
        self.catat("PY_COMPREHENSION", n)
        self.generic_visit(n)

    visit_ListComp = visit_DictComp = visit_SetComp = visit_GeneratorExp = _komprehensi

    def visit_Lambda(self, n: ast.Lambda) -> None:
        self.catat("FUNGSI", n, "lambda")
        self.generic_visit(n)

    def visit_Call(self, n: ast.Call) -> None:
        if baca_masukan(n):
            self.catat("IO_DASAR", n, "input()")
            return  # isi idiom ini tidak dihitung konversi tipe atau operasi string
        nama = nama_panggilan(n)
        if self.fungsi_kini and nama:
            self.panggil.setdefault(self.fungsi_kini[-1], set()).add(nama)

        if nama == "print":
            self.catat("IO_DASAR", n, "print()")
            for a in n.args:
                if isinstance(a, ast.JoinedStr):
                    self.visit_JoinedStr(a, dicetak=True)
                else:
                    self.visit(a)
            for k in n.keywords:
                self.visit(k.value)
            return
        if nama in KONVERSI:
            self.catat("KONVERSI_TIPE", n, nama + "()")
        elif nama == "format":
            self.catat("FORMAT_OUTPUT", n, "format()")
        elif nama in PEMBUAT_LIST:
            self.catat("PY_LIST", n, nama + "()")
        elif nama in PEMBUAT_DICT:
            self.catat("PY_DICT_SET", n, nama + "()")
        elif nama == "open":
            self.catat("FILE_IO", n, "open()")

        if isinstance(n.func, ast.Attribute):
            metode, penerima = n.func.attr, n.func.value
            if metode == "format" and self.string(penerima):
                self.catat("FORMAT_OUTPUT", n, "str.format()")
            elif metode in METODE_STRING:
                self.catat("STRING", n, "." + metode + "()")
            elif metode in ("count", "index") and self.string(penerima):
                self.catat("STRING", n, "." + metode + "()")
            elif metode in METODE_LIST and not (isinstance(penerima, ast.Name) and self.jenis.get(penerima.id) == "dict"):
                self.catat("PY_LIST", n, "." + metode + "()")
            elif metode in METODE_DICT:
                self.catat("PY_DICT_SET", n, "." + metode + "()")
        self.generic_visit(n)

    # ---- alur ---------------------------------------------------------------------

    def visit_If(self, n: ast.If) -> None:
        uji = n.test
        utama = (isinstance(uji, ast.Compare) and isinstance(uji.left, ast.Name) and uji.left.id == "__name__")
        if utama:
            # `if __name__ == "__main__":` adalah idiom modul, bukan percabangan yang diajarkan.
            self.catat("MODUL", n, 'if __name__ == "__main__"')
            for anak in n.body + n.orelse:
                self.visit(anak)
            return
        self.catat("PERCABANGAN", n, "if")
        self.generic_visit(n)

    def visit_Match(self, n: ast.Match) -> None:
        self.catat("PERCABANGAN", n, "match")
        self.generic_visit(n)

    def _loop(self, n: ast.AST, kata: str) -> None:
        self.catat("PERULANGAN", n, kata)
        if self.dalam_loop:
            self.catat("NESTED_LOOP", n, kata + " di dalam perulangan lain")
        self.dalam_loop += 1
        self.generic_visit(n)
        self.dalam_loop -= 1

    def visit_For(self, n: ast.For) -> None:
        self._loop(n, "for")

    def visit_While(self, n: ast.While) -> None:
        self._loop(n, "while")

    def visit_Break(self, n: ast.Break) -> None:
        self.catat("BREAK_CONTINUE", n, "break")

    def visit_Continue(self, n: ast.Continue) -> None:
        self.catat("BREAK_CONTINUE", n, "continue")

    def visit_Try(self, n: ast.Try) -> None:
        self.catat("EXCEPTION", n, "try")
        self.generic_visit(n)

    visit_TryStar = visit_Try

    def visit_Raise(self, n: ast.Raise) -> None:
        self.catat("EXCEPTION", n, "raise")
        self.generic_visit(n)

    # ---- fungsi dan kelas ---------------------------------------------------------

    def visit_FunctionDef(self, n: ast.FunctionDef) -> None:
        dalam_kelas = bool(self.kelas_kini) and n in self.kelas_kini[-1].body
        if dalam_kelas:
            self._metode(n)
        else:
            self.catat("FUNGSI", n, f"def {n.name}(...)")
        luar, self.dalam_loop = self.dalam_loop, 0
        self.fungsi_kini.append(n.name)
        self.generic_visit(n)
        self.fungsi_kini.pop()
        self.dalam_loop = luar

    visit_AsyncFunctionDef = visit_FunctionDef

    @staticmethod
    def _dekorator(n: ast.FunctionDef) -> set[str]:
        nama = set()
        for d in n.decorator_list:
            d = d.func if isinstance(d, ast.Call) else d
            nama.add(d.attr if isinstance(d, ast.Attribute) else getattr(d, "id", ""))
        return nama

    def _metode(self, n: ast.FunctionDef) -> None:
        kelas = self.kelas_kini[-1]
        if n.name in ("__init__", "__del__"):
            self.catat("CONSTRUCTOR_DESTRUCTOR", n, f"def {n.name}(...)")
        elif n.name in OPERATOR_DUNDER:
            self.catat("OPERATOR_OVERLOAD", n, f"def {n.name}(...)")
        dekorator = self._dekorator(n)
        if "abstractmethod" in dekorator:
            self.catat("ABSTRACT_CLASS", n, "@abstractmethod")
        if dekorator & {"property", "setter", "getter"}:
            self.catat("ENCAPSULATION", n, "@property")
        # Metode dengan nama yang sama di kelas induk (di berkas ini) = override.
        if not n.name.startswith("__"):
            for induk in self._induk(kelas):
                if any(isinstance(m, ast.FunctionDef) and m.name == n.name for m in induk.body):
                    self.catat("POLYMORPHISM", n, f"{kelas.name}.{n.name}() menimpa {induk.name}.{n.name}()")

    def _induk(self, kelas: ast.ClassDef, terlihat: set[str] | None = None) -> list[ast.ClassDef]:
        terlihat = terlihat or {kelas.name}
        hasil = []
        for b in kelas.bases:
            nama = b.id if isinstance(b, ast.Name) else ""
            if nama in self.kelas and nama not in terlihat:
                terlihat.add(nama)
                hasil.append(self.kelas[nama])
                hasil.extend(self._induk(self.kelas[nama], terlihat))
        return hasil

    def visit_ClassDef(self, n: ast.ClassDef) -> None:
        self.catat("CLASS_OBJECT", n, f"class {n.name}")
        induk = [b.attr if isinstance(b, ast.Attribute) else getattr(b, "id", "") for b in n.bases]
        if any(b not in ("object", "ABC") for b in induk):
            self.catat("INHERITANCE", n, f"class {n.name}({', '.join(induk)})")
        abstrak = "ABC" in induk or any(k.arg == "metaclass" for k in n.keywords)
        if abstrak:
            self.catat("ABSTRACT_CLASS", n, f"class {n.name}(ABC)")
        metode = [m for m in n.body if isinstance(m, ast.FunctionDef)]
        if metode and all("abstractmethod" in self._dekorator(m) for m in metode) \
                and not any(isinstance(x, (ast.Assign, ast.AnnAssign)) for x in n.body):
            self.catat("INTERFACE", n, f"class {n.name}: semua metodenya abstrak")
        self.kelas_kini.append(n)
        self.generic_visit(n)
        self.kelas_kini.pop()

    def visit_Attribute(self, n: ast.Attribute) -> None:
        # self._nama atau self.__nama: atribut yang disembunyikan dari luar kelas.
        if self.kelas_kini and isinstance(n.value, ast.Name) and n.value.id == "self" \
                and n.attr.startswith("_") and not n.attr.endswith("__"):
            self.catat("ENCAPSULATION", n, "self." + n.attr)
        self.generic_visit(n)

    def _atribut_komposisi(self, n: ast.Assign) -> None:
        """self.x = KelasLain(...) atau self.x = parameter beranotasi KelasLain, di dalam sebuah kelas."""
        if not self.kelas_kini:
            return
        kelas = self.kelas_kini[-1]
        ke_self = any(isinstance(t, ast.Attribute) and isinstance(t.value, ast.Name) and t.value.id == "self" for t in n.targets)
        if not ke_self:
            return
        lain = ""
        if isinstance(n.value, ast.Call) and nama_panggilan(n.value) in self.kelas:
            lain = nama_panggilan(n.value)
        elif isinstance(n.value, ast.Name) and self.fungsi_kini:
            fungsi = next((m for m in kelas.body if isinstance(m, ast.FunctionDef) and m.name == self.fungsi_kini[-1]), None)
            for a in (fungsi.args.args if fungsi else []):
                if a.arg == n.value.id and a.annotation is not None:
                    disebut = {x.id for x in ast.walk(a.annotation) if isinstance(x, ast.Name)}
                    lain = next(iter(sorted(disebut & set(self.kelas))), "")
        if lain and lain != kelas.name:
            self.catat("KOMPOSISI_AGREGASI", n, f"{kelas.name} memuat {lain}")


def analisis(sumber: bytes) -> list[dict]:
    try:
        teks = sumber.decode("utf-8")
        pohon = ast.parse(teks)
    except (SyntaxError, ValueError, UnicodeDecodeError, RecursionError) as e:
        raise ValueError(f"kode Python tidak bisa diurai: {type(e).__name__}") from e
    return Analis(pohon, teks.splitlines()).jalan()
