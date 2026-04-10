#!/usr/bin/env bash
set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/webaudit/current}"
BRANCH="${BRANCH:-main}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"
RUN_BUILD="${RUN_BUILD:-true}"
RUN_TESTS="${RUN_TESTS:-false}"

echo "[deploy] app dir: ${APP_DIR}"
cd "${APP_DIR}"

echo "[deploy] fetch + checkout ${BRANCH}"
git fetch --all --prune
git checkout "${BRANCH}"
git reset --hard "origin/${BRANCH}"

echo "[deploy] composer install"
"${COMPOSER_BIN}" install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

if [[ "${RUN_BUILD}" == "true" ]]; then
  if [[ -f package-lock.json ]]; then
    echo "[deploy] npm ci"
    "${NPM_BIN}" ci
  else
    echo "[deploy] npm install"
    "${NPM_BIN}" install
  fi

  echo "[deploy] npm run build"
  "${NPM_BIN}" run build
fi

if [[ "${RUN_TESTS}" == "true" ]]; then
  echo "[deploy] php artisan test"
  "${PHP_BIN}" artisan test
fi

if [[ "${RUN_MIGRATIONS}" == "true" ]]; then
  echo "[deploy] php artisan migrate --force"
  "${PHP_BIN}" artisan migrate --force
fi

echo "[deploy] php artisan storage:link"
"${PHP_BIN}" artisan storage:link || true

echo "[deploy] optimize caches"
"${PHP_BIN}" artisan optimize:clear
"${PHP_BIN}" artisan config:cache
"${PHP_BIN}" artisan route:cache
"${PHP_BIN}" artisan view:cache

echo "[deploy] restart queue workers"
"${PHP_BIN}" artisan queue:restart || true

echo "[deploy] completed"
