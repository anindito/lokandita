# Image judge-c dan judge-cpp (SPEC.md §10): GCC 13 di atas Ubuntu 24.04.
# Image dasar dikunci dengan digest. Dibangun hanya lewat judge/bangun-image.sh.
#
#   --build-arg BAHASA=c     -> gcc-13 saja
#   --build-arg BAHASA=cpp   -> gcc-13 dan g++-13
FROM ubuntu:24.04@sha256:2198e4c65076e9cb0b3d695219df667c0e3b0072da64ca3c2648aca17dcecaf5 AS dasar
ARG BAHASA
RUN test "$BAHASA" = c -o "$BAHASA" = cpp \
 && apt-get update \
 && apt-get install -y --no-install-recommends gcc-13 libc6-dev $( [ "$BAHASA" = cpp ] && echo g++-13 ) \
 && ln -s /usr/bin/gcc-13 /usr/local/bin/gcc \
 && ln -s /usr/bin/gcc-13 /usr/local/bin/cc \
 && if [ "$BAHASA" = cpp ]; then ln -s /usr/bin/g++-13 /usr/local/bin/g++; fi \
 && rm -rf /var/lib/apt/lists/* \
 && gcc --version | head -n 1 | grep -q ' 13\.'

# pelari dikompilasi statis di sini supaya sama persis di ketiga image.
FROM dasar AS pelari
COPY pelari.c /tmp/pelari.c
RUN gcc -O2 -Wall -Wextra -Werror -static -o /pelari /tmp/pelari.c

FROM dasar
COPY --from=pelari /pelari /usr/local/bin/pelari
COPY kompilasi /usr/local/bin/kompilasi
# /box dan /src adalah titik kait: tmpfs dan direktori job (hanya-baca).
RUN mkdir /box /src && chmod 0755 /usr/local/bin/pelari /usr/local/bin/kompilasi
ENV LANG=C.UTF-8 HOME=/box TMPDIR=/box
WORKDIR /box
USER 65534:65534
