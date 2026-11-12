#!/usr/bin/env bash

set -Eeuo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
API_DIR="$ROOT_DIR/tiketku-api"
WEB_DIR="$ROOT_DIR/tiketku-web"
API_PID=""
WEB_PID=""

cleanup() {
    trap - EXIT INT TERM

    if [[ -n "$WEB_PID" ]] && kill -0 "$WEB_PID" 2>/dev/null; then
        kill "$WEB_PID" 2>/dev/null || true
    fi

    if [[ -n "$API_PID" ]] && kill -0 "$API_PID" 2>/dev/null; then
        kill "$API_PID" 2>/dev/null || true
    fi

    wait 2>/dev/null || true
}

trap cleanup EXIT INT TERM

command -v php >/dev/null 2>&1 || {
    echo "PHP tidak ditemukan. Install PHP terlebih dahulu." >&2
    exit 1
}

command -v node >/dev/null 2>&1 || {
    echo "Node.js tidak ditemukan. Install Node.js terlebih dahulu." >&2
    exit 1
}

[[ -f "$API_DIR/artisan" ]] || {
    echo "Folder API tidak ditemukan: $API_DIR" >&2
    exit 1
}

[[ -d "$API_DIR/vendor" ]] || {
    echo "Dependency API belum tersedia. Jalankan: cd tiketku-api && composer install" >&2
    exit 1
}

[[ -d "$WEB_DIR/node_modules" ]] || {
    echo "Dependency web belum tersedia. Jalankan: cd tiketku-web && npm install" >&2
    exit 1
}

echo "Menjalankan API di http://127.0.0.1:8000"
(
    cd "$API_DIR"
    php artisan serve --host=127.0.0.1 --port=8000
) &
API_PID=$!

echo "Menjalankan web di http://localhost:3000"
(
    cd "$WEB_DIR"
    npm start
) &
WEB_PID=$!

wait -n "$API_PID" "$WEB_PID"
STATUS=$?

if [[ "$STATUS" -ne 0 ]]; then
    echo "Salah satu service berhenti dengan status $STATUS." >&2
fi

exit "$STATUS"
