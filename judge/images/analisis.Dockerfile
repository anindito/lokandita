# Image judge-analisis (SPEC.md §9): analisis konsep atas kode sumber dengan
# parser sungguhan — tree-sitter untuk C dan C++, modul `ast` untuk Python.
# Kode yang dianalisis TIDAK pernah dijalankan di sini; ia hanya diurai.
# Kontainernya memakai batas yang sama dengan kontainer judge lain (tanpa
# jaringan, root hanya-baca, tanpa capability). Dibangun hanya lewat
# judge/bangun-image.sh.
FROM ubuntu:24.04@sha256:2198e4c65076e9cb0b3d695219df667c0e3b0072da64ca3c2648aca17dcecaf5 AS pelari
RUN apt-get update && apt-get install -y --no-install-recommends gcc-13 libc6-dev && rm -rf /var/lib/apt/lists/*
COPY pelari.c /tmp/pelari.c
RUN gcc-13 -O2 -Wall -Wextra -Werror -static -o /pelari /tmp/pelari.c

FROM python:3.12-slim-bookworm@sha256:935c3c5b12357a7e10caa9c1e6ca5692344d97cf229832ef584be2cec9fc03df
COPY requirements-analisis.txt /tmp/requirements.txt
RUN pip install --no-cache-dir --disable-pip-version-check --root-user-action=ignore \
        --only-binary=:all: --require-hashes -r /tmp/requirements.txt \
 && rm /tmp/requirements.txt
ENV LANG=C.UTF-8 HOME=/box TMPDIR=/box PYTHONDONTWRITEBYTECODE=1 PYTHONIOENCODING=utf-8 PYTHONPATH=/opt
COPY analisis /opt/analisis
COPY --from=pelari /pelari /usr/local/bin/pelari
RUN mkdir /box /src && chmod 0755 /usr/local/bin/pelari && chmod -R a+rX /opt/analisis \
 && chmod 0600 /etc/passwd /etc/group \
 && python3 -c "import tree_sitter, tree_sitter_c, tree_sitter_cpp"
WORKDIR /box
USER 65534:65534
