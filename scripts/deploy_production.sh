#!/usr/bin/env sh
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
cd "$ROOT_DIR"

if [ ! -f ".env.production" ]; then
  echo "Missing .env.production. Copy .env.production.sample and set real values first." >&2
  exit 1
fi

echo "Building and starting production services..."
docker compose -f compose.production.yaml --env-file .env.production build --pull

docker compose -f compose.production.yaml --env-file .env.production up -d --remove-orphans

echo "Production stack status:"
docker compose -f compose.production.yaml --env-file .env.production ps

echo "Done. App should be available on APP_PORT (default 3000)."
