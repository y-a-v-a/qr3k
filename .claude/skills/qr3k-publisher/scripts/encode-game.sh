#!/usr/bin/env bash
# Encode game code and post to QR3K API
# Usage: ./encode-game.sh <game-file> [api-url]

set -euo pipefail

GAME_FILE="${1:-}"
API_URL="${2:-https://www.vincentbruijn.nl/qr3k/api.php}"

if [ -z "$GAME_FILE" ]; then
    echo "Usage: $0 <game-file> [api-url]" >&2
    echo "" >&2
    echo "Examples:" >&2
    echo "  $0 snake.js" >&2
    echo "  $0 game.html http://localhost/api.php" >&2
    exit 1
fi

if [ ! -f "$GAME_FILE" ]; then
    echo "Error: File not found: $GAME_FILE" >&2
    exit 1
fi

# Check for required tools
if ! command -v jq &> /dev/null; then
    echo "Error: jq is required but not installed" >&2
    exit 1
fi

# Read and escape game code for JSON
GAME_CODE=$(jq -Rs . < "$GAME_FILE")

# Post to API
RESPONSE=$(curl -X POST "$API_URL" \
    -H "Content-Type: application/json" \
    -d "{\"code\": $GAME_CODE}" \
    -s -w "\n%{http_code}")

# Split response and HTTP code
HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
BODY=$(echo "$RESPONSE" | sed '$d')

# Check HTTP status
if [ "$HTTP_CODE" != "200" ]; then
    echo "Error: HTTP $HTTP_CODE" >&2
    echo "$BODY" | jq -r '.error // .' >&2
    exit 1
fi

# Check API success
SUCCESS=$(echo "$BODY" | jq -r '.success')
if [ "$SUCCESS" != "true" ]; then
    echo "Error: API returned failure" >&2
    echo "$BODY" | jq -r '.error // .' >&2
    exit 1
fi

# Output the full response
echo "$BODY" | jq .
