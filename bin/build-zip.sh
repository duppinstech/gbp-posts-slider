#!/usr/bin/env bash
# Build gbp-posts-slider.zip from tracked files only, excluding development files.
# Used by .github/workflows/release.yml; run locally with `bash bin/build-zip.sh` to inspect the package.
set -euo pipefail

cd "$(dirname "$0")/.."
rm -rf build gbp-posts-slider.zip
mkdir -p build/gbp-posts-slider

# Tracked files only (never local settings or untracked scratch files), minus dev-only paths.
git ls-files -z \
  | grep -zvE '^(\.github/|\.claude/|tests/|bin/|docs/|\.gitignore$|\.gitattributes$|AGENTS\.md$|CLAUDE\.md$|CLAUDE\.local\.md\.example$|OWNERSHIP\.md$|CONTRIBUTING\.md$|INTENT\.md$|PLAN\.md$)' \
  | xargs -0 -I{} cp --parents {} build/gbp-posts-slider/

( cd build && zip -qr9 ../gbp-posts-slider.zip gbp-posts-slider )
unzip -l gbp-posts-slider.zip
