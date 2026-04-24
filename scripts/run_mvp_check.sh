#!/usr/bin/env bash
set -euo pipefail

echo "[1/6] Frontend dependencies"
cd frontend
npm ci

echo "[2/6] Frontend smoke/lint/build"
npm run smoke
npm run lint
npm run build

cd ../backend
echo "[3/6] Backend dependencies"
composer install --no-interaction --prefer-dist || true

echo "[4/6] Prepare env"
cp -n .env.example .env || true
php artisan key:generate --force

echo "[5/6] Migrate + seed"
php artisan migrate:fresh --seed

echo "[6/6] Run tests"
php artisan test
