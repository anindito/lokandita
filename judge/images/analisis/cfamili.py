"""Detektor konsep untuk C dan C++ di atas pohon sintaks tree-sitter.

Tanpa informasi tipe dari kompiler: tipe sebuah variabel diketahui hanya dari
deklarasinya di berkas yang sama. Itu cukup untuk aturan SPEC.md §9.1, dan
keterbatasannya ditulis di docs/DETEKSI-KONSEP.md.
"""

from __future__ import annotations

import re

import tree_sitter_c
import tree_sitter_cpp
from tree_sitter import Language, Node, Parser

from . import Temuan

KODE_C = {
    "IO_DASAR", "VARIABEL_TIPE", "ARITMETIKA", "KONVERSI_TIPE", "FORMAT_OUTPUT", "STRING",
    "PERCABANGAN", "PERULANGAN", "NESTED_LOOP", "BREAK_CONTINUE",
    "ARRAY_1D", "ARRAY_2D", "POINTER", "MEMORI_DINAMIS", "STRUCT",
    "FUNGSI", "PARAMETER_REF", "REKURSI", "FILE_IO", "MODUL",
}
KODE_CPP = KODE_C | {
    "REFERENSI", "OVERLOAD_FUNGSI", "EXCEPTION", "MODERN_CPP", "CLASS_OBJECT", "CONSTRUCTOR_DESTRUCTOR",
    "COPY_SEMANTICS", "ENCAPSULATION", "KOMPOSISI_AGREGASI", "INHERITANCE", "POLYMORPHISM", "ABSTRACT_CLASS",
    "INTERFACE", "OPERATOR_OVERLOAD", "TEMPLATE_FUNGSI", "TEMPLATE_CLASS", "STL_CONTAINER", "STL_ALGORITMA",
    "SMART_POINTER",
}

TIPE_STRING = {"string", "wstring", "string_view"}
KONTAINER = {"vector", "map", "set", "list", "deque", "queue", "stack", "priority_queue", "unordered_map",
             "unordered_set", "multimap", "multiset", "pair", "tuple", "array", "forward_list", "bitset"}
POINTER_PINTAR = {"unique_ptr", "shared_ptr", "weak_ptr"}
TIPE_BERKAS = {"ifstream", "ofstream", "fstream", "FILE"}

IO = {"printf", "scanf", "puts", "putchar", "getchar", "cin", "cout", "cerr", "clog"}
BACA_C = {"scanf", "fscanf", "sscanf"}  # & pada argumennya adalah cara membaca, bukan konsep pointer
FUNGSI_STRING = {"getline", "strlen", "strcpy", "strncpy", "strcat", "strncat", "strcmp", "strncmp", "strstr", "strchr",
                 "strrchr", "strtok", "gets", "fgets", "sprintf", "snprintf", "sscanf", "to_string", "stoi", "stol",
                 "stoll", "stof", "stod", "atoi", "atof"}
METODE_STRING = {"length", "size", "substr", "find", "rfind", "append", "erase", "insert", "replace", "compare",
                 "c_str", "empty", "at", "push_back", "pop_back", "back", "front", "clear"}
MANIPULATOR_PANGGIL = {"setw", "setprecision", "setfill"}
MANIPULATOR = {"fixed", "scientific", "left", "right", "showpoint", "showpos", "boolalpha", "hex", "oct"}
MEMORI = {"malloc", "calloc", "realloc", "free"}
BERKAS = {"fopen", "fclose", "freopen", "fscanf", "fread", "fwrite"}
ALGORITMA = {"sort", "stable_sort", "find", "find_if", "count", "count_if", "accumulate", "reverse", "max_element",
             "min_element", "binary_search", "lower_bound", "upper_bound", "unique", "for_each", "transform", "fill",
             "copy", "next_permutation", "prev_permutation", "any_of", "all_of", "none_of", "iota", "partial_sum"}
CAST = {"static_cast", "dynamic_cast", "const_cast", "reinterpret_cast"}
ARITMETIKA = {"+", "-", "*", "/", "%"}
ISI_ARITMETIKA = {"+=", "-=", "*=", "/=", "%="}
FORMAT_PRINTF = re.compile(r"%[-+ 0#]*(?:\d+(?:\.\d+)?|\.\d+)[a-zA-Z]")

_PARSER: dict[str, Parser] = {}


def _parser(bahasa: str) -> Parser:
    if bahasa not in _PARSER:
        modul = tree_sitter_cpp if bahasa == "cpp" else tree_sitter_c
        _PARSER[bahasa] = Parser(Language(modul.language()))
    return _PARSER[bahasa]


def teks(n: Node | None) -> str:
    return n.text.decode("utf-8", "replace") if n is not None and n.text is not None else ""


def punya_token(n: Node, token: str) -> bool:
    return any(c.type == token for c in n.children)


def turunan(n: Node, tipe: str | tuple[str, ...]):
    """Semua simpul bertipe itu di bawah n (n sendiri tidak termasuk)."""
    tipe = (tipe,) if isinstance(tipe, str) else tipe
    tumpuk = list(n.children)
    while tumpuk:
        c = tumpuk.pop()
        if c.type in tipe:
            yield c
        tumpuk.extend(c.children)


def nama_akhir(n: Node | None) -> str:
    """Nama sederhana dari identifier, std::nama, obj.nama, atau nama<...>."""
    while n is not None:
        if n.type in ("identifier", "field_identifier", "type_identifier", "namespace_identifier"):
            return teks(n)
        if n.type in ("qualified_identifier", "template_function", "template_type", "template_method"):
            n = n.child_by_field_name("name")
        elif n.type == "field_expression":
            n = n.child_by_field_name("field")
        else:
            return ""
    return ""


def nama_deklarator(n: Node | None) -> Node | None:
    """Simpul nama di dalam deklarator (menembus pointer, referensi, array, fungsi, inisialisasi)."""
    while n is not None and n.type not in ("identifier", "field_identifier", "type_identifier", "qualified_identifier",
                                           "operator_name", "destructor_name", "template_function"):
        berikut = n.child_by_field_name("declarator")
        if berikut is None:
            berikut = next((c for c in n.named_children if c.type.endswith("declarator")
                            or c.type in ("identifier", "field_identifier")), None)
        n = berikut
    return n


def deklarator_fungsi(n: Node | None) -> Node | None:
    """function_declarator di dalam deklarator sebuah definisi fungsi, bila ada."""
    while n is not None and n.type != "function_declarator":
        if n.type not in ("pointer_declarator", "reference_declarator", "parenthesized_declarator"):
            return None
        n = n.child_by_field_name("declarator") or next((c for c in n.named_children), None)
    return n


class Analis:
    def __init__(self, bahasa: str, akar: Node):
        self.bahasa = bahasa
        self.akar = akar
        self.t = Temuan()
        self.var_string: set[str] = set()
        self.kelas: set[str] = set()
        self.fungsi_bebas: dict[str, int] = {}      # nama -> jumlah definisi
        self.param_pointer: dict[str, bool] = {}    # nama fungsi -> punya parameter pointer
        self.panggil: dict[str, set[str]] = {}      # pemanggil -> yang dipanggil (untuk rekursi)
        self.simpul_fungsi: dict[str, Node] = {}

    def catat(self, kode: str, n: Node, konstruksi: str | None = None) -> None:
        if kode not in (KODE_CPP if self.bahasa == "cpp" else KODE_C):
            return
        if konstruksi is None:
            konstruksi = teks(n).split("\n", 1)[0].strip()
        self.t.catat(kode, n.start_point[0] + 1, n.start_point[1] + 1, konstruksi)

    # ---- lintasan pertama: nama-nama yang perlu diketahui lebih dulu --------------

    def kumpulkan(self) -> None:
        for n in turunan(self.akar, ("declaration", "field_declaration", "parameter_declaration",
                                     "optional_parameter_declaration")):
            tipe = n.child_by_field_name("type")
            nama_tipe = nama_akhir(tipe) if tipe is not None and tipe.type != "primitive_type" else teks(tipe)
            for c in n.children_by_field_name("declarator"):
                inti = c.child_by_field_name("declarator") if c.type == "init_declarator" else c
                nama = teks(nama_deklarator(inti))
                if not nama:
                    continue
                if nama_tipe in TIPE_STRING:
                    self.var_string.add(nama)
                elif nama_tipe == "char" and inti is not None and inti.type in ("array_declarator", "pointer_declarator"):
                    self.var_string.add(nama)
        for n in turunan(self.akar, ("class_specifier", "struct_specifier")):
            if n.child_by_field_name("body") is not None and n.child_by_field_name("name") is not None:
                self.kelas.add(nama_akhir(n.child_by_field_name("name")))
        for n in turunan(self.akar, "function_definition"):
            fd = deklarator_fungsi(n.child_by_field_name("declarator"))
            if fd is None:
                continue
            simpul_nama = fd.child_by_field_name("declarator")
            nama = nama_akhir(simpul_nama) or teks(simpul_nama)
            self.simpul_fungsi.setdefault(nama, n)
            if self.metode(n) or nama == "main":
                continue
            self.fungsi_bebas[nama] = self.fungsi_bebas.get(nama, 0) + 1
            parameter = fd.child_by_field_name("parameters")
            if parameter is not None and any(True for _ in turunan(parameter, "pointer_declarator")):
                self.param_pointer[nama] = True

    @staticmethod
    def metode(n: Node) -> bool:
        """Definisi fungsi ini adalah metode: di dalam badan kelas, atau ditulis Kelas::nama di luar."""
        fd = deklarator_fungsi(n.child_by_field_name("declarator"))
        if fd is not None and fd.child_by_field_name("declarator") is not None \
                and fd.child_by_field_name("declarator").type == "qualified_identifier":
            return True
        p = n.parent
        while p is not None:
            if p.type == "field_declaration_list":
                return True
            if p.type in ("function_definition", "translation_unit", "namespace_definition"):
                return False
            p = p.parent
        return False

    # ---- lintasan utama -----------------------------------------------------------

    def jalan(self) -> list[dict]:
        self.kumpulkan()
        self.kunjungi(self.akar, [], None, set())
        for nama, jumlah in self.fungsi_bebas.items():
            if jumlah > 1:
                self.catat("OVERLOAD_FUNGSI", self.simpul_fungsi[nama], f"{nama}(...) didefinisikan {jumlah} kali")
        self.rekursi()
        return self.t.daftar()

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
            if nama in self.simpul_fungsi and mencapai(nama, nama, set()):
                self.catat("REKURSI", self.simpul_fungsi[nama], f"{nama}() memanggil dirinya sendiri")

    def kunjungi(self, n: Node, lingkar: list[str], fungsi: str | None, ptr_param: set[str]) -> None:
        """lingkar: tumpukan 'loop'/'switch' yang melingkupi; fungsi: nama fungsi yang sedang dimasuki;
        ptr_param: nama parameter pointer fungsi itu."""
        tipe = n.type
        tangani = getattr(self, "n_" + tipe, None)
        if tangani is not None:
            hasil = tangani(n, lingkar, fungsi, ptr_param)
            if hasil is False:  # penangan sudah mengunjungi anak-anaknya sendiri
                return
        for c in n.children:
            self.kunjungi(c, lingkar, fungsi, ptr_param)

    # -- praprosesor, ruang nama, tipe ---------------------------------------------

    def n_preproc_include(self, n, *_):
        jalur = n.child_by_field_name("path")
        if jalur is not None and jalur.type == "string_literal":
            self.catat("MODUL", n, "#include " + teks(jalur))
        return False

    def n_namespace_definition(self, n, *_):
        self.catat("MODUL", n, "namespace " + teks(n.child_by_field_name("name")))

    def n_type_identifier(self, n, *_):
        nama = teks(n)
        if nama in TIPE_STRING:
            self.catat("STRING", n, nama)
        elif nama in KONTAINER:
            self.catat("STL_CONTAINER", n, nama)
        elif nama in POINTER_PINTAR:
            self.catat("SMART_POINTER", n, nama)
        elif nama in TIPE_BERKAS:
            self.catat("FILE_IO", n, nama)

    def n_placeholder_type_specifier(self, n, *_):
        self.catat("MODERN_CPP", n, "auto")

    def n_null(self, n, *_):
        if teks(n) == "nullptr":
            self.catat("MODERN_CPP", n, "nullptr")

    def n_type_definition(self, n, *_):
        self.catat("STRUCT", n, "typedef")

    def n_alias_declaration(self, n, *_):
        self.catat("STRUCT", n, "using … =")

    def n_union_specifier(self, n, *_):
        if n.child_by_field_name("body") is not None:
            self.catat("STRUCT", n, "union")

    def n_enum_specifier(self, n, *_):
        if n.child_by_field_name("body") is not None:
            self.catat("STRUCT", n, "enum")

    # -- deklarasi ------------------------------------------------------------------

    def n_declaration(self, n, *_):
        tipe = n.child_by_field_name("type")
        deklarator = n.children_by_field_name("declarator")
        if any(deklarator_fungsi(d) is not None for d in deklarator):
            return  # prototipe fungsi, bukan variabel
        self.catat("VARIABEL_TIPE", n)
        if any(c.type == "storage_class_specifier" and teks(c) == "extern" for c in n.children):
            self.catat("MODUL", n, "extern")
        for d in deklarator:
            inti = d
            if d.type == "init_declarator":
                inti = d.child_by_field_name("declarator")
                nilai = d.child_by_field_name("value")
                if nilai is not None and nilai.type == "initializer_list" and not punya_token(d, "="):
                    self.catat("MODERN_CPP", d, teks(d))
            self.bentuk_deklarator(inti, teks(tipe) == "char")

    def n_field_declaration(self, n, *_):
        tipe = n.child_by_field_name("type")
        for d in n.children_by_field_name("declarator"):
            if deklarator_fungsi(d) is None:
                self.bentuk_deklarator(d, teks(tipe) == "char")

    def bentuk_deklarator(self, d: Node | None, char: bool) -> None:
        """Array, pointer dan referensi pada satu nama yang dideklarasikan."""
        kedalaman_array = 0
        awal = d
        while d is not None:
            if d.type == "array_declarator":
                kedalaman_array += 1
            elif d.type == "pointer_declarator":
                self.catat("POINTER", awal, teks(awal))
                if char:
                    self.catat("STRING", awal, "char " + teks(awal))
            elif d.type == "reference_declarator":
                self.catat("REFERENSI", awal, teks(awal))
            elif d.type != "parenthesized_declarator":
                break
            d = d.child_by_field_name("declarator") or next(iter(d.named_children), None)
        if kedalaman_array:
            if char:
                # char nama[20] adalah string (SPEC.md §9.1), bukan pelajaran array.
                self.catat("STRING", awal, "char " + teks(awal))
                kedalaman_array -= 1
            if kedalaman_array == 1:
                self.catat("ARRAY_1D", awal, teks(awal))
            elif kedalaman_array > 1:
                self.catat("ARRAY_2D", awal, teks(awal))

    # -- fungsi ---------------------------------------------------------------------

    def n_function_definition(self, n, lingkar, fungsi, ptr_param):
        fd = deklarator_fungsi(n.child_by_field_name("declarator"))
        if fd is None:
            return None
        simpul_nama = fd.child_by_field_name("declarator")
        nama = nama_akhir(simpul_nama) or teks(simpul_nama)
        metode = self.metode(n)
        if simpul_nama is not None and simpul_nama.type == "operator_name":
            if teks(simpul_nama).replace(" ", "") != "operator=":
                self.catat("OPERATOR_OVERLOAD", simpul_nama)
        elif not metode and nama != "main":
            self.catat("FUNGSI", n, f"{teks(n.child_by_field_name('type'))} {nama}(...)".strip())

        pointer_baru: set[str] = set()
        parameter = fd.child_by_field_name("parameters")
        if parameter is not None and nama != "main":
            for p in parameter.named_children:
                if p.type not in ("parameter_declaration", "optional_parameter_declaration"):
                    continue
                if p.type == "optional_parameter_declaration":
                    self.catat("OVERLOAD_FUNGSI", p, "parameter bawaan: " + teks(p))
                d = p.child_by_field_name("declarator")
                bentuk = d
                while bentuk is not None and bentuk.type in ("pointer_declarator", "reference_declarator",
                                                             "array_declarator", "abstract_pointer_declarator",
                                                             "abstract_reference_declarator"):
                    if bentuk.type.endswith("pointer_declarator"):
                        self.catat("PARAMETER_REF", p, teks(p))
                        pointer_baru.add(teks(nama_deklarator(d)))
                    elif bentuk.type.endswith("reference_declarator"):
                        # const T& dan T&& pada konstruktor salin/pindah dicatat sebagai COPY_SEMANTICS di kelasnya.
                        self.catat("PARAMETER_REF", p, teks(p))
                    else:
                        self.catat("ARRAY_1D", p, teks(p))
                    bentuk = bentuk.child_by_field_name("declarator") or next(iter(bentuk.named_children), None)
        # Tipe pada parameter (string, vector, …) tetap dikunjungi; bentuk deklaratornya sudah ditangani.
        for c in n.children:
            if c.type == "compound_statement" or c.type == "field_initializer_list" or c.type == "try_statement":
                self.kunjungi(c, [], nama, pointer_baru)
            elif c is n.child_by_field_name("declarator"):
                for t in turunan(c, ("type_identifier", "placeholder_type_specifier", "optional_parameter_declaration")):
                    if t.type == "optional_parameter_declaration":
                        nilai = t.child_by_field_name("default_value")
                        if nilai is not None:
                            self.kunjungi(nilai, [], nama, set())
                    else:
                        self.kunjungi(t, [], nama, set())
            else:
                self.kunjungi(c, [], nama, set())
        return False

    def n_lambda_expression(self, n, *_):
        self.catat("FUNGSI", n, "lambda")
        self.catat("MODERN_CPP", n, "lambda")

    def n_template_declaration(self, n, *_):
        for c in n.named_children:
            if c.type in ("class_specifier", "struct_specifier"):
                self.catat("TEMPLATE_CLASS", n, "template class " + nama_akhir(c.child_by_field_name("name")))
            elif c.type in ("function_definition", "declaration"):
                fd = deklarator_fungsi(c.child_by_field_name("declarator"))
                if fd is not None:
                    self.catat("TEMPLATE_FUNGSI", n, "template " + nama_akhir(fd.child_by_field_name("declarator")) + "(...)")

    # -- kelas ----------------------------------------------------------------------

    def n_struct_specifier(self, n, *a):
        if n.child_by_field_name("body") is not None:
            self.catat("STRUCT", n, "struct " + nama_akhir(n.child_by_field_name("name")))
            self.kelas_isi(n, "struct")

    def n_class_specifier(self, n, *a):
        if n.child_by_field_name("body") is not None:
            self.catat("CLASS_OBJECT", n, "class " + nama_akhir(n.child_by_field_name("name")))
            self.kelas_isi(n, "class")

    def kelas_isi(self, n: Node, kata: str) -> None:
        nama = nama_akhir(n.child_by_field_name("name"))
        badan = n.child_by_field_name("body")
        seperti_kelas = kata == "class"
        if any(c.type == "base_class_clause" for c in n.children):
            self.catat("INHERITANCE", n, f"{kata} {nama} : …")
            seperti_kelas = True
        akses = "private" if kata == "class" else "public"
        data, metode, murni = 0, 0, 0
        for c in badan.named_children:
            if c.type == "access_specifier":
                akses = teks(c).rstrip(":").strip()
                seperti_kelas = True
                if akses in ("private", "protected"):
                    self.catat("ENCAPSULATION", c, akses + ":")
                continue
            if c.type == "template_declaration":
                c = next((x for x in c.named_children if x.type in ("function_definition", "declaration", "field_declaration")), c)
            if c.type not in ("function_definition", "field_declaration", "declaration"):
                continue
            fd = None
            for d in c.children_by_field_name("declarator"):
                fd = fd or deklarator_fungsi(d)
            if fd is None:
                if c.type == "field_declaration":
                    data += 1
                    if akses != "public":
                        self.catat("ENCAPSULATION", c, f"anggota {akses}: " + teks(c))
                    tipe = c.child_by_field_name("type")
                    dipakai = {teks(t) for t in turunan(c, "type_identifier")} | ({teks(tipe)} if tipe is not None else set())
                    lain = sorted((dipakai & self.kelas) - {nama})
                    if lain:
                        self.catat("KOMPOSISI_AGREGASI", c, f"{nama} memuat {lain[0]}: " + teks(c))
                continue

            seperti_kelas = True
            simpul_nama = fd.child_by_field_name("declarator")
            nama_fungsi = teks(simpul_nama)
            destruktor = simpul_nama is not None and simpul_nama.type == "destructor_name"
            if destruktor or (nama_fungsi == nama and c.child_by_field_name("type") is None):
                self.catat("CONSTRUCTOR_DESTRUCTOR", c, ("~" if destruktor else "") + nama + "(...)")
                parameter = [p for p in fd.child_by_field_name("parameters").named_children if p.type == "parameter_declaration"]
                if not destruktor and len(parameter) == 1 and nama_akhir(parameter[0].child_by_field_name("type")) == nama \
                        and any(True for _ in turunan(parameter[0], ("reference_declarator", "abstract_reference_declarator"))):
                    self.catat("COPY_SEMANTICS", c, teks(fd))
            elif simpul_nama is not None and simpul_nama.type == "operator_name":
                if nama_fungsi.replace(" ", "") == "operator=":
                    self.catat("COPY_SEMANTICS", c, "operator=")
                elif c.type != "function_definition":
                    self.catat("OPERATOR_OVERLOAD", simpul_nama)

            virtual = punya_token(c, "virtual")
            if virtual or any(True for _ in turunan(fd, "virtual_specifier")):
                self.catat("POLYMORPHISM", c, teks(c).split("{", 1)[0].strip())
            nilai = c.child_by_field_name("default_value")
            if virtual and nilai is not None and teks(nilai) == "0":
                murni += 1
                self.catat("ABSTRACT_CLASS", c, teks(c))
            elif not destruktor:
                metode += 1
        if seperti_kelas and kata == "struct":
            self.catat("CLASS_OBJECT", n, "struct " + nama + " dengan anggota fungsi atau hak akses")
        if murni > 0 and metode == 0 and data == 0:
            self.catat("INTERFACE", n, f"{kata} {nama}: semua metodenya virtual murni")

    # -- pernyataan -----------------------------------------------------------------

    def n_if_statement(self, n, *_):
        self.catat("PERCABANGAN", n, "if")

    def n_conditional_expression(self, n, *_):
        self.catat("PERCABANGAN", n, "? :")

    def n_switch_statement(self, n, lingkar, fungsi, ptr_param):
        self.catat("PERCABANGAN", n, "switch")
        for c in n.children:
            self.kunjungi(c, lingkar + ["switch"], fungsi, ptr_param)
        return False

    def _loop(self, n, lingkar, fungsi, ptr_param, kata):
        self.catat("PERULANGAN", n, kata)
        if "loop" in lingkar:
            self.catat("NESTED_LOOP", n, kata + " di dalam perulangan lain")
        for c in n.children:
            self.kunjungi(c, lingkar + ["loop"], fungsi, ptr_param)
        return False

    def n_for_statement(self, n, *a):
        return self._loop(n, *a, "for")

    def n_while_statement(self, n, *a):
        return self._loop(n, *a, "while")

    def n_do_statement(self, n, *a):
        return self._loop(n, *a, "do … while")

    def n_for_range_loop(self, n, *a):
        self.catat("MODERN_CPP", n, "for (… : …)")
        return self._loop(n, *a, "for (… : …)")

    def n_break_statement(self, n, lingkar, *_):
        # break yang menutup case adalah bagian dari switch (PERCABANGAN).
        if lingkar and lingkar[-1] == "loop":
            self.catat("BREAK_CONTINUE", n, "break")

    def n_continue_statement(self, n, *_):
        self.catat("BREAK_CONTINUE", n, "continue")

    def n_try_statement(self, n, *_):
        self.catat("EXCEPTION", n, "try")

    def n_throw_statement(self, n, *_):
        self.catat("EXCEPTION", n, "throw")

    def n_catch_clause(self, n, lingkar, fungsi, ptr_param):
        # Parameter catch (misalnya const exception& e) bukan variabel referensi yang diajarkan.
        for c in n.children:
            if c.type != "parameter_list":
                self.kunjungi(c, lingkar, fungsi, ptr_param)
        return False

    # -- ekspresi -------------------------------------------------------------------

    def string(self, n: Node | None) -> bool:
        """Operand ini jelas sebuah string: literal string atau variabel yang dideklarasikan sebagai string."""
        if n is None:
            return False
        if n.type in ("string_literal", "raw_string_literal", "concatenated_string"):
            return True
        return n.type == "identifier" and teks(n) in self.var_string

    def n_binary_expression(self, n, *_):
        op = n.child_by_field_name("operator")
        op = op.type if op is not None else ""
        if op in ("&&", "||", "and", "or"):
            self.catat("PERCABANGAN", n, op)
        elif op == "+" and (self.string(n.child_by_field_name("left")) or self.string(n.child_by_field_name("right"))):
            self.catat("STRING", n, "penyambungan string: " + teks(n))
        elif op in ARITMETIKA:
            self.catat("ARITMETIKA", n)
        elif op == "<<":
            kanan = n.child_by_field_name("right")
            if kanan is not None and kanan.type in ("identifier", "qualified_identifier") and nama_akhir(kanan) in MANIPULATOR:
                self.catat("FORMAT_OUTPUT", kanan)

    def n_unary_expression(self, n, *_):
        op = n.child_by_field_name("operator")
        if op is not None and op.type in ("!", "not"):
            self.catat("PERCABANGAN", n, "!")

    def n_update_expression(self, n, *_):
        self.catat("ARITMETIKA", n)

    def n_assignment_expression(self, n, *_):
        op = n.child_by_field_name("operator")
        if op is not None and op.type in ISI_ARITMETIKA:
            if op.type == "+=" and self.string(n.child_by_field_name("left")):
                self.catat("STRING", n, "penyambungan string: " + teks(n))
            else:
                self.catat("ARITMETIKA", n)

    def n_cast_expression(self, n, *_):
        self.catat("KONVERSI_TIPE", n)

    def n_new_expression(self, n, *_):
        self.catat("MEMORI_DINAMIS", n)

    def n_delete_expression(self, n, *_):
        self.catat("MEMORI_DINAMIS", n)

    def n_subscript_expression(self, n, *_):
        sasaran = n.child_by_field_name("argument")
        if self.string(sasaran):
            self.catat("STRING", n, "indeks pada string: " + teks(n))
        elif sasaran is not None and sasaran.type == "subscript_expression":
            self.catat("ARRAY_2D", n)
        else:
            self.catat("ARRAY_1D", n)

    def n_field_expression(self, n, *_):
        if punya_token(n, "->"):
            sasaran = n.child_by_field_name("argument")
            if sasaran is None or sasaran.type != "this":
                self.catat("POINTER", n)

    def n_pointer_expression(self, n, lingkar, fungsi, ptr_param):
        op = n.child_by_field_name("operator")
        sasaran = n.child_by_field_name("argument")
        if op is None:
            return
        if op.type == "*":
            if sasaran is not None and sasaran.type == "this":
                return
            if sasaran is not None and sasaran.type == "identifier" and teks(sasaran) in ptr_param:
                self.catat("PARAMETER_REF", n)
            else:
                self.catat("POINTER", n)
        elif op.type == "&":
            # &x sebagai argumen: cara membaca dengan scanf, atau cara mengisi parameter pointer.
            induk = n.parent
            if induk is not None and induk.type == "argument_list" and induk.parent is not None:
                dipanggil = nama_akhir(induk.parent.child_by_field_name("function"))
                if dipanggil in BACA_C:
                    return
                if self.param_pointer.get(dipanggil):
                    self.catat("PARAMETER_REF", n)
                    return
            self.catat("POINTER", n)

    def n_identifier(self, n, *_):
        if teks(n) in ("cin", "cout", "cerr", "clog"):
            self.catat("IO_DASAR", n)

    def n_call_expression(self, n, lingkar, fungsi, ptr_param):
        f = n.child_by_field_name("function")
        if f is None:
            return
        nama = nama_akhir(f)
        metode = f.type == "field_expression"
        if f.type == "primitive_type":
            self.catat("KONVERSI_TIPE", n)
        elif f.type == "template_function" and nama in CAST:
            self.catat("KONVERSI_TIPE", n)
        if metode:
            penerima = f.child_by_field_name("argument")
            if nama in METODE_STRING and self.string(penerima):
                self.catat("STRING", n)
            return
        if fungsi is not None and nama:
            self.panggil.setdefault(fungsi, set()).add(nama)
        if nama in IO:
            self.catat("IO_DASAR", n, nama)
        if nama in ("printf", "fprintf", "sprintf", "snprintf"):
            argumen = n.child_by_field_name("arguments")
            for a in (argumen.named_children if argumen is not None else []):
                if a.type in ("string_literal", "concatenated_string") and FORMAT_PRINTF.search(teks(a)):
                    self.catat("FORMAT_OUTPUT", a)
                    break
        if nama == "fprintf":
            argumen = n.child_by_field_name("arguments")
            pertama = argumen.named_children[0] if argumen is not None and argumen.named_children else None
            if teks(pertama) not in ("stdout", "stderr"):
                self.catat("FILE_IO", n, "fprintf")
        if nama in FUNGSI_STRING:
            self.catat("STRING", n, nama)
        elif nama in MANIPULATOR_PANGGIL:
            self.catat("FORMAT_OUTPUT", n)
        elif nama in MEMORI:
            self.catat("MEMORI_DINAMIS", n, nama)
        elif nama in BERKAS:
            self.catat("FILE_IO", n, nama)
        elif nama in ("make_unique", "make_shared"):
            self.catat("SMART_POINTER", n, nama)
        elif nama == "move" and f.type == "qualified_identifier":
            self.catat("COPY_SEMANTICS", n, "std::move")
        elif nama in ALGORITMA and nama not in self.fungsi_bebas:
            self.catat("STL_ALGORITMA", n, nama)


def analisis(bahasa: str, sumber: bytes) -> list[dict]:
    pohon = _parser(bahasa).parse(sumber)
    return Analis(bahasa, pohon.root_node).jalan()
