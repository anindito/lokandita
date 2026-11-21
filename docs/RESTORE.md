# Backup and restore

## What is backed up

`infra/backup.sh` runs every day at 02:00 WIB from the crontab of user `deploy` and writes

```
/srv/lokandita-data/backups/lokandita-YYYYmmdd-HHMMSS/     (time in UTC)
├── db.sql.gz      mysqldump of identity, lokakode, portal (one consistent snapshot)
├── env.tar.gz     every component's .env, including INSTANCE_SECRET and SEB_ENCRYPTION_KEY
├── media.tar.gz   the Portal's uploaded hero photos (Docker volume lokandita_portal_media, KONTRAK.md §16)
└── SHA256SUMS
```

Folders older than `BACKUP_RETENTION_DAYS` (14) are deleted. Output of each run is appended to `/srv/lokandita-data/backups/backup.log`.

Not included: the Portal's default hero photo (it is part of the image, built from `docs/branding/foto/hero-portal.jpg`), `*_test` schemas, Redis (it only carries job IDs; the database is the source of truth), and Caddy's certificates (they are issued again automatically).

**Limits you should know.** The backups are not encrypted and sit on the same disk as the database. If the disk or the server is lost, the backups are lost with it. Copy the folder elsewhere if that matters.

## Restore test (safe, run it any time)

```
infra/uji-restore.sh                 # newest backup
infra/uji-restore.sh <backup folder>
```

It verifies the checksums, loads the dump into a temporary MySQL container that has no network, compares the list of schemas and tables with the live database, and removes the container. It then unpacks `media.tar.gz` into a temporary directory and checks that it holds only server-generated photo files, that every photo named in the restored `portal.foto` table is complete there (12 files each), that the published photo is among them, and that every file equals the one in the live volume. It never writes to the live database or the live volume. Run it after each stage that adds tables. A backup made before the photos were included has no `media.tar.gz` and is refused; make a new backup first.

## Real restore

This overwrites live data. Do it only on purpose.

1. Put every host in maintenance: `infra/pemeliharaan.sh auth on`, then `lokakode`, then `portal`.
2. Take a fresh backup of the current state first: `infra/backup.sh`.
3. Check the backup you want to restore: `cd <backup folder> && sha256sum -c SHA256SUMS`.
4. If the `.env` files are lost too, restore them: `tar -C /srv/lokandita -xzf <backup folder>/env.tar.gz`, then `infra/cek-env.sh`.
5. On a new server only: `infra/deploy.sh infra` to create MySQL, Redis, Caddy, schemas and users.
6. Load the dump (it drops and recreates the tables it contains):
   ```
   zcat <backup folder>/db.sql.gz | docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot'
   ```
7. `infra/deploy.sh auth`, `lokakode`, `portal`, then `infra/smoke.sh`.
8. Restore the Portal's uploaded photos into the volume of the running `portal` container (after step 7, so that the container and its volume exist). Files that are already there are overwritten with the same content; nothing is deleted:
   ```
   docker exec -i -u www-data portal tar -C /srv/media -xzf - < <backup folder>/media.tar.gz
   ```
   Then open `https://lokandita.com/` and `/admin/teks/foto`. If a published photo's files are missing the landing page shows the default photo and the Portal log has a line `foto.berkas_hilang`.
9. Open the hosts again with `infra/pemeliharaan.sh <app> off`.
