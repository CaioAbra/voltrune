#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$HOME/voltrune"
BRANCH="main"
STATE_DIR="$APP_DIR/storage/app/deploy"
LAST_FILE="$STATE_DIR/last_commit"
LOCK="/tmp/voltrune_postdeploy.lock"

mkdir -p "$STATE_DIR"

exec 9>"$LOCK"
flock -n 9 || exit 0

cd "$APP_DIR"

# commit atual no servidor
CUR="$(git rev-parse HEAD)"

# commit anterior processado
PREV=""
[ -f "$LAST_FILE" ] && PREV="$(cat "$LAST_FILE" || true)"

# não mudou? não faz nada
[ "$CUR" = "$PREV" ] && exit 0

# ---- mudou: roda pós-deploy ----

# caches (produção)
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# build frontend (precisa do manifest.json)
npm ci --no-audit --no-fund
npm run build

# marca que já processou esse commit
echo "$CUR" > "$LAST_FILE"