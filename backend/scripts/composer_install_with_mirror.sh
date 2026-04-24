#!/usr/bin/env bash
set -euo pipefail

# Optional mirror for restricted environments
# Example:
#   GITHUB_MIRROR_URL=https://ghproxy.com/https://github.com ./backend/scripts/composer_install_with_mirror.sh
if [[ -n "${GITHUB_MIRROR_URL:-}" ]]; then
  git config --global url."${GITHUB_MIRROR_URL}/".insteadOf https://github.com/
  git config --global url."${GITHUB_MIRROR_URL}/".insteadOf git@github.com:
fi

composer clear-cache || true
composer install --no-interaction --prefer-dist --no-progress
