#!/usr/bin/env sh
#
# Deploy Roam to a homelab VPS over SSH.
#
# Usage:
#   ./deploy.sh user@host [path]
#
# Assumes the checkout on the VPS has a .env (see .env.production.example) and
# that the external `traefik` and `mysql-shared` Docker networks exist.
set -e

HOST="${1:?usage: ./deploy.sh user@host [path]}"
DIR="${2:-/opt/trevviq}"

echo "==> Deploying to ${HOST}:${DIR}"
ssh "${HOST}" "set -e; cd '${DIR}'; git pull --ff-only; docker compose -f docker-compose.prod.yml up -d --build; docker image prune -f; docker compose -f docker-compose.prod.yml ps"

echo "==> Done. Tail logs with: ssh ${HOST} 'cd ${DIR} && docker compose -f docker-compose.prod.yml logs -f app'"
