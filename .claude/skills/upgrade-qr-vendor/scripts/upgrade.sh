#!/usr/bin/env bash
# Upgrade the vendored chillerlan/php-qrcode (and optionally settings-container).
# Downloads the release tag, replaces vendor sources, prunes the unused QR
# reader, refreshes licenses, and regenerates vendor/VERSIONS.
set -euo pipefail

QR_VERSION="${1:?Usage: upgrade.sh <php-qrcode-tag> [settings-container-tag]}"
SETTINGS_VERSION="${2:-}"

REPO_ROOT="$(git rev-parse --show-toplevel)"
VENDOR="$REPO_ROOT/src/encoding/vendor"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

fetch() { # fetch <repo> <tag> -> extracted dir path on stdout
    local repo="$1" tag="$2"
    curl -sfL "https://github.com/chillerlan/${repo}/archive/refs/tags/${tag}.tar.gz" -o "$TMP/${repo}.tgz" \
        || { echo "ERROR: tag '${tag}' not found for chillerlan/${repo}" >&2; exit 1; }
    tar xzf "$TMP/${repo}.tgz" -C "$TMP"
    echo "$TMP/${repo}-${tag}"
}

QR_SRC="$(fetch php-qrcode "$QR_VERSION")"

# The runtime container runs PHP 8.2 — newer library majors may require more.
PHP_REQ="$(python3 -c "import json; print(json.load(open('$QR_SRC/composer.json'))['require'].get('php', '?'))")"
echo "php-qrcode ${QR_VERSION} requires PHP: ${PHP_REQ}"
case "$PHP_REQ" in
    *8.3*|*8.4*|*8.5*|*9.*)
        echo "ERROR: requires a newer PHP than the container's 8.2. Pick an older tag" \
             "or upgrade the base image in Dockerfile first." >&2
        exit 1
        ;;
esac

SETTINGS_REQ="$(python3 -c "import json; print(json.load(open('$QR_SRC/composer.json'))['require'].get('chillerlan/php-settings-container', '?'))")"
echo "php-qrcode ${QR_VERSION} requires settings-container: ${SETTINGS_REQ}"

rm -rf "$VENDOR/php-qrcode/src"
cp -R "$QR_SRC/src" "$VENDOR/php-qrcode/src"
rm -rf "$VENDOR/php-qrcode/src/Decoder" "$VENDOR/php-qrcode/src/Detector"
rm -f "$VENDOR/php-qrcode"/LICENSE* "$VENDOR/php-qrcode/NOTICE"
cp "$QR_SRC"/LICENSE* "$VENDOR/php-qrcode/" 2>/dev/null || true
[ -f "$QR_SRC/NOTICE" ] && cp "$QR_SRC/NOTICE" "$VENDOR/php-qrcode/"

if [ -n "$SETTINGS_VERSION" ]; then
    SETTINGS_SRC="$(fetch php-settings-container "$SETTINGS_VERSION")"
    rm -rf "$VENDOR/settings-container/src"
    cp -R "$SETTINGS_SRC/src" "$VENDOR/settings-container/src"
    rm -f "$VENDOR/settings-container"/LICENSE*
    cp "$SETTINGS_SRC"/LICENSE* "$VENDOR/settings-container/" 2>/dev/null || true
else
    # Keep the currently vendored version; read it from VERSIONS for the rewrite
    SETTINGS_VERSION="$(awk '/php-settings-container/ {print $2; exit}' "$VENDOR/VERSIONS")"
    echo "settings-container not upgraded, keeping ${SETTINGS_VERSION}" \
         "(verify it satisfies '${SETTINGS_REQ}')"
fi

cat > "$VENDOR/VERSIONS" <<EOF
Vendored libraries (installed without Composer — see autoload.php)

chillerlan/php-qrcode ${QR_VERSION}
  https://github.com/chillerlan/php-qrcode/archive/refs/tags/${QR_VERSION}.tar.gz
  Copied: src/ (minus Decoder/ and Detector/ — the QR *reader*, unused here),
  LICENSE files, NOTICE (if present)

chillerlan/php-settings-container ${SETTINGS_VERSION}
  https://github.com/chillerlan/php-settings-container/archive/refs/tags/${SETTINGS_VERSION}.tar.gz
  Copied: src/, LICENSE

To upgrade: .claude/skills/upgrade-qr-vendor/scripts/upgrade.sh <tag> [settings-tag]
Then validate: .claude/skills/upgrade-qr-vendor/scripts/verify.sh
EOF

echo
echo "Vendored files updated. Review with: git -C '$REPO_ROOT' diff --stat src/encoding/vendor"
echo "Now run verify.sh before committing."
