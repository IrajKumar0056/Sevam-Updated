#!/usr/bin/env bash
# ==============================================================================
# Sevam - Local Host Launcher (macOS / Linux / WSL / Git Bash)
# ==============================================================================

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd "$DIR"

echo "=========================================================="
echo "           SEVAM Food Donation Platform Launcher          "
echo "=========================================================="

# Check if Docker is available
if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
    echo "Docker detected! Would you like to start using Docker Compose?"
    echo "1) Start with Docker Compose (Recommended - Zero Setup)"
    echo "2) Start with Local PHP & MySQL"
    read -p "Select [1 or 2, default 1]: " choice
    choice=${choice:-1}
    if [ "$choice" = "1" ]; then
        echo "Starting Sevam containers via Docker Compose..."
        docker compose up -d --build
        echo ""
        echo "----------------------------------------------------------"
        echo " Sevam is now live at: http://localhost:8000"
        echo " Admin Login:    admin / admin123"
        echo " Provider Login: annapurna_kitchen / provider123"
        echo " NGO Login:      hope_foundation / group123"
        echo "----------------------------------------------------------"
        echo "To stop the site: docker compose down"
        exit 0
    fi
fi

# Fallback: Local PHP + MySQL CLI
if ! command -v php >/dev/null 2>&1; then
    echo "ERROR: PHP is not installed on this system."
    echo "Please install PHP (>= 8.0) with pdo_mysql extension or install Docker Desktop."
    exit 1
fi

PORT="${PORT:-8000}"
export DB_HOST="${DB_HOST:-127.0.0.1}"
export DB_PORT="${DB_PORT:-3306}"
export DB_NAME="${DB_NAME:-sevam}"
export DB_USER="${DB_USER:-root}"
export DB_PASS="${DB_PASS:-}"

echo ""
echo "Connecting to MySQL at $DB_HOST:$DB_PORT with user '$DB_USER'..."

# Auto-check and import database if mysql CLI is available
if command -v mysql >/dev/null 2>&1; then
    PASS_FLAG=""
    if [ -n "$DB_PASS" ]; then
        PASS_FLAG="-p$DB_PASS"
    fi
    echo "Ensuring database '$DB_NAME' exists and schema is loaded..."
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" $PASS_FLAG -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>/dev/null || true
    mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" $PASS_FLAG "$DB_NAME" < "$DIR/database/sevam.sql" 2>/dev/null || true
fi

echo ""
echo "Starting PHP built-in web server on http://localhost:$PORT..."
echo "Press Ctrl+C to terminate."
echo ""
php -S "0.0.0.0:$PORT" -t "$DIR" "$DIR/router.php"
