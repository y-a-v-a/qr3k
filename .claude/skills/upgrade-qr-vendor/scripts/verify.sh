#!/usr/bin/env bash
# Validate the vendored QR library end-to-end against a running dev container:
# syntax-checks every vendored file, exercises qr.php's guards, and proves the
# generated PNGs decode back to the exact input URL (jsQR round-trip).
set -euo pipefail

BASE_URL="${1:-http://localhost:8081}"
REPO_ROOT="$(git rev-parse --show-toplevel)"
GAME_URL='https://www.vincentbruijn.nl/qr3k/?z=H4sIAAAAAAAAA0vOzy0oSi0uTk0BAEHbmPcNAAAA'
CONTAINER=qr3k-runtime

pass=0
step() { echo "--- $1"; }
ok() { echo "    OK: $1"; pass=$((pass+1)); }
die() { echo "    FAIL: $1" >&2; exit 1; }

step "PHP syntax check on every vendored file (in container)"
docker exec "$CONTAINER" sh -c \
    'find /var/www/encoding/vendor -name "*.php" -exec php -l {} \; | grep -v "No syntax errors" || true' \
    | grep . && die "syntax errors found" || ok "all vendored files lint clean"

step "Endpoint is up and serves SVG"
CT=$(curl -sfG "$BASE_URL/qr.php" --data-urlencode "d=$GAME_URL" -o /dev/null -w '%{content_type}') \
    || die "SVG request failed"
[ "$CT" = "image/svg+xml" ] || die "unexpected content type: $CT"
ok "SVG renders"

step "Guards still reject bad input"
[ "$(curl -sG "$BASE_URL/qr.php" --data-urlencode "d=https://evil.example/" -o /dev/null -w '%{http_code}')" = 400 ] \
    || die "foreign URL was not rejected"
[ "$(curl -sG "$BASE_URL/qr.php" --data-urlencode "d=$GAME_URL" --data-urlencode "fg=ffff00" --data-urlencode "bg=ffffff" -o /dev/null -w '%{http_code}')" = 400 ] \
    || die "low-contrast colors were not rejected"
ok "URL allowlist and contrast guard intact"

step "PNG round-trip: rendered QR decodes to the exact input URL"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT
cp "$(dirname "$0")/decode-qr.js" "$WORK/"
(cd "$WORK" && npm init -y >/dev/null 2>&1 && npm install --no-fund --no-audit jsqr pngjs >/dev/null 2>&1)

roundtrip() { # roundtrip <label> <url> [extra curl args...]
    local label="$1" url="$2"; shift 2
    curl -sfG "$BASE_URL/qr.php" --data-urlencode "d=$url" --data-urlencode "format=png" "$@" \
        -o "$WORK/t.png" || die "$label: PNG request failed"
    local decoded
    decoded="$(node "$WORK/decode-qr.js" "$WORK/t.png")" || die "$label: QR did not decode"
    [ "$decoded" = "$url" ] || die "$label: decoded data differs from input"
    ok "$label"
}

roundtrip "default colors" "$GAME_URL"
roundtrip "custom colors (magenta on grey)" "$GAME_URL" --data-urlencode "fg=ff00ff" --data-urlencode "bg=f0f0f0"
BIG_URL="https://www.vincentbruijn.nl/qr3k/?z=$(python3 -c 'print("Ab3"*950)')"
roundtrip "near-limit URL (version 40, $(echo -n "$BIG_URL" | wc -c | tr -d ' ') bytes)" "$BIG_URL"

step "Project test suite"
(cd "$REPO_ROOT" && npm test >/dev/null 2>&1) || die "npm test failed"
ok "npm test passes"

echo
echo "All $pass checks passed. Safe to commit the vendor upgrade."
