#!/usr/bin/env bash
# Memberi `judge_worker` hak pada tabel yang disebut KONTRAK.md §6, dan tidak
# lebih: worker tidak boleh membaca kelas, pendaftaran, log audit atau impor.
#   judge_jobs         SELECT, UPDATE   (mengklaim pekerjaan, menulis hasil)
#   problem_versions   SELECT           (generator dan solusi referensi untuk GENERATE)
#   problem_instances  SELECT, INSERT   (menyimpan instans; tidak bisa mengubah atau menghapus) Dijalankan sebagai root MySQL setelah migrasi LokaKode,
# karena lokakode_app tidak berhak memberi hak. Aman dijalankan berulang kali.
#
#   infra/mysql-hak-judge.sh [lokakode|lokakode_test]     (bawaan: lokakode)
source "$(dirname "${BASH_SOURCE[0]}")/lib.sh"

skema="${1:-lokakode}"
case "$skema" in lokakode | lokakode_test) ;; *) gagal "skema harus lokakode atau lokakode_test" ;; esac

docker exec -i mysql sh -c 'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" exec mysql -uroot' <<SQL || gagal "mysql-hak-judge: pemberian hak pada $skema ditolak"
GRANT SELECT, UPDATE ON \`$skema\`.\`judge_jobs\` TO 'judge_worker'@'%';
GRANT SELECT ON \`$skema\`.\`problem_versions\` TO 'judge_worker'@'%';
GRANT SELECT, INSERT ON \`$skema\`.\`problem_instances\` TO 'judge_worker'@'%';
SQL
echo "mysql-hak-judge: judge_worker di $skema: judge_jobs (SELECT, UPDATE), problem_versions (SELECT), problem_instances (SELECT, INSERT)"
