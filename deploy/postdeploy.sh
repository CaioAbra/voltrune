#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-$HOME/voltrune}"
STATE_DIR="$APP_DIR/storage/app/deploy"
LAST_FILE="$STATE_DIR/last_commit"
LOCK_FILE="/tmp/voltrune_postdeploy.lock"
LOG_FILE="$APP_DIR/storage/logs/deploy.log"

mkdir -p "$STATE_DIR" "$(dirname "$LOG_FILE")"
touch "$LOG_FILE"

exec >>"$LOG_FILE" 2>&1

echo "-----"
echo "$(date '+%Y-%m-%d %H:%M:%S') start"

exec 9>"$LOCK_FILE"
flock -n 9 || {
  echo "locked, exit"
  exit 0
}

cd "$APP_DIR"

CURRENT_BRANCH="$(git symbolic-ref --quiet --short HEAD 2>/dev/null || true)"
UPSTREAM_REF=""

if [ -n "$CURRENT_BRANCH" ]; then
  UPSTREAM_REF="$(git rev-parse --abbrev-ref --symbolic-full-name '@{u}' 2>/dev/null || true)"
fi

if [ -n "$UPSTREAM_REF" ]; then
  echo "[git] fetch $UPSTREAM_REF"
  git fetch --prune --quiet

  LOCAL_BEFORE="$(git rev-parse HEAD)"
  REMOTE_HEAD="$(git rev-parse "$UPSTREAM_REF" 2>/dev/null || true)"

  if [ -n "$REMOTE_HEAD" ] && [ "$LOCAL_BEFORE" != "$REMOTE_HEAD" ]; then
    echo "[git] fast-forward $LOCAL_BEFORE -> $REMOTE_HEAD"
    git merge --ff-only "$UPSTREAM_REF"
  else
    echo "[git] already up to date ($LOCAL_BEFORE)"
  fi
else
  echo "[git] no upstream branch configured, skipping fetch"
fi

CUR="$(git rev-parse HEAD)"
PREV=""
[ -f "$LAST_FILE" ] && PREV="$(cat "$LAST_FILE" || true)"

if [ "$CUR" = "$PREV" ]; then
  echo "no changes ($CUR)"
  exit 0
fi

echo "new commit: ${PREV:-<none>} -> $CUR"

if [ -n "$PREV" ]; then
  CHANGED="$(git diff --name-only "$PREV" "$CUR" || true)"
else
  CHANGED="$(git show --name-only --pretty='' "$CUR" || true)"
fi

echo "changed files:"
if [ -n "$CHANGED" ]; then
  echo "$CHANGED"
else
  echo "<none detected>"
fi

changed_any() {
  local pattern="$1"
  printf '%s\n' "$CHANGED" | grep -E -q "$pattern"
}

has_file() {
  [ -e "$1" ]
}

ensure_node_toolchain() {
  if command -v npm >/dev/null 2>&1 && command -v node >/dev/null 2>&1; then
    return 0
  fi

  local nvm_dir="${NVM_DIR:-$HOME/.nvm}"

  if [ -s "$nvm_dir/nvm.sh" ]; then
    # Cron and non-login shells do not load the user's shell profile.
    # Load nvm explicitly so npm/node are available during deploy.
    # shellcheck disable=SC1090
    . "$nvm_dir/nvm.sh"
  fi

  if command -v npm >/dev/null 2>&1 && command -v node >/dev/null 2>&1; then
    return 0
  fi

  local current_node_bin
  current_node_bin="$(find "$nvm_dir/versions/node" -mindepth 3 -maxdepth 3 -type d -name bin 2>/dev/null | sort | tail -n 1 || true)"

  if [ -n "$current_node_bin" ]; then
    export PATH="$current_node_bin:$PATH"
  fi

  command -v npm >/dev/null 2>&1 && command -v node >/dev/null 2>&1
}

need_composer=0
need_migrate=0
need_config_cache=0
need_route_cache=0
need_view_cache=0
need_npm=0
need_build=0

if ! has_file "vendor/autoload.php"; then
  need_composer=1
fi

if ! has_file "public/build/manifest.json"; then
  need_build=1
fi

if ! has_file "node_modules"; then
  need_npm=1
fi

if changed_any '^(composer\.json|composer\.lock)$'; then
  need_composer=1
fi

if changed_any '^(database/migrations/)'; then
  need_migrate=1
fi

if changed_any '^(\.env|config/)'; then
  need_config_cache=1
fi

if changed_any '^(routes/)'; then
  need_route_cache=1
fi

if changed_any '^(resources/views/)'; then
  need_view_cache=1
fi

if changed_any '^(package\.json|package-lock\.json)$'; then
  need_npm=1
fi

if changed_any '^(package\.json|package-lock\.json|resources/(js|scss)/|vite\.config\.(js|ts)|postcss\.config\.(js|cjs)|tailwind\.config\.(js|cjs)|tsconfig\.json)'; then
  need_build=1
fi

if [ "$need_composer" -eq 1 ]; then
  echo "[composer] install"
  composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
else
  echo "[composer] skip install"
fi

if [ "$need_migrate" -eq 1 ]; then
  echo "[artisan] migrate --force"
  php artisan migrate --force
else
  echo "[artisan] skip migrate"
fi

if [ "$need_config_cache" -eq 1 ]; then
  echo "[artisan] config:clear && config:cache"
  php artisan config:clear
  php artisan config:cache
else
  echo "[artisan] skip config cache"
fi

if [ "$need_route_cache" -eq 1 ]; then
  echo "[artisan] route:clear && route:cache"
  php artisan route:clear
  php artisan route:cache
else
  echo "[artisan] skip route cache"
fi

if [ "$need_view_cache" -eq 1 ]; then
  echo "[artisan] view:clear && view:cache"
  php artisan view:clear
  php artisan view:cache
else
  echo "[artisan] skip view cache"
fi

if [ "$need_npm" -eq 1 ]; then
  if ! ensure_node_toolchain; then
    echo "[npm] unavailable: node/npm not found in PATH or nvm"
    exit 1
  fi
  echo "[npm] ci"
  npm ci --no-audit --no-fund
else
  echo "[npm] skip ci"
fi

if [ "$need_build" -eq 1 ]; then
  if ! ensure_node_toolchain; then
    echo "[npm] unavailable: node/npm not found in PATH or nvm"
    exit 1
  fi
  echo "[npm] run build"
  npm run build
else
  echo "[npm] skip build"
fi

echo "$CUR" > "$LAST_FILE"
echo "done, saved last_commit=$CUR"
echo "$(date '+%Y-%m-%d %H:%M:%S') end"
