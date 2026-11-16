from __future__ import annotations

import json
import sys

from . import analisis


def main() -> int:
    if len(sys.argv) < 3:
        print("pemakaian: python3 -m analisis <c|cpp|python> <berkas> [berkas…]", file=sys.stderr)
        return 2
    bahasa = sys.argv[1]
    for berkas in sys.argv[2:]:
        hasil = {"file": berkas, "concepts": [], "error": None}
        try:
            with open(berkas, "rb") as f:
                hasil["concepts"] = analisis(bahasa, f.read())
        except ValueError as e:
            hasil["error"] = str(e)[:300]
        except RecursionError:
            hasil["error"] = "kode bersarang terlalu dalam untuk dianalisis"
        except OSError as e:
            hasil["error"] = f"berkas tidak terbaca: {type(e).__name__}"
        print(json.dumps(hasil, ensure_ascii=False), flush=True)
    return 0


if __name__ == "__main__":
    sys.exit(main())
