"""Mode perbandingan keluaran — SPEC.md §7."""

import pytest

from lokandita_judge.banding import sama


def test_exact():
    assert sama("1 2\n", "1 2\n", "exact")
    assert not sama("1 2 \n", "1 2\n", "exact")
    assert not sama("1 2", "1 2\n", "exact")


def test_trimmed_adalah_bawaan():
    assert sama("1 2  \n3\n\n\n", "1 2\n3")
    assert sama("1 2\r\n3\r\n", "1 2\n3\n")
    assert not sama("1  2\n", "1 2\n")      # spasi di tengah tetap berarti
    assert not sama("  1 2\n", "1 2\n")     # spasi di depan tetap berarti
    assert not sama("1 2\n\n3\n", "1 2\n3\n")  # baris kosong di tengah tetap berarti


def test_token():
    assert sama("1   2\n\n3", "1 2 3\n", "token")
    assert not sama("1 2", "1 2 3", "token")
    assert not sama("12 3", "1 23", "token")


def test_float():
    assert sama("3.1415927", "3.1415926", "float")
    assert sama("1e3 abc", "1000.0000001 abc", "float")
    assert sama("1000000.5", "1000000.0", "float", 1e-6)   # relatif untuk bilangan besar
    assert not sama("3.15", "3.14", "float")
    assert sama("3.15", "3.14", "float", 0.1)
    assert not sama("abc", "abd", "float")
    assert not sama("1 2", "1", "float")
    assert not sama("nan", "1", "float")
    assert not sama("inf", "1e308", "float")


def test_mode_tak_dikenal_ditolak():
    with pytest.raises(ValueError):
        sama("a", "a", "kira-kira")
