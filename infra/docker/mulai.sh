#!/bin/sh
# Entrypoint kontainer aplikasi. Menolak mulai bila ada variabel wajib yang
# belum diisi, dan menyebut namanya (KONTRAK.md §10).
set -e
php /srv/app/vendor/lokandita/auth/bin/cek-env.php /srv/app/.env.example
exec apache2-foreground
