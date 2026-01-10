---
name: qr3k-publisher
description: Encode game code and generate QR codes for QR3K games. Use when the user wants to (1) encode their game code to a QR-compatible URL, (2) generate a QR code image for a game, (3) test or share QR3K games, or (4) get both the game URL and downloadable QR code image.
---

# QR3K Publisher

Encode QR3K game code and generate scannable QR codes.

## Quick Start

Use the complete workflow script:

```bash
./scripts/publish-game.sh <game-file> [output-file] [api-url]
```

This will:
- Encode the game via API
- Display the game URL and QR URL
- Download the QR code image
- Show size information and warnings

## Individual Scripts

### Encode Game

Post game code to API and get URLs:

```bash
./scripts/encode-game.sh <game-file> [api-url]
```

Returns JSON with `gameUrl`, `qrUrl`, and size information.

### Fetch QR Image

Download QR code image from URL:

```bash
./scripts/fetch-qr.sh <qr-url> [output-file]
```

## Examples

**Publish a game:**
```bash
./scripts/publish-game.sh src/games/snake.js
```

**Publish with custom output:**
```bash
./scripts/publish-game.sh game.html my-qr.png
```

**Local development:**
```bash
./scripts/publish-game.sh game.js qr.png http://localhost/api.php
```

**Just encode (no download):**
```bash
./scripts/encode-game.sh snake.js | jq -r '.gameUrl'
```

## API Response Format

```json
{
  "success": true,
  "gameUrl": "https://www.vincentbruijn.nl/qr3k/?x=...",
  "qrUrl": "https://cdn.vincentbruijn.nl/qr/img.php?q=...",
  "size": {
    "bytes": 1234,
    "base64Bytes": 1646,
    "limit": 2953,
    "isOverLimit": false
  },
  "warning": "Optional warning if approaching limit"
}
```

## Size Limits

- Hard limit: 2,953 bytes (after base64 encoding)
- Scripts automatically check and warn
- Games over limit may not scan properly

## Requirements

- `curl` - HTTP requests
- `jq` - JSON parsing
- `bash` or `zsh` - Script execution

## Typical Use Cases

**"Encode my game"** → Run `publish-game.sh` with the game file

**"Generate QR code for snake.js"** → Run `publish-game.sh src/games/snake.js`

**"I want to test on mobile"** → Generate QR, suggest scanning to test

**"Get shareable link"** → Run `encode-game.sh` and extract `gameUrl`
