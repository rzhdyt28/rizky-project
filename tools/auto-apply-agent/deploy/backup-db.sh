#!/usr/bin/env bash
#
# =============================================================================
# BAB 11 — SCRIPT BACKUP DATABASE
# =============================================================================
# Backup data/app.db dengan timestamp, dan hapus backup yang lebih tua dari
# RETENTION_DAYS hari agar tidak memenuhi disk VPS yang terbatas.
#
# Cara pakai manual:
#   chmod +x deploy/backup-db.sh
#   ./deploy/backup-db.sh
#
# Cara jadwalkan via cron (jalan tiap hari jam 23:50):
#   crontab -e
#   50 23 * * * /path/ke/auto-apply-agent/deploy/backup-db.sh >> /path/ke/auto-apply-agent/data/backup/backup.log 2>&1
#
set -euo pipefail

RETENTION_DAYS="${RETENTION_DAYS:-14}"
DB_PATH="${DATABASE_PATH:-./data/app.db}"
BACKUP_DIR="./data/backup"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] [backup-db] $1"; }

if [[ ! -f "$DB_PATH" ]]; then
  log "Database tidak ditemukan di $DB_PATH — dilewati."
  exit 0
fi

mkdir -p "$BACKUP_DIR"

TIMESTAMP="$(date +%F_%H%M%S)"
BACKUP_FILE="${BACKUP_DIR}/app-${TIMESTAMP}.db"

cp "$DB_PATH" "$BACKUP_FILE"
log "Backup dibuat: $BACKUP_FILE"

log "Menghapus backup lebih tua dari ${RETENTION_DAYS} hari..."
find "$BACKUP_DIR" -name "app-*.db" -mtime "+${RETENTION_DAYS}" -print -delete | while read -r f; do
  log "Dihapus (kadaluwarsa): $f"
done

log "Selesai. Total backup saat ini: $(find "$BACKUP_DIR" -name 'app-*.db' | wc -l)"
