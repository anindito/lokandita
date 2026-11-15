# Image judge-python (SPEC.md §10): Python 3.12 dengan NumPy dan Matplotlib
# yang dikunci. Dibangun hanya lewat judge/bangun-image.sh.
FROM ubuntu:24.04@sha256:2198e4c65076e9cb0b3d695219df667c0e3b0072da64ca3c2648aca17dcecaf5 AS pelari
RUN apt-get update && apt-get install -y --no-install-recommends gcc-13 libc6-dev && rm -rf /var/lib/apt/lists/*
COPY pelari.c /tmp/pelari.c
RUN gcc-13 -O2 -Wall -Wextra -Werror -static -o /pelari /tmp/pelari.c

FROM python:3.12-slim-bookworm@sha256:935c3c5b12357a7e10caa9c1e6ca5692344d97cf229832ef584be2cec9fc03df
COPY requirements-python.txt /tmp/requirements.txt
RUN pip install --no-cache-dir --disable-pip-version-check --root-user-action=ignore \
        --only-binary=:all: --require-hashes -r /tmp/requirements.txt \
 && rm /tmp/requirements.txt
ENV LANG=C.UTF-8 HOME=/box TMPDIR=/box MPLBACKEND=Agg MPLCONFIGDIR=/box/.mpl \
    PYTHONDONTWRITEBYTECODE=1 PYTHONIOENCODING=utf-8 \
    OPENBLAS_NUM_THREADS=1 OMP_NUM_THREADS=1
# Cache font Matplotlib dibuat sekali di sini (lihat jalankan-python).
RUN mkdir /box /src /opt/mpl \
 && MPLCONFIGDIR=/opt/mpl python3 -c "import matplotlib.pyplot as p; p.figure(); p.title('x'); p.savefig('/tmp/x.png')" \
 && rm /tmp/x.png && chmod -R a+rX /opt/mpl && ls /opt/mpl | grep -q fontlist
COPY --from=pelari /pelari /usr/local/bin/pelari
COPY jalankan-python periksa-python /usr/local/bin/
# Daftar akun image tidak perlu terbaca oleh program yang dinilai (SPEC.md §10).
RUN chmod 0755 /usr/local/bin/pelari /usr/local/bin/jalankan-python /usr/local/bin/periksa-python \
 && chmod 0600 /etc/passwd /etc/group
WORKDIR /box
USER 65534:65534
