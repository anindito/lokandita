# Backup and restore

## What is backed up

`infra/backup.sh` runs every day at 02:00 WIB from the crontab of user `deploy` and writes

```
/srv/lokandita-data/backups/lokandita-YYYYmmdd-HHMMSS/     (time in UTC)
├── db.sql.gz      mysqldump of identity, lokakode, portal (one consistent snapshot)
├── env.tar.gz     every component's .env, including INSTANCE_SECRET and SEB_ENCRYPTION_KEY
└── SHA256SUMS
```

Folders older than `BACKUP_RETENTION_DAYS` (14) are deleted. Output of each run is appended to `/srv/lokandita-data/backups/backup.log`.

Not included: `*_test` schemas, Redis (it only carries job IDs; the database is the source of truth), and Caddy's certificates (they are issued again automatically).

**Limits you should know.** The backups are not encrypted and sit on the same disk as the database. If the disk or the server is lost, the backups are lost with it. Copy the folder elsewhere if that matters.

## Restore test (safe, run it any time)

```
infra/uji-restore.sh                 # newest backup
infra/uji-restore.sh <backup folder>
```

It verifies the checksums, loads the dump into a temporary MySQL container that has no network, compares the list of schemas and tables with the live database, and removes the container. It never writes to the live database. Run it after each stage that adds tables.

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
8. Open the hosts again with `infra/pemeliharaan.sh <app> off`.
