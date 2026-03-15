#!/usr/bin/env sh
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
cd "$ROOT_DIR"

if [ ! -f ".env.production" ]; then
  echo "Missing .env.production. Copy .env.production.sample and set real values first." >&2
  exit 1
fi

if [ "$#" -lt 1 ]; then
  echo "Usage: $0 <log-file> [--follow]" >&2
  exit 1
fi

LOG_FILE=$1
FOLLOW=${2:-}
mkdir -p "$(dirname -- "$LOG_FILE")"

if [ "$FOLLOW" = "--follow" ]; then
  docker compose -f compose.production.yaml --env-file .env.production \
    logs -f --timestamps --no-color nginx php_app mysql | tee -a "$LOG_FILE"
  exit 0
fi

docker compose -f compose.production.yaml --env-file .env.production \
  logs --timestamps --no-color nginx php_app mysql >> "$LOG_FILE"

echo "Logs appended to $LOG_FILE"
