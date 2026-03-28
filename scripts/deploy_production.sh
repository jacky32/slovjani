#!/usr/bin/env sh
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
cd "$ROOT_DIR"

if [ ! -f ".env.production" ]; then
  echo "Missing .env.production. Copy .env.production.sample and set real values first." >&2
  exit 1
fi

read_env_value() {
  key="$1"
  # Read the last matching key from .env.production, ignoring comments.
  sed -n "s/^${key}=//p" .env.production | tail -n 1
}

# Backward-compatible port mapping:
# - preferred: NGINX_HTTP_PORT
# - legacy aliases: NGINX_PORT / nginx_port
if [ "${NGINX_HTTP_PORT:-}" = "" ]; then
  if [ "${NGINX_PORT:-}" != "" ]; then
    NGINX_HTTP_PORT="$NGINX_PORT"
  elif [ "${nginx_port:-}" != "" ]; then
    NGINX_HTTP_PORT="$nginx_port"
  else
    nginx_port_from_file="$(read_env_value "NGINX_PORT")"
    if [ "$nginx_port_from_file" = "" ]; then
      nginx_port_from_file="$(read_env_value "nginx_port")"
    fi

    if [ "$nginx_port_from_file" != "" ]; then
      NGINX_HTTP_PORT="$nginx_port_from_file"
    fi
  fi

  if [ "${NGINX_HTTP_PORT:-}" != "" ]; then
    export NGINX_HTTP_PORT
    echo "Using NGINX_HTTP_PORT=$NGINX_HTTP_PORT"
  fi
fi

compose() {
  docker compose -f compose.production.yaml --env-file .env.production "$@"
}

wait_for_healthy_replicas() {
  target="$1"
  timeout_seconds="$2"
  started_at=$(date +%s)

  while :; do
    ids=$(compose ps -q slovjani || true)
    total=0
    healthy=0

    for id in $ids; do
      total=$((total + 1))
      status=$(docker inspect -f '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$id" 2>/dev/null || printf 'unknown')
      if [ "$status" = "healthy" ]; then
        healthy=$((healthy + 1))
      fi
    done

    if [ "$total" -ge "$target" ] && [ "$healthy" -ge "$target" ]; then
      return 0
    fi

    now=$(date +%s)
    if [ $((now - started_at)) -ge "$timeout_seconds" ]; then
      echo "Timed out waiting for $target healthy slovjani replicas (found $healthy healthy out of $total total)." >&2
      return 1
    fi

    sleep 3
  done
}

echo "Building production images..."
compose build --pull

existing_ids=$(compose ps -q slovjani || true)

if [ -z "$existing_ids" ]; then
  echo "No running app container found, performing cold start..."
  compose up -d --remove-orphans
else
  echo "Rolling deploy: starting a new app replica while current version keeps running..."
  compose up -d --no-deps --no-recreate --scale slovjani=2 slovjani

  wait_for_healthy_replicas 2 240

  newest_id=""
  newest_created=""
  current_ids=$(compose ps -q slovjani)

  for id in $current_ids; do
    created=$(docker inspect -f '{{.Created}}' "$id")
    if [ -z "$newest_created" ] || [ "$created" \> "$newest_created" ]; then
      newest_created="$created"
      newest_id="$id"
    fi
  done

  if [ -z "$newest_id" ]; then
    echo "Could not detect newest slovjani container, aborting." >&2
    exit 1
  fi

  for id in $current_ids; do
    if [ "$id" != "$newest_id" ]; then
      echo "Stopping old app container $id"
      docker rm -f "$id" >/dev/null
    fi
  done

  compose up -d --no-deps --scale slovjani=1 slovjani
  compose up -d --remove-orphans mysql nginx
fi

echo "Production stack status:"
compose ps

if [ "${LOG_FILE:-}" != "" ]; then
  mkdir -p "$(dirname -- "$LOG_FILE")"
  compose \
    logs --timestamps --no-color nginx slovjani mysql >> "$LOG_FILE"
  echo "Startup logs exported to $LOG_FILE"
fi

echo "Done. App should be available via NGINX_HTTP_PORT (default 80)."
