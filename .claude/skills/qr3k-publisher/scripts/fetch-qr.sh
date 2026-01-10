#!/usr/bin/env bash
# Fetch QR code image from URL
# Usage: ./fetch-qr.sh <qr-url> [output-file]

set -euo pipefail

QR_URL="${1:-}"
OUTPUT_FILE="${2:-qr-code.png}"

if [ -z "$QR_URL" ]; then
    echo "Usage: $0 <qr-url> [output-file]" >&2
    echo "" >&2
    echo "Examples:" >&2
    echo "  $0 'https://cdn.vincentbruijn.nl/qr/img.php?q=...'" >&2
    echo "  $0 'https://cdn.vincentbruijn.nl/qr/img.php?q=...' my-game-qr.png" >&2
    exit 1
fi

# Fetch QR code image
if curl -f -o "$OUTPUT_FILE" "$QR_URL" -s; then
    echo "QR code saved to: $OUTPUT_FILE"

    # Show file info if file command exists
    if command -v file &> /dev/null; then
        file "$OUTPUT_FILE"
    fi
else
    echo "Error: Failed to download QR code from $QR_URL" >&2
    exit 1
fi
