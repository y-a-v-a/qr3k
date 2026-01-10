---
name: qr3k-publisher
description: Encode game code and generate QR codes for QR3K games. Use when the user wants to (1) encode their game code to a QR-compatible URL, (2) generate a QR code image for a game, (3) test or share QR3K games, or (4) get both the game URL and downloadable QR code image.
---

# QR3K Publisher

Encode QR3K game code, generate shareable URLs, and fetch QR code images.

## Overview

This skill posts game code to the QR3K API endpoint and retrieves:
- An encoded game URL (playable via QR scan)
- A QR code image URL
- The actual QR code image file

## Quick Usage

When the user asks to encode a game or generate a QR code:

1. Read the game code file
2. POST to the API endpoint with JSON payload
3. Parse the response to extract URLs
4. Fetch the QR code image
5. Display results to the user

## Step-by-Step Process

### 1. Post Game Code to API

Use curl to POST game code as JSON:

```bash
curl -X POST https://www.vincentbruijn.nl/qr3k/api.php \
  -H "Content-Type: application/json" \
  -d '{"code": "GAME_CODE_HERE"}' \
  -s
```

For local development:

```bash
curl -X POST http://localhost/api.php \
  -H "Content-Type: application/json" \
  -d '{"code": "GAME_CODE_HERE"}' \
  -s
```

### 2. Parse API Response

The API returns JSON in this format:

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
  "warning": "Optional warning message if approaching limit"
}
```

Extract the `qrUrl` field from the response using jq or grep.

### 3. Fetch QR Code Image

Download the QR code image:

```bash
curl -o qr-code.png "QR_URL_FROM_RESPONSE"
```

The image is returned as PNG format, ready to display or share.

## Complete Example

Here's a complete workflow for encoding a game file:

```bash
# 1. Read game code from file
GAME_CODE=$(cat src/games/snake.js)

# 2. Escape for JSON
ESCAPED_CODE=$(echo "$GAME_CODE" | jq -Rs .)

# 3. Post to API and save response
RESPONSE=$(curl -X POST https://www.vincentbruijn.nl/qr3k/api.php \
  -H "Content-Type: application/json" \
  -d "{\"code\": $ESCAPED_CODE}" \
  -s)

# 4. Check if successful
SUCCESS=$(echo "$RESPONSE" | jq -r '.success')

if [ "$SUCCESS" = "true" ]; then
  # 5. Extract URLs
  GAME_URL=$(echo "$RESPONSE" | jq -r '.gameUrl')
  QR_URL=$(echo "$RESPONSE" | jq -r '.qrUrl')

  # 6. Display info
  echo "Game URL: $GAME_URL"
  echo "QR Code URL: $QR_URL"

  # 7. Check size warnings
  WARNING=$(echo "$RESPONSE" | jq -r '.warning // empty')
  if [ -n "$WARNING" ]; then
    echo "Warning: $WARNING"
  fi

  # 8. Fetch QR code image
  curl -o qr-code.png "$QR_URL"
  echo "QR code saved to qr-code.png"
else
  # Handle error
  ERROR=$(echo "$RESPONSE" | jq -r '.error')
  echo "Error: $ERROR"
fi
```

## Important Notes

### JSON Escaping

Game code often contains special characters (quotes, newlines, backslashes). Always escape properly:

- Use `jq -Rs .` to read file and output JSON-escaped string
- Or manually escape quotes and backslashes

### Size Limits

The QR code has a hard limit of 2,953 bytes (after base64 encoding):

- Check the `size.isOverLimit` field in the response
- If true, the QR code may not scan properly
- Consider the `warning` field for approaching limit notifications

### Error Handling

Always check `success` field before processing:

- `success: false` means encoding failed
- Check `error` field for details
- Common errors: empty code, invalid JSON, encoding failures

## Typical User Requests

**"Encode my game and generate a QR code"**
→ Read game file, POST to API, fetch QR image, show both URLs and save image

**"Create a QR code for snake.js"**
→ Same as above, specific to the snake.js file

**"I want to test my game on mobile"**
→ Generate QR code, suggest scanning with phone to test

**"Generate a shareable link for my game"**
→ POST to API, return the gameUrl (the ?x= URL)

## Display Format

When presenting results to the user, include:

1. Success confirmation
2. Game URL (for direct access)
3. QR code URL (for manual access)
4. Size information (bytes used / limit)
5. Warning if approaching or exceeding limit
6. Confirmation that QR image was saved locally

Example output:

```
✓ Successfully encoded your game!

Game URL: https://www.vincentbruijn.nl/qr3k/?x=ABC123...
QR Code URL: https://cdn.vincentbruijn.nl/qr/img.php?q=...

Size: 1,234 bytes (1,646 after encoding) - Well within the 2,953 byte limit

QR code image saved to: qr-code.png

Scan the QR code with your phone to play instantly!
```

## Local Development

For local testing with Docker:

```bash
# Use localhost instead of production URL
curl -X POST http://localhost/api.php \
  -H "Content-Type: application/json" \
  -d '{"code": "..."}'
```

Make sure the dev server is running (`npm run dev`).
