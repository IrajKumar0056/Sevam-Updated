#!/bin/bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd "$DIR"

# Ensure bin directory permissions
if [ -f "$DIR/bin/php" ]; then
    chmod +x "$DIR/bin/php" 2>/dev/null || true
fi

# Locate PHP binary
PHP_BIN=""
if [ -x "$DIR/bin/php" ]; then
    PHP_BIN="$DIR/bin/php"
elif command -v php8.2 >/dev/null 2>&1; then
    PHP_BIN="$(command -v php8.2)"
elif command -v php >/dev/null 2>&1; then
    PHP_BIN="$(command -v php)"
elif [ -x "/usr/bin/php" ]; then
    PHP_BIN="/usr/bin/php"
fi

# If PHP binary is still missing and we have root, try apt-get
if [ -z "$PHP_BIN" ] && [ "$(id -u)" -eq 0 ]; then
    echo "PHP not found. Installing PHP CLI..."
    DEBIAN_FRONTEND=noninteractive apt-get update -qq 2>/dev/null || true
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends php-cli php-sqlite3 2>/dev/null || true
    PHP_BIN="$(command -v php 2>/dev/null || echo '')"
fi

if [ -z "$PHP_BIN" ]; then
    echo "ERROR: PHP binary could not be found or executed."
    exit 1
fi

# Ensure SQLite database is pre-seeded
if [ ! -f "$DIR/database/sevam.sqlite" ] || [ ! -s "$DIR/database/sevam.sqlite" ]; then
    echo "Initializing database..."
    "$PHP_BIN" "$DIR/database/init_sqlite.php" 2>/dev/null || true
fi

# Try to start local MariaDB if running as root in dev container
if [ "$(id -u)" -eq 0 ] && command -v mariadbd >/dev/null 2>&1; then
    mkdir -p /run/mysqld /var/lib/mysql 2>/dev/null || true
    chown -R mysql:mysql /run/mysqld /var/lib/mysql 2>/dev/null || true
    if ! /etc/init.d/mariadb status > /dev/null 2>&1; then
        echo "Starting local MariaDB daemon..."
        /etc/init.d/mariadb start 2>/dev/null || service mariadb start 2>/dev/null || true
    fi
    if command -v mariadb >/dev/null 2>&1; then
        mariadb -e "CREATE DATABASE IF NOT EXISTS sevam CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>/dev/null || true
        mariadb sevam < "$DIR/database/sevam.sql" 2>/dev/null || true
    fi
fi

# Port selection (Cloud Run uses PORT env var, defaults to 3000 in dev)
PORT="${PORT:-3000}"

echo "Starting Sevam Server on port $PORT using $PHP_BIN..."
exec "$PHP_BIN" -S "0.0.0.0:$PORT" -t "$DIR" "$DIR/router.php"
