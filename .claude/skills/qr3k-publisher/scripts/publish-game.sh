#!/usr/bin/env bash
# Complete workflow: Encode game and fetch QR code
# Usage: ./publish-game.sh <game-file> [output-file] [api-url]

set -euo pipefail

GAME_FILE="${1:-}"
OUTPUT_FILE="${2:-qr-code.png}"
API_URL="${3:-https://www.vincentbruijn.nl/qr3k/api.php}"

if [ -z "$GAME_FILE" ]; then
    echo "Usage: $0 <game-file> [output-file] [api-url]" >&2
    echo "" >&2
    echo "Examples:" >&2
    echo "  $0 snake.js" >&2
    echo "  $0 game.html my-qr.png" >&2
    echo "  $0 game.js qr.png http://localhost/api.php" >&2
    exit 1
fi

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Step 1: Encode game
echo "Encoding game: $GAME_FILE"
RESPONSE=$("$SCRIPT_DIR/encode-game.sh" "$GAME_FILE" "$API_URL")

# Step 2: Extract URLs
GAME_URL=$(echo "$RESPONSE" | jq -r '.gameUrl')
QR_URL=$(echo "$RESPONSE" | jq -r '.qrUrl')
BYTES=$(echo "$RESPONSE" | jq -r '.size.bytes')
BASE64_BYTES=$(echo "$RESPONSE" | jq -r '.size.base64Bytes')
IS_OVER_LIMIT=$(echo "$RESPONSE" | jq -r '.size.isOverLimit')
WARNING=$(echo "$RESPONSE" | jq -r '.warning // empty')

# Step 3: Display results
echo ""
echo "✓ Successfully encoded your game!"
echo ""
echo "Game URL: $GAME_URL"
echo "QR Code URL: $QR_URL"
echo ""
echo "Size: $BYTES bytes ($BASE64_BYTES after encoding)"

if [ "$IS_OVER_LIMIT" = "true" ]; then
    echo "⚠️  WARNING: $WARNING" >&2
elif [ -n "$WARNING" ]; then
    echo "⚠️  $WARNING"
else
    echo "✓ Well within the 2,953 byte limit"
fi

# Step 4: Fetch QR code
echo ""
echo "Downloading QR code..."
"$SCRIPT_DIR/fetch-qr.sh" "$QR_URL" "$OUTPUT_FILE"

echo ""
echo "🎮 Scan the QR code with your phone to play instantly!"
