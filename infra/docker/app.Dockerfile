# Satu Dockerfile untuk ketiga aplikasi CI4. Konteks build = akar repositori,
# supaya paket bersama packages/lokandita-auth ikut tersalin.
# Dibangun hanya lewat infra/deploy.sh (yang mengisi APP dan GIT_COMMIT).

FROM composer:2.11.2 AS vendor
ARG APP
# infra/tes.sh mengosongkan nilai ini supaya PHPUnit ikut terpasang di image :tes.
ARG COMPOSER_DEV=--no-dev
COPY packages/lokandita-auth /srv/lokandita/packages/lokandita-auth
WORKDIR /srv/lokandita/apps/${APP}
COPY apps/${APP}/composer.json apps/${APP}/composer.lock ./
RUN composer install ${COMPOSER_DEV} --no-interaction --no-progress --no-scripts \
        --optimize-autoloader --ignore-platform-reqs

# Tailwind CSS (SPEC.md §2) dikompilasi di sini, di dalam build, dari berkas biner
# resmi yang versinya dipatok dan sha256-nya diperiksa. Tidak ada yang dijalankan
# di host dan tidak ada CDN. Menaikkan versi: ganti versi DAN sha256 (dari
# sha256sums.txt pada rilis GitHub tailwindlabs/tailwindcss).
FROM php:8.3.36-apache AS gaya
ARG APP
ADD --checksum=sha256:e47695cee3f0d90623a91f3939b08bdee610eb5ad99340adff9d4145b284d6cc --chmod=755 \
    https://github.com/tailwindlabs/tailwindcss/releases/download/v4.3.5/tailwindcss-linux-x64 /usr/local/bin/tailwindcss
COPY packages/lokandita-auth/gaya /srv/lokandita/packages/lokandita-auth/gaya
COPY packages/lokandita-auth/src/Views /srv/lokandita/packages/lokandita-auth/src/Views
COPY apps/${APP}/app/Views /srv/lokandita/apps/${APP}/app/Views
# Gagal bila hasilnya kosong atau masih berisi perintah Tailwind yang belum terkompilasi.
RUN mkdir /keluar \
 && tailwindcss --input /srv/lokandita/packages/lokandita-auth/gaya/lokandita.css --output /keluar/lokandita.css --minify \
 && grep -q 'kartu' /keluar/lokandita.css \
 && ! grep -q -e '@apply' -e '@source' -e '@import' /keluar/lokandita.css

FROM php:8.3.36-apache
RUN apt-get update \
 && apt-get install -y --no-install-recommends libicu-dev libzip-dev libpng-dev \
 && docker-php-ext-install -j"$(nproc)" intl mysqli pdo_mysql opcache zip gd \
 && pecl install redis-6.2.0 \
 && docker-php-ext-enable redis \
 && rm -rf /var/lib/apt/lists/* /tmp/pear \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && printf 'expose_php = Off\ndate.timezone = UTC\n' > "$PHP_INI_DIR/conf.d/lokandita.ini"

COPY infra/docker/apache-app.conf /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
 && printf 'ServerName localhost\nServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/zz-lokandita.conf \
 && a2enconf zz-lokandita >/dev/null

ARG APP
COPY apps/${APP} /srv/lokandita/apps/${APP}
COPY --from=vendor /srv/lokandita/apps/${APP}/vendor /srv/lokandita/apps/${APP}/vendor
# Aset bersama (CSS, JS) disajikan dari host aplikasi itu sendiri: tidak ada CDN.
COPY packages/lokandita-auth/aset /srv/lokandita/apps/${APP}/public/aset
COPY --from=gaya /keluar/lokandita.css /srv/lokandita/apps/${APP}/public/aset/lokandita.css
COPY infra/docker/mulai.sh /usr/local/bin/mulai.sh
RUN ln -s /srv/lokandita/apps/${APP} /srv/app \
 && cd /srv/app \
 && mkdir -p writable/cache writable/logs writable/session writable/uploads writable/debugbar \
 && chown -R www-data:www-data writable \
 && chmod 755 /usr/local/bin/mulai.sh

ARG GIT_COMMIT
ENV LOKANDITA_APP=${APP} \
    LOKANDITA_COMMIT=${GIT_COMMIT} \
    CI_ENVIRONMENT=production

USER www-data
WORKDIR /srv/app
EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/mulai.sh"]
